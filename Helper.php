<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019/3/24
 * Time: 7:28 PM
 */

namespace App\Libraries\Sensitive;

/**
 * 帮助函数
 *
 * @package App\Libraries\Sensitive
 */
class Helper
{
    /**
     * 将字符串转数组
     *
     * @param string $string
     * @return array
     */
    public static function stringToArray(string $string): array
    {
        $chars = [];
        $length = mb_strlen($string);
        while ($length > 0) {
            $chars[] = mb_substr($string, 0, 1); //每次获取第一个字符
            $string = mb_substr($string, 1); //获取剩余的字符
            $length = mb_strlen($string); //获取长度做判断
        }

        return $chars;
    }
}