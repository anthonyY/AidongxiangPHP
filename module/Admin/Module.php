<?php

namespace Admin;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;


// use Admin\Model\UserListModel;
use Admin\Model\CommonModel;
use Admin\Model\AdminModel;
// use Admin\Model\AdminSettingModel;
use Admin\Model\OtherModel;
// use Admin\Model\SMSCodeModel;
// use Admin\Model\CertificationModel;
// use Admin\Model\SocialModel;
// use Admin\Model\WalletModel;
use Admin\Model\SystemModel;
use Admin\Model\ContentListModel;
// use Admin\Model\IndexAdminModel;
// use Admin\Model\FamilysModel;
// use Admin\Model\LoveModel;
// use Admin\Model\FCodeModel;
// use Admin\Model\StartModel;
// use Admin\Model\RecommendModel;
// use Admin\Model\BookModel;
// use Admin\Model\FinancialModel;
// use Admin\Model\LinuxModel;
use Admin\Model\AdminAdsModel;
use Admin\Model\AdminUserModel;
use Admin\Model\AdminCommentModel;
use Admin\Model\AdminOrderModel;
use Admin\Model\GroupMemberModel;
use Admin\Model\AdminFeedbackModel;
use Admin\Model\IndexAdminModel;
// use Admin\Model\UserModel;

class Module
{
    public function onBootstrap(MvcEvent $mvcEvent)
    {
        $eventManager = $mvcEvent->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        // Register a dispatch event
        $application = $mvcEvent->getParam('application');

        $application->getEventManager()->attach('dispatch', array(
            $this,
            'setLayout'
        ));
    }

    public function setLayout($mvcEvent)
    {
        $matches = $mvcEvent->getRouteMatch();
        $controller = $matches->getParam('controller');
        if (false === strpos($controller, __NAMESPACE__))
        {
            // not a controller from this module
            return false;
        }

        // Set the layout template
        $viewModel = $mvcEvent->getViewModel();
        $viewModel->setTemplate('admin/layout/block');
    }


    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'Core\System' => dirname(dirname(__DIR__)) . '/vendor/Core/System'
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Admin' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new  AdminModel($dbAdapter);
                    return $table;
                },
                'AdminAds' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new  AdminAdsModel($dbAdapter);
                    return $table;
                },
                'AdminUser' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new  AdminUserModel($dbAdapter);
                    return $table;
                },
                'AdminComment' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new AdminCommentModel($dbAdapter);
                    return $table;
                },
                'AdminOrder' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new AdminOrderModel($dbAdapter);
                    return $table;
                },
                'AdminFeedback' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new AdminFeedbackModel($dbAdapter);
                    return $table;
                },
                'IndexAdmin' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new IndexAdminModel($dbAdapter);
                    return $table;
                },
                'GroupMember' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new  GroupMemberModel($dbAdapter);
                    return $table;
                },
                'System' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new  SystemModel($dbAdapter);
                    return $table;
                },
                'Other' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new  OtherModel($dbAdapter);
                    return $table;
                },
                'ContentList' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ContentListModel($dbAdapter);
                    return $table;
                },
                
            ),
        );
    }    

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}