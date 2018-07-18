CREATE TABLE `shopy_nf_brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`name` VARCHAR(10) DEFAULT '',
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



CREATE TABLE `shopy_nf_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`uid` int(11)  NOT NULL,
`sum` int(11) DEFAULT '0',
`hold` int(11) DEFAULT '0',
  `deleted` tinyint(3) DEFAULT '0',
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `shopy_nf_order_benefit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  
`oid` int(11)  NOT NULL,
`type` tinyint(5) DEFAULT '0',
`rule` VARCHAR(255) DEFAULT '',
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table shopy_nf_product add brand_id int(11) DEFAULT 0;
alter table shopy_nf_item add brand_id int(11) DEFAULT 0;
alter table shopy_nf_brand add logo VARCHAR (255) DEFAULT '';
alter table shopy_nf_order_pre add dealer_profit int(11) DEFAULT '0';
alter table shopy_nf_order add dealer_profit int(11) DEFAULT '0';


-------------------



CREATE TABLE `shopy_nf_item_block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`iid` int(11)  NOT NULL,
`type` tinyint(5) DEFAULT '1',
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-------------------
alter table shopy_nf_needs add remark VARCHAR (1024) DEFAULT '';
alter table shopy_nf_needs_type add tips VARCHAR (1024) DEFAULT '';

CREATE TABLE `shopy_nf_out_cash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`extra` varchar(1024)  NOT NULL,
`create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


alter table shopy_nf_out_cash add name VARCHAR (20) DEFAULT '';
alter table shopy_nf_out_cash add bank_name VARCHAR (20) DEFAULT '';

alter table shopy_nf_out_cash add bank_code VARCHAR (50) DEFAULT '';
alter table shopy_nf_out_cash add sum int (11) DEFAULT '0';
alter table shopy_nf_out_cash add status tinyint (5) DEFAULT '0';
alter table shopy_nf_out_cash add uid int (11) DEFAULT '0';



CREATE TABLE `shopy_nf_ones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`qq` varchar(50)  NOT NULL,
`yy` varchar(50)  NOT NULL,
`status` tinyint(3) DEFAULT '1',
`create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table shopy_nf_ones add type int (11) DEFAULT '0';


CREATE TABLE `shopy_nf_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`ip` varchar(20)  NOT NULL,
`count` INT(11) DEFAULT '1',
`create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table shopy_nf_clicks add type int (11) DEFAULT '0';


CREATE TABLE `shopy_nf_docs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `title` VARCHAR(50) DEFAULT '',
  `method` VARCHAR(10) DEFAULT '',
`content_from` text ,
`content_to` text ,
`create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


alter table shopy_nf_docs add url varchar (200) DEFAULT '';
alter table shopy_nf_docs add last_edit_time datetime DEFAULT NULL;


alter table shopy_nf_user add count int (11) DEFAULT '0';
alter table shopy_nf_user add openid varchar (50) DEFAULT '';
alter table shopy_nf_article add uid int(11) DEFAULT '0';
alter table shopy_nf_article add from tinyint(3) DEFAULT '1';

CREATE TABLE `shopy_nf_article_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL DEFAULT '1',
  `desc` VARCHAR(10) DEFAULT '',
  `uid` int(11) DEFAULT '0',
`aid` int(11) DEFAULT '0',
`create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table shopy_nf_article add status tinyint(3) DEFAULT '0';


CREATE TABLE `shopy_nf_user_operate_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL DEFAULT '1',
  `desc` VARCHAR(10) DEFAULT '',
  `uid` int(11) DEFAULT '0',
`count` int(11) DEFAULT '0',
`create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table shopy_nf_user_operate_limit add gmt int(11) DEFAULT '0';

