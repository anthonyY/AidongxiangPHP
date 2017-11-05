<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Api\Controller\Index' => 'Api\Controller\IndexController',
            'Api\Controller\Plan' => 'Api\Controller\PlanController',
            'Api\Controller\Public' => 'Api\Controller\PublicController',
            'Api\Controller\CallBack' => 'Api\Controller\CallBackController',
            'Api\Controller\Plan' => 'Api\Controller\PlanController',
        )
    ),
    'router' => array(
        'routes' => array(
            'api' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    // 'route' => MODULE_API,
                    'route' => MODULE_API.'[/:controller][/:action]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                )
            ),
            'api-common' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_API.'[/:controller][/:action]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Api\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                )
            ),
            'api-public' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_API . '/public[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\Public',
                        'action' => 'index'
                    )
                )
            ),
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'apiPage' => __DIR__ . '/../view/layout/page.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    )
);