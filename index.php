<?php
ob_start();
require_once('/config/define.php');
require_once(BASE_DIR . 'registry/Registry.php');

$registry = new Registry();
$registry->setDebugging(true);//DEBUG
$registry->getFirePHP()->log('index.php: start logging');
$registry->getObject('MySQL')->newConnection('');

$controller = $registry->getObject('Url')->getController();
include(BASE_DIR . 'controllers/' . $controller . '/controller.php' );
$controller = $controller.'Controller';
new $controller($registry);
$registry->getFirePHP()->log('End of PHP.');
?>