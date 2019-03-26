<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019/3/24
 * Time: 7:13 PM
 */

namespace App\Libraries\Sensitive;

/**
 * 进行结果处理
 *
 * @package App\Libraries\Sensitive
 */
class Result
{
    private $totalNeedChange = []; //处理后需要改变的字符串

    private $orignWordsString = ''; //原始需要处理的字符串

    public function __construct(Handle $handle, String $orignWordsString)
    {
        $this->totalNeedChange = $handle->checkWord();
        $this->orignWordsString = $orignWordsString;
    }

    /**
     * debug调试，看匹配出来的结果是什么
     *
     * @return array
     */
    public function debug(): array
    {
        return $this->totalNeedChange;
    }

    /**
     * 检查是否有敏感词
     *
     * @return bool
     */
    public function exist(): bool
    {
        if ($this->totalNeedChange) {
            return true;
        }
        return false;
    }

    /**
     * 获取替换后的字符串
     *
     * @param bool $double
     * @param string $replace
     * @return mixed
     */
    public function get(bool $double = false, string $replace = '*'): string
    {
        return $this->all($double, $replace)['current'];
    }

    /**
     * 获取处理后的字符串
     *
     * @param bool $double
     * @param string $replace
     * @return array
     */
    public function all(bool $double = false, string $replace = '*'): array
    {
        $nowWordsString = $this->orignWordsString;
        if ($this->totalNeedChange) {
            foreach ($this->totalNeedChange as $change) {
                $stringChange = implode('', $change);

                $replaceString = $double ? $this->replaceNumCount(count($change), $replace) : $replace;

                $nowWordsString = str_replace($stringChange, $replaceString, $nowWordsString);
            }
        }

        return [
            'source' => $this->orignWordsString,
            'current' => $nowWordsString,
            'change' => $this->totalNeedChange
        ];
    }

    /**
     * 字符串填充为指定长度
     *
     * @param int $countNum
     * @param string $replace
     * @return string
     */
    private function replaceNumCount(int $countNum, string $replace): string
    {
        return str_pad($replace, $countNum, "*");
    }
}