CREATE TABLE `shopy_nf_article_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL DEFAULT '1',
  `desc` VARCHAR(10) DEFAULT '',
  `count` int(11) DEFAULT '0',
`aid` int(11) DEFAULT '0',
`create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `shopy_nf_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL DEFAULT '1',
  `title` VARCHAR(10) DEFAULT '',
   `img` VARCHAR(50) DEFAULT '',
  `content`  text ,
  `extra` VARCHAR(500) DEFAULT '',
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `shopy_nf_user_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',

  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `shopy_nf_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `title` VARCHAR(10) DEFAULT '',
  `img` VARCHAR(50) DEFAULT '',
  `url`  VARCHAR(50) DEFAULT '',
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
alter table shopy_nf_page add tmp_data MediumText;

CREATE TABLE `shopy_nf_vip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
   `type` tinyint(3) NOT NULL DEFAULT '1',
  `uid` int(11) NOT NULL DEFAULT '0',
  `start_time` datetime DEFAULT NULL,
    `end_time` datetime DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `shopy_nf_suggest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
   `type` tinyint(3) NOT NULL DEFAULT '1',
  `uid` int(11) NOT NULL DEFAULT '0',
 `content` VARCHAR(2048) DEFAULT '',
 `pid` int(11) NOT NULL DEFAULT '0',

  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `shopy_nf_page_sign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
     `page_id` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `shopy_nf_page_cutprice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `price` int(11) NOT NULL DEFAULT '0',
   `pid` int(11) NOT NULL DEFAULT '0',
    `cutprice` int(11) NOT NULL DEFAULT '0',
       `page_id` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `shopy_nf_page_praise` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `sum` int(11) NOT NULL DEFAULT '0',
   `pid` int(11) NOT NULL DEFAULT '0',
   `page_id` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `shopy_nf_page_sort` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_id` int(11) NOT NULL DEFAULT '0',
  `sum` int(11) NOT NULL DEFAULT '0',
     `page_id` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `shopy_nf_page_sort_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `sort_id` int(11) NOT NULL DEFAULT '0',
     `page_id` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



CREATE TABLE `shopy_nf_user_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
     `page_id` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


alter table shopy_nf_page_cutprice add remark VARCHAR (50) DEFAULT '';
alter table shopy_nf_page_sign add remark VARCHAR (50) DEFAULT '';
alter table shopy_nf_page_praise add remark VARCHAR (50) DEFAULT '';

alter table shopy_nf_article add imgs VARCHAR (1024) DEFAULT '';


CREATE TABLE `shopy_nf_article_pic_ids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
     `aid` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `shopy_nf_page_fightgroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `price` int(11) NOT NULL DEFAULT '0',
   `pid` int(11) NOT NULL DEFAULT '0',
    `group` VARCHAR (2048) DEFAULT '',
       `page_id` int(11) NOT NULL DEFAULT '0',
  `create_time` timestamp DEFAULT NOW(),
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table shopy_nf_page_fightgroup add status tinyint (3) DEFAULT '0';
alter table shopy_nf_page_fightgroup add group_number int (6) DEFAULT '0';
alter table shopy_nf_page_fightgroup add max_number int (6) DEFAULT '0';
alter table shopy_nf_page_fightgroup add phone varchar(12) DEFAULT '';
alter table shopy_nf_page_cutprice add phone varchar(12) DEFAULT '';
alter table shopy_nf_page_quick_buy add phone varchar(12) DEFAULT '';
alter table shopy_nf_page_sign add phone varchar(12) DEFAULT '';
alter table shopy_nf_page_praise add phone varchar(12) DEFAULT '';


CREATE TABLE `shopy_nf_activity_pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_no` varchar(50) DEFAULT NULL,
  `status` tinyint(3) DEFAULT '0',
  `uid` int(11) DEFAULT NULL,
  `extra_uid` int(11) DEFAULT NULL,
  `activity_id` varchar(255) DEFAULT NULL,
  `sum` int(11) DEFAULT NULL,
  `callback_content` VARCHAR (2048) DEFAULT NULL,
  `create_time` timestamp DEFAULT NOW(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pay_no` (`pay_no`) USING BTREE COMMENT '(null)'
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;
alter table shopy_nf_activity_pay add label VARCHAR (20) DEFAULT '';
alter table shopy_nf_page add status tinyint(1) DEFAULT '1';
