<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class Template {
    private static array $vars = [];
    private static string $templatePath = '';
    private const VAR_PREFIX = 'template_';

    public static function init(string $templatePath = ''): void {
        if (empty($templatePath)) {
            self::$templatePath = ROOT_PATH . '/templates';
        } else {
            self::$templatePath = $templatePath;
        }
    }

    public static function set(string $key, mixed $value): void {
        self::$vars[self::VAR_PREFIX . $key] = $value;
    }

    public static function render(string $template, string $layout = 'layout/base'): string {
        $templatePath = self::$templatePath;

        $renderTemplate = function () use ($template, $templatePath): void {
            extract(self::$vars, EXTR_SKIP);
            include $templatePath . '/' . $template . '.php';
        };

        ob_start();
        $renderTemplate();
        $content = ob_get_clean();

        if ($layout) {
            $renderLayout = function () use ($layout, $templatePath, $content): void {
                extract(self::$vars, EXTR_SKIP);
                include $templatePath . '/' . $layout . '.php';
            };

            ob_start();
            $renderLayout();
            return self::injectCsrfFields((string)ob_get_clean());
        }

        return self::injectCsrfFields((string)$content);
    }

    public static function display(string $template): void {
        echo self::render($template);
    }

    public static function clear(): void {
        self::$vars = [];
    }

    private static function injectCsrfFields(string $html): string {
        $field = CsrfHelper::field();
        if ($field === '') {
            return $html;
        }

        return (string)preg_replace_callback(
            '~<form\b[^>]*\bmethod\s*=\s*([\'"]?)post\1[^>]*>~i',
            static function (array $matches) use ($field): string {
                $formTag = $matches[0];
                return $formTag . $field;
            },
            $html
        );
    }
}
?>
