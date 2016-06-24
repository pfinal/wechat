<?php

namespace PFinal\Wechat\Support;

class Curl
{

    /**
     * @param $url
     * @param string $method 'post' or 'get'
     * @param null $postData
     *      类似'para1=val1&para2=val2&...'，
     *      也可以使用一个以字段名为键值，字段数据为值的数组。
     *      如果value是一个数组，Content-Type头将会被设置成multipart/form-data
     *      从 PHP 5.2.0 开始，使用 @ 前缀传递文件时，value 必须是个数组。
     *      从 PHP 5.5.0 开始, @ 前缀已被废弃，文件可通过 \CURLFile 发送。
     * @param array $options
     * @return mixed
     */
    public static function execute($url, $method, $postData = null, $options = array(), &$errors = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 150); //设置cURL允许执行的最长秒数

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if (strtolower($method) === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postData !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
        }

        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        if (!($output = curl_exec($ch))) {
            $errors = array(
                    'errno' => curl_errno($ch),
                    'error' => curl_error($ch),
                ) + curl_getinfo($ch);
        }

        curl_close($ch);
        return $output;
    }

    public static function get($url)
    {
        return self::execute($url, 'get');
    }

    public static function post($url, $postData)
    {
        return self::execute($url, 'post', $postData);
    }

    public static function file($url, $field, $filename, $postData = array())
    {
        $filename = realpath($filename);

        //PHP 5.6 禁用了 '@/path/filename' 语法上传文件
        if (class_exists('\CURLFile')) {
            $postData[$field] = new \CURLFile($filename);
        } else {
            $postData[$field] = '@' . $filename;
        }

        return self::execute($url, 'post', $postData);
    }
}