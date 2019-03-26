## sensitive for php

使用前缀树算法实现的敏感词过滤算法

## 使用方法

    $sensitive = new Sensitive();

    $word = "我有硝铵炸2fu……%ck2药配方，我被炸了22需要吃药";
    $result = $sensitive->word($word)->get();
    
## 功能

- 使用前缀树算法实现
- 收集了15000+个敏感词（涵盖广告、非法、政治、色情、网址5大板块，截止2016年）
- 支持敏感词中间冗余字符检测