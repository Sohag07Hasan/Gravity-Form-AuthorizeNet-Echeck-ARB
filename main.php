<?php 
/*
 * Plugin Name: Gravity From addon Authorize.Net with Echeck Payment and ARB
 * Author: Mahibul Hasan Sohag
 * Description: Supports Echeck and ARB payment for Authorize.net
 * version: 1.0.0
 * author uri: http://sohag07hasan.elance.com
 * */


define('GfAuthorizeNetEcheckARB', dirname(__FILE__));
define('GfAuthorizeNetEcheckARB_FILE', __FILE__);
define('GfAuthorizeNetEcheckARB_URL', plugins_url('', __FILE__));

include GfAuthorizeNetEcheckARB . '/classes/authorizenet-settings.php';
AuthorizeNetSettings::init();

include GfAuthorizeNetEcheckARB . '/classes/Echeck.php';
AurhorizeNetEcheck::init();
