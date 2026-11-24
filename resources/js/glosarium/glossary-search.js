/**
 * Normalize raw search text for consistent cache keys and matching.
 *
 * @param {unknown} raw
 * @returns {string}
 */
export function normalizeSearchQuery(raw) {
    if (typeof raw !== "string") {
        return "";
    }

    return raw.trim().replace(/\s+/g, " ").toLowerCase();
}

/**
 * Split a search query into unique non-empty tokens across spaces and hyphens.
 *
 * @param {unknown} raw
 * @returns {string[]}
 */
export function tokenizeSearchQuery(raw) {
    const normalized = normalizeSearchQuery(raw);
    if (!normalized) {
        return [];
    }

    const tokens = normalized.split(/[\s-]+/).filter(Boolean);
    return Array.from(new Set(tokens));
}

/**
 * Split a title into non-overlapping highlight segments matching any query token
 * (case-insensitive, space/hyphen aware, regex-free).
 *
 * @param {string} text
 * @param {string} rawQuery
 * @returns {Array<{ text: string, match: boolean }>}
 */
export function buildHighlightSegments(text, rawQuery) {
    if (typeof text !== "string" || text.length === 0) {
        return [];
    }

    const tokens = tokenizeSearchQuery(rawQuery);
    if (tokens.length === 0) {
        return [{ text, match: false }];
    }

    const lowerText = text.toLowerCase();
    if (lowerText.length !== text.length) {
        return [{ text, match: false }];
    }

    const ranges = [];
    for (const token of tokens) {
        let searchFrom = 0;
        while (searchFrom < lowerText.length) {
            const idx = lowerText.indexOf(token, searchFrom);
            if (idx === -1) {
                break;
            }
            ranges.push([idx, idx + token.length]);
            searchFrom = idx + Math.max(1, token.length);
        }
    }

    if (ranges.length === 0) {
        return [{ text, match: false }];
    }

    ranges.sort((a, b) => a[0] - b[0] || a[1] - b[1]);

    const merged = [ranges[0]];
    for (let i = 1; i < ranges.length; i++) {
        const prev = merged[merged.length - 1];
        const curr = ranges[i];
        if (curr[0] <= prev[1]) {
            prev[1] = Math.max(prev[1], curr[1]);
        } else {
            merged.push(curr);
        }
    }

    const segments = [];
    let cursor = 0;
    for (const [start, end] of merged) {
        if (start > cursor) {
            segments.push({ text: text.slice(cursor, start), match: false });
        }
        segments.push({ text: text.slice(start, end), match: true });
        cursor = end;
    }

    if (cursor < text.length) {
        segments.push({ text: text.slice(cursor), match: false });
    }

    return segments;
}

/**
 * Compute the next highlighted option index for keyboard listbox navigation.
 *
 * @param {number} currentIndex
 * @param {number} totalOptions
 * @param {"down"|"up"|"home"|"end"} direction
 * @returns {number}
 */
export function computeNextOptionIndex(currentIndex, totalOptions, direction) {
    if (!Number.isInteger(totalOptions) || totalOptions <= 0) {
        return -1;
    }

    if (direction === "home") {
        return 0;
    }

    if (direction === "end") {
        return totalOptions - 1;
    }

    if (direction === "down") {
        if (currentIndex < 0 || currentIndex >= totalOptions - 1) {
            return 0;
        }
        return currentIndex + 1;
    }

    if (direction === "up") {
        if (currentIndex <= 0) {
            return totalOptions - 1;
        }
        return currentIndex - 1;
    }

    return currentIndex;
}

/**
 * Safely interpolate named :placeholders without regex replacement side effects.
 *
 * @param {string} template
 * @param {Record<string, string | number>} replacements
 * @returns {string}
 */
export function formatLabelTemplate(template, replacements) {
    let result = typeof template === "string" ? template : "";
    for (const [key, value] of Object.entries(replacements || {})) {
        const token = `:${key}`;
        const safeValue = String(value ?? "");
        result = result.split(token).join(safeValue);
    }
    return result;
}

function sanitizeNavigationUrl(rawUrl, fallbackUrl) {
    if (typeof rawUrl !== "string" || rawUrl.trim() === "") {
        return fallbackUrl;
    }

    const trimmed = rawUrl.trim();
    if (trimmed.startsWith("/") && !trimmed.startsWith("//")) {
        return trimmed;
    }

    try {
        const parsed = new URL(trimmed, window.location.origin);
        if (parsed.origin === window.location.origin) {
            return parsed.pathname + parsed.search + parsed.hash;
        }
    } catch {
        // Fall through to safe fallback URL.
    }

    return fallbackUrl;
}

function createIcon(className) {
    const icon = document.createElement("i");
    icon.className = className;
    icon.setAttribute("aria-hidden", "true");
    return icon;
}

/**
 * Initialize all glossary search widgets on the current page.
 */
export function initGlossarySearchBoxes() {
    if (typeof document === "undefined") {
        return;
    }

    const widgets = Array.from(
        document.querySelectorAll("[data-glossary-search-widget]"),
    );
    widgets.forEach((widget) => {
        if (widget.dataset.glossarySearchInit === "true") {
            return;
        }
        widget.dataset.glossarySearchInit = "true";
        setupGlossarySearchWidget(widget);
    });
}

function setupGlossarySearchWidget(widget) {
    const form = widget.querySelector("form");
    const input = widget.querySelector("[data-search-input]");
    const clearBtn = widget.querySelector("[data-search-clear]");
    const spinner = widget.querySelector("[data-search-spinner]");
    const panel = widget.querySelector("[data-suggest-panel]");
    const headerEl = widget.querySelector("[data-suggest-header]");
    const stateEl = widget.querySelector("[data-suggest-state]");
    const listboxEl = widget.querySelector("[data-suggest-listbox]");
    const footerEl = widget.querySelector("[data-suggest-footer]");
    const statusEl = widget.querySelector("[data-suggest-status]");

    if (!form || !input || !panel || !listboxEl) {
        return;
    }

    const endpoint = widget.dataset.endpoint || "/glosarium/cari";
    const indexUrl = widget.dataset.indexUrl || "/glosarium";
    const labels = {
        popular: widget.dataset.labelPopular || "Istilah Populer",
        results: widget.dataset.labelResults || "Saran Istilah Medis",
        viewAll:
            widget.dataset.labelViewAll ||
            'Lihat semua :count hasil untuk ":query"',
        viewAllOne: widget.dataset.labelViewAllOne || "",
        empty:
            widget.dataset.labelEmpty ||
            "Tidak ada nama istilah medis yang cocok",
        emptyHint:
            widget.dataset.labelEmptyHint ||
            "Coba gunakan nama istilah medis seperti Diabetes, Asthma, atau Stroke.",
        loading: widget.dataset.labelLoading || "Mencari istilah medis...",
        error:
            widget.dataset.labelError ||
            "Gagal memuat saran pencarian. Tekan Enter untuk mencari.",
        liveResults:
            widget.dataset.labelLiveResults ||
            "Menampilkan :count saran istilah medis",
        livePopular:
            widget.dataset.labelLivePopular ||
            "Menampilkan :count istilah populer",
    };

    const DEBOUNCE_MS = 170;
    const SPINNER_DELAY_MS = 120;

    const cache = new Map();
    let debounceTimer = null;
    let spinnerTimer = null;
    let abortController = null;
    let requestSeq = 0;
    let isComposing = false;
    let suppressNextFocusLookup = false;
    let isPointerInsidePanel = false;
    let isOpen = false;
    let activeIndex = -1;
    let renderedOptions = [];

    function syncClearButton() {
        if (clearBtn) {
            clearBtn.hidden = input.value.length === 0;
        }
    }

    function announceStatus(message) {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = "";
        window.requestAnimationFrame(() => {
            statusEl.textContent = message;
        });
    }

    function showSpinner() {
        if (spinner) {
            spinner.hidden = false;
        }
    }

    function hideSpinner() {
        if (spinnerTimer) {
            clearTimeout(spinnerTimer);
            spinnerTimer = null;
        }
        if (spinner) {
            spinner.hidden = true;
        }
    }

    function cancelPendingRequests() {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }
        if (abortController) {
            abortController.abort();
            abortController = null;
        }
        requestSeq += 1;
        hideSpinner();
    }

    function openPanel() {
        if (!isOpen) {
            isOpen = true;
            panel.hidden = false;
            input.setAttribute("aria-expanded", "true");
            widget.classList.add("is-suggest-open");
        }
    }

    function closePanel() {
        cancelPendingRequests();
        if (isOpen) {
            isOpen = false;
            panel.hidden = true;
            input.setAttribute("aria-expanded", "false");
            input.removeAttribute("aria-activedescendant");
            widget.classList.remove("is-suggest-open");
            setActiveOption(-1, false);
        }
    }

    function setActiveOption(nextIndex, shouldScroll = true) {
        activeIndex = nextIndex;

        renderedOptions.forEach((opt, idx) => {
            const isSelected = idx === activeIndex;
            opt.element.classList.toggle("is-active", isSelected);
            opt.element.setAttribute(
                "aria-selected",
                isSelected ? "true" : "false",
            );
        });

        if (activeIndex >= 0 && renderedOptions[activeIndex]) {
            const activeOpt = renderedOptions[activeIndex];
            input.setAttribute("aria-activedescendant", activeOpt.id);
            if (
                shouldScroll &&
                typeof activeOpt.element.scrollIntoView === "function"
            ) {
                activeOpt.element.scrollIntoView({ block: "nearest" });
            }
        } else {
            input.removeAttribute("aria-activedescendant");
        }
    }

    function clearElement(el) {
        if (!el) {
            return;
        }
        while (el.firstChild) {
            el.removeChild(el.firstChild);
        }
    }

    function renderHeader(mode) {
        if (!headerEl) {
            return;
        }
        clearElement(headerEl);

        const iconClass =
            mode === "popular"
                ? "fa-solid fa-fire-flame-curved"
                : "fa-solid fa-magnifying-glass";
        headerEl.appendChild(createIcon(iconClass));

        const textSpan = document.createElement("span");
        textSpan.textContent =
            mode === "popular" ? labels.popular : labels.results;
        headerEl.appendChild(textSpan);

        headerEl.hidden = false;
    }

    function renderStateMessage(kind, titleText, hintText = "") {
        if (!stateEl) {
            return;
        }
        clearElement(stateEl);
        clearElement(listboxEl);
        renderedOptions = [];
        activeIndex = -1;
        input.removeAttribute("aria-activedescendant");

        if (footerEl) {
            clearElement(footerEl);
            footerEl.hidden = true;
        }

        stateEl.className = `glossary-suggest-state glossary-suggest-state--${kind}`;

        const iconWrap = document.createElement("span");
        iconWrap.className = "glossary-suggest-state-icon";
        if (kind === "loading") {
            iconWrap.appendChild(
                createIcon("fa-solid fa-circle-notch fa-spin"),
            );
        } else if (kind === "error") {
            iconWrap.appendChild(
                createIcon("fa-solid fa-triangle-exclamation"),
            );
        } else {
            iconWrap.appendChild(
                createIcon("fa-solid fa-magnifying-glass-minus"),
            );
        }
        stateEl.appendChild(iconWrap);

        const textWrap = document.createElement("div");
        textWrap.className = "glossary-suggest-state-copy";

        const titleEl = document.createElement("p");
        titleEl.className = "glossary-suggest-state-title";
        titleEl.textContent = titleText;
        textWrap.appendChild(titleEl);

        if (hintText) {
            const hintEl = document.createElement("p");
            hintEl.className = "glossary-suggest-state-hint";
            hintEl.textContent = hintText;
            textWrap.appendChild(hintEl);
        }

        stateEl.appendChild(textWrap);
        stateEl.hidden = false;
        listboxEl.hidden = true;
    }

    function renderPayload(payload, rawQuery) {
        const mode =
            payload && payload.mode === "popular" ? "popular" : "search";
        const items = Array.isArray(payload?.items) ? payload.items : [];
        const total = Number.isFinite(Number(payload?.total))
            ? Number(payload.total)
            : items.length;
        const displayQuery =
            typeof payload?.query === "string" && payload.query !== ""
                ? payload.query
                : rawQuery.trim();

        renderHeader(mode);

        if (items.length === 0) {
            renderStateMessage("empty", labels.empty, labels.emptyHint);
            openPanel();
            announceStatus(labels.empty);
            return;
        }

        if (stateEl) {
            clearElement(stateEl);
            stateEl.hidden = true;
        }

        clearElement(listboxEl);
        listboxEl.hidden = false;
        renderedOptions = [];
        activeIndex = -1;
        input.removeAttribute("aria-activedescendant");

        const baseListboxId = listboxEl.id || "glossarySuggestListbox";

        items.forEach((item, idx) => {
            const optionId = `${baseListboxId}-option-${idx}`;
            const titleText =
                typeof item?.istilah === "string" ? item.istilah : "";
            const excerptText =
                typeof item?.excerpt === "string" ? item.excerpt : "";
            const letterText =
                typeof item?.letter === "string"
                    ? item.letter
                    : titleText.charAt(0).toUpperCase();
            const safeUrl = sanitizeNavigationUrl(item?.url, indexUrl);

            const li = document.createElement("li");
            li.id = optionId;
            li.className = "glossary-suggest-option";
            li.setAttribute("role", "option");
            li.setAttribute("aria-selected", "false");

            const link = document.createElement("a");
            link.href = safeUrl;
            link.className = "glossary-suggest-link";
            link.setAttribute("tabindex", "-1");

            const iconSpan = document.createElement("span");
            iconSpan.className =
                mode === "popular"
                    ? "glossary-suggest-item-icon is-popular"
                    : "glossary-suggest-item-icon";
            iconSpan.appendChild(
                createIcon(
                    mode === "popular"
                        ? "fa-solid fa-fire-flame-curved"
                        : "fa-solid fa-book-medical",
                ),
            );
            link.appendChild(iconSpan);

            const bodyDiv = document.createElement("span");
            bodyDiv.className = "glossary-suggest-item-body";

            const titleRow = document.createElement("span");
            titleRow.className = "glossary-suggest-item-title-row";

            const titleSpan = document.createElement("span");
            titleSpan.className = "glossary-suggest-item-title";

            if (mode === "search" && displayQuery !== "") {
                const segments = buildHighlightSegments(
                    titleText,
                    displayQuery,
                );
                segments.forEach((seg) => {
                    if (seg.match) {
                        const mark = document.createElement("mark");
                        mark.className = "glossary-suggest-mark";
                        mark.textContent = seg.text;
                        titleSpan.appendChild(mark);
                    } else {
                        titleSpan.appendChild(
                            document.createTextNode(seg.text),
                        );
                    }
                });
            } else {
                titleSpan.textContent = titleText;
            }

            titleRow.appendChild(titleSpan);

            if (
                mode === "popular" &&
                item?.is_trending &&
                typeof item?.trending_label === "string" &&
                item.trending_label !== ""
            ) {
                const trendBadge = document.createElement("span");
                trendBadge.className = "glossary-suggest-trending";
                trendBadge.appendChild(
                    createIcon("fa-solid fa-arrow-trend-up"),
                );
                const trendLabel = document.createElement("span");
                trendLabel.textContent = item.trending_label;
                trendBadge.appendChild(trendLabel);
                titleRow.appendChild(trendBadge);
            }

            bodyDiv.appendChild(titleRow);

            if (excerptText) {
                const excerptSpan = document.createElement("span");
                excerptSpan.className = "glossary-suggest-item-excerpt";
                excerptSpan.textContent = excerptText;
                bodyDiv.appendChild(excerptSpan);
            }

            link.appendChild(bodyDiv);

            if (letterText) {
                const letterBadge = document.createElement("span");
                letterBadge.className = "glossary-suggest-letter";
                letterBadge.setAttribute("aria-hidden", "true");
                letterBadge.textContent = letterText;
                link.appendChild(letterBadge);
            }

            li.addEventListener("mouseenter", () => {
                setActiveOption(idx, false);
            });

            li.appendChild(link);
            listboxEl.appendChild(li);
            renderedOptions.push({ id: optionId, element: li, url: safeUrl });
        });

        if (footerEl) {
            clearElement(footerEl);
            if (mode === "search" && total > 0 && displayQuery !== "") {
                const fallbackAllUrl = `${indexUrl}?q=${encodeURIComponent(displayQuery)}`;
                const safeAllUrl = sanitizeNavigationUrl(
                    payload?.all_results_url,
                    fallbackAllUrl,
                );

                const footerLink = document.createElement("a");
                footerLink.href = safeAllUrl;
                footerLink.className = "glossary-suggest-footer-link";

                const footerTemplate =
                    total === 1 && labels.viewAllOne !== ""
                        ? labels.viewAllOne
                        : labels.viewAll;
                const footerText = document.createElement("span");
                footerText.textContent = formatLabelTemplate(footerTemplate, {
                    count: total,
                    query: displayQuery,
                });
                footerLink.appendChild(footerText);
                footerLink.appendChild(createIcon("fa-solid fa-arrow-right"));

                footerEl.appendChild(footerLink);
                footerEl.hidden = false;
            } else {
                footerEl.hidden = true;
            }
        }

        openPanel();

        const liveTemplate =
            mode === "popular" ? labels.livePopular : labels.liveResults;
        announceStatus(
            formatLabelTemplate(liveTemplate, { count: items.length }),
        );
    }

    async function fetchSuggestions(rawQuery) {
        const trimmed = rawQuery.trim();
        const cacheKey = normalizeSearchQuery(trimmed);

        cancelPendingRequests();

        if (cache.has(cacheKey)) {
            renderPayload(cache.get(cacheKey), trimmed);
            return;
        }

        abortController = new AbortController();
        const seq = requestSeq;

        spinnerTimer = window.setTimeout(() => {
            if (seq !== requestSeq) {
                return;
            }
            showSpinner();
            if (!isOpen || renderedOptions.length === 0) {
                renderHeader(trimmed === "" ? "popular" : "search");
                renderStateMessage("loading", labels.loading);
                openPanel();
            }
        }, SPINNER_DELAY_MS);

        try {
            const reqUrl = new URL(endpoint, window.location.origin);
            if (trimmed !== "") {
                reqUrl.searchParams.set("q", trimmed);
            }

            const response = await fetch(reqUrl.toString(), {
                method: "GET",
                signal: abortController.signal,
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (seq !== requestSeq) {
                return;
            }

            hideSpinner();

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            if (seq !== requestSeq) {
                return;
            }

            cache.set(cacheKey, data);
            renderPayload(data, trimmed);
        } catch (err) {
            if (err && err.name === "AbortError") {
                return;
            }
            if (seq !== requestSeq) {
                return;
            }

            hideSpinner();
            renderHeader(trimmed === "" ? "popular" : "search");
            renderStateMessage("error", labels.error);
            openPanel();
            announceStatus(labels.error);
        }
    }

    function scheduleLookup(rawQuery, immediate = false) {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }

        const trimmed = rawQuery.trim();
        const cacheKey = normalizeSearchQuery(trimmed);

        if (immediate || cache.has(cacheKey)) {
            fetchSuggestions(trimmed);
            return;
        }

        debounceTimer = window.setTimeout(() => {
            fetchSuggestions(trimmed);
        }, DEBOUNCE_MS);
    }

    syncClearButton();

    input.addEventListener("focus", () => {
        if (suppressNextFocusLookup) {
            return;
        }
        scheduleLookup(input.value, true);
    });

    input.addEventListener("compositionstart", () => {
        isComposing = true;
    });

    input.addEventListener("compositionend", () => {
        isComposing = false;
        syncClearButton();
        scheduleLookup(input.value, false);
    });

    input.addEventListener("input", () => {
        syncClearButton();
        if (isComposing) {
            return;
        }
        const trimmed = input.value.trim();
        scheduleLookup(trimmed, trimmed === "");
    });

    input.addEventListener("keydown", (event) => {
        if (isComposing) {
            return;
        }

        if (event.key === "ArrowDown") {
            event.preventDefault();
            if (!isOpen) {
                scheduleLookup(input.value, true);
                return;
            }
            if (renderedOptions.length > 0) {
                const next = computeNextOptionIndex(
                    activeIndex,
                    renderedOptions.length,
                    "down",
                );
                setActiveOption(next, true);
            }
            return;
        }

        if (event.key === "ArrowUp") {
            if (!isOpen || renderedOptions.length === 0) {
                return;
            }
            event.preventDefault();
            const next = computeNextOptionIndex(
                activeIndex,
                renderedOptions.length,
                "up",
            );
            setActiveOption(next, true);
            return;
        }

        if (
            event.key === "Home" &&
            isOpen &&
            renderedOptions.length > 0 &&
            activeIndex >= 0
        ) {
            event.preventDefault();
            setActiveOption(
                computeNextOptionIndex(
                    activeIndex,
                    renderedOptions.length,
                    "home",
                ),
                true,
            );
            return;
        }

        if (
            event.key === "End" &&
            isOpen &&
            renderedOptions.length > 0 &&
            activeIndex >= 0
        ) {
            event.preventDefault();
            setActiveOption(
                computeNextOptionIndex(
                    activeIndex,
                    renderedOptions.length,
                    "end",
                ),
                true,
            );
            return;
        }

        if (event.key === "Escape") {
            if (isOpen || debounceTimer || abortController) {
                event.preventDefault();
                closePanel();
            }
            return;
        }

        if (
            event.key === "Enter" &&
            isOpen &&
            activeIndex >= 0 &&
            renderedOptions[activeIndex]
        ) {
            event.preventDefault();
            const targetUrl = renderedOptions[activeIndex].url;
            closePanel();
            window.location.assign(targetUrl);
        }
    });

    form.addEventListener("submit", (event) => {
        cancelPendingRequests();
        const trimmed = input.value.trim();
        input.value = trimmed;

        if (trimmed === "") {
            event.preventDefault();
            window.location.assign(indexUrl);
        }
    });

    if (clearBtn) {
        clearBtn.addEventListener("click", () => {
            cancelPendingRequests();

            input.value = "";
            syncClearButton();
            if (document.activeElement !== input) {
                suppressNextFocusLookup = true;
                input.focus();
                suppressNextFocusLookup = false;
            }
            scheduleLookup("", true);
        });
    }

    // Keep input focused when clicking suggestion content without blocking scrollbar dragging.
    panel.addEventListener("pointerdown", () => {
        isPointerInsidePanel = true;
    });

    panel.addEventListener("mousedown", (event) => {
        isPointerInsidePanel = true;
        if (
            event.button === 0 &&
            event.target !== panel &&
            event.target !== listboxEl
        ) {
            event.preventDefault();
        }
    });

    const handlePointerRelease = () => {
        if (!isPointerInsidePanel) {
            return;
        }
        isPointerInsidePanel = false;
        const activeEl = document.activeElement;
        if (
            isOpen &&
            activeEl !== input &&
            (!activeEl ||
                activeEl === document.body ||
                activeEl === listboxEl ||
                activeEl === panel)
        ) {
            suppressNextFocusLookup = true;
            input.focus({ preventScroll: true });
            suppressNextFocusLookup = false;
        }
    };

    window.addEventListener("pointerup", handlePointerRelease);
    window.addEventListener("pointercancel", handlePointerRelease);

    document.addEventListener("pointerdown", (event) => {
        if (!widget.contains(event.target)) {
            isPointerInsidePanel = false;
            closePanel();
        }
    });

    widget.addEventListener("focusout", (event) => {
        const nextFocus = event.relatedTarget;
        if (nextFocus && widget.contains(nextFocus)) {
            return;
        }
        window.setTimeout(() => {
            if (isPointerInsidePanel) {
                return;
            }
            if (!widget.contains(document.activeElement)) {
                closePanel();
            }
        }, 0);
    });

    window.addEventListener("pageshow", (event) => {
        if (event.persisted) {
            closePanel();
            syncClearButton();
        }
    });
}
