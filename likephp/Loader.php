<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/17
 * Time: 21:41
 */

namespace likephp;


class Loader
{
    private static $instance;

    private static $classMap = [];//匹配的类与路径关系

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    private function __construct()
    {
        spl_autoload_register([$this, 'autoLoad']);
    }

    /**
     * 实现自动加载
     * @param string $class
     * @return bool
     */
    private function autoLoad($class)
    {
        $class_tmp = $class;
        while (false !== $pos = strrpos($class_tmp, '\\')) {
            $class_tmp = substr($class, 0, $pos + 1);
            $real_class = substr($class, $pos + 1);
            if (isset(self::$classMap[$class_tmp]) !== false) {
                foreach (self::$classMap[$class_tmp] as $base_dir) {
                    $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $real_class) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                        return true;
                    }
                }
            }
            $class_tmp = rtrim($class_tmp, '\\');
        }
        return false;
    }

    /**
     * 添加一个命名空间与路径匹配
     * @param string $namespace 命名空间
     * @param string $base_dir 对应路径
     * @param bool $is_first
     */
    public function addNamespace($namespace, $base_dir, $is_first = false)
    {
        $namespace = trim($namespace, '\\') . '\\';
        if (!isset(self::$classMap[$namespace])) {
            self::$classMap[$namespace] = [];
        }
        if ($is_first) {
            //优先
            array_unshift(self::$classMap[$namespace], $base_dir);
        } else {
            array_push(self::$classMap[$namespace], $base_dir);
        }
    }

    /**
     * 批量注册命名空间
     * @param array $name_map
     */
    public function registerNamespaces($name_map = [])
    {
        if (is_array($name_map) && !empty($name_map)) {
            foreach ($name_map as $value) {
                if (!isset($value[0]) || !isset($value[1])) {
                    die('批量注册命名空间失败-无有效对应关系');
                }
                $is_first = isset($value[2]) ? $value[2] : false;
                $this->addNamespace($value[0], $value[1], $is_first);
            }
        }
    }
}