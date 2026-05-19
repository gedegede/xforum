<?php
class Template {
    private static $vars = [];
    private static $templatePath = '';

    public static function init($templatePath = '') {
        if (empty($templatePath)) {
            self::$templatePath = ROOT_PATH . '/templates';
        } else {
            self::$templatePath = $templatePath;
        }
    }

    public static function set($key, $value) {
        self::$vars[$key] = $value;
    }

    public static function render($template, $layout = 'layout/base') {
        $templatePath = self::$templatePath;

        $renderTemplate = function () use ($template, $templatePath) {
            extract(self::$vars, EXTR_SKIP);
            include $templatePath . '/' . $template . '.php';
        };

        ob_start();
        $renderTemplate();
        $content = ob_get_clean();

        if ($layout) {
            $renderLayout = function () use ($layout, $templatePath, $content) {
                extract(self::$vars, EXTR_SKIP);
                include $templatePath . '/' . $layout . '.php';
            };

            ob_start();
            $renderLayout();
            return ob_get_clean();
        }

        return $content;
    }

    public static function display($template) {
        echo self::render($template);
    }

    public static function clear() {
        self::$vars = [];
    }
}