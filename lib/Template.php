<?php
declare(strict_types=1);

namespace Lib;

class Template {
    private static array $vars = [];
    private static string $templatePath = '';

    public static function init(string $templatePath = ''): void {
        if (empty($templatePath)) {
            self::$templatePath = ROOT_PATH . '/templates';
        } else {
            self::$templatePath = $templatePath;
        }
    }

    public static function set(string $key, mixed $value): void {
        self::$vars[$key] = $value;
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
            return ob_get_clean();
        }

        return (string)$content;
    }

    public static function display(string $template): void {
        echo self::render($template);
    }

    public static function clear(): void {
        self::$vars = [];
    }
}
?>