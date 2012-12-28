CREATE TABLE `notice_log` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `log` varchar(1500) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'LOG正文',
  `level` tinyint(3) unsigned zerofill NOT NULL DEFAULT '000' COMMENT 'LOG等级: 0:normal;1:warning;2:error',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8

CREATE TABLE `notice_mail_table` (
  `id` int(10) unsigned NOT NULL COMMENT '邮件id',
  `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '发送邮箱',
  `template` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '邮件模板',
  `servicetype` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '发送通道',
  `ctime` int(11) unsigned zerofill NOT NULL DEFAULT '00000000000',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '发送状态:0:等待;1:发送中;2:错误;3:失败;4:成功',
  `store` text CHARACTER SET utf8 COLLATE utf8_bin COMMENT '邮件所有内容，json字符串格式',
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `template` (`template`),
  KEY `servicetype` (`servicetype`),
  KEY `ctime` (`ctime`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE `notice_sms_table` (
  `id` int(11) unsigned NOT NULL COMMENT '短信ID',
  `mobile` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '手机号码',
  `type` tinyint(3) unsigned zerofill NOT NULL DEFAULT '000' COMMENT '类型',
  `servicetype` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '服务商标识',
  `status` tinyint(3) unsigned zerofill NOT NULL DEFAULT '000' COMMENT '发送状态',
  `template` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '短信模板',
  `ctime` int(11) unsigned zerofill NOT NULL DEFAULT '00000000000' COMMENT '创建时间',
  `store` text CHARACTER SET utf8 COLLATE utf8_bin COMMENT '原始数据，JSON格式',
  PRIMARY KEY (`id`),
  KEY `mobile` (`mobile`),
  KEY `type` (`type`),
  KEY `servicetype` (`servicetype`),
  KEY `status` (`status`),
  KEY `ctime` (`ctime`),
  KEY `template` (`template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8