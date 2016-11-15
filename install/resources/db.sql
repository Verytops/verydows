-- ----------------------------
-- Table structure for `admin`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#admin`;
CREATE TABLE `#tablepre#admin` (
  `user_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(16) NOT NULL,
  `password` char(32) NOT NULL,
  `name` varchar(60) NOT NULL DEFAULT '',
  `email` varchar(60) NOT NULL DEFAULT '',
  `last_ip` char(15) NOT NULL DEFAULT '',
  `last_date` int(10) unsigned NOT NULL DEFAULT '0',
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  `hash` char(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `admin_active`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#admin_active`;
CREATE TABLE `#tablepre#admin_active` (
  `sess_id` char(32) NOT NULL DEFAULT '',
  `user_id` smallint(5) unsigned NOT NULL,
  `ip` char(15) NOT NULL DEFAULT '0.0.0.0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `expires` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sess_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `admin_role`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#admin_role`;
CREATE TABLE `#tablepre#admin_role` (
  `user_id` smallint(5) unsigned NOT NULL,
  `role_id` smallint(5) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `adv`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#adv`;
CREATE TABLE `#tablepre#adv` (
  `adv_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `position_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `type` char(5) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `codes` text NOT NULL,
  `start_date` int(10) unsigned NOT NULL DEFAULT '0',
  `end_date` int(10) unsigned NOT NULL DEFAULT '0',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '99',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`adv_id`),
  KEY `position_id` (`position_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `adv_position`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#adv_position`;
CREATE TABLE `#tablepre#adv_position` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `width` smallint(4) unsigned NOT NULL DEFAULT '0',
  `height` smallint(4) unsigned NOT NULL DEFAULT '0',
  `codes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `aftersales`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#aftersales`;
CREATE TABLE `#tablepre#aftersales` (
  `as_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `order_id` char(15) NOT NULL DEFAULT '',
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_qty` smallint(5) unsigned NOT NULL DEFAULT '1',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `cause` text NOT NULL,
  `mobile` char(11) NOT NULL DEFAULT '',
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`as_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `aftersales_message`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#aftersales_message`;
CREATE TABLE `#tablepre#aftersales_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `as_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `admin_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `as_id` (`as_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `article`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#article`;
CREATE TABLE `#tablepre#article` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `title` varchar(180) NOT NULL,
  `picture` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `brief` varchar(240) NOT NULL DEFAULT '',
  `meta_keywords` varchar(240) NOT NULL DEFAULT '',
  `meta_description` varchar(240) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cate_id` USING BTREE(`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `article_cate`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#article_cate`;
CREATE TABLE `#tablepre#article_cate` (
  `cate_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cate_name` varchar(60) CHARACTER SET utf8 NOT NULL,
  `meta_keywords` varchar(240) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `meta_description` varchar(240) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '99',
  PRIMARY KEY (`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `brand`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#brand`;
CREATE TABLE `#tablepre#brand` (
  `brand_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(60) NOT NULL DEFAULT '',
  `brand_logo` varchar(255) NOT NULL DEFAULT '',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '99',
  PRIMARY KEY (`brand_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `email_queue`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#email_queue`;
CREATE TABLE `#tablepre#email_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(60) NOT NULL DEFAULT '',
  `tpl_id` char(30) NOT NULL DEFAULT '',
  `subject` varchar(240) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `is_html` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `last_err` varchar(255) NOT NULL DEFAULT '',
  `err_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `email_subscription`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#email_subscription`;
CREATE TABLE `#tablepre#email_subscription` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(60) NOT NULL DEFAULT '',
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `email_template`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#email_template`;
CREATE TABLE `#tablepre#email_template` (
  `id` char(30) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `subject` varchar(240) NOT NULL DEFAULT '',
  `is_html` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of email_template
-- ----------------------------
INSERT INTO `#tablepre#email_template` VALUES ('email_captcha', '邮箱验证码', '验证码', '1');

-- ----------------------------
-- Table structure for `feedback`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#feedback`;
CREATE TABLE `#tablepre#feedback` (
  `fb_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(120) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `mobile` char(11) NOT NULL DEFAULT '',
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fb_id`),
  KEY `user_id` USING BTREE(`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `feedback_message`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#feedback_message`;
CREATE TABLE `#tablepre#feedback_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fb_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `admin_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fb_id` USING BTREE(`fb_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `friendlink`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#friendlink`;
CREATE TABLE `#tablepre#friendlink` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods`;
CREATE TABLE `#tablepre#goods` (
  `goods_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `brand_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `goods_name` varchar(180) NOT NULL DEFAULT '',
  `goods_sn` char(20) NOT NULL DEFAULT '',
  `now_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `original_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `goods_image` varchar(30) NOT NULL DEFAULT '',
  `goods_brief` text NOT NULL,
  `goods_content` text NOT NULL,
  `goods_weight` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `stock_qty` smallint(4) unsigned NOT NULL DEFAULT '0',
  `meta_keywords` varchar(240) NOT NULL DEFAULT '',
  `meta_description` varchar(240) NOT NULL DEFAULT '',
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  `newarrival` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `bargain` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`goods_id`),
  KEY `cate_id` (`cate_id`),
  FULLTEXT KEY `indexing` (`goods_name`,`meta_keywords`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods_album`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods_album`;
CREATE TABLE `#tablepre#goods_album` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` mediumint(8) unsigned NOT NULL,
  `image` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods_attr`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods_attr`;
CREATE TABLE `#tablepre#goods_attr` (
  `goods_id` mediumint(8) unsigned NOT NULL,
  `attr_id` mediumint(8) NOT NULL,
  `value` varchar(160) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods_cate`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods_cate`;
CREATE TABLE `#tablepre#goods_cate` (
  `cate_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `cate_name` varchar(60) NOT NULL DEFAULT '',
  `meta_keywords` varchar(240) NOT NULL DEFAULT '',
  `meta_description` varchar(240) NOT NULL DEFAULT '',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '99',
  PRIMARY KEY (`cate_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods_cate_attr`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods_cate_attr`;
CREATE TABLE `#tablepre#goods_cate_attr` (
  `attr_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(60) NOT NULL DEFAULT '',
  `opts` text NOT NULL,
  `uom` varchar(20) NOT NULL DEFAULT '',
  `filtrate` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '99',
  PRIMARY KEY (`attr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods_cate_brand`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods_cate_brand`;
CREATE TABLE `#tablepre#goods_cate_brand` (
  `cate_id` smallint(5) unsigned NOT NULL,
  `brand_id` smallint(5) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods_optional`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods_optional`;
CREATE TABLE `#tablepre#goods_optional` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `opt_text` varchar(80) NOT NULL DEFAULT '',
  `opt_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods_optional_type`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods_optional_type`;
CREATE TABLE `#tablepre#goods_optional_type` (
  `type_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods_related`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods_related`;
CREATE TABLE `#tablepre#goods_related` (
  `goods_id` mediumint(8) NOT NULL,
  `related_id` mediumint(8) NOT NULL,
  `direction` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`goods_id`,`related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `goods_review`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#goods_review`;
CREATE TABLE `#tablepre#goods_review` (
  `review_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` char(15) NOT NULL DEFAULT '',
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rating` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `content` text NOT NULL,
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `replied` text NOT NULL,
  PRIMARY KEY (`review_id`),
  KEY `goods_id` (`goods_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `help`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#help`;
CREATE TABLE `#tablepre#help` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `meta_keywords` varchar(240) NOT NULL DEFAULT '',
  `meta_description` varchar(240) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cate_id` (`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `help_cate`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#help_cate`;
CREATE TABLE `#tablepre#help_cate` (
  `cate_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cate_name` varchar(60) NOT NULL DEFAULT '',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `navigation`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#navigation`;
CREATE TABLE `#tablepre#navigation` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `position` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `target` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '99',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `oauth`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#oauth`;
CREATE TABLE `#tablepre#oauth` (
  `party` char(10) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL,
  `params` text NOT NULL,
  `instruction` varchar(240) NOT NULL DEFAULT '',
  `enable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`party`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oauth
-- ----------------------------
INSERT INTO `#tablepre#oauth` VALUES ('qq', '腾讯QQ', '{\"app_id\":\"\",\"app_key\":\"\"}', 'QQ互联开放平台为第三方网站提供了丰富的API。第三方网站接入QQ互联开放平台后，即可通过调用平台提供的API实现用户使用QQ帐号登录网站功能，且可以获取到腾讯QQ用户的相关信息。', '1');
INSERT INTO `#tablepre#oauth` VALUES ('weibo', '新浪微博', '{\"app_key\":\"\",\"app_secret\":\"\"}', '网站接入是微博针对第三方网站提供的社会化网络接入方案。接入微连接让您的网站支持用微博帐号登录，基于OAuth2.0协议，使用微博 Open API 进行开发， 即可用微博帐号登录你的网站， 让你的网站降低新用户注册成本，快速获取大量用户。', '1');

-- ----------------------------
-- Table structure for `order`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#order`;
CREATE TABLE `#tablepre#order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` char(15) NOT NULL DEFAULT '',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `shipping_method` smallint(5) unsigned NOT NULL DEFAULT '0',
  `payment_method` smallint(5) unsigned NOT NULL DEFAULT '0',
  `order_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `goods_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `shipping_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `order_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `memos` varchar(240) NOT NULL DEFAULT '',
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  `payment_date` int(10) unsigned NOT NULL DEFAULT '0',
  `thirdparty_trade_id` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `order_consignee`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#order_consignee`;
CREATE TABLE `#tablepre#order_consignee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` char(15) NOT NULL DEFAULT '',
  `receiver` varchar(20) NOT NULL DEFAULT '',
  `province` varchar(20) NOT NULL DEFAULT '',
  `city` varchar(20) NOT NULL DEFAULT '',
  `borough` varchar(20) NOT NULL DEFAULT '',
  `address` varchar(240) NOT NULL DEFAULT '',
  `zip` char(6) NOT NULL DEFAULT '',
  `mobile` char(11) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `order_goods`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#order_goods`;
CREATE TABLE `#tablepre#order_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` char(15) NOT NULL DEFAULT '',
  `goods_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_name` varchar(180) NOT NULL DEFAULT '',
  `goods_image` varchar(30) NOT NULL DEFAULT '',
  `goods_qty` smallint(5) NOT NULL DEFAULT '1',
  `goods_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `is_reviewed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `order_goods_optional`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#order_goods_optional`;
CREATE TABLE `#tablepre#order_goods_optional` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `map_id` int(10) unsigned NOT NULL DEFAULT '0',
  `opt_id` int(10) unsigned NOT NULL DEFAULT '0',
  `opt_type` varchar(20) NOT NULL DEFAULT '',
  `opt_text` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `order_log`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#order_log`;
CREATE TABLE `#tablepre#order_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` char(15) NOT NULL DEFAULT '',
  `admin_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `operate` char(10) NOT NULL,
  `cause` varchar(240) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `order_shipping`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#order_shipping`;
CREATE TABLE `#tablepre#order_shipping` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` char(15) NOT NULL DEFAULT '',
  `carrier_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `tracking_no` varchar(20) NOT NULL DEFAULT '',
  `memos` varchar(240) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `payment_method`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#payment_method`;
CREATE TABLE `#tablepre#payment_method` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(30) NOT NULL DEFAULT '',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pcode` varchar(20) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `instruction` varchar(240) NOT NULL DEFAULT '',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '99',
  `enable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of payment_method
-- ----------------------------
INSERT INTO `#tablepre#payment_method` VALUES ('1', '余额支付', '0', 'balance', '[\"\"]', '', '2', '1');
INSERT INTO `#tablepre#payment_method` VALUES ('2', '货到付款', '1', 'cod', '[\"\"]', '', '3', '1');
INSERT INTO `#tablepre#payment_method` VALUES ('3', '支付宝', '0', 'alipay', '{\"seller\":\"\",\"partner\":\"\",\"key\":\"\"}', '', '1', '1');

-- ----------------------------
-- Table structure for `request_error`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#request_error`;
CREATE TABLE `#tablepre#request_error` (
  `ip` char(15) NOT NULL DEFAULT '0.0.0.0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `count` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `lockout` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`,`dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `role`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#role`;
CREATE TABLE `#tablepre#role` (
  `role_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL DEFAULT '',
  `role_desc` varchar(240) NOT NULL DEFAULT '',
  `role_acl` text NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `sendmail_limit`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#sendmail_limit`;
CREATE TABLE `#tablepre#sendmail_limit` (
  `ip` char(15) NOT NULL DEFAULT '',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` char(30) NOT NULL DEFAULT '',
  `count` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `setting`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#setting`;
CREATE TABLE `#tablepre#setting` (
  `sk` varchar(30) NOT NULL,
  `sv` text NOT NULL,
  PRIMARY KEY (`sk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of setting
-- ----------------------------
INSERT INTO `#tablepre#setting` VALUES ('site_name', 'Verydows 开源电商系统');
INSERT INTO `#tablepre#setting` VALUES ('home_title', 'Verydows 开源电子商务系统 | 轻松开启电商之旅');
INSERT INTO `#tablepre#setting` VALUES ('home_keywords', '');
INSERT INTO `#tablepre#setting` VALUES ('home_description', '');
INSERT INTO `#tablepre#setting` VALUES ('footer_info', '<div class=\"copyright mt10\"><p>联系邮箱：service@verydows.com<span class=\"sep\">|</span>QQ交流群：372701906<span class=\"sep\">|</span>Copyright © 2016 Verydows.com 版权所有</p></div>');
INSERT INTO `#tablepre#setting` VALUES ('goods_search_per_num', '20');
INSERT INTO `#tablepre#setting` VALUES ('upload_filetype', '.jpg|.jpeg|.gif|.png|.bmp|.swf|.flv|.avi|.rmvb');
INSERT INTO `#tablepre#setting` VALUES ('upload_filesize', '2MB');
INSERT INTO `#tablepre#setting` VALUES ('captcha_admin_login', '2');
INSERT INTO `#tablepre#setting` VALUES ('captcha_user_login', '2');
INSERT INTO `#tablepre#setting` VALUES ('captcha_user_register', '1');
INSERT INTO `#tablepre#setting` VALUES ('captcha_feedback', '1');
INSERT INTO `#tablepre#setting` VALUES ('smtp_server', 'smtp.163.com');
INSERT INTO `#tablepre#setting` VALUES ('smtp_user', 'test@163.com');
INSERT INTO `#tablepre#setting` VALUES ('smtp_password', '');
INSERT INTO `#tablepre#setting` VALUES ('smtp_port', '25');
INSERT INTO `#tablepre#setting` VALUES ('smtp_secure', '');
INSERT INTO `#tablepre#setting` VALUES ('admin_mult_ip_login', '0');
INSERT INTO `#tablepre#setting` VALUES ('upload_goods_filesize', '300KB');
INSERT INTO `#tablepre#setting` VALUES ('visitor_stats', '1');
INSERT INTO `#tablepre#setting` VALUES ('goods_hot_searches', '');
INSERT INTO `#tablepre#setting` VALUES ('cate_goods_per_num', '20');
INSERT INTO `#tablepre#setting` VALUES ('goods_history_num', '5');
INSERT INTO `#tablepre#setting` VALUES ('goods_related_num', '5');
INSERT INTO `#tablepre#setting` VALUES ('goods_review_per_num', '10');
INSERT INTO `#tablepre#setting` VALUES ('upload_goods_filetype', '.jpg|.png|.gif');
INSERT INTO `#tablepre#setting` VALUES ('show_goods_stock', '0');
INSERT INTO `#tablepre#setting` VALUES ('order_cancel_expires', '2');
INSERT INTO `#tablepre#setting` VALUES ('goods_img_thumb', '[{\"w\":350,\"h\":350},{\"w\":150,\"h\":150},{\"w\":100,\"h\":100},{\"w\":50,\"h\":50}]');
INSERT INTO `#tablepre#setting` VALUES ('goods_album_thumb', '[{\"w\":350,\"h\":350},{\"w\":50,\"h\":50}]');
INSERT INTO `#tablepre#setting` VALUES ('enabled_theme', 'default');
INSERT INTO `#tablepre#setting` VALUES ('user_consignee_limits', '15');
INSERT INTO `#tablepre#setting` VALUES ('upload_avatar_filesize', '200KB');
INSERT INTO `#tablepre#setting` VALUES ('order_delivery_expires', '7');
INSERT INTO `#tablepre#setting` VALUES ('user_register_email_verify', '0');
INSERT INTO `#tablepre#setting` VALUES ('user_review_approve', '0');
INSERT INTO `#tablepre#setting` VALUES ('rewrite_enable', '1');
INSERT INTO `#tablepre#setting` VALUES ('data_cache_lifetime', '7200');
INSERT INTO `#tablepre#setting` VALUES ('goods_fulltext_query', '0');
INSERT INTO `#tablepre#setting` VALUES ('debug', '1');
INSERT INTO `#tablepre#setting` VALUES ('rewrite_rule', '{\"m\\/pay\\/return\\/<pcode>.html\":\"mobile\\/pay\\/return\",\"pay\\/return\\/<pcode>.html\":\"pay\\/return\",\"api\\/pay\\/notify\\/<pcode>\":\"api\\/pay\\/notify\",\"api\\/oauth\\/callback\\/<party>\":\"api\\/oauth\\/callback\",\"m\\/index.html\":\"mobile\\/main\\/index\",\"m\\/<c>\\/<a>.html\":\"mobile\\/<c>\\/<a>\",\"api\\/<c>\\/<a>\":\"api\\/<c>\\/<a>\",\"404.html\":\"main\\/404\",\"index.html\":\"main\\/index\",\"<c>\\/<a>.html\":\"<c>\\/<a>\"}');
INSERT INTO `#tablepre#setting` VALUES ('encrypt_key', '9e290273e0f7eeb6747911efb164b0b7');

-- ----------------------------
-- Table structure for `shipping_carrier`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#shipping_carrier`;
CREATE TABLE `#tablepre#shipping_carrier` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `tracking_url` varchar(255) NOT NULL DEFAULT '',
  `service_tel` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `shipping_method`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#shipping_method`;
CREATE TABLE `#tablepre#shipping_method` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `instruction` varchar(240) NOT NULL DEFAULT '',
  `seq` tinyint(2) unsigned NOT NULL DEFAULT '99',
  `enable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of shipping_method
-- ----------------------------
INSERT INTO `#tablepre#shipping_method` VALUES ('1', '普通快递', '{\"0\":{\"type\":\"fixed\",\"area\":\"0\",\"charges\":\"0\"}}', '全国范围免邮', '1', '1');

-- ----------------------------
-- Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#user`;
CREATE TABLE `#tablepre#user` (
  `user_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(16) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `email` varchar(60) NOT NULL DEFAULT '',
  `mobile` char(11) NOT NULL DEFAULT '',
  `avatar` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `email_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `mobile_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `user_account`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#user_account`;
CREATE TABLE `#tablepre#user_account` (
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `balance` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `points` int(10) unsigned NOT NULL DEFAULT '0',
  `exp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `user_account_log`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#user_account_log`;
CREATE TABLE `#tablepre#user_account_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `admin_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `points` int(10) unsigned NOT NULL DEFAULT '0',
  `exp` int(10) unsigned NOT NULL DEFAULT '0',
  `cause` varchar(255) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `user_consignee`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#user_consignee`;
CREATE TABLE `#tablepre#user_consignee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `receiver` varchar(20) NOT NULL DEFAULT '',
  `province` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `city` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `borough` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `address` varchar(240) NOT NULL DEFAULT '',
  `zip` char(6) NOT NULL DEFAULT '',
  `mobile` char(11) NOT NULL DEFAULT '',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `user_favorite`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#user_favorite`;
CREATE TABLE `#tablepre#user_favorite` (
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `goods_id` mediumint(8) unsigned NOT NULL,
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`goods_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `user_group`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#user_group`;
CREATE TABLE `#tablepre#user_group` (
  `group_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(60) NOT NULL,
  `min_exp` int(10) unsigned NOT NULL DEFAULT '0',
  `discount_rate` tinyint(3) unsigned NOT NULL DEFAULT '100',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_group
-- ----------------------------
INSERT INTO `#tablepre#user_group` VALUES ('1', '初级会员', '0', '100');
INSERT INTO `#tablepre#user_group` VALUES ('2', '中级会员', '2000', '100');
INSERT INTO `#tablepre#user_group` VALUES ('3', '高级会员', '7000', '100');

-- ----------------------------
-- Table structure for `user_oauth`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#user_oauth`;
CREATE TABLE `#tablepre#user_oauth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `party` char(10) NOT NULL DEFAULT '',
  `oauth_key` char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `user_profile`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#user_profile`;
CREATE TABLE `#tablepre#user_profile` (
  `user_id` mediumint(8) unsigned NOT NULL,
  `nickname` varchar(30) NOT NULL DEFAULT '',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `birth_year` smallint(4) unsigned NOT NULL DEFAULT '0',
  `birth_month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `birth_day` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `qq` varchar(15) NOT NULL DEFAULT '',
  `signature` varchar(120) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `user_record`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#user_record`;
CREATE TABLE `#tablepre#user_record` (
  `user_id` mediumint(8) unsigned NOT NULL,
  `created_date` int(10) unsigned NOT NULL DEFAULT '0',
  `created_ip` char(15) NOT NULL DEFAULT '0.0.0.0',
  `last_date` int(10) unsigned NOT NULL DEFAULT '0',
  `last_ip` char(15) NOT NULL DEFAULT '0.0.0.0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `visitor_stats`
-- ----------------------------
DROP TABLE IF EXISTS `#tablepre#visitor_stats`;
CREATE TABLE `#tablepre#visitor_stats` (
  `sessid` char(32) NOT NULL DEFAULT '',
  `ip` char(15) NOT NULL DEFAULT '0.0.0.0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `pv` smallint(5) unsigned NOT NULL DEFAULT '1',
  `referrer` varchar(80) NOT NULL DEFAULT '',
  `browser` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `platform` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `area` char(10) NOT NULL DEFAULT '',
  KEY `sessid` USING BTREE(`sessid`),
  KEY `ip` (`ip`),
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;