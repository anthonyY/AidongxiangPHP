<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class UserController extends CommonController
{
    public function indexAction()
    {
        $this->checkLogin('admin_user_index');
        $admin_table = $this->getAdminTable();
        $admin_table->id = 1;
        $res = $admin_table->getList();
        $view = new ViewModel();
        $view->setTemplate("admin/index/index");
        return $this->setMenu($view);
    }

}
