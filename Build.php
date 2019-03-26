<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019/3/24
 * Time: 7:14 PM
 */

namespace App\Libraries\Sensitive;

/**
 * 敏感词树构造器
 *
 * @package App\Libraries\Sensitive
 */
class Build
{
    const CACHE_SENSITIVE_TREE = "sensitive_tree"; //缓存key
    const CACHE_TTL = 24 * 60; //缓存过期时间，默认24小时

    private $wordsTree = []; //敏感词树

    /**
     * 初始化敏感词树
     *
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function initWordsTree(): array
    {
        $words = [];
        $wordsTree = \Cache::get(self::CACHE_SENSITIVE_TREE);
        if (!$wordsTree) {
            //从文件读取敏感词
            $list = glob(dirname(__FILE__) . '/words/*');
            foreach ($list as $item) {
                if (is_file($item)) {
                    $myfile = fopen($item, "r") or die("Unable to open sensitive file!");
                    //单行读取数据
                    while (!feof($myfile)) {
                        $words[] = trim(fgets($myfile));
                    }
                    fclose($myfile);
                }
            }

            $this->wordsTree = $this->buildTree($words);
            \Cache::set(self::CACHE_SENSITIVE_TREE, json_encode($this->wordsTree), self::CACHE_TTL);
        } else {
            $this->wordsTree = json_decode($wordsTree, true);
        }

        return $this->wordsTree;
    }

    /**
     * 建立完整的敏感词树
     *
     * @param array $words
     * @return array
     */
    private function buildTree(array $words): array
    {
        $wordsTree = [];
        foreach ($words as $word) {
            $wordChars = Helper::stringToArray($word);
            $currentWordsTree = $this->buildSingleTree($wordChars);

            //多维数组合并
            //不能使用array_merge_recursive函数，因为合并后会出现bug
            //数组A为[3=>"test"],数组B为[7=>"test2"]，合并后会成为[3=>"test",4=>"test2"]
            $wordsTree = $this->combineWordTree($wordsTree, $currentWordsTree);
        }

        return $wordsTree;
    }

    /**
     * 递归的合并数组
     *
     * array_merge_recursive改版
     *
     * @param array $wordsTree
     * @param $currentWordsTree
     * @return mixed
     */
    private function combineWordTree(array $wordsTree, $currentWordsTree): array
    {
        if (is_array($currentWordsTree)) {
            //获取key
            foreach ($currentWordsTree as $key => $currentWords) {
                $wordKey = $key;
                break;
            }

            if (isset($wordsTree[$wordKey]) && is_array($wordsTree[$wordKey])) {
                $wordsTree[$wordKey] = $this->combineWordTree($wordsTree[$wordKey], $currentWordsTree[$wordKey]);
                return $wordsTree;
            } else {
                //不存在就直接赋值整个结果
                $wordsTree[$wordKey] = $currentWordsTree[$wordKey];
                return $wordsTree;
            }
        } else {
            return $wordsTree;
        }
    }


    /**
     * 建立单个前缀树
     *
     * @param array $wordChars
     * @return array
     */
    private function buildSingleTree(array $wordChars): array
    {
        //将数组反转，反向处理
        $newWordChars = array_reverse($wordChars);
        $wordsSingleTree = ['end' => 1];

        foreach ($newWordChars as $wordChar) {
            $current = [];
            $current[$wordChar] = $wordsSingleTree;
            $wordsSingleTree = $current;
        }

        return $wordsSingleTree;
    }
}