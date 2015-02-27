<?php
/**
 * Definicia dolezitych premennych
 * User: mitko
 */
DEFINE("BASE_DIR", dirname(dirname(__FILE__)) . "/");  //cesta
DEFINE("BASE_LINK", 'http://192.168.10.248/mafia/');  //url
//DEFINE("BASE_DIR",BASE_LINK);
//DEFINE('BASE_LINK','http://147.251.44.67/moto/');
DEFINE('THEME','default');
DEFINE('LOGIN_CONTROLLER','main');
DEFINE('MAIN_CONTROLLER','main');
date_default_timezone_set('Europe/Bratislava');