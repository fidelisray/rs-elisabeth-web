import test from "node:test";
import assert from "node:assert/strict";

import {
    normalizeSearchQuery,
    tokenizeSearchQuery,
    buildHighlightSegments,
    computeNextOptionIndex,
    formatLabelTemplate,
} from "../../resources/js/glosarium/glossary-search.js";

test("normalizeSearchQuery trims, collapses whitespace, and lowercases", () => {
    assert.equal(normalizeSearchQuery("  Diabetes   Type 2  "), "diabetes type 2");
    assert.equal(normalizeSearchQuery("   "), "");
    assert.equal(normalizeSearchQuery(null), "");
    assert.equal(normalizeSearchQuery(undefined), "");
});

test("tokenizeSearchQuery splits across spaces and hyphens and deduplicates", () => {
    assert.deepEqual(tokenizeSearchQuery("diabetes type 2"), ["diabetes", "type", "2"]);
    assert.deepEqual(tokenizeSearchQuery("type-2-diabetes"), ["type", "2", "diabetes"]);
    assert.deepEqual(tokenizeSearchQuery("Diabetes   diabetes - DIABETES"), ["diabetes"]);
    assert.deepEqual(tokenizeSearchQuery("---   ---"), []);
});

test("buildHighlightSegments highlights multi-token out-of-order and hyphenated queries", () => {
    assert.deepEqual(buildHighlightSegments("Type 2 Diabetes", "diabetes type 2"), [
        { text: "Type", match: true },
        { text: " ", match: false },
        { text: "2", match: true },
        { text: " ", match: false },
        { text: "Diabetes", match: true },
    ]);

    assert.deepEqual(buildHighlightSegments("Type 2 Diabetes", "type-2"), [
        { text: "Type", match: true },
        { text: " ", match: false },
        { text: "2", match: true },
        { text: " Diabetes", match: false },
    ]);

    assert.deepEqual(buildHighlightSegments("Prediabetes", "diabetes"), [
        { text: "Pre", match: false },
        { text: "diabetes", match: true },
    ]);
});

test("buildHighlightSegments merges overlapping tokens and handles regex metacharacters safely", () => {
    assert.deepEqual(buildHighlightSegments("Diabetes", "diab diabetes"), [
        { text: "Diabetes", match: true },
    ]);

    assert.deepEqual(buildHighlightSegments("Diabetes", "[a-z]+(.*)"), [
        { text: "Diabetes", match: false },
    ]);

    assert.deepEqual(buildHighlightSegments("", "diabetes"), []);
    assert.deepEqual(buildHighlightSegments("Asthma", ""), [{ text: "Asthma", match: false }]);
});

test("computeNextOptionIndex handles ArrowDown, ArrowUp, Home, End, and empty lists", () => {
    assert.equal(computeNextOptionIndex(-1, 0, "down"), -1);
    assert.equal(computeNextOptionIndex(-1, 4, "down"), 0);
    assert.equal(computeNextOptionIndex(0, 4, "down"), 1);
    assert.equal(computeNextOptionIndex(3, 4, "down"), 0);

    assert.equal(computeNextOptionIndex(-1, 4, "up"), 3);
    assert.equal(computeNextOptionIndex(0, 4, "up"), 3);
    assert.equal(computeNextOptionIndex(2, 4, "up"), 1);

    assert.equal(computeNextOptionIndex(2, 4, "home"), 0);
    assert.equal(computeNextOptionIndex(0, 4, "end"), 3);
});

test("formatLabelTemplate interpolates placeholders safely even with dollar tokens", () => {
    assert.equal(
        formatLabelTemplate('Lihat semua :count hasil untuk ":query"', {
            count: 8,
            query: "diabetes",
        }),
        'Lihat semua 8 hasil untuk "diabetes"'
    );

    assert.equal(
        formatLabelTemplate('View all :count results for ":query"', {
            count: 2,
            query: "$& $1",
        }),
        'View all 2 results for "$& $1"'
    );

    assert.equal(
        formatLabelTemplate('View 1 result for ":query"', {
            count: 1,
            query: "diabetes insipidus",
        }),
        'View 1 result for "diabetes insipidus"'
    );
});
