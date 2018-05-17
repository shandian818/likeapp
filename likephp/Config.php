<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/5/17
 * Time: 21:59
 */

namespace likephp;


class Config
{
    private static $instance;

    private $configCache;//配置信息

    /**
     * 获取当前对象的实例-单例
     * @return mixed
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    /**
     * 设置配置信息
     * @param $name
     * @param null $value 为null时,把name当作配置存入
     * @return bool
     */
    public function set($name, $value = null)
    {
        $config_data = $this->getConfigFromCache();
        $config_array = $this->updateConfig($name, $value, $config_data);
        $set_status = $this->setConfigToCache($config_array);
        return $set_status;
    }

    /**
     * @param string $name 配置项（可英文句号连接）
     * @param null $default_value
     * @return null
     */
    public function get($name = '', $default_value = null)
    {
        $config_data = $this->getConfigFromCache();
        $value = $this->getConfigValueByName($name, $config_data);
        return !is_null($value) ? $value : $default_value;
    }

    /**
     * 配置写入缓存
     * User: jiangxijun
     * Email: jiang818@qq.com
     * Qq: 263088049
     * @param $config_array
     * @return bool
     */
    private function setConfigToCache($config_array)
    {
        return ($this->configCache = serialize($config_array)) ? true : false;
    }

    /**
     * 缓存中读取配置（目前存在静态变量）
     * User: jiangxijun
     * Email: jiang818@qq.com
     * Qq: 263088049
     * @return mixed
     */
    private function getConfigFromCache()
    {
        return unserialize($this->configCache);
    }

    /**
     * 更新配置信息的变量
     * @param $name
     * @param $value
     * @param $config_data
     * @return array
     */
    private function updateConfig($name, $value, $config_data)
    {
        $configArray = [];
        if ('' === $name) {
            $configArray = $value;
        } elseif (is_string($name)) {
            $nameArr = explode('.', $name);
            $item = &$config_data;
            for ($i = 0; $i < count($nameArr) - 1; $i++) {
                if (!key_exists($nameArr[$i], $item) || (key_exists($nameArr[$i], $item) && !is_array($item[$nameArr[$i]]))) {
                    $item[$nameArr[$i]] = [];
                }
                $item = &$item[$nameArr[$i]];
            }
            if (is_null($value)) {
                //value为null，清除key为name的项
                unset($item[$nameArr[$i]]);
            } else {
                $item[$nameArr[$i]] = $value;
            }
            $configArray = $config_data;
        } else if (is_array($name)) {
            $configArray = $name;
        }
        return $configArray;
    }

    /**
     * 根据name获取配置的值
     * @param $name
     * @param $config_data
     * @return null
     */
    private function getConfigValueByName($name, $config_data)
    {
        $value = null;//默认为null
        if ('' === $name) {
            $value = $config_data;
        } elseif (is_string($name)) {
            $name_array = explode('.', $name);
            $item = $config_data;
            for ($i = 0; $i < count($name_array); $i++) {
                if (isset($item[$name_array[$i]])) {
                    $item = &$item[$name_array[$i]];
                } else {
                    $item = null;
                }
            }
            $value = $item;
        }
        return $value;
    }
}