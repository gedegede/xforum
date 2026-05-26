<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;

class CreditModel {
    const TABLE_ACTION = 'next_action';
    const TABLE_CREDIT = 'next_credit';

    public const ACTION_SIGNIN = 'Signin';
    public const ACTION_THREAD_CREATE = 'ThreadCreate';
    public const ACTION_THREAD_REPLY = 'ThreadReply';
    public const ACTION_PM_SEND = 'PmSend';
    public const ACTION_THREAD_REPORT = 'ThreadReport';
    public const ACTION_USERNAME_CHANGE = 'UsernameChange';

    private const PAGE_SIZE = 20;
    private const DAY_SECONDS = 86400;

    private const DEFAULT_RULES = [
        self::ACTION_SIGNIN => ['credit' => 1, 'daily_max' => 0],
        self::ACTION_THREAD_CREATE => ['credit' => 1, 'daily_max' => 10],
        self::ACTION_THREAD_REPLY => ['credit' => 1, 'daily_max' => 10],
        self::ACTION_PM_SEND => ['credit' => 0, 'daily_max' => 0],
        self::ACTION_THREAD_REPORT => ['credit' => 0, 'daily_max' => 0],
        self::ACTION_USERNAME_CHANGE => ['credit' => 0, 'daily_max' => 0],
    ];

    public static function getDefaultRuleText(): string {
        return "ThreadCreate,1,10\nThreadReply,1,10\nPmSend,0,0\nThreadReport,0,0";
    }

    public static function getActionLabels(): array {
        return [
            self::ACTION_SIGNIN => '每日签到',
            self::ACTION_THREAD_CREATE => '发布主题',
            self::ACTION_THREAD_REPLY => '回复主题',
            self::ACTION_PM_SEND => '发送私信',
            self::ACTION_THREAD_REPORT => '举报主题',
            self::ACTION_USERNAME_CHANGE => '修改用户名',
        ];
    }

    public static function getRule(string $action): array {
        $rules = self::getRules();
        return $rules[$action] ?? (self::DEFAULT_RULES[$action] ?? ['credit' => 0, 'daily_max' => 0]);
    }

    public static function getRules(): array {
        $rules = self::DEFAULT_RULES;
        $ruleText = SettingModel::get('credit_rules', self::getDefaultRuleText());

        foreach (preg_split('/\r\n|\r|\n/', $ruleText) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = array_map('trim', explode(',', $line));
            if (count($parts) < 2) {
                continue;
            }

            $action = $parts[0];
            if (!isset($rules[$action])) {
                continue;
            }

            $rules[$action] = [
                'credit' => (int)$parts[1],
                'daily_max' => isset($parts[2]) ? max(0, (int)$parts[2]) : 0,
            ];
        }

        return $rules;
    }

    public static function canApply(string $action, int $uid): bool {
        $amount = self::getPendingAmount($action, $uid);
        if ($amount >= 0) {
            return true;
        }

        $member = MemberModel::get($uid);
        return (int)($member['credit'] ?? 0) + $amount >= 0;
    }

    public static function getInsufficientMessage(string $action): string {
        $amount = abs(self::getRule($action)['credit'] ?? 0);
        $label = self::getActionLabels()[$action] ?? $action;
        return $amount > 0 ? "{$label}需要 {$amount} 金币，余额不足" : '金币余额不足';
    }

    public static function apply(string $action, int $uid, string $message, string $url = ''): int {
        if ($uid <= 0) {
            return 0;
        }

        $amount = self::getPendingAmount($action, $uid);
        if ($amount === 0) {
            return 0;
        }

        if ($amount < 0 && !self::canApply($action, $uid)) {
            return 0;
        }

        $did = self::changeCredit($uid, $amount, $message, $url);
        if ($did <= 0) {
            return 0;
        }
        self::recordAction($uid, $action, $amount);
        return $did;
    }

    public static function applyWithId(string $action, int $uid, string $message, string $url = ''): int {
        if ($uid <= 0) {
            return 0;
        }

        $amount = self::getPendingAmount($action, $uid);
        if ($amount === 0) {
            return 0;
        }

        if ($amount < 0 && !self::canApply($action, $uid)) {
            return 0;
        }

        $result = self::changeCredit($uid, $amount, $message, $url);
        if ($result['did'] <= 0) {
            return 0;
        }
        self::recordAction($uid, $action, $amount);
        return $result['did'];
    }

    public static function updateCreditUrl(int $did, string $url): void {
        if ($did <= 0 || $url === '') {
            return;
        }

        Database::query(
            "UPDATE " . self::TABLE_CREDIT . " SET url = :url WHERE did = :did",
            ['url' => $url, 'did' => $did]
        );
    }

