delimiter $$

CREATE DATABASE `1015220` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */$$

delimiter $$

CREATE TABLE `controllers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `privileges` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci$$


delimiter $$

CREATE TABLE `sessions` (
  `id` char(128) CHARACTER SET latin1 NOT NULL,
  `set_time` char(10) CHARACTER SET latin1 NOT NULL,
  `data` text CHARACTER SET latin1 NOT NULL,
  `session_key` char(128) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8$$


delimiter $$

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `salt` varchar(128) CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL,
  `active` enum('0','1') CHARACTER SET utf8 COLLATE utf8_slovak_ci NOT NULL DEFAULT '0',
  `privileges` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci$$