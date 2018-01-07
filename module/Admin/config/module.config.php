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

                //用户管理
                'admin-user' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/user[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\UserController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],

                'admin-business' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/business[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+',
                        ),
                        'defaults' => [
                            'controller' => Controller\BusinessController::class,
                            'action'     => 'adsList',
                        ],
                    ],
                ],

                'admin-video' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/video[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\VideoController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],

                'admin-audio' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/audio[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\AudioController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],

                'admin-microblog' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/microblog[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\MicroblogController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],

                'admin-article' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/article[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\ArticleController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],

                'admin-comment' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/comment[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\CommentController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],

                'admin-finance' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/finance[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\FinanceController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],

                //管理模块
                'admin-setting' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_ADMIN . '/setting[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\SettingController::class,
                            'action'     => 'adminList',
                        ],
                    ],
                ],

            ],
        ],
        'controller_plugins' => array(
            'invokables' => array(
                'AdminPlugin'   => 'Admin\Controller\Plugin\AdminPlugin',
            )
        ),
        'controllers' => [
            'factories' => [
                Controller\IndexController::class => InvokableFactory::class,
                Controller\SettingController::class => InvokableFactory::class,
                Controller\ArticleController::class => InvokableFactory::class,
                Controller\AudioController::class => InvokableFactory::class,
                Controller\BusinessController::class => InvokableFactory::class,
                Controller\CommentController::class => InvokableFactory::class,
                Controller\FinanceController::class => InvokableFactory::class,
                Controller\MicroblogController::class => InvokableFactory::class,
                Controller\UserController::class => InvokableFactory::class,
                Controller\VideoController::class => InvokableFactory::class,
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
