<?php
namespace Api\Controller\Common;

/**
 * 
 * @author WZ
 *
 */
class Item
{
    /**
     * 返回对象id
     * @var Number
     */
    public $id;
    
    /**
     * 这个对象属性的一些设置，
     * key的转换:key
     * 默认值:default
     * 过滤:
     *
     */
    public $options = array();
    
    /**
     * 修改options的值
     * @param string $key 'key','default','functions',...
     * @param array $value key=>value
     * @param number $type 0添加，1覆盖，默认是0
     * @version 2014-12-5 WZ
     */
    function setOptions($key,$value,$type = 0)
    {
        if(0 == $type && isset($this->options[$key]))
        {
            $this->options[$key] = array_merge($this->options[$key], $value);
        }
        else
        {
            $this->options[$key] = $value;
        }
    }
    
    /**
     * 批量获取对象的值
     * 
     * @param String||Array $keys
     * @param number $type 0原来的key，1用协议的key
     * @param number $get_empty true空也拿，false空就不拿
     * @return multitype:NULL |string
     * @version 2014-12-5 WZ
     */
    function getValues($keys, $type = 0, $get_empty = true)
    {
        
        //echo"11111";exit;
        if(is_array($keys))
        {
            $values = array();
            foreach($keys as $key)
            {
                if(($this->$key || $get_empty) && 'options' != $key)
                {
                    if(1 == $type && isset($this->options['key'][$key]))
                    {
                        $values[$this->options['key'][$key]] = $this->$key;
                    }
                    else 
                    {
                        $values[$key] = $this->$key;
                    }
                }
            }
            return $values;
        }
        elseif(is_string($keys))
        {
            return isset($this->$keys) ? $this->$keys : '';
        }
    }
    
    /**
     * 批量设置对象的值
     *
     * @param String||Array $keys
     * @param number $set_empty 0空的不复制不覆盖，1空也赋值覆盖
     * @return multitype:NULL |string
     * @version 2014-12-5 WZ
     */
    function setValues($data, $get_empty = 0)
    {
        if(is_array($data) || is_object($data))
        {
            $values = array();
            foreach($data as $key => $value)
            {
                if($value || ! $get_empty)
                {
                    $this->$key = $value;
                }
            }
        }
        return $this;
    }
    
    function __construct()
    {
        // 
    }
    
    /**
     * 禁止生成未定义的属性并对其赋值
     * @param unknown $name
     * @param unknown $value
     * @return boolean
     * @version 2014-12-8 WZ
     */
    function __set($name, $value)
    {
        return false;// 
    }
}