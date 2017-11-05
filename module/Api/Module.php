<?php
namespace Api;

use Api\Model\CommonModel;
use Api\Model\SessionModel;
use Api\Model\UserModel;
use Api\Model\AdModel;
use Api\Model\MessageModel;
use Api\Model\InformationModel;
use Api\Model\RelationshipModel;
use Api\Model\LiveModel;
use Api\Model\QAModel;
use Api\Model\SMSCodeModel;
use Api\Model\ReviewModel;
use Api\Model\FinanceModel;
use Api\Model\RegionModel;
use Api\Model\DeleteModel;
use Api\Model\GalleryModel;
use Api\Model\ImageModel;
use Api\Model\NotificationModel;
use Api\Model\FamilySwitchModel;
use Api\Model\FamilyModel;
use Api\Model\ShopModel;
use Api\Model\WitnessModel;



class Module
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'Core\System' => dirname(dirname(__DIR__)) . '/vendor/Core/System'
                )
            )

        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Session' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SessionModel($dbAdapter);
                    return $table;
                },
                'User' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserModel($dbAdapter);
                    return $table;
                },
                'Ad' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new AdModel($dbAdapter);
                    return $table;
                },
                'Message' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new MessageModel($dbAdapter);
                    return $table;
                },
                'Information' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new InformationModel($dbAdapter);
                    return $table;
                },

                'Relationship' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new RelationshipModel($dbAdapter);
                    return $table;
                },

                'SMSCode' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SMScodeModel($dbAdapter);
                    return $table;
                },
                'Live' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new LiveModel($dbAdapter);
                    return $table;
                },

                'QA' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new QAModel($dbAdapter);
                    return $table;
                },
                'Review' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ReviewModel($dbAdapter);
                    return $table;
                },
                'Finance' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new FinanceModel($dbAdapter);
                    return $table;
                },

                'Region' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new RegionModel($dbAdapter);
                    return $table;
                },
                'Delete' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new DeleteModel($dbAdapter);
                    return $table;
                },

                'Gallery' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GalleryModel($dbAdapter);
                    return $table;
                },
                'Image' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ImageModel($dbAdapter);
                    return $table;
                },
                'Notification' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new NotificationModel($dbAdapter);
                    return $table;
                },
                'FamilySwitch' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new FamilySwitchModel($dbAdapter);
                    return $table;
                },
                'Family' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new FamilyModel($dbAdapter);
                    return $table;
                },
                'Shop' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ShopModel($dbAdapter);
                    return $table;
                },
                'Witness' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new WitnessModel($dbAdapter);
                    return $table;
                },
               

            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}