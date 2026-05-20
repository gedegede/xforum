<?php
declare(strict_types=1);

namespace Lib;

use League\CommonMark\CommonMarkConverter;

final class MarkdownHelper {
    private static ?CommonMarkConverter $converter = null;

    public static function parse(string $markdown): string {
        if ($markdown === '') {
            return '';
        }

        return self::converter()->convert($markdown)->getContent();
    }

    public static function strip(string $markdown): string {
        $html = self::parse($markdown);

        $text = html_entity_decode(
            strip_tags($html),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        return trim(
            preg_replace('/\s+/u', ' ', $text)
        );
    }

    private static function converter(): CommonMarkConverter {
        if (self::$converter !== null) {
            return self::$converter;
        }

        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];

        self::$converter = new CommonMarkConverter($config);

        return self::$converter;
    }
}
?>