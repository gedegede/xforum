<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

final class MarkdownHelper {
    public static function parse(string $markdown): string {
        $markdown = str_replace(["\r\n", "\r"], "\n", trim($markdown));
        if ($markdown === '') {
            return '';
        }

        $lines = explode("\n", $markdown);
        $html = [];
        $paragraph = [];
        $listType = null;
        $listItems = [];
        $blockquote = [];
        $codeLines = [];
        $inCodeBlock = false;
        $codeLanguage = '';

        $flushParagraph = static function () use (&$html, &$paragraph): void {
            if ($paragraph === []) {
                return;
            }

            $text = implode("\n", $paragraph);
            $html[] = '<p class="mb-3 last:mb-0">' . self::parseInline($text) . '</p>';
            $paragraph = [];
        };

        $flushList = static function () use (&$html, &$listType, &$listItems): void {
            if ($listType === null) {
                return;
            }

            $items = array_map(
                static fn(string $item): string => '<li class="mb-1 last:mb-0">' . self::parseInline($item) . '</li>',
                $listItems
            );

            $listClass = $listType === 'ol' ? 'list-decimal pl-5 mb-3' : 'list-disc pl-5 mb-3';
            $html[] = '<' . $listType . ' class="' . $listClass . '">' . implode('', $items) . '</' . $listType . '>';
            $listType = null;
            $listItems = [];
        };

        $flushBlockquote = static function () use (&$html, &$blockquote): void {
            if ($blockquote === []) {
                return;
            }

            $html[] = '<blockquote class="mb-3 pl-4 border-l-3 border-primary text-sub">' . self::parse(implode("\n", $blockquote)) . '</blockquote>';
            $blockquote = [];
        };

        $flushBlocks = static function () use ($flushParagraph, $flushList, $flushBlockquote): void {
            $flushParagraph();
            $flushList();
            $flushBlockquote();
        };

        foreach ($lines as $line) {
            if ($inCodeBlock) {
                if (preg_match('/^\s*```\s*$/', $line)) {
                    $languageClass = $codeLanguage !== '' ? ' language-' . self::escapeAttribute($codeLanguage) : '';
                    $html[] = '<pre class="mb-3 p-3 bg-text text-gray-400 font-mono text-sm leading-normal rounded table-wrap"><code class="font-mono' . $languageClass . '">' . htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
                    $codeLines = [];
                    $codeLanguage = '';
                    $inCodeBlock = false;
                } else {
                    $codeLines[] = $line;
                }
                continue;
            }

            if (preg_match('/^\s*```([A-Za-z0-9_-]*)\s*$/', $line, $matches)) {
                $flushBlocks();
                $inCodeBlock = true;
                $codeLanguage = $matches[1] ?? '';
                continue;
            }

            $trimmed = trim($line);
            if ($trimmed === '') {
                $flushBlocks();
                continue;
            }

            if (preg_match('/^#{1,6}\s+/', $trimmed)) {
                $flushBlocks();
                preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $matches);
                $level = strlen($matches[1]);
                $headingClass = $level <= 2 ? 'mt-4 mb-2 text-xl font-bold' : 'mt-4 mb-2 text-lg font-semibold';
                $html[] = '<h' . $level . ' class="' . $headingClass . '">' . self::parseInline($matches[2]) . '</h' . $level . '>';
                continue;
            }

            if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trimmed)) {
                $flushBlocks();
                $html[] = '<hr class="my-4 border-0 border-t border-border">';
                continue;
            }

            if (str_starts_with($trimmed, '>')) {
                $flushParagraph();
                $flushList();
                $blockquote[] = ltrim(substr($trimmed, 1));
                continue;
            }

            if (preg_match('/^[-+*]\s+(.+)$/', $trimmed, $matches)) {
                $flushParagraph();
                $flushBlockquote();
                if ($listType !== 'ul') {
                    $flushList();
                    $listType = 'ul';
                }
                $listItems[] = $matches[1];
                continue;
            }

            if (preg_match('/^\d+[.)]\s+(.+)$/', $trimmed, $matches)) {
                $flushParagraph();
                $flushBlockquote();
                if ($listType !== 'ol') {
                    $flushList();
                    $listType = 'ol';
                }
                $listItems[] = $matches[1];
                continue;
            }

            $flushList();
            $flushBlockquote();
            $paragraph[] = $trimmed;
        }

        if ($inCodeBlock) {
            $languageClass = $codeLanguage !== '' ? ' language-' . self::escapeAttribute($codeLanguage) : '';
            $html[] = '<pre class="mb-3 p-3 bg-text text-gray-400 font-mono text-sm leading-normal rounded table-wrap"><code class="font-mono' . $languageClass . '">' . htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
        }

        $flushBlocks();

        return implode("\n", $html);
    }

    public static function strip(string $markdown): string {
        $text = html_entity_decode(
            strip_tags(self::parse($markdown)),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        return trim((string)preg_replace('/\s+/u', ' ', $text));
    }

    private static function parseInline(string $text): string {
        $placeholders = [];

        $text = preg_replace_callback('/`([^`\n]+)`/', static function (array $matches) use (&$placeholders): string {
            $key = "\x1A" . count($placeholders) . "\x1A";
            $placeholders[$key] = '<code class="px-1.5 py-0.5 bg-soft rounded-sm font-mono text-danger">' . htmlspecialchars($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
            return $key;
        }, $text) ?? $text;

        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $text = preg_replace_callback('/!\[([^\]\n]*)\]\(([^)\s]+)\)/', static function (array $matches) use (&$placeholders): string {
            $url = self::safeUrl(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'), false);
            if ($url === null) {
                return $matches[0];
            }

            $key = "\x1A" . count($placeholders) . "\x1A";
            $placeholders[$key] = '<img class="max-w-full rounded border border-border" src="' . self::escapeAttribute($url) . '" alt="' . self::escapeAttribute($matches[1]) . '">';
            return $key;
        }, $text) ?? $text;

        $text = preg_replace_callback('/(?<!!)\[([^\]\n]+)\]\(([^)\s]+)\)/', static function (array $matches) use (&$placeholders): string {
            $url = self::safeUrl(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
            if ($url === null) {
                return $matches[0];
            }

            $key = "\x1A" . count($placeholders) . "\x1A";
            $placeholders[$key] = '<a class="text-primary hover:underline" href="' . self::escapeAttribute($url) . '" target="_blank" rel="noopener noreferrer">' . $matches[1] . '</a>';
            return $key;
        }, $text) ?? $text;

        $patterns = [
            '/\*\*([^\n*]+)\*\*/' => '<strong>$1</strong>',
            '/__([^\n_]+)__/' => '<strong>$1</strong>',
            '/~~([^\n~]+)~~/' => '<del>$1</del>',
            '/(?<!\*)\*([^\n*]+)\*(?!\*)/' => '<em>$1</em>',
            '/(?<!_)_([^\n_]+)_(?!_)/' => '<em>$1</em>',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        $text = nl2br($text, false);

        return strtr($text, $placeholders);
    }

    private static function safeUrl(string $url, bool $allowMailto): ?string {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return null;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '/') || str_starts_with($url, './') || str_starts_with($url, '../')) {
            return $url;
        }

        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        $allowedSchemes = $allowMailto ? ['http', 'https', 'mailto'] : ['http', 'https'];
        return in_array($scheme, $allowedSchemes, true) ? $url : null;
    }

    private static function escapeAttribute(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
?>
