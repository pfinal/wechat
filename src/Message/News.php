<?php

namespace PFinal\Wechat\Message;

use PFinal\Wechat\Contract\ReplyMessage;
use PFinal\Wechat\Contract\SendMessage;

class News implements ReplyMessage, SendMessage
{
    protected $type = 'news';
    protected $attributes;

    /**
     * $arr = ['文章标题', '描述', 'url', 'image'];
     *
     * $news1 = new News('文章标题', '描述', 'url', 'image')
     * $news2 = new News($arr)
     * $news3 = new News([$arr,$arr])
     * $news4 = new News([$news1,$news2])
     *
     * @param string $title
     * @param string $description
     * @param string $url
     * @param string $picUrl
     */
    public function __construct($title = '', $description = '', $url = '', $picUrl = '')
    {
        if (is_array($title)) {

            $articles = $title;
            if (array_keys($articles) !== range(0, count($articles) - 1)) {
                //第一个参是关联数组
                $articles = array($articles);
            } else {
                //第一个参是索引数组
                foreach ($articles as $key => $item) {
                    if ($item instanceof News) {
                        $articles[$key] = $item->toArray();
                    } else {
                        $articles[$key] = (array)$item;
                    }
                }
            }
        } else {
            //第一个参数不是数组
            $articles = array(compact(array('title', 'description', 'url', 'picUrl')));
        }

        //将key的首字母转为大写(微信Xml格式首字大写)
        foreach ($articles as $key => $value) {

            $temp = array();
            foreach ($value as $k => $v) {
                $temp[ucfirst($k)] = $v;
            }

            $articles[$key] = $temp;
        }


        $this->attributes = array(
            'ArticleCount' => count($articles),
            'Articles' => $articles,//此参数需要索引数组，XML::arrayToXml()会将索引数组以item为父级生成xml
        );
    }

    public function toArray()
    {
        return current($this->attributes['Articles']);
    }

    /**
     * @return array
     */
    public function xmlData()
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function jsonData()
    {
        $articles = array();

        //将key转为小写，微信json格式为全小写
        foreach ($this->attributes['Articles'] as $k => $v) {
            $articles[$k] = array_change_key_case($v, CASE_LOWER);
        }

        return array('news' => array('articles' => $articles));
    }
}