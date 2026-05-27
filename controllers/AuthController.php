<?php
declare(strict_types=1);

namespace Controllers;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Session;
use Lib\Template;
use Lib\Response;
use Lib\Request;
use Models\MemberModel;
use Models\UsergroupModel;
use Models\SessionModel;
use Models\SettingModel;
use Lib\Permission;

class AuthController {
    public static function login(): void {
        Template::clear();
        if (Permission::isLoggedIn()) {
            Response::redirect('index.php');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ip = self::getClientIp();
            
            $interval = (int)SettingModel::get('login_ip_interval', '5');
            if ($interval > 0) {
                $lastAttempt = Session::get('last_login_attempt_' . $ip, 0);
                if (time() - $lastAttempt < $interval) {
                    $error = '请稍后再试，距离上次尝试不足 ' . $interval . ' 秒';
                }
            }

            if (empty($error)) {
                $maxFail = (int)SettingModel::get('login_max_fail', '10');
                if ($maxFail > 0) {
                    $failCount = Session::get('login_fail_count_' . $ip, 0);
                    $failTime = Session::get('login_fail_time_' . $ip, 0);
                    
                    if (time() - $failTime < 86400) {
                        if ($failCount >= $maxFail) {
                            $error = '登录失败次数过多，请24小时后再试';
                        }
                    } else {
                        Session::delete('login_fail_count_' . $ip);
                        Session::delete('login_fail_time_' . $ip);
                    }
                }
            }

            if (empty($error)) {
                $username = Request::postRaw('username');
                $password = Request::postRaw('password');

                if (MemberModel::checkPassword($username, $password)) {
                    $member = MemberModel::getByUsername($username);
                    if ((int)($member['status'] ?? 0) === -1) {
                        $error = '账号已被禁止登录';
                    } else {
                        Session::regenerateId();
                        Session::set('uid', $member['uid']);
                        Session::set('username', $member['username']);
                        
                        Session::delete('login_fail_count_' . $ip);
                        Session::delete('login_fail_time_' . $ip);
                        
                        SessionModel::updateOnline($member['uid'], $member['gid'], 0);
                        
                        Response::redirect('index.php');
                    }
                } else {
                    $failCount = Session::get('login_fail_count_' . $ip, 0) + 1;
                    Session::set('login_fail_count_' . $ip, $failCount);
                    Session::set('login_fail_time_' . $ip, time());
                    $error = '用户名或密码错误';
                }
            }
            
            Session::set('last_login_attempt_' . $ip, time());
        }

        Template::set('title', '登录');
        Template::set('error', $error);
        Template::display('auth/login');
    }

    public static function register(): void {
        Template::clear();
        if (Permission::isLoggedIn()) {
            Response::redirect('index.php');
        }

        $closeRegister = SettingModel::get('close_register', '0');
        if ($closeRegister === '1') {
            $error = '当前站点已关闭注册';
            Template::set('title', '注册');
            Template::set('error', $error);
            Template::display('auth/register');
            return;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = Request::postString('username');
            $email = Request::postString('email');
            $password = Request::postRaw('password');
            $confirmPassword = Request::postRaw('confirm_password');

            if (empty($username) || empty($email) || empty($password)) {
                $error = '请填写所有必填项';
            } elseif ($password !== $confirmPassword) {
                $error = '两次密码不一致';
            } elseif (strlen($password) < 6) {
                $error = '密码长度至少6位';
            } else {
                $reservedKeywords = SettingModel::get('username_reserved', '');
                if (!empty($reservedKeywords)) {
                    foreach (explode("\n", $reservedKeywords) as $keyword) {
                        $keyword = trim($keyword);
                        if (empty($keyword)) continue;
                        if (stripos($username, $keyword) !== false) {
                            $error = '用户名包含保留关键字';
                            break;
                        }
                    }
                }

                if (empty($error)) {
                    $allowedDomains = SettingModel::get('allowed_email_domains', '');
                    if (!empty($allowedDomains)) {
                        $emailDomain = substr(strrchr($email, '@'), 1);
                        $allowed = false;
                        foreach (explode("\n", $allowedDomains) as $domain) {
                            $domain = trim($domain);
                            if (empty($domain)) continue;
                            if (strcasecmp($emailDomain, $domain) === 0) {
                                $allowed = true;
                                break;
                            }
                        }
                        if (!$allowed) {
                            $error = '不允许使用该邮箱域名注册';
                        }
                    }
                }

                $ip = self::getClientIp();
                
                if (empty($error)) {
                    $blockIps = SettingModel::get('block_register_ips', '');
                    if (!empty($blockIps)) {
                        foreach (explode("\n", $blockIps) as $pattern) {
                            $pattern = trim($pattern);
                            if (empty($pattern)) continue;
                            if (self::matchIpPattern($ip, $pattern)) {
                                $error = '您的IP地址已被禁止注册';
                                break;
                            }
                        }
                    }
                }

                if (empty($error)) {
                    $interval = (int)SettingModel::get('register_ip_interval', '3600');
                    if ($interval > 0) {
                        $lastRegister = Session::get('last_register_' . $ip, 0);
                        if (time() - $lastRegister < $interval) {
                            $remaining = $interval - (time() - $lastRegister);
                            $error = '该IP注册过于频繁，请等待 ' . self::formatTime($remaining) . ' 后再试';
                        }
                    }
                }

                if (empty($error)) {
                    if (MemberModel::getByUsername($username)) {
                        $error = '用户名已存在';
                    } elseif (MemberModel::getByEmail($email)) {
                        $error = '邮箱已被注册';
                    } else {
                        $defaultGroup = UsergroupModel::getRegisterDefaultGroup((int)SettingModel::get('register_default_gid', '0'));
                        if (!$defaultGroup) {
                            $error = '注册默认用户组未配置，请联系管理员';
                        }
                    }

                    if (empty($error)) {
                        $uid = MemberModel::register([
                            'username' => $username,
                            'email' => $email,
                            'password' => $password,
                            'gid' => (int)$defaultGroup['gid'],
                        ]);

                        Session::set('last_register_' . $ip, time());
                        Session::regenerateId();
                        Session::set('uid', $uid);
                        Session::set('username', $username);
                        SessionModel::updateOnline($uid, (int)$defaultGroup['gid'], 0);

                        Response::redirect('index.php');
                    }
                }
            }
        }

        Template::set('title', '注册');
        Template::set('error', $error);
        Template::display('auth/register');
    }

    public static function logout(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('index.php');
        }

        Session::clear();
        Response::redirect('index.php');
    }

    private static function getClientIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private static function matchIpPattern(string $ip, string $pattern): bool {
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '\d+', $pattern);
        return preg_match("/^$pattern$/", $ip) === 1;
    }

    private static function formatTime(int $seconds): string {
        if ($seconds < 60) {
            return $seconds . '秒';
        } elseif ($seconds < 3600) {
            return (int)($seconds / 60) . '分钟';
        } else {
            return (int)($seconds / 3600) . '小时';
        }
    }
}
?>
