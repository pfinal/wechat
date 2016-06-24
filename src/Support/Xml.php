<?php

namespace PFinal\Wechat\Support;

/**
 * 构建微信的XML
 */
class Xml
{
    /**
     * @param $data
     * @param string $root
     * @param string $item 索引数组，以$item作为key
     * @return string
     */
    public static function build(array $data, $root = 'xml', $item = 'item')
    {
        return '<' . $root . '>' . self::arrayToXml($data, $item) . '</' . $root . '>';
    }

    /**
     * @param $data
     * @param string $item 索引数组，以$item作为key
     * @return string
     */
    private static function arrayToXml($data, $item = 'item')
    {
        $xml = '';

        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $key = $item;
            }

            $xml .= '<' . $key . '>';

            if (is_array($val)) {
                $xml .= self::arrayToXml((array)$val, $item);
            } else {
                $xml .= is_numeric($val) ? $val : sprintf('<![CDATA[%s]]>', $val);
            }

            $xml .= '</' . $key . '>';
        }

        return $xml;
    }
}
