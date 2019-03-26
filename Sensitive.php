<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019/3/10
 * Time: 7:48 PM
 */

namespace App\Libraries\Sensitive;

/**
 * 敏感词过滤
 *
 * 前缀树算法实现
 *
 * @package App\Libraries\Sensitive
 */
class Sensitive
{
    const BF_KEY = 'bloom_filter';

    private $wordsTree = [];

    /**
     * 初始化敏感词树
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function __construct()
    {
        $this->wordsTree = (new Build())->initWordsTree();
    }

    /**
     * 写入敏感词
     *
     * @param string $words
     * @return Result
     */
    public function words($words = '')
    {
        return new Result(new Handle($this->wordsTree, $words), $words);
    }
}