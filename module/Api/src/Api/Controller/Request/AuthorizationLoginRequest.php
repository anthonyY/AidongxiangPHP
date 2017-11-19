<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 *
 * @author leezuolin
 *
 */
class AuthorizationLoginRequest extends Request
{

    /**
     * @var mobile
     */
    public $mobile = "mobile";

    /**
     * @var token
     */
    public $token = "token";
}