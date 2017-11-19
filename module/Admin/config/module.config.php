<?php
    /**
     * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
     * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     */
    
    namespace Platform;
    
    use Zend\Router\Http\Literal;
    use Zend\Router\Http\Segment;
    use Zend\ServiceManager\Factory\InvokableFactory;
    
    return [
        'router' => [
            'routes' => [
                'platform' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/index[/:action][/:page]',
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
                'platform-user' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/user[/a:action][/p:page][/i:id]',
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
                //商家管理
                'platform-store' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/store[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\StoreController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //商品管理
                'platform-product' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/product[/a:action][/p:page][/c:category_id][/l:label_id][/g:goods_id][/s:store_id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\ProductController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //服务管理
                'platform-service' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM.'/service[/a:action][/p:page][/c:category_id][/l:label_id][/t:type_id][/g:goods_id][/s:store_id][/at:attribute_id][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\ServiceController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //订单管理
                'platform-order' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/order[/a:action][/p:page][/i:id][/s:sn][/t:status]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\OrderController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //评论管理
                'platform-comment' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/comment[/a:action][/p:page][/i:id][/m:mid]',
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
                //优惠券管理
                'platform-coupon' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/coupon[/a:action][/p:page][/i:id][/m:mid]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\CouponController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //分销管理
                'platform-distribution' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/distribution[/a:action][/p:page][/i:id][/m:mid]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\DistributionController::class,
                            'action'     => 'userShare',
                        ],
                    ],
                ],
                //推送消息管理
                'platform-message' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/message[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\MessageController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //审核模块
                'platform-check' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/check[/a:action][/p:page][/i:id][/s:status]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\CheckController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //物流模块
                'platform-delivery' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/delivery[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\DeliveryController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                // 物流配送费用
                'platform-exp' => [
                    'type'    => Segment::class,
                    'options' => [
                        'route'    => '/platform/exp[/:action][/:page]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\ExpressTemplateController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                // 社区配送费用
                'platform-community' => [
                    'type'    => Segment::class,
                    'options' => [
                        'route'    => '/platform/community[/:action][/:page]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\CommunityExpressTemplateController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //财务模块
                'platform-finance' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/finance[/a:action][/p:page][/i:id][/s:status]',
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
                //日志模块
                'platform-log' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/log[/a:action][/p:page]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\LogController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //运营模块
                'platform-business' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/business[/a:action][/i:id][/p:page][/n:navigation_id][/m:material_id][/ad:advert_id][/s:spread_id][/b:scare_id][/ac:activity_id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\BusinessController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //管理模块
                'platform-setting' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/setting[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\SettingController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
                //增值服务模块
                'platform-appreciation' => [
                    'type'    => Segment::class,
                    'options' => ['route' => MODULE_PLATFORM . '/appreciation[/a:action][/p:page][/i:id]',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'page' => '[0-9]+'
                        ),
                        'defaults' => [
                            'controller' => Controller\AppreciationController::class,
                            'action'     => 'index',
                        ],
                    ],
                ],
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
                Controller\UserController::class => InvokableFactory::class,
                Controller\StoreController::class => InvokableFactory::class,
                Controller\ProductController::class => InvokableFactory::class,
                Controller\ServiceController::class => InvokableFactory::class,
                Controller\OrderController::class => InvokableFactory::class,
                Controller\CommentController::class => InvokableFactory::class,
                Controller\CouponController::class => InvokableFactory::class,
                Controller\DistributionController::class => InvokableFactory::class,
                Controller\MessageController::class => InvokableFactory::class,
                Controller\CheckController::class => InvokableFactory::class,
                Controller\DeliveryController::class => InvokableFactory::class,
                Controller\ExpressTemplateController::class => InvokableFactory::class,
                Controller\CommunityExpressTemplateController::class => InvokableFactory::class,
                Controller\FinanceController::class => InvokableFactory::class,
                Controller\LogController::class => InvokableFactory::class,
                Controller\BusinessController::class => InvokableFactory::class,
                Controller\SettingController::class => InvokableFactory::class,
                Controller\AppreciationController::class => InvokableFactory::class,
            ],
        ],
        'view_manager' => [
            'display_not_found_reason' => true,
            'display_exceptions'       => true,
            'doctype'                  => 'HTML5',
            'not_found_template'       => 'error/404',
            'exception_template'       => 'error/index',
            'template_map' => ['layout/layout' => __DIR__ . '/../view/layout/layout.phtml', 'page' => __DIR__ . '/../view/layout/page.phtml',
                'platform/index/index' => __DIR__ . '/../view/platform/index/index.phtml',
                'error/404'               => __DIR__ . '/../view/error/404.phtml',
                'error/index'             => __DIR__ . '/../view/error/index.phtml',
            ],
            'template_path_stack' => [
                __DIR__ . '/../view',
            ],
        ],
    ];
