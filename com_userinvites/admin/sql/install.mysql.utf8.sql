DROP TABLE IF EXISTS `#__userinvites`;

CREATE TABLE `#__userinvites` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
  `code` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
  `groups` TEXT COLLATE utf8_unicode_ci,
  `email_body` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `sent_by` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `sent_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expires` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
)
    ENGINE          = MyISAM
    AUTO_INCREMENT  = 0
    DEFAULT CHARSET = utf8;