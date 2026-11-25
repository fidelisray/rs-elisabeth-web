<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

class RichTextSanitizer
{
    /**
     * @var list<string>
     */
    private const ALLOWED_TAGS = [
        'p',
        'br',
        'strong',
        'b',
        'em',
        'i',
        'u',
        's',
        'del',
        'h2',
        'h3',
        'ul',
        'ol',
        'li',
        'blockquote',
        'a',
    ];

    /**
     * @var list<string>
     */
    private const DROP_WITH_CONTENT_TAGS = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'svg',
        'noscript',
        'img',
    ];

    /**
     * Sanitasi HTML Rich Text untuk deskripsi formulir dengan allowlist tag aman.
     * Teks polos tanpa tag tetap dibungkus paragraf rapi; konten kosong mengembalikan null.
     */
    public static function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $trimmed = trim($html);
        if ($trimmed === '') {
            return null;
        }

        if (self::toPlainText($trimmed) === null) {
            return null;
        }

        // Jika teks lama berupa teks polos tanpa tag HTML, bungkus ke paragraf ter-escape.
        if (preg_match('/<[a-z][\s\S]*>/i', $trimmed) !== 1) {
            return '<p>'.nl2br(e($trimmed), false).'</p>';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="__rse_root__">'.$trimmed.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $dom->getElementById('__rse_root__');
        if (! $root instanceof DOMElement) {
            return null;
        }

        self::sanitizeChildren($dom, $root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        $output = trim($output);
        if ($output === '' || self::toPlainText($output) === null) {
            return null;
        }

        return $output;
    }

    public static function toPlainText(?string $html, ?int $limit = null): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $spaced = (string) preg_replace('/<(?:br\s*\/?|\/(?:p|div|li|h[1-6]|blockquote))>/i', ' ', $html);
        $decoded = html_entity_decode(strip_tags($spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', $decoded));

        if ($normalized === '') {
            return null;
        }

        return $limit !== null ? Str::limit($normalized, $limit) : $normalized;
    }

    private static function sanitizeChildren(DOMDocument $dom, DOMNode $parent): void
    {
        $children = [];
        foreach ($parent->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE || ! $child instanceof DOMElement) {
                $parent->removeChild($child);

                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT_TAGS, true)) {
                $parent->removeChild($child);

                continue;
            }

            self::sanitizeChildren($dom, $child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild !== null) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);

                continue;
            }

            self::sanitizeAttributes($child, $tag);
        }
    }

    private static function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $attrNames = [];
        foreach ($element->attributes as $attr) {
            $attrNames[] = $attr->name;
        }

        if ($tag !== 'a') {
            foreach ($attrNames as $name) {
                $element->removeAttribute($name);
            }

            return;
        }

        $href = trim($element->getAttribute('href'));
        $target = strtolower(trim($element->getAttribute('target')));

        foreach ($attrNames as $name) {
            $element->removeAttribute($name);
        }

        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));
        if ($href !== '' && in_array($scheme, ['http', 'https', 'mailto'], true)) {
            $element->setAttribute('href', $href);
            if ($target === '_blank' || in_array($scheme, ['http', 'https'], true)) {
                $element->setAttribute('target', '_blank');
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }
}
