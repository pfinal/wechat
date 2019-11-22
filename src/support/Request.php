<?php
/**
 *   Author: Yanlongli <ahlyl94@gmail.com>
 *   Date:   2019/8/2
 *   IDE:    PhpStorm
 *   Desc:   请求参数
 */


namespace yanlongli\wechat\support;


class Request
{
    protected static $params;
    protected static $get;
    protected static $post;

    /**
     * @param null $name
     * @param null $default
     *
     * @return mixed
     * 修饰符    作用
     * s    强制转换为字符串类型
     * d    强制转换为整型类型
     * b    强制转换为布尔类型
     * a    强制转换为数组类型
     * f    强制转换为浮点类型
     */
    public static function param($name = null, $default = null)
    {
        self::$params = null;
        if (null === self::$params) {
            // 合并Get和Post数据，以Post为主覆盖Get数据
            self::$get = static::get(null, []);
            self::$post = static::post(null, []);
            self::$params = Config::arrayMerge(self::$get, self::$post);
        }
        return static::input(self::$params, $name, $default);
    }

    /**
     * 检测是否存在指定请求参数
     * @param $name
     * @return bool
     */
    public static function has($name)
    {
        return isset(static::param()[$name]);
    }

    /**
     * 获取指定的参数
     * @access public
     * @param string|array $name 变量名
     * @param string $type 变量类型
     * @return mixed
     */
    public static function only($name, $type = 'param')
    {

        $param = static::$type();

        if (is_string($name)) {
            $name = explode(',', $name);
        }

        $item = [];
        foreach ($name as $key => $val) {

            if (is_int($key)) {
                $default = null;
                $key = $val;
            } else {
                $default = $val;
            }

            if (isset($param[$key])) {
                $item[$key] = $param[$key];
            } elseif (isset($default)) {
                $item[$key] = $default;
            }
        }

        return $item;
    }

    /**
     * 排除指定参数获取
     * @access public
     * @param string|array $name 变量名
     * @param string $type 变量类型
     * @return mixed
     */
    public static function except($name, $type = 'param')
    {
        $param = static::$type();
        if (is_string($name)) {
            $name = explode(',', $name);
        }

        foreach ($name as $key) {
            if (isset($param[$key])) {
                unset($param[$key]);
            }
        }

        return $param;
    }


    public static function get($name = null, $default = null)
    {
        if (null === self::$get) {
            self::$get = $_GET;
        }
        return static::input(self::$get, $name, $default);
    }

    /**
     * @param null $name
     * @param null $default
     * @return mixed
     */
    public static function post($name = null, $default = null)
    {
        if (null === self::$post) {

            $rawContentType = self::getContentType();
            if (false !== ($pos = strpos($rawContentType, ';'))) {
                $contentType = substr($rawContentType, 0, $pos);
            } else {
                $contentType = $rawContentType;
            }
            $data = null;
            try {
                switch ($contentType) {
                    case 'application/json':
                        $data = file_get_contents('php://input');
                        $data = json_decode($data, true);
                        break;
                    case 'application/xml':
                    case 'text/xml':
                        $data = file_get_contents('php://input');
                        $data = self::xmlToArray($data);
                        break;
                    case 'text/plain':
                    case 'application/javascript':
                    case 'text/html':
                        break;
                    case 'multipart/form-data':
                    default:
                        $data = $_POST;
                        break;

                }
            } catch (\Exception $exception) {
                // 修复如果解析报错则返回空数组，出现在xml或json格式不合法问题
            }
            self::$post = $data;
        }

        return static::input(self::$post, $name, $default);
    }


    /**
     * @param array $data
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected static function input($data, $name, $default)
    {
        $name = (string)$name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                list($name, $suffix) = explode('/', $name);
            }

            $data = static::getData($data, $name);

            if (is_null($data)) {
                return $default;
            }

            if (is_object($data)) {
                return $data;
            }
        }
        // 强制类型转换
        if (isset($suffix)) {
            return static::typeCast($data, $suffix);
        }
        return $data;
    }

    /**
     * 获取数据
     * @access public
     * @param array $data 数据源
     * @param string|false $name 字段名
     * @return mixed
     */
    protected static function getData($data, $name)
    {
        foreach (explode('.', $name) as $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * 强制类型转换
     * @access public
     * @param string $value
     * @param string $type
     * @return mixed
     */
    public static function typeCast($value, $type)
    {
        $_value = null;
        switch ($type) {
            case 's':
                if (is_scalar($value)) {
                    $_value = (string)$value;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($value));
                }
                break;
            case 'd':
                $_value = (integer)$value;
                break;
            case 'b':
                if (in_array(strtolower($value), ['true', 1, 't'])) {
                    $value = true;
                }
                if (in_array(strtolower($value), ['false', 0, 'f'])) {
                    $value = false;
                }
                $_value = (boolean)$value;
                break;
            case 'a':
                $_value = (array)$value;
                break;
            case 'f':
                $_value = (float)$value;
                break;
            default:
                $_value = $value;
                break;
        }

        return $_value;
    }

    /**
     * 生成请求令牌
     * @access public
     *
     * @param $user
     *
     * @return string
     */
    public static function buildToken()
    {
        $str = microtime(true);
        $str .= uniqid($str);

        return strtoupper(md5($str));
    }

    public static function getContentType()
    {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            return $_SERVER['CONTENT_TYPE'];
        }

        return 'multipart/form-data';
    }

    /**
     * @param string|array $name
     * @param null $value
     */
    public static function setPost($name, $value = null): void
    {
        //先读取
        self::post();
        if (is_array($name)) {
            //递归合并
            self::$params = Config::arrayMerge(self::$params, $name);
            return;
        }
        //后设置
        self::$post[$name] = $value;
    }

    /**
     * @param string|array $name
     * @param null $value
     */
    public static function setGet($name, $value = null): void
    {
        //先读取
        self::get();
        if (is_array($name)) {
            //递归合并
            self::$params = Config::arrayMerge(self::$params, $name);
            return;
        }
        //后设置
        self::$get[$name] = $value;
    }

    /**
     * @param string|array $name
     * @param null $value
     */
    public static function setParams($name, $value = null): void
    {
        //先读取
        self::param();

        if (is_array($name)) {
            //递归合并
            self::$params = Config::arrayMerge(self::$params, $name);
            return;
        }

        //后设置
        self::$params[$name] = $value;
    }

    public static function xmlToArray($xml)
    {
        $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($xml), true);
    }

}