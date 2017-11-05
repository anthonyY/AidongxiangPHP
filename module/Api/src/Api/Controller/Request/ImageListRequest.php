<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class  ImageListRequest extends Request
{
    public $id;
  
    
 

    function __construct()
    {
        parent::__construct();
      
    }
}