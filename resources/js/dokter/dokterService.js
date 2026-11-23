export const state = {
    itemsPerPage: 8,
    fullDoctorList: [],
    filteredDoctorList: [],
    currentPage: 1,
    days: [1, 2, 3, 4, 5, 6, 7],
};

export function preprocessingApiData(doctorData, knownSpecialtyCode = null, activePoliCodesOverride = undefined) {
    const doctorMap = new Map();

    if (!Array.isArray(doctorData)) return [];

    doctorData.forEach((item) => {
        if (!item || !item.ParamedicCode || !Number.isInteger(Number(item.DayNumber)) || Number(item.DayNumber) < 1 || Number(item.DayNumber) > 7) return;
        if (!doctorMap.has(item.ParamedicCode)) {
            const schedule = {};
            state.days.forEach((day) => {
                schedule[day] = [];
            });

            doctorMap.set(item.ParamedicCode, {
                entry_id: `${item.ServiceUnitCode}0823${item.ParamedicCode}9373`,
                paramedicCode: String(item.ParamedicCode),
                paramedicName: item.ParamedicName,
                specialtyCode: item.SpecialityCode || knownSpecialtyCode,
                specialtyName: item.SpecialtyName || "",
                serviceUnitName: item.ServiceUnitName || "",
                schedule,
            });
        }

        const doc = doctorMap.get(item.ParamedicCode);

        let sessions = [];
        if (Array.isArray(item.ListTimeSchedule) && item.ListTimeSchedule.length > 0) {
            sessions = item.ListTimeSchedule.map((ts, idx) => ({
                jam: `${ts.StartTime} - ${ts.EndTime}`,
                sesi: ts.Session ?? (idx + 1),
            }));
        } else if (typeof item.OperationalTimeName === 'string' && item.OperationalTimeName.trim() !== '') {
            sessions = item.OperationalTimeName.split("|").map((jamStr, idx) => ({
                jam: jamStr.trim(),
                sesi: idx + 1,
            }));
        }

        doc.schedule[item.DayNumber].push({
            jam: sessions.map((s) => s.jam),
            sessions,
            serviceUnitCode: String(item.ServiceUnitCode || "").trim(),
            serviceUnitName: String(item.ServiceUnitName || "").trim(),
        });
    });

    const activePoliSet = resolveActivePoliSet(activePoliCodesOverride);
    const doctors = [...doctorMap.values()];
    doctors.forEach((doc) => markRegOnlineBookableSlots(doc, activePoliSet));
    return doctors;
}

let activePoliCacheSet;

function getActivePoliCodeSet() {
    if (activePoliCacheSet !== undefined) return activePoliCacheSet;
    if (typeof document === "undefined") {
        activePoliCacheSet = null;
        return activePoliCacheSet;
    }

    try {
        const el = document.getElementById("regonline-active-poli-json");
        if (!el) {
            activePoliCacheSet = null;
            return activePoliCacheSet;
        }
        const parsed = JSON.parse(el.textContent || "null");
        if (Array.isArray(parsed)) {
            activePoliCacheSet = new Set(parsed.map((code) => String(code).trim()).filter(Boolean));
        } else {
            activePoliCacheSet = null;
        }
    } catch (e) {
        console.error("Error parsing active poli json:", e);
        activePoliCacheSet = null;
    }

    return activePoliCacheSet;
}

function resolveActivePoliSet(override) {
    if (override === undefined) return getActivePoliCodeSet();
    if (override === null) return null;
    if (override instanceof Set) return override;
    if (Array.isArray(override)) {
        return new Set(override.map((code) => String(code).trim()).filter(Boolean));
    }
    return null;
}

function getCommonClinicName(unitName = "") {
    return String(unitName).replace(/Klinik\s+(Elisabeth\s+)?/i, "").trim().toLowerCase();
}

function isElisabethClinic(unitName = "") {
    return String(unitName).toLowerCase().includes("elisabeth");
}

