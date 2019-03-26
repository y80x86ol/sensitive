## sensitive for php

使用前缀树算法实现的敏感词过滤算法

## 使用方法

    $sensitive = new Sensitive();

    $words = "我有硝铵炸2fu……%ck2药配方，我被炸了22需要炸弹销售吃药";
    $result = $sensitive->words($words)->get();
    
    敏感词为：
    硝铵炸药，炸弹
    
    结果如下：
    "我有*配方，我被炸了22需要**销售吃药";
    
只获取处理后的结果

    $sensitive->words($words)->get();
    
    默认采用敏感词只使用'*'替换，你可以指定使用'@'符号
    get(false,'@');
    
    默认采用敏感词替换只使用一个替换符，你可以指定一个字符一个替换符
    get(true)
    
获取处理后的结果和匹配出的敏感词

    $sensitive->words($words)->all();
    
    参数同get()参数一致
    
判断字符串中是否有敏感词

    $sensitive->words($words)->exist();
    
调试，返回匹配出的敏感词

    $sensitive->words($words)->debug();
    
## 功能

- 使用前缀树算法实现
- 收集了15000+个敏感词（涵盖广告、非法、政治、色情、网址5大板块，截止2016年）
- 支持敏感词中间冗余字符检测
- 支持英文敏感词

备注：目前只能在laravel中使用，简单修改即可在其他php项目中使用