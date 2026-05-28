<?php
declare(strict_types=1);

namespace Lib;

use Models\SettingModel;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class Helper {
    private const BUTTON_BASE = 'btn';
    private const BUTTON_SOFT = self::BUTTON_BASE . ' btn-soft';
    private const BUTTON_SOFT_SM = self::BUTTON_SOFT . ' btn-sm';

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

        $html = '<div class="pagination">';

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
        $html .= '<div class="pagination-list hide-mobile">';

        if ($page > 1) {
            $prevUrl = self::addPageParam($baseUrl, 1);
            $html .= '<a href="' . htmlspecialchars($prevUrl) . '" class="pagination-item">首页</a>';

            $prevUrl = self::addPageParam($baseUrl, $page - 1);
            $html .= '<a href="' . htmlspecialchars($prevUrl) . '" class="pagination-item">上一页</a>';
        }

        foreach ($showPages as $pageNum) {
            if ($pageNum === '...') {
                $html .= '<span class="pagination-ellipsis">' . $pageNum . '</span>';
            } else {
                $active = $pageNum == $page ? 'active' : '';
                $pageUrl = self::addPageParam($baseUrl, $pageNum);
                $html .= '<a href="' . htmlspecialchars($pageUrl) . '" class="pagination-item ' . $active . '">' . $pageNum . '</a>';
            }
        }

        if ($page < $pages) {
            $nextUrl = self::addPageParam($baseUrl, $page + 1);
            $html .= '<a href="' . htmlspecialchars($nextUrl) . '" class="pagination-item">下一页</a>';

            $lastUrl = self::addPageParam($baseUrl, $pages);
            $html .= '<a href="' . htmlspecialchars($lastUrl) . '" class="pagination-item">最后</a>';
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
        if (strpos($url, 'page=') !== false) {
            return preg_replace('/([?&])page=\d+/', '$1page=' . $page, $url);
        }
        return $url . $separator . 'page=' . $page;
    }

    public static function formatTime(int $timestamp, ?bool $human = null): string {
        if ($timestamp <= 0) {
            return '';
        }

        if ($human === null) {
            $human = SettingModel::get('human_time', '0') === '1';
        }

        if ($human) {
            return self::formatHumanTime($timestamp);
        }

        $format = SettingModel::get('date_format', 'Y-m-d H:i:s');
        return date($format, $timestamp);
    }

    private static function formatHumanTime(int $timestamp): string {
        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 0) {
            $diff = abs($diff);
            if ($diff < 60) {
                return '马上';
            } elseif ($diff < 3600) {
                return (int)ceil($diff / 60) . '分钟后';
            } elseif ($diff < 86400) {
                return (int)ceil($diff / 3600) . '小时后';
            } elseif ($diff < 604800) {
                return (int)ceil($diff / 86400) . '天后';
            }

            return date('Y-m-d', $timestamp);
        }

        if ($diff < 60) {
            return '刚刚';
        } elseif ($diff < 3600) {
            return (int)($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return (int)($diff / 3600) . '小时前';
        } elseif ($diff < 604800) {
            return (int)($diff / 86400) . '天前';
        } else {
            return date('Y-m-d', $timestamp);
        }
    }
}
