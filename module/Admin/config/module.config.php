<?php
    /**
     * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
     * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     */
    
    namespace Admin;
    
    use Zend\Router\Http\Literal;
    use Zend\Router\Http\Segment;
    use Zend\ServiceManager\Factory\InvokableFactory;
    
    return [
        'router' => [
            'routes' => [
                'admin' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/index[/:action][/:page]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\IndexController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
//                //用户管理
//                'admin-user' => [
//                    'type'    => Segment::class,
//                    'options' => ['route' => MODULE_ADMIN . '/user[/a:action][/p:page][/i:id]',
//                        'constraints' => array(
//                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
//                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
//                            'page' => '[0-9]+'
//                        ),
//                        'defaults' => [
//                            'controller' => Controller\UserController::class,
//                            'action'     => 'index',
//                        ],
//                    ],
//                ],
            ],
        ],
        'controller_plugins' => array(
            'invokables' => array(
                'PlatformPlugin'   => 'Platform\Controller\Plugin\PlatformPlugin',
            )
        ),
        'controllers' => [
            'factories' => [
                Controller\IndexController::class => InvokableFactory::class,
            ],
        ],
        'view_manager' => [
            'display_not_found_reason' => true,
            'display_exceptions'       => true,
            'doctype'                  => 'HTML5',
            'not_found_template'       => 'error/404',
            'exception_template'       => 'error/index',
            'template_map' => ['layout/layout' => __DIR__ . '/../view/layout/layout.phtml', 'page' => __DIR__ . '/../view/layout/page.phtml',
                'admin/index/index' => __DIR__ . '/../view/admin/index/index.phtml',
                'error/404'               => __DIR__ . '/../view/error/404.phtml',
                'error/index'             => __DIR__ . '/../view/error/index.phtml',
            ],
            'template_path_stack' => [
                __DIR__ . '/../view',
            ],
        ],
    ];
