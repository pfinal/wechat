<?php
/**
 *   Copyright (c) [2019] [Yanlongli <jobs@yanlongli.com>]
 *   [Wechat] is licensed under the Mulan PSL v1.
 *   You can use this software according to the terms and conditions of the Mulan PSL v1.
 *   You may obtain a copy of Mulan PSL v1 at:
 *       http://license.coscl.org.cn/MulanPSL
 *   THIS SOFTWARE IS PROVIDED ON AN "AS IS" BASIS, WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO NON-INFRINGEMENT, MERCHANTABILITY OR FIT FOR A PARTICULAR
 *   PURPOSE.
 *   See the Mulan PSL v1 for more details.
 *
 *   Author: Yanlongli <jobs@yanlongli.com>
 *   Date:   2019/11/14
 *   IDE:    PhpStorm
 *   Desc:  配置管理
 */

namespace yanlongli\wechat\support;


class Config
{
    //配置集合
    protected static $config = null;
    //排除文件列表
    protected static array $exclude = [];

    /**
     * 获取配置参数
     * 兼容 key.key
     * key. 获取key下的所有数据 value
     *
     * @param string $name
     * @param mixed $default
     * @param mixed $config
     *
     * @return array|mixed|null
     */
    public static function get($name = '', $default = null, $config = null)
    {
        if (is_null($config)) {
            $config = static::$config;
        }
        if (isset($config[$name])) {
            return $config[$name];
        }
        $name = explode('.', $name);

        if (1 === count($name)) {
            if ('' === trim($name[0])) {
                return $config;
            }

            return isset($config[$name[0]]) ? $config[$name[0]] : $default;
        } else {

            for ($i = 1; $i < count($name); $i++) {
                $_name = implode('.', array_slice($name, 0, count($name) - $i));
                if (isset($config[$_name])) {
                    return static::get(implode('.', array_slice($name, count($name) - $i)), $default, $config[$_name]);
                }
            }

            if (isset($config[$name[0]])) {
                $newName = $name[0];
                unset($name[0]);
                $name = implode('.', $name);
            } else {
                return $default;
            }

            return static::get($name, $default, $config[$newName]);
        }
    }

    /**
     * 加载配置文件 已支持递归合并数组
     * @param $_config_load_config_file_path
     * @param null $_config_load_config_file_config
     * @return array
     */
    public static function loadConfigFile($_config_load_config_file_path, &$_config_load_config_file_config = null)
    {


        if (is_null($_config_load_config_file_config)) {
            $_config_load_config_file_config = &static::$config;
        }
        if (is_file($_config_load_config_file_path)) {
            //文件伪装成文件夹文件夹
            $_config_load_config_basename = basename($_config_load_config_file_path);
            $_config_load_config_temp[] = $_config_load_config_basename;
            if ('-local.php' !== substr($_config_load_config_basename, -10)) {
                //支持本地化配置文件
                $_config_load_config_temp[] = substr($_config_load_config_basename, 0, -4) . '-local' . substr($_config_load_config_basename, -4);
            }

            $_config_load_config_file_path = substr($_config_load_config_file_path, 0, -strlen($_config_load_config_basename));
        } else {
            //读取文件夹
            $_config_load_config_temp = scandir($_config_load_config_file_path);
        }
        // 反向排序文件顺序 因为 -local.php 的 - 比. 的优先级高
        rsort($_config_load_config_temp);
        //遍历文件夹
        foreach ($_config_load_config_temp as $_config_load_config_foreach_value) {
            $_config_load_config_foreach_file_path = $_config_load_config_file_path . '/' . $_config_load_config_foreach_value;
            if (is_dir($_config_load_config_foreach_file_path)) {
                //忽略子目录
                if (('.' == $_config_load_config_foreach_value) || ('..' == $_config_load_config_foreach_value)) {//判断是否为系统隐藏的文件.和..  如果是则跳过否则就继续往下走，防止无限循环再这里。
                    continue;
                }
                if (!isset($_config_load_config_file_config[$_config_load_config_foreach_value]))
                    $_config_load_config_file_config[$_config_load_config_foreach_value] = [];
                // 深度加载文件
                static::LoadConfigFile($_config_load_config_foreach_file_path, $_config_load_config_file_config[$_config_load_config_foreach_value]);
            } else {
                if ('.' == substr($_config_load_config_foreach_value, 0, 1)) {
                    continue;
                } else {
                    if ('-local.php' === substr($_config_load_config_foreach_value, -10)) {
                        $_config_load_config_foreach_key = substr($_config_load_config_foreach_value, 0, -10);
                    } else {
                        $_config_load_config_foreach_key = substr($_config_load_config_foreach_value, 0, -4);
                    }
                    // 过滤 Yii 的主要配置文件
                    if (in_array($_config_load_config_foreach_key, self::$exclude) || ('.php' !== substr($_config_load_config_foreach_value, -4))) {
                        continue;
                    }
                    if (file_exists($_config_load_config_foreach_file_path)) {
                        $_config = require $_config_load_config_foreach_file_path;
                        if (isset($_config_load_config_file_config[$_config_load_config_foreach_key])) {
                            $_config_load_config_file_config[$_config_load_config_foreach_key] = static::arrayMerge($_config_load_config_file_config[$_config_load_config_foreach_key], $_config);
                        } else {
                            $_config_load_config_file_config[$_config_load_config_foreach_key] = $_config;
                        }
                    }
                }
            }

        }
        return $_config_load_config_file_config;
    }

    /**
     * 设置配置参数 name为数组则为批量设置
     * @access public
     * @param string|array $name 配置参数名（支持无限层级 .号分割）
     * @param mixed $value 配置值
     * @param array $config 配置，默认为Config
     * @return mixed
     */
    public static function set($name, $value = null, &$config = null)
    {
        if (is_null($config)) {
            $config = &static::$config;
        }
        if (is_string($name)) {

            $name = explode('.', $name);

            // 一级配置
            if (1 === count($name)) {
                $config[$name[0]] = $value;
                return true;
            }
            if (!isset($config[$name[0]])) {
                $config[$name[0]] = [];
            }
            $newName = $name;
            unset($newName[0]);
            $newName = implode('.', $newName);
            return static::set($newName, $value, $config[$name[0]]);
        } elseif (is_array($name)) {
            if (is_null($value)) {

                foreach ($name as $key => $value) {
                    static::$config[$key] = static::arrayMerge(static::$config[$key], $value);
                }
                return true;

            }
        }

        return false;
    }

    /**
     * 获取一级配置
     * @param $name
     * @return array
     */
    public static function pull($name)
    {
        if ($name && isset(static::$config[$name])) {
            return static::$config[$name];
        }
        return [];
    }

    /**
     * 递归合并两个数组
     * 如果要合并的两个数组的对应键并非都是数组（无法合并），则以第二个数组的键值覆盖第一个数组对应的键值
     * @param array $array1 主数组
     * @param array $array2 附加数组
     * @return array 返回主数组
     */
    public static function arrayMerge($array1, $array2)
    {
        if (!is_array($array1))
            $array1 = [];
        if (!is_array($array2)) {
            $array2 = [];
        }
        /**
         * 遍历数组2
         */
        foreach ($array2 as $key2 => $item2) {
            // 如果附加数组的某个键的值是数组 ，并且 主数组的对应键的值也是数组 那么合并这两个数组
            if (is_array($item2) && isset($array1[$key2]) && is_array($array1[$key2])) {
                $array1[$key2] = static::arrayMerge($array1[$key2], $item2);
            } else {
                $array1[$key2] = $array2[$key2];
            }
        }
        return $array1;
    }
}