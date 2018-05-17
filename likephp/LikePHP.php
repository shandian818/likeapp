<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/17
 * Time: 20:36
 */

namespace likephp;


class LikePHP
{
    private static $instance;

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->checkEnv();
        $this->initPath();
        $this->loadFunc();
        $this->registerAutoLoad();
    }

    private function checkEnv()
    {
        //校验php版本
        $php_version_limit = '7.0.0';
        if (version_compare(PHP_VERSION, $php_version_limit, '<')) {
            Console::getInstance()->error('php版本(' . PHP_VERSION . ')必须大于' . $php_version_limit);
            die();
        }
        $swoole_version_limit = '2.0.0';
        if (php_sapi_name() != 'cli') {
            Console::getInstance()->error('server必须在CLI模式下运行');
            die();
        }
        //2.校验swoole扩展
        if (!extension_loaded('swoole')) {
            Console::getInstance()->error('swoole扩展未安装');
            die();
        }
        //3.校验swoole版本
        $swoole_version = swoole_version();
        if (version_compare($swoole_version, $swoole_version_limit, '<')) {
            Console::getInstance()->error('swoole版本(' . $swoole_version . ')必须大于' . $swoole_version_limit);
            die();
        }
    }

    private function initPath()
    {
        defined('LIKE_PATH') or define('LIKE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
        defined('ROOT_PATH') or define('ROOT_PATH', realpath(LIKE_PATH . '/../') . DIRECTORY_SEPARATOR);
        defined('APPS_PATH') or define('APPS_PATH', ROOT_PATH . 'apps' . DIRECTORY_SEPARATOR);
        defined('CONF_PATH') or define('CONF_PATH', ROOT_PATH . 'conf' . DIRECTORY_SEPARATOR);
        defined('FUNC_PATH') or define('FUNC_PATH', ROOT_PATH . 'func' . DIRECTORY_SEPARATOR);
        defined('TMP_PATH') or define('TMP_PATH', ROOT_PATH . 'tmp' . DIRECTORY_SEPARATOR);
        defined('WEB_PATH') or define('WEB_PATH', ROOT_PATH . 'web' . DIRECTORY_SEPARATOR);
    }

    private function loadFunc($dir = '')
    {
        $dir = rtrim($dir ?: FUNC_PATH, DIRECTORY_SEPARATOR);
        $files = scandir($dir);
        $files = array_diff($files, ['.', '..']);
        if (!empty($files)) {
            foreach ($files as $file) {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    $this->loadFunc($path);
                } elseif (false !== strpos($file, '.php')) {
                    require_once $path;
                }
            }
        }
    }

    /**
     * 自动加载
     */
    private static function registerAutoLoad()
    {
        $name_map = [
            ['likephp', LIKE_PATH],
            ['apps', APPS_PATH]
        ];
        require_once __DIR__ . '/Loader.php';
        Loader::getInstance()->registerNamespaces($name_map);
        //处理composer自动加载
        $composer_autoload_file = ROOT_PATH . 'vendor/autoload.php';
        if (file_exists($composer_autoload_file)) {
            require_once $composer_autoload_file;
        }
    }

    public function run()
    {
        global $argv;
        $act_name = isset($argv[1]) ? strtolower($argv[1]) : null;
        $app_name = isset($argv[2]) ? strtolower($argv[2]) : null;
        $allow_act_list = ['start', 'stop', 'status', 'restart'];
        $allow_app_list = $this->getAllowAppList();
        if (!in_array($act_name, $allow_act_list) || !in_array($app_name, $allow_app_list)) {
            $this->serverHelp($allow_act_list, $allow_app_list);
        }
        call_user_func_array([$this, 'server' . ucfirst($act_name)], [$app_name]);
    }

    private function getAllowAppList()
    {
        $app_list = [];
        $files = scandir(CONF_PATH);
        $files = array_diff($files, ['.', '..']);
        if (!empty($files)) {
            foreach ($files as $file) {
                if (false !== strpos($file, '.php')) {
                    $app_name = strtolower(basename($file, '.php'));
                    $app_list[] = $app_name;
                }
            }
        }
        return $app_list;
    }

    private function serverHelp($allow_act_list, $allow_app_list)
    {
        $help_string = '' . PHP_EOL;
        $allow_action_cmd_string = implode(' | ', $allow_act_list);
        $allow_server_cmd_string = implode(' | ', $allow_app_list);
        $help_string .= '=====欢迎使用LikePHP服务=====' . PHP_EOL;
        $help_string .= '支持命令: ' . $allow_action_cmd_string . PHP_EOL;
        $help_string .= '支持应用: ' . $allow_server_cmd_string . PHP_EOL;
        $help_string .= '例子如下:' . PHP_EOL;
        $help_string .= 'php likephp [ ' . $allow_action_cmd_string . ' ] [ ' . $allow_server_cmd_string . ' ]:' . PHP_EOL;
        $help_string .= '例如:php likephp ' . $allow_act_list[0] . ' ' . $allow_app_list[0];
        Console::getInstance()->warn($help_string);
        die();
    }

    private function serverStart($app_name)
    {
        $pid = $this->getServerPid($app_name);
        if ($pid && \swoole_process::kill($pid, 0)) {
            Console::getInstance()->warn('LikePHP服务已开启');
            die();
        }
        Console::getInstance()->info('LikePHP服务正在开启...');
        $app_config = $this->loadAppConfig($app_name);
        $default_setting = [
            'daemonize' => 1,//守护进程
            'log_file' => TMP_PATH . 'server' . DIRECTORY_SEPARATOR . $app_name . '.log',
            'pid_file' => TMP_PATH . 'server' . DIRECTORY_SEPARATOR . $app_name . '.pid',
        ];
        $app_config['server']['setting'] = array_merge($default_setting, $app_config['server']['setting']);
        $server_config = $app_config['server'];
        $callback_name = '\\likephp\\event\\' . ucfirst($server_config['type']) . 'Event';
        Server::getInstance()->run($server_config, $callback_name);
    }

    private function serverStop($app_name)
    {
        $pid = $this->getServerPid($app_name);
        if ($pid) {
            Console::getInstance()->info('LikePHP服务关闭中...');
            if (\swoole_process::kill($pid, 0)) {
                \swoole_process::kill($pid, 15);
            }
            Console::getInstance()->info('LikePHP服务关闭成功');
            die();
        }
        Console::getInstance()->info('LikePHP服务未开启');
        die();
    }

    private function serverStatus($app_name)
    {
        $pid = $this->getServerPid($app_name);
        if ($pid && \swoole_process::kill($pid, 0)) {
            Console::getInstance()->info('LikePHP服务运行中！');
            die();
        }
        Console::getInstance()->info('LikePHP服务未开启');
        die();
    }

    private function serverRestart($app_name)
    {
        $pid = $this->getServerPid($app_name);
        if ($pid) {
            if (\swoole_process::kill($pid, 0)) {
                \swoole_process::kill($pid, 15);
            }
            Console::getInstance()->info('LikePHP服务关闭成功');
        }
        sleep(1);
        $this->serverStart($app_name);
    }

    private function loadAppConfig($app_name)
    {
        $config_file = CONF_PATH . $app_name . '.php';
        $config = require_once $config_file;
        Config::getInstance()->set($config);
        return $config;
    }

    private function getServerPid($app_name)
    {
        $pid_dir = TMP_PATH . 'server' . DIRECTORY_SEPARATOR;
        if (!is_dir($pid_dir)) {
            mkdir($pid_dir, 0777, true);
        }
        $pid_file = $pid_dir . $app_name . '.pid';
        $pid = @file_get_contents($pid_file);
        return $pid;
    }
}