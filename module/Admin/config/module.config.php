<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Common' => 'Admin\Controller\CommonController',
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Admin' => 'Admin\Controller\AdminController',
            'Admin\Controller\Ads' => 'Admin\Controller\AdsController',
            'Admin\Controller\System'=> 'Admin\Controller\SystemController',
            'Admin\Controller\User'=> 'Admin\Controller\UserController',
            'Admin\Controller\Manage'=> 'Admin\Controller\ManageController',
            'Admin\Controller\ContentList'=> 'Admin\Controller\ContentListController',
            'Admin\Controller\Comment'=> 'Admin\Controller\CommentController',
            'Admin\Controller\Order'=> 'Admin\Controller\OrderController',
            'Admin\Controller\GroupMember'=> 'Admin\Controller\GroupMemberController',
            'Admin\Controller\Feedback'=> 'Admin\Controller\FeedbackController',
            'Admin\Controller\Detail'=>'Admin\Controller\DetailController',
        ),
           
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
            'admin' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN.'[/:controller][/a:action][/i:id][/pid:pid][/c:cid][/t:types][/n:num][/au:a_type][/p:page][/k:keyword][/add:add]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'pid' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'types' => '[0-9]*',
                        'a_type' => '[0-9]*',
                        'num' => '[0-9]*',
                        'page' => '[0-9]*',
                        'keyword' => '[\w\W]*',
                        'add' => '[0-9]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => 'index',
                        'action' => 'index'
                    )
                )
            ),
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        //'not_found_template' => 'error/404',
        //'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout'=>__DIR__ . '/../view/admin/layout/layout1.phtml',
            'admin/layout' => __DIR__ . '/../view/admin/layout/layout.phtml',
            'admin/menu' => __DIR__ . '/../view/admin/layout/menu.phtml',
            'admin/menu2' => __DIR__ . '/../view/admin/layout/menu2.phtml',
            'error/404' => __DIR__ . '/../view/admin/error/404.phtml',
            'error/index' => __DIR__ . '/../view/admin/error/index.phtml',
            'page'        => __DIR__ . '/../view/admin/layout/page.phtml',

        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);