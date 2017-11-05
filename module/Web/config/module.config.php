<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Web\Controller\Index' => 'Web\Controller\IndexController',
            'Web\Controller\Common' => 'Web\Controller\CommonController',
            'Web\Controller\Audio' => 'Web\Controller\AudioController',
            'Web\Controller\Video' => 'Web\Controller\VideoController',
            'Web\Controller\User' => 'Web\Controller\UserController',
            'Web\Controller\Tutor' => 'Web\Controller\TutorController',
            'Web\Controller\Search' => 'Web\Controller\SearchController',
            'Web\Controller\ScanCode' => 'Web\Controller\ScanCodeController',
        )
    ),
    
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory'
        )
    ),
    
    'translator' => array(
        'locale' => 'zh_CN',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo'
            )
        )
    ),
    
    'router' => array(
        'routes' => array(
            'web' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => MODULE_WEB,
                    'defaults' => array(
                        'controller' => 'Index\Controller\Index',
                        'action' => 'index'
                    )
                )
            ),
            
            'web-common' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_WEB . '[/:controller][/:action][/t:type][/i:id][/c:cid][/u:uid][/s:status][/p:page][/o:other][/k:keyword]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'uid' => '[0-9]*',
                        'status' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*',
                        'type' => '[0-9]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Web\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                )
            )
        )
        
    )
    ,
/*     
    'view_manager' => array(
    		'template_path_stack' => array(
    				'sourcing' => __DIR__ . '/../view',
    		),
    ), */

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'index/login' => __DIR__ . '/../view/index/login.phtml',
            'layout_web/layout2' => __DIR__ . '/../view/layout_web/layout1.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'web_page' => __DIR__ . '/../view/layout_web/page.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    )
);