// Menentukan status bookable berdasarkan referensi aktif dbo.ref_poli_online dan aturan sesi Elisabeth vs Reguler
function markRegOnlineBookableSlots(doc, activePoliSet = null) {
    state.days.forEach((day) => {
        const dayItems = doc.schedule[day] || [];
        if (dayItems.length === 0) return;

        // Unit yang tidak aktif di ref_poli_online tidak ikut dihitung dalam rasio Elisabeth vs Reguler
        const clinicStats = new Map();
        dayItems.forEach((item) => {
            const unitCode = String(item.serviceUnitCode || "").trim();
            if (activePoliSet !== null && !activePoliSet.has(unitCode)) return;

            const key = getCommonClinicName(item.serviceUnitName);
            const stat = clinicStats.get(key) ?? { elisabeth: 0, biasa: 0 };
            if (isElisabethClinic(item.serviceUnitName)) {
                stat.elisabeth += item.sessions.length;
            } else {
                stat.biasa += item.sessions.length;
            }
            clinicStats.set(key, stat);
        });

        dayItems.forEach((item) => {
            const unitCode = String(item.serviceUnitCode || "").trim();
            const isPoliActive = activePoliSet === null || activePoliSet.has(unitCode);

            if (!isPoliActive) {
                item.sessions = item.sessions.map((slot) => ({ ...slot, bookable: false }));
                return;
            }

            const isElisabeth = isElisabethClinic(item.serviceUnitName);
            const stat = clinicStats.get(getCommonClinicName(item.serviceUnitName)) ?? { elisabeth: 0, biasa: 0 };
            const hasElisabeth = stat.elisabeth > 0;
            const hasMultipleRegularSessions = stat.biasa > 1;

            item.sessions = item.sessions.map((slot) => {
                if (!hasElisabeth || isElisabeth || !hasMultipleRegularSessions) {
                    return { ...slot, bookable: true };
                }

                // Ada Klinik Elisabeth & Klinik Reguler sesi > 1: Sesi 1 non-bookable, Sesi 2+ bookable
                return { ...slot, bookable: Number(slot.sesi) !== 1 };
            });
        });
    });
}

export function setDoctorContext(doctorList) {
    state.fullDoctorList = doctorList;
    state.filteredDoctorList = doctorList;
    state.currentPage = 1;
}

let specialtyCacheMap = null;
let poliReferenceCache;

export function resetReferenceCaches() {
    activePoliCacheSet = undefined;
    poliReferenceCache = undefined;
    specialtyCacheMap = null;
}

export function getSpecialtyMap(override = undefined) {
    if (override instanceof Map) return override;
    if (!specialtyCacheMap) {
        specialtyCacheMap = new Map();
        if (typeof document !== "undefined") {
            try {
                const el = document.getElementById("specialties-json");
                if (el) {
                    const data = JSON.parse(el.textContent || "[]");
                    if (Array.isArray(data)) {
                        data.forEach((item) => {
                            const code = item.Code ?? item.code;
                            const name = item.Name ?? item.name;
                            if (code && name) {
                                specialtyCacheMap.set(String(code), String(name));
                            }
                        });
                    }
                }
            } catch (e) {
                console.error("Error parsing specialties json:", e);
            }
        }
    }
    return specialtyCacheMap;
}

function normalizePoliReferenceItems(rawList) {
    if (!Array.isArray(rawList)) return null;

    const seen = new Set();
    const normalized = [];
    rawList.forEach((item) => {
        if (!item || typeof item !== "object") return;
        const code = String(item.code ?? item.kode_poli ?? "").trim();
        const name = String(item.name ?? item.nama_poli ?? "").trim();
        if (!code || !name || seen.has(code)) return;
        seen.add(code);

        const rawActive = item.is_active ?? item.isActive;
        const isActive =
            typeof rawActive === "boolean"
                ? rawActive
                : String(rawActive ?? "").trim().toLowerCase() === "y";

        normalized.push({ code, name, isActive });
    });

    return normalized;
}

export function getPoliReferenceList(override = undefined) {
    if (override !== undefined) {
        return override === null ? null : normalizePoliReferenceItems(override);
    }

    if (poliReferenceCache !== undefined) return poliReferenceCache;
    if (typeof document === "undefined") {
        poliReferenceCache = null;
        return poliReferenceCache;
    }

    try {
        const el = document.getElementById("regonline-poli-list-json");
        if (!el) {
            poliReferenceCache = null;
            return poliReferenceCache;
        }
        const parsed = JSON.parse(el.textContent || "null");
        poliReferenceCache = normalizePoliReferenceItems(parsed);
    } catch (e) {
        console.error("Error parsing poli reference json:", e);
        poliReferenceCache = null;
    }

    return poliReferenceCache;
}

