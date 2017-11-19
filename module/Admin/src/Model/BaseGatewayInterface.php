<?php
/**
 * BaseGatewayInterface.php
 * ketx
 * 
 * Created by danny on 2017年8月3日.
 * Copyright © 2017年 Aiitec. All rights reserved.
 */

namespace Platform\Model;

interface BaseGatewayInterface
{
//     public function getTable();
//     public function select($where = null);
//     public function insert($set);
//     public function update($set, $where = null);
//     public function delete($where);
    
    public function jsonDecode($json_string);
    public function jsonEncode();
    
    public function getSet();
    
    public function selectObjectById($id);
    public function selectCollectionByIds($ids = array());
    public function selectCollectionByPaging($id = array());
    public function selectCollectionBySearch($id = array());
//     public function executeSelectByWhere($where);// 受保护的.
    
    public function insertObject();
    public function updateObject();
    public function deleteMarkObject();
    public function deleteObjectById($id);
    public function deleteCollectionByIds($ids = array());
}

 







