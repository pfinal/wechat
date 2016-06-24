<?php

namespace PFinal\Wechat\Service;

use PFinal\Wechat\Kernel;
use PFinal\Wechat\Support\Curl;
use PFinal\Wechat\Support\Json;

class MaterialService extends BaseService
{

    /**
     * 上传临时素材文件
     *
     * curl -F media=@test.jpg "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=image"
     *
     * @param string $filename 文件名
     * @param string $type 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb，主要用于视频与音乐格式的缩略图）
     *      图片（image）: 大小不超过2M，支持bmp/png/jpeg/jpg/gif格式
     *      语音（voice）：大小不超过5M，播放长度不超过60s，支持AMR\MP3格式
     *      视频（video）：10MB，支持MP4格式
     *      缩略图（thumb）：64KB，支持JPG格式
     * @return array
     */
    public static function uploadFileTemporary($filename, $type)
    {
        $filename = realpath($filename);


        //PHP 5.6 禁用了 '@/path/filename' 语法上传文件
        if (class_exists('\CURLFile')) {
            $data['media'] = new \CURLFile($filename);
        } else {
            $data['media'] = '@' . $filename;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=' . $type;

        return parent::request($url, $data, false);
    }


    /**
     * 新增永久素材 媒体文件类型别有图片（image）、语音（voice) 、视频（video）和缩略图（thumb）
     *
     * @param string $filename
     * @param string $type
     * @param string $title 视频素材的标题 只对类型为video有效
     * @param string $introduction 视频素材的描述 只对类型为video有效
     * @return array
     */
    public static function uploadFile($filename, $type, $title = null, $introduction = null)
    {
        $filename = realpath($filename);

        if (class_exists('\CURLFile')) {
            $data['media'] = new \CURLFile($filename);
        } else {
            $data['media'] = '@' . $filename;
        }

        $data['type'] = $type;

        if ($type === 'video') {
            $data['description'] = Json::encode(array(
                'title' => $title,
                'introduction' => $introduction
            ));
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN';

        return parent::request($url, $data, false);
    }


    /**
     * 上传图文消息内的图片获取URL (在图文消息的具体内容中，将过滤外部的图片链接)，只能使用此方法返回的url
     * 本接口所上传的图片不占用公众号的素材库中图片数量的5000个的限制。图片仅支持jpg/png格式，大小必须在1MB以下。
     * @param $filename
     * @return string 例如 http://mmbiz.qpic.cn/mmbiz/D7sHwECXBUtWxg2eVOmIsqWOERic2dfBWYhWtOzIxhiaYAIt8ludGP0QHh8cO6pVQT8V8KKZcahvzQiblMWXlA4Pw/0
     * @throws \Exception
     * @throws \PFinal\Wechat\WechatException
     */
    public static function uploadNewsImage($filename)
    {
        $filename = realpath($filename);

        if (class_exists('\CURLFile')) {
            $data['media'] = new \CURLFile($filename);
        } else {
            $data['media'] = '@' . $filename;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=ACCESS_TOKEN';

        $result = parent::request($url, $data, false);
        return $result['url'];
    }

    /**
     * 新增永久图文素材，所有参数全是必填，多图文素材一个参数传入数组即可，数组的key与本方法参数一致
     * @param string|array $title
     * @param string $thumb_media_id
     * @param string $author
     * @param string $digest
     * @param string $show_cover_pic
     * @param string $content
     * @param string $content_source_url
     * @return array
     * @throws \PFinal\Wechat\WechatException
     */
    public static function uploadNews($title, $thumb_media_id = null, $author = null, $digest = null, $show_cover_pic = null, $content = null, $content_source_url = null)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=ACCESS_TOKEN';

        if (!is_array($title)) {
            $title = array(compact(array('title', 'thumb_media_id', 'author', 'digest', 'show_cover_pic', 'content', 'content_source_url')));
        }

        $data = array(
            'articles' => $title,
        );

        return parent::request($url, $data);
    }

}