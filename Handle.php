<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019/3/24
 * Time: 7:13 PM
 */

namespace App\Libraries\Sensitive;

/**
 * 核心敏感词处理
 *
 * @package App\Libraries\Sensitive
 */
class Handle
{
    private $wordsTree = []; //敏感词树

    private $wordsString = ''; //需要处理的字符串

    private $totalNeedChange = []; //总的需要处理的数组

    private $currentNeedChange = []; //当前需要处理的数组

    private $targetTree = [];//目标前缀树

    private $newEnWordsTree = []; //处理后剩余的新的英文字符串

    private $newEnWordsTreeTotal = []; //处理后剩余的所有的英文字符串

    /**
     * 构造函数处理
     *
     * @param $wordsTree
     * @param $wordsString
     */
    public function __construct(&$wordsTree, $wordsString)
    {
        $this->wordsTree = $wordsTree;
        $this->wordsString = $wordsString;
    }

    /**
     * 根据内容进行检查
     *
     * @return array
     */
    public function checkWord(): array
    {
        $this->targetTree = $this->wordsTree; //目标前缀树

        $wordChars = Helper::stringToArray($this->wordsString); //将字符串转换为数组

        foreach ($wordChars as $key => $wordChar) {
            if (isset($this->targetTree[$wordChar])) {
                //如果当前文字存在于敏感词中，进行记录
                $this->isExistSensitive($wordChars, $wordChar, $key);
            } else if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $wordChar) && isset($this->wordsTree[$wordChar])) {
                //当文字不存在于当前敏感词树中的时候，判断是否存在于根敏感词树中
                $this->targetTree = $this->wordsTree;
                $this->currentNeedChange = [];
                $this->isExistSensitive($wordChars, $wordChar, $key);
            } else {
                //如果当前文字完全不存在于敏感词中，则进行逻辑判断
                $this->isNotExistSensitive($wordChars, $wordChar, $key);
            }
        }

        //第一次处理完后重置相关数据
        $this->targetTree = $this->wordsTree; //目标前缀树
        $this->currentNeedChange = []; //当前需求改变的值

        //处理完以后再处理英文
        $this->checkEnSensitive();

        return $this->totalNeedChange;
    }

    /**
     * 检查英文的敏感词
     */
    private function checkEnSensitive(): void
    {
        foreach ($this->newEnWordsTreeTotal as $item) {
            foreach ($item as $key => $wordChar) {
                if (isset($this->targetTree[$wordChar])) {
                    //如果当前文字存在于敏感词中，进行记录
                    $this->isExistSensitive($item, $wordChar, $key, 'en');
                } else {
                    if ($this->currentNeedChange && isset($this->targetTree['end'])) {
                        //将当前需要处理的数组进行保存，并且初始化为空
                        $this->totalNeedChange[] = $this->currentNeedChange;
                    }

                    // 如果当前文字不存在于敏感词中，则需要重置目前前缀树
                    $this->targetTree = $this->wordsTree;
                    $this->currentNeedChange = [];

                    if (isset($this->targetTree[$wordChar])) {
                        $this->targetTree = $this->targetTree[$wordChar];
                        $this->currentNeedChange[] = $wordChar;
                    }
                }
            }
            $this->targetTree = $this->wordsTree; //目标前缀树
            $this->currentNeedChange = []; //当前需求改变的值
        }

    }

    /**
     * 如果存在于敏感词中
     *
     * @param $wordChars
     * @param $wordChar
     * @param $key
     * @param $en
     */
    private function isExistSensitive($wordChars, $wordChar, $key, $en = false): void
    {
        $this->targetTree = $this->targetTree[$wordChar];

        $this->currentNeedChange[] = $wordChar;


        if (isset($this->targetTree['end']) && count($wordChars) == $key + 1) {
            //当该字是最后一个文字，并且存在于敏感词中，则需要进行记录
            $this->totalNeedChange[] = $this->currentNeedChange;
            $this->currentNeedChange = [];
            $this->targetTree = $this->wordsTree;
        } else if (isset($wordChars[$key + 1])) {
            $nextWordChar = $wordChars[$key + 1];
            //如果当前没有到达结尾，并且存在下一个关键词
            if (!isset($this->targetTree[$nextWordChar]) && isset($this->targetTree['end'])) {
                $this->totalNeedChange[] = $this->currentNeedChange;
                $this->currentNeedChange = [];
                $this->targetTree = $this->wordsTree;
            }
        }

        if (!$en) {
            $this->checkEnWordsTree($wordChar);
        }
    }

    /**
     * 如果不存在于敏感词中
     *
     * @param $wordChar
     */
    private function isNotExistSensitive($wordChars, $wordChar, $key): void
    {
        if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $wordChar)) {  //判断字符串是否是中文
            //因为当前子未匹配，所以需要再判断上面一个字
            if (($key - 1) >= 0 && isset($this->wordsTree[$wordChars[$key - 1]])) {
                $this->targetTree = $this->wordsTree;
                $this->currentNeedChange = [];
                $this->isExistSensitive($wordChars, $wordChars[$key - 1], $key - 1);
                if ($this->currentNeedChange) {
                    if (isset($this->targetTree[$wordChar])) {
                        $this->isExistSensitive($wordChars, $wordChar, $key);
                    }
                }

            } else {
                if ($this->currentNeedChange && isset($this->targetTree['end'])) {
                    //将当前需要处理的数组进行保存，并且初始化为空
                    $this->totalNeedChange[] = $this->currentNeedChange;
                }

                // 如果当前文字不存在于敏感词中，则需要重置目前前缀树
                $this->targetTree = $this->wordsTree;
                $this->currentNeedChange = [];

                if (isset($this->targetTree[$wordChar])) {
                    $this->targetTree = $this->targetTree[$wordChar];
                    $this->currentNeedChange[] = $wordChar;
                }
            }

            $this->checkEnWordsTree($wordChar);
        } else {
            //如果不是中文字符，直接跳过继续处理
            if ($this->targetTree != [] && $this->currentNeedChange) {
                $this->currentNeedChange[] = $wordChar;
            }

            $this->checkEnWordsTree($wordChar);
        }
    }

    /**
     * 检查英文的情况
     *
     * @param $wordChar
     */
    private function checkEnWordsTree($wordChar): void
    {
        //如果是英文字符则剥离出来再处理
        if (preg_match("/^[A-Za-z0-9_.-]+$/u", $wordChar)) {
            $this->newEnWordsTree[] = $wordChar;
            $this->newEnContinue = true;
        } else {
            $this->newEnContinue = false;
            if ($this->newEnWordsTree) {
                $this->newEnWordsTreeTotal[] = $this->newEnWordsTree;
                $this->newEnWordsTree = [];
            }
        }
    }
}