<?php
namespace Web;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Web\Model\UserTable;
use Web\Model\VideoModel;
use Web\Model\IndexModel;
use Web\Model\CommonModel;
use Web\Model\SMSCodeModel;
use Web\Model\AudioModel;
use Web\Model\TutorModel;
use Web\Model\UserModel;
use Web\Model\SearchModel;
use Web\Model\ScanCodeModel;


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
        if (explode('\\', $controller)[0] != __NAMESPACE__)
        {
            // not a controller from this module
            return false;
        }
        
        // Set the layout template
        $viewModel = $mvcEvent->getViewModel();
        $viewModel->setTemplate('layout_web/layout2');
    }

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
            ) // 2014.1.22hexin

        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Index' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new IndexModel($dbAdapter);
                    return $table;
                },
                'Video' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VideoModel($dbAdapter);
                    return $table;
                },
                'Audio' =>  function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $table = new AudioModel($dbAdapter);
                return $table;
                },
                'Tutor' =>  function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $table = new TutorModel($dbAdapter);
                return $table;
                },
                'User' =>  function($sm) {
                $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                $table = new UserModel($dbAdapter);
                return $table;
                },
                'Search' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SearchModel($dbAdapter);
                    return $table;
                },

                'Common' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CommonModel($dbAdapter);
                    return $table;
                },

                'ScanCode' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ScanCodeModel($dbAdapter);
                    return $table;
                },
               
            ),
        );
    }
}