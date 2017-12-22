<?php
/**
 * 框架级Helper
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-12-22
 */
namespace Pails\Helpers;

/**
 * 图片尺寸调整
 * @package Pails\Helpers
 */
class ImageSize extends \stdClass
{
    /**
     * 将阿里云OSS的图片按指定尺寸输出
     * @link https://help.aliyun.com/document_detail/44688.html
     *
     * @param string $url OSS存储地址
     * @param int    $width 输出宽度/当为0时, 按原比例自动缩放
     * @param int    $height 输出高度/当为0时, 按原比例自动缩放
     *
     * @return string
     * @example ImageSize::oss('http://example.com/example.png');          // 输出原图
     * @example ImageSize::oss('http://example.com/example.png', 0, 90);   // 输出高度为90, 宽度自动的图片
     * @example ImageSize::oss('http://example.com/example.png', 160, 0);  // 输出宽度为160, 高度自动的图片
     * @example ImageSize::oss('http://example.com/example.png', 160, 90); // 输出成160x90的固定比例图片, 居中裁剪
     */
    public static function oss($url, $width = 0, $height = 0)
    {
        $url = preg_replace("/\?(.*)/", '', $url);
        if ($width > 0 && $height > 0) {
            $url .= '?x-oss-process=image/resize,m_fill,w_'.$width.',h_'.$height;
        } else {
            if ($width > 0) {
                $url .= '?x-oss-process=image/resize,m_lfit,w_'.$width;
            } else if ($height > 0) {
                $url .= '?x-oss-process=image/resize,m_lfit,h_'.$height;
            }
        }
        return $url;
    }
}