<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class Helper {
    private const BUTTON_BASE = 'inline-flex items-center justify-center gap-1.5 h-control px-4 border rounded bg-panel text-text text-base font-medium cursor-pointer transition-all whitespace-nowrap hover:bg-hover active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed';
    private const BUTTON_SOFT = self::BUTTON_BASE . ' bg-soft border-border text-text hover:bg-hover';
    private const BUTTON_SOFT_SM = self::BUTTON_SOFT . ' h-control-sm px-3 text-sm';

    public static function getAvatarInitial(string $username): string {
        if (empty($username)) {
            return '?';
        }

        return mb_strtoupper(mb_substr($username, 0, 1, 'UTF-8'), 'UTF-8');
    }

    public static function renderPagination(int $page, int $pages, string $baseUrl): string {
        if ($pages <= 1) {
            return '';
        }

        $html = '<div class="flex items-center justify-center gap-1 flex-wrap py-4">';

        $visibleCount = 5;
        $halfVisible = floor($visibleCount / 2);
        $showPages = [];

        if ($pages <= $visibleCount) {
            for ($i = 1; $i <= $pages; $i++) {
                $showPages[] = $i;
            }
        } else {
            if ($page <= $halfVisible + 1) {
                for ($i = 1; $i <= $visibleCount; $i++) {
                    $showPages[] = $i;
                }
                $showPages[] = '...';
                $showPages[] = $pages;
            } elseif ($page >= $pages - $halfVisible) {
                $showPages[] = 1;
                $showPages[] = '...';
                for ($i = $pages - $visibleCount + 1; $i <= $pages; $i++) {
                    $showPages[] = $i;
                }
            } else {
                $showPages[] = 1;
                $showPages[] = '...';
                for ($i = $page - $halfVisible; $i <= $page + $halfVisible; $i++) {
                    $showPages[] = $i;
                }
                $showPages[] = '...';
                $showPages[] = $pages;
            }
        }

        // Desktop pagination
        $html .= '<div class="flex items-center gap-1 flex-wrap hide-mobile">';

        if ($page > 1) {
            $prevUrl = self::addPageParam($baseUrl, 1);
            $html .= '<a href="' . htmlspecialchars($prevUrl) . '" class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm text-sub hover:bg-hover hover:text-text transition-colors">首页</a>';

            $prevUrl = self::addPageParam($baseUrl, $page - 1);
            $html .= '<a href="' . htmlspecialchars($prevUrl) . '" class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm text-sub hover:bg-hover hover:text-text transition-colors">上一页</a>';
        }

        foreach ($showPages as $pageNum) {
            if ($pageNum === '...') {
                $html .= '<span class="inline-flex items-center justify-center min-w-8 h-8 px-2 text-sm text-muted">' . $pageNum . '</span>';
            } else {
                $active = $pageNum == $page ? 'bg-primary text-white' : 'text-sub hover:bg-hover hover:text-text';
                $pageUrl = self::addPageParam($baseUrl, $pageNum);
                $html .= '<a href="' . htmlspecialchars($pageUrl) . '" class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm transition-colors ' . $active . '">' . $pageNum . '</a>';
            }
        }

        if ($page < $pages) {
            $nextUrl = self::addPageParam($baseUrl, $page + 1);
            $html .= '<a href="' . htmlspecialchars($nextUrl) . '" class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm text-sub hover:bg-hover hover:text-text transition-colors">下一页</a>';

            $lastUrl = self::addPageParam($baseUrl, $pages);
            $html .= '<a href="' . htmlspecialchars($lastUrl) . '" class="inline-flex items-center justify-center min-w-8 h-8 px-2 rounded text-sm text-sub hover:bg-hover hover:text-text transition-colors">最后</a>';
        }

        $html .= '</div>';

        // Mobile pagination
        $html .= '<div class="flex items-center justify-between gap-2 mobile-only">';

        if ($page > 1) {
            $prevUrl = self::addPageParam($baseUrl, $page - 1);
            $html .= '<a href="' . htmlspecialchars($prevUrl) . '" class="' . self::BUTTON_SOFT_SM . '">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                上一页
            </a>';
        } else {
            $html .= '<span></span>';
        }

        $html .= '<span class="text-muted">' . $page . ' / ' . $pages . '</span>';

        if ($page < $pages) {
            $nextUrl = self::addPageParam($baseUrl, $page + 1);
            $html .= '<a href="' . htmlspecialchars($nextUrl) . '" class="' . self::BUTTON_SOFT_SM . '">
                下一页
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>';
        } else {
            $html .= '<span></span>';
        }

        $html .= '</div></div>';

        return $html;
    }

    private static function addPageParam(string $url, int $page): string {
        $separator = strpos($url, '?') !== false ? '&' : '?';
        if ($page == 1) {
            return preg_replace('/([?&])page=\d+/', '$1', rtrim($url, '?'));
        }
        if (strpos($url, 'page=') !== false) {
            return preg_replace('/([?&])page=\d+/', '$1page=' . $page, $url);
        }
        return $url . $separator . 'page=' . $page;
    }
}
