<?php
    function XssFilter (&$arr)
    {
    $ra=Array('/script/','/javascript/','/vbscript/');
    
    if(is_array($arr))
    {
        foreach($arr as $key  => $value)
        {
            if($key != 'description' && $key != 'content') {
                if (!is_array($value)) {
        
                    if (!get_magic_quotes_gpc())             //不对magic_quotes_gpc转义过的字符使用addslashes(),避免双重转义。
        
                    {
                        $value = addslashes($value);          //给单引号（'）、双引号（"）、反斜线（\）与 NUL（NULL 字符）加上反斜线转义
                    }
        
                    $value = preg_replace($ra, '', $value);    //删除非打印字符，粗暴式过滤xss可疑字符串
        
                    $arr[$key] = htmlentities(strip_tags($value));//去除 HTML 和 PHP 标记并转换为 HTML 实体
                }
                else {
                    XssFilter($arr[$key]);
                }
            }
        }
    }
 }
     
    