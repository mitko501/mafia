<?php
ob_start();
require_once('/config/define.php');
require_once(BASE_DIR . 'libs/FirePHPCore/FirePHP.class.php');
require_once(BASE_DIR . 'libs/FirePHPCore/fb.php');
require_once(BASE_DIR . 'registry/MySQL.class.php');
require_once(BASE_DIR . 'registry/registry.php');

$Registry= new Registry();
$Registry->setDebugging(true);//DEBUG
$Registry->firephp->log('start');
$Registry->createAndStoreObject('MySQL','db');
$Registry->GetObject('db')->newConnection('');
$Registry->createAndStoreObject('session','session');
$Registry->createAndStoreObject('user','usr');
$Registry->createAndStoreObject('url','url');

$controller=$Registry->GetObject('url')->GetUrlBit(0);
include(BASE_DIR . 'controllers/' . $controller . '/controller.php' );
$controller = $controller.'Controller';
new $controller($Registry);
$Registry->firephp->log('stop');
?>