    public static function signin(int $uid): array {
        if ($uid <= 0) {
            return ['success' => false, 'message' => '请先登录', 'credit' => 0];
        }

        $member = MemberModel::get($uid);
        if (!$member) {
            return ['success' => false, 'message' => '用户不存在', 'credit' => 0];
        }

        if (self::isSameDay((int)($member['signin_time'] ?? 0), time())) {
            return ['success' => false, 'message' => '今天已经签到过了', 'credit' => 0];
        }

        [$min, $max] = self::getSigninRange();
        $amount = random_int($min, $max);
        if ($amount > 0) {
            self::changeCredit($uid, $amount, '每日签到', 'index.php?c=member&a=profile&uid=' . $uid . '&type=credits');
            self::recordAction($uid, self::ACTION_SIGNIN, $amount);
        }

        MemberModel::updateSigninTime($uid, time());
        return [
            'success' => true,
            'message' => $amount > 0 ? "签到成功，获得 {$amount} 金币" : '签到成功',
            'credit' => $amount,
        ];
    }

    public static function getSigninRange(): array {
        $raw = trim(SettingModel::get('signin_credit_range', '1,5'));
        $parts = array_map('intval', preg_split('/[,，\s]+/', $raw));
        $min = max(0, $parts[0] ?? 1);
        $max = max(0, $parts[1] ?? $min);

        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }

        return [$min, $max];
    }

    public static function hasSignedToday(int $uid): bool {
        $member = MemberModel::get($uid);
        return $member ? self::isSameDay((int)($member['signin_time'] ?? 0), time()) : false;
    }

    public static function getUserCredits(int $uid, int $page = 1): array {
        $offset = (max(1, $page) - 1) * self::PAGE_SIZE;
        return Database::fetchAll(
            "SELECT * FROM " . self::TABLE_CREDIT . " WHERE uid = :uid ORDER BY did DESC LIMIT :limit OFFSET :offset",
            ['uid' => $uid, 'limit' => self::PAGE_SIZE, 'offset' => $offset]
        );
    }

    public static function getUserCreditCount(int $uid): int {
        $result = Database::fetch(
            "SELECT COUNT(*) as count FROM " . self::TABLE_CREDIT . " WHERE uid = :uid",
            ['uid' => $uid]
        );
        return (int)($result['count'] ?? 0);
    }

    private static function getPendingAmount(string $action, int $uid): int {
        $rule = self::getRule($action);
        $amount = (int)($rule['credit'] ?? 0);
        if ($amount <= 0) {
            return $amount;
        }

        $dailyMax = (int)($rule['daily_max'] ?? 0);
        if ($dailyMax <= 0) {
            return 0;
        }

        $row = self::getActionRow($uid, $action);
        $used = 0;
        if ($row && time() - (int)$row['dateline'] < self::DAY_SECONDS) {
            $used = max(0, (int)$row['num']);
        }

        return max(0, min($amount, $dailyMax - $used));
    }

    private static function recordAction(int $uid, string $action, int $amount): void {
        if ($amount === 0) {
            return;
        }

        $now = time();
        $row = self::getActionRow($uid, $action);
        if (!$row) {
            Database::insert(self::TABLE_ACTION, [
                'uid' => $uid,
                'action' => $action,
                'num' => $amount,
                'dateline' => $now,
            ]);
            return;
        }

        $num = ($now - (int)$row['dateline'] < self::DAY_SECONDS) ? (int)$row['num'] + $amount : $amount;
        Database::query(
            "UPDATE " . self::TABLE_ACTION . " SET num = :num, dateline = :dateline WHERE uid = :uid AND action = :action",
            ['num' => $num, 'dateline' => $now, 'uid' => $uid, 'action' => $action]
        );
    }

    private static function getActionRow(int $uid, string $action): ?array {
        return Database::fetch(
            "SELECT * FROM " . self::TABLE_ACTION . " WHERE uid = :uid AND action = :action",
            ['uid' => $uid, 'action' => $action]
        );
    }

    private static function changeCredit(int $uid, int $credit, string $message, string $url = ''): int {
        if (!MemberModel::changeCredit($uid, $credit)) {
            return 0;
        }

        return Database::insert(self::TABLE_CREDIT, [
            'uid' => $uid,
            'credit' => $credit,
            'dateline' => time(),
            'message' => $message,
            'url' => $url,
        ]);
    }

    private static function isSameDay(int $timestamp, int $now): bool {
        return $timestamp > 0 && date('Y-m-d', $timestamp) === date('Y-m-d', $now);
    }
}
?>
