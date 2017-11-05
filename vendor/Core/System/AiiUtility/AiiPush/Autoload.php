<?php
//	以下代码请保留

function myAutoload($class)
{
    $file = 'vendor/' . $class . '.php';
    $file = strtr($file, array('\\' => '/'));
    try
    {
        if (is_file($file))
        {
            include_once $file;
        }
        else
        {
            throw new Exception('CAN NOT FIND THE CLASS: ' . $class);
        }
    }
    catch (Exception $e)
    {
        echo $e;
    }
}

spl_autoload_register('myAutoload');