export function getPoliReferenceMap(override = undefined) {
    const map = new Map();
    const list = getPoliReferenceList(override);
    if (Array.isArray(list)) {
        list.forEach((item) => {
            map.set(item.code, item);
        });
    }
    return map;
}

const UPPERCASE_MEDICAL_ACRONYMS = new Set([
    "THT",
    "KL",
    "BKIA",
    "MCU",
    "ESWL",
    "IGD",
    "USG",
    "CT",
    "MRI",
    "ICU",
    "NICU",
    "PICU",
    "HIV",
    "VCT",
    "DOTS",
]);

const LOWERCASE_CONJUNCTIONS = new Set(["dan", "of", "di"]);

export function formatPoliName(rawName = "") {
    const cleaned = String(rawName || "").trim().replace(/\s+/g, " ");
    if (!cleaned) return "";

    return cleaned
        .split(" ")
        .map((segment, index) =>
            segment.replace(/[A-Za-z0-9]+/g, (word) => {
                const upper = word.toUpperCase();
                if (UPPERCASE_MEDICAL_ACRONYMS.has(upper)) {
                    return upper;
                }
                const lower = word.toLowerCase();
                if (index > 0 && LOWERCASE_CONJUNCTIONS.has(lower)) {
                    return lower;
                }
                return lower.charAt(0).toUpperCase() + lower.slice(1);
            }),
        )
        .join(" ");
}

function collectDoctorUnits(dokter) {
    const units = new Map();
    if (!dokter?.schedule) return units;

    for (const day of state.days) {
        const items = dokter.schedule[day] || [];
        for (const item of items) {
            const code = String(item?.serviceUnitCode || "").trim();
            if (!code) continue;
            if (!units.has(code)) {
                units.set(code, String(item?.serviceUnitName || "").trim());
            }
        }
    }

    return units;
}

export function getAvailablePolis(doctors = state.fullDoctorList, poliReferencesOverride = undefined) {
    const doctorUnitStats = new Map();

    if (Array.isArray(doctors)) {
        doctors.forEach((dokter) => {
            const docUnits = collectDoctorUnits(dokter);
            docUnits.forEach((unitName, code) => {
                const existing = doctorUnitStats.get(code) ?? {
                    code,
                    fallbackName: unitName,
                    totalDoctors: 0,
                };
                if (!existing.fallbackName && unitName) {
                    existing.fallbackName = unitName;
                }
                existing.totalDoctors += 1;
                doctorUnitStats.set(code, existing);
            });
        });
    }

    const poliRefList = getPoliReferenceList(poliReferencesOverride);
    let sourceItems = [];

    if (Array.isArray(poliRefList) && poliRefList.length > 0) {
        // Menampilkan seluruh poli dari ref_poli_online (isActive y/n) yang punya jadwal dokter di halaman
        sourceItems = poliRefList
            .filter((item) => doctorUnitStats.has(item.code))
            .map((item) => ({
                code: item.code,
                rawName: item.name,
                isActive: item.isActive,
                totalDoctors: doctorUnitStats.get(item.code)?.totalDoctors || 0,
            }));
    } else {
        // Fallback aman saat referensi SQL Server dan cache fallback belum tersedia
        sourceItems = [...doctorUnitStats.values()]
            .filter((item) => item.fallbackName)
            .map((item) => ({
                code: item.code,
                rawName: item.fallbackName,
                isActive: true,
                totalDoctors: item.totalDoctors,
            }));
    }

    const formattedCounts = new Map();
    const withFormatted = sourceItems.map((item) => {
        const formatted = formatPoliName(item.rawName);
        const key = formatted.toLowerCase();
        formattedCounts.set(key, (formattedCounts.get(key) || 0) + 1);
        return { ...item, formatted };
    });

    return withFormatted
        .map((item) => {
            const isDuplicateName = (formattedCounts.get(item.formatted.toLowerCase()) || 0) > 1;
            return {
                code: item.code,
                name: isDuplicateName ? `${item.formatted} (${item.code})` : item.formatted,
                rawName: item.rawName,
                isActive: item.isActive,
                totalDoctors: item.totalDoctors,
            };
        })
        .sort((a, b) => a.name.localeCompare(b.name, "id") || a.code.localeCompare(b.code));
}

const POLI_MARKER_WORDS = new Set(["klinik", "poli", "poliklinik", "instalasi", "unit"]);

function normalizeSearchText(value = "") {
    return String(value || "")
        .toLowerCase()
        .replace(/&/g, " dan ")
        .replace(/[^a-z0-9]+/g, " ")
        .trim();
}

function buildJoinedWordPairs(segments = []) {
    const pairs = new Set();
    segments.forEach((normText) => {
        const words = normText ? normText.split(" ") : [];
        for (let i = 0; i < words.length - 1; i++) {
            pairs.add(`${words[i]}${words[i + 1]}`);
        }
    });
    return pairs;
}

function buildSearchField(values = [], exactCodes = []) {
    const cleanValues = values
        .filter((v) => typeof v === "string" && v.trim() !== "")
        .map((v) => v.toLowerCase());
    const normSegments = [...new Set(cleanValues.map((v) => normalizeSearchText(v)).filter(Boolean))];
    const raw = cleanValues.join(" ");
    const norm = normSegments.join(" ");
    const codeSet = new Set();

    exactCodes.forEach((code) => {
        const cleaned = String(code || "").trim().toLowerCase();
        if (!cleaned) return;
        codeSet.add(cleaned);
        const normCode = normalizeSearchText(cleaned);
        if (normCode) codeSet.add(normCode);
    });

    return {
        raw,
        norm,
        normSegments,
        joinedPairs: buildJoinedWordPairs(normSegments),
        exactCodes: codeSet,
    };
}

function buildPoliSearchField(unitName = "", officialPoliName = "", unitCode = "") {
    const base = buildSearchField([unitName, officialPoliName], unitCode ? [unitCode] : []);
    if (/\bklinik\b/.test(base.norm)) {
        return {
            raw: `${base.raw} poli poliklinik`.trim(),
            norm: `${base.norm} poli poliklinik`.trim(),
            normSegments: base.normSegments,
            joinedPairs: base.joinedPairs,
            exactCodes: base.exactCodes,
        };
    }
    return base;
}

function tokenizeKeyword(keyword = "") {
    if (typeof keyword !== "string" || keyword.trim() === "") return [];
    const rawTokens = keyword
        .trim()
        .toLowerCase()
        .split(/\s+/)
        .filter((raw) => raw.length > 0)
        .map((raw) => ({
            raw,
            norm: normalizeSearchText(raw),
        }));

    const meaningful = rawTokens.filter((token) => token.norm !== "");
    return meaningful.length > 0 ? meaningful : rawTokens;
}

function fieldMatchesToken(field, token) {
    if (!field || !token) return false;
    if (field.raw.includes(token.raw)) return true;
    if (!token.norm) return false;
    if (field.norm.includes(token.norm)) return true;
    if (field.joinedPairs?.has(token.norm)) return true;
    if (field.exactCodes?.has(token.raw) || field.exactCodes?.has(token.norm)) return true;
    return false;
}

function fieldContainsPhrase(field, tokens) {
    if (!field || !Array.isArray(tokens) || tokens.length === 0) return false;
    const phraseNorm = tokens.map((token) => token.norm).filter(Boolean).join(" ");
    if (!phraseNorm) return false;
    return (field.normSegments || []).some((segment) => segment.includes(phraseNorm));
}

export function matchesPoliOptionQuery(query = "", displayName = "", rawName = "", code = "") {
    const tokens = tokenizeKeyword(query);
    if (tokens.length === 0) return true;
    const field = buildPoliSearchField(displayName, rawName, code);
    return tokens.every((token) => fieldMatchesToken(field, token));
}

function buildAllPoliCatalog(doctors, poliRefMap) {
    const byCode = new Map();

    if (poliRefMap instanceof Map) {
        poliRefMap.forEach((item, code) => {
            byCode.set(code, buildPoliSearchField(item.name, item.name, code));
        });
    }

    if (Array.isArray(doctors)) {
        doctors.forEach((dokter) => {
            const docUnits = collectDoctorUnits(dokter);
            docUnits.forEach((unitName, code) => {
                const officialName = poliRefMap?.get(code)?.name || "";
                byCode.set(code, buildPoliSearchField(unitName, officialName, code));
            });
        });
    }

    return [...byCode.values()];
}

export function buildFilteredSchedule(schedule, allowedUnitCodes) {
    if (!allowedUnitCodes || !schedule) return schedule;

    const filtered = {};
    state.days.forEach((day) => {
        const items = schedule[day] || [];
        filtered[day] = items.filter((item) =>
            allowedUnitCodes.has(String(item?.serviceUnitCode || "").trim()),
        );
    });
    return filtered;
}

export function evaluateDoctorMatch(dokter, options = {}) {
    const {
        tokens = [],
        specSet = null,
        poliSet = null,
        specialtyMap = getSpecialtyMap(),
        poliRefMap = getPoliReferenceMap(),
        allPoliCatalog = [],
    } = options;

    // 1. Filter spesialisasi (OR antar spesialisasi terpilih)
    if (specSet && specSet.size > 0) {
        const docSpecCode = String(dokter?.specialtyCode || "");
        if (!specSet.has(docSpecCode)) {
            return { matched: false, allowedUnitCodes: null };
        }
    }

    // 2. Filter poliklinik (OR antar poliklinik terpilih)
    const docUnits = collectDoctorUnits(dokter);
    let candidateUnits = [...docUnits.entries()].map(([code, unitName]) => ({
        code,
        field: buildPoliSearchField(unitName, poliRefMap?.get(code)?.name || "", code),
    }));

    if (poliSet && poliSet.size > 0) {
        candidateUnits = candidateUnits.filter((unit) => poliSet.has(unit.code));
        if (candidateUnits.length === 0) {
            return { matched: false, allowedUnitCodes: null };
        }
    }

    const baseAllowedUnitCodes =
        poliSet && poliSet.size > 0
            ? new Set(candidateUnits.map((unit) => unit.code))
            : null;

    // 3. Filter kata kunci teks & penyaringan chip jadwal berbasis poli
    if (!Array.isArray(tokens) || tokens.length === 0) {
        return { matched: true, allowedUnitCodes: baseAllowedUnitCodes };
    }

    const specCode = String(dokter?.specialtyCode || "");
    const nameField = buildSearchField([dokter?.paramedicName || ""]);
    const specField = buildSearchField([
        dokter?.specialtyName || "",
        specialtyMap?.get(specCode) || "",
    ]);
    const identityField = buildSearchField([
        dokter?.paramedicName || "",
        dokter?.specialtyName || "",
        specialtyMap?.get(specCode) || "",
    ]);

    const allInName = tokens.every((token) => fieldMatchesToken(nameField, token));
    const allInSpec = tokens.every((token) => fieldMatchesToken(specField, token));
    const allInIdentity = tokens.every((token) => fieldMatchesToken(identityField, token));

    const matchUnits = (requiredTokens) =>
        candidateUnits.filter((unit) =>
            requiredTokens.every((token) => fieldMatchesToken(unit.field, token)),
        );

    const catalogHasPoli = (requiredTokens) =>
        allPoliCatalog.some((poliField) =>
            requiredTokens.every((token) => fieldMatchesToken(poliField, token)),
        );

    // 3a. Deteksi frasa poli eksplisit (diawali klinik/poli/poliklinik/instalasi/unit)
    // agar query seperti "Klinik Gizi", "Bedah Umum Klinik Umum", atau "dr. Elisabeth Klinik Elisabeth"
    // selalu menargetkan poli yang dimaksud dan tidak jatuh ke pencocokan token "klinik" secara umum.
    for (let start = 0; start < tokens.length; start++) {
        if (!POLI_MARKER_WORDS.has(tokens[start].norm)) continue;

        for (let end = tokens.length; end >= start + 2; end--) {
            const poliSpan = tokens.slice(start, end);
            if (!catalogHasPoli(poliSpan)) continue;
            if (fieldContainsPhrase(nameField, poliSpan) || fieldContainsPhrase(specField, poliSpan)) {
                continue;
            }

            const remainingTokens = [...tokens.slice(0, start), ...tokens.slice(end)];
            const nonIdRemaining = remainingTokens.filter((tok) => !fieldMatchesToken(identityField, tok));
            const poliSpanNorms = new Set(poliSpan.map((tok) => tok.norm).filter(Boolean));

            // Jika token duplikat dari frasa poli (misal "Elisabeth" pada "dr. Elisabeth Klinik Elisabeth")
            // tidak cocok di identitas dokter, jangan serap dua kali ke unit yang sama.
            if (nonIdRemaining.some((tok) => poliSpanNorms.has(tok.norm))) {
                return { matched: false, allowedUnitCodes: null };
            }

            const matchedUnits = matchUnits([...poliSpan, ...nonIdRemaining]);
            if (matchedUnits.length > 0) {
                return {
                    matched: true,
                    allowedUnitCodes: new Set(matchedUnits.map((unit) => unit.code)),
                };
            }

            if (nonIdRemaining.length === 0) {
                return { matched: false, allowedUnitCodes: null };
            }
        }
    }

    const isFullQueryPoliMatch = catalogHasPoli(tokens);

    if (isFullQueryPoliMatch) {
        if (allInName || allInSpec) {
            return { matched: true, allowedUnitCodes: baseAllowedUnitCodes };
        }

        const directUnits = matchUnits(tokens);
        if (directUnits.length > 0) {
            return {
                matched: true,
                allowedUnitCodes: new Set(directUnits.map((unit) => unit.code)),
            };
        }

        return { matched: false, allowedUnitCodes: null };
    }

    if (allInIdentity) {
        return { matched: true, allowedUnitCodes: baseAllowedUnitCodes };
    }

    const nonIdentityTokens = tokens.filter((token) => !fieldMatchesToken(identityField, token));
    const allSharedTokens = tokens.filter(
        (token) =>
            fieldMatchesToken(identityField, token) &&
            allPoliCatalog.some((poliField) => fieldMatchesToken(poliField, token)),
    );
    const specSharedTokens = allSharedTokens.filter(
        (token) =>
            fieldMatchesToken(specField, token) &&
            !fieldMatchesToken(nameField, token),
    );

    const strictTokens = [...nonIdentityTokens, ...allSharedTokens];
    let matchedUnits = matchUnits(strictTokens);

    if (matchedUnits.length === 0 && specSharedTokens.length !== allSharedTokens.length) {
        const specCombinedTokens = [...nonIdentityTokens, ...specSharedTokens];
        matchedUnits = matchUnits(specCombinedTokens);
        if (
            matchedUnits.length === 0 &&
            specSharedTokens.length > 0 &&
            catalogHasPoli(specCombinedTokens)
        ) {
            return { matched: false, allowedUnitCodes: null };
        }
    } else if (
        matchedUnits.length === 0 &&
        specSharedTokens.length > 0 &&
        catalogHasPoli([...nonIdentityTokens, ...specSharedTokens])
    ) {
        return { matched: false, allowedUnitCodes: null };
    }

    if (matchedUnits.length === 0) {
        matchedUnits = matchUnits(nonIdentityTokens);
    }

    if (matchedUnits.length === 0) {
        return { matched: false, allowedUnitCodes: null };
    }

    return {
        matched: true,
        allowedUnitCodes: new Set(matchedUnits.map((unit) => unit.code)),
    };
}

function createFilterContext(keyword = "", selectedSpecialtyCodes = [], selectedPoliCodes = [], options = {}) {
    const doctors = options.doctors ?? state.fullDoctorList;
    const specialtyMap = getSpecialtyMap(options.specialtyMap);
    const poliRefMap = getPoliReferenceMap(options.poliReferences);
    const tokens = tokenizeKeyword(keyword);
    const specSet =
        Array.isArray(selectedSpecialtyCodes) && selectedSpecialtyCodes.length > 0
            ? new Set(selectedSpecialtyCodes.map((c) => String(c).trim()).filter(Boolean))
            : null;
    const poliSet =
        Array.isArray(selectedPoliCodes) && selectedPoliCodes.length > 0
            ? new Set(selectedPoliCodes.map((c) => String(c).trim()).filter(Boolean))
            : null;
    const allPoliCatalog = buildAllPoliCatalog(doctors, poliRefMap);

    return { doctors, tokens, specSet, poliSet, specialtyMap, poliRefMap, allPoliCatalog };
}

export function getSpecialtyCounts(keyword = "", selectedPoliCodes = [], options = {}) {
    const counts = new Map();
    const ctx = createFilterContext(keyword, [], selectedPoliCodes, options);

    for (const dokter of ctx.doctors) {
        const { matched } = evaluateDoctorMatch(dokter, ctx);
        if (!matched) continue;
        const code = String(dokter.specialtyCode || "");
        if (code) {
            counts.set(code, (counts.get(code) || 0) + 1);
        }
    }
    return counts;
}

export function getPoliCounts(keyword = "", selectedSpecialtyCodes = [], options = {}) {
    const counts = new Map();
    const ctx = createFilterContext(keyword, selectedSpecialtyCodes, [], options);

    for (const dokter of ctx.doctors) {
        const { matched, allowedUnitCodes } = evaluateDoctorMatch(dokter, ctx);
        if (!matched) continue;

        const unitCodes = allowedUnitCodes ?? new Set(collectDoctorUnits(dokter).keys());
        unitCodes.forEach((code) => {
            if (code) {
                counts.set(code, (counts.get(code) || 0) + 1);
            }
        });
    }
    return counts;
}

export function filterDoctors(keyword = "", selectedSpecialtyCodes = [], selectedPoliCodes = [], options = {}) {
    const ctx = createFilterContext(keyword, selectedSpecialtyCodes, selectedPoliCodes, options);
    const hasActiveCriteria =
        ctx.tokens.length > 0 ||
        (ctx.specSet !== null && ctx.specSet.size > 0) ||
        (ctx.poliSet !== null && ctx.poliSet.size > 0);

    if (!hasActiveCriteria) {
        state.filteredDoctorList = ctx.doctors;
    } else {
        const filtered = [];
        for (const dokter of ctx.doctors) {
            const { matched, allowedUnitCodes } = evaluateDoctorMatch(dokter, ctx);
            if (!matched) continue;

            if (allowedUnitCodes !== null) {
                filtered.push({
                    ...dokter,
                    schedule: buildFilteredSchedule(dokter.schedule, allowedUnitCodes),
                    isScheduleFiltered: true,
                });
            } else {
                filtered.push(dokter);
            }
        }
        state.filteredDoctorList = filtered;
    }
    state.currentPage = 1;
    return state.filteredDoctorList;
}

export async function fetchAllDokter() {
    try {
        const csrfTokenElement = document.querySelector(
            'meta[name="csrf-token"]',
        );
        const csrfToken = csrfTokenElement
            ? csrfTokenElement.getAttribute("content")
            : "";

        const response = await fetch(
            `/dokter/all-dokter?_t=${new Date().getTime()}`,
            {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                    "Cache-Control": "no-cache",
                },
            },
        );

        if (!response.ok)
            throw new Error(`${response.status}: Data tidak tersedia`);

        const data = await response.json();
        if (!data || data.length === 0) return false;

        const doctorList = preprocessingApiData(data);
        setDoctorContext(doctorList);
        return true;
    } catch (error) {
        console.error("Error fetching all doctors:", error);
        return false;
    }
}

export async function fetchDokterBySpecialtyCode(specialtyCode) {
    try {
        const response = await fetch(`/dokter/${encodeURIComponent(specialtyCode)}`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        if (data.Success === false) return false;
        const { ScheduleRoutine } = data;

        if (!ScheduleRoutine || ScheduleRoutine.length === 0) {
            setDoctorContext([]);
            return true;
        }

        const doctorList = preprocessingApiData(ScheduleRoutine, specialtyCode);
        setDoctorContext(doctorList);
        return true;
    } catch (error) {
        console.error("Error fetching specialty doctors:", error);
        return false;
    }
}

// Browser memanggil server Web; server Web yang menyimpan secret dan menghubungi CMS.
export async function fetchDoctorProfile(code, signal) {
    const locale = document.documentElement.lang?.toLowerCase().startsWith('en') ? 'en' : 'id';
    const response = await fetch(`/dokter/profil/${encodeURIComponent(code)}?lang=${locale}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept-Language': locale },
        cache: 'no-store',
        signal,
    });
    if (response.status === 404) return { status: 'not_found', profile: null };
    if (!response.ok) throw new Error('Profil tidak dapat dimuat');
    const payload = await response.json();
    if (!payload.success || payload.data?.paramedic_code !== String(code)) throw new Error('Profil tidak cocok');
    return { status: 'ready', profile: payload.data };
}
