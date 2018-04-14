<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Api;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
return ['router' => ['routes' => ['api-index' => ['type' => Segment::class, 'options' => ['route' => MODULE_API . '/index[/:action][/:page][/t:type][/i:id]', 'constraints' => array('controller' => '[a-zA-Z][a-zA-Z0-9_-]*', 'action' => '[a-zA-Z][a-zA-Z0-9_-]*', 'page' => '[0-9]+', 'type' => '[0-9]+', 'id' => '[0-9]+'), 'defaults' => ['controller' => Controller\IndexController::class, 'action' => 'index',],],],],], 'controllers' => ['factories' => [Controller\IndexController::class => InvokableFactory::class],], 'view_manager' => ['display_not_found_reason' => true, 'display_exceptions' => true, 'doctype' => 'HTML5', 'not_found_template' => 'error/404', 'exception_template' => 'error/index', 'template_map' => ['api/index/index' => __DIR__ . '/../view/api/index/index.phtml', 'error/404' => __DIR__ . '/../view/error/404.phtml', 'error/index' => __DIR__ . '/../view/error/index.phtml',], 'template_path_stack' => [__DIR__ . '/../view',],],];
