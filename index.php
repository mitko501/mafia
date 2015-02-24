<?php
ob_start();
require_once('/config/define.php');
require_once(BASE_DIR . 'libs/FirePHPCore/FirePHP.class.php');
require_once(BASE_DIR . 'libs/FirePHPCore/fb.php');
require_once(BASE_DIR . 'registry/MySQL.class.php');
require_once(BASE_DIR . 'registry/registry.php');

$registry= new Registry();
$registry->setDebugging(true);//DEBUG
$registry->firephp->log('index.php: start logging');
$registry->getObject('MySQL')->newConnection('');

$controller = $registry->getObject('url')->getUrlBit(0);
include(BASE_DIR . 'controllers/' . $controller . '/controller.php' );
$controller = $controller.'Controller';
new $controller($registry);
$registry->firephp->log('End of PHP.');
?>