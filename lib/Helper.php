<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class Helper {
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

        $html = '<div class="pagination-container">';

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

        $html .= '<div class="pagination hide-mobile">';

        if ($page > 1) {
            $prevUrl = self::addPageParam($baseUrl, 1);
            $html .= '<a href="' . htmlspecialchars($prevUrl) . '">首页</a>';

            $prevUrl = self::addPageParam($baseUrl, $page - 1);
            $html .= '<a href="' . htmlspecialchars($prevUrl) . '">上一页</a>';
        }

        foreach ($showPages as $pageNum) {
            if ($pageNum === '...') {
                $html .= '<span class="pagination-ellipsis">...</span>';
            } else {
                $active = $pageNum == $page ? 'class="active"' : '';
                $pageUrl = self::addPageParam($baseUrl, $pageNum);
                $html .= '<a href="' . htmlspecialchars($pageUrl) . '" ' . $active . '>' . $pageNum . '</a>';
            }
        }

        if ($page < $pages) {
            $nextUrl = self::addPageParam($baseUrl, $page + 1);
            $html .= '<a href="' . htmlspecialchars($nextUrl) . '">下一页</a>';

            $lastUrl = self::addPageParam($baseUrl, $pages);
            $html .= '<a href="' . htmlspecialchars($lastUrl) . '">最后</a>';
        }

        $html .= '</div>';

        $html .= '<div class="pagination show-mobile-only">';

        if ($page > 1) {
            $prevUrl = self::addPageParam($baseUrl, $page - 1);
            $html .= '<a href="' . htmlspecialchars($prevUrl) . '" class="pagination-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                上一页
            </a>';
        }

        $html .= '<span class="pagination-info">' . $page . ' / ' . $pages . '</span>';

        if ($page < $pages) {
            $nextUrl = self::addPageParam($baseUrl, $page + 1);
            $html .= '<a href="' . htmlspecialchars($nextUrl) . '" class="pagination-btn">
                下一页
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>';
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
