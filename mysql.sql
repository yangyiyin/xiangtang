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
alter table shopy_nf_order add pay_type int(3) DEFAULT '1';
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



CREATE TABLE `shopy_nf_conf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`key_name` varchar(20)  NOT NULL,
`content` varchar(1024)  NOT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table shopy_nf_order_pre add freight int (11) DEFAULT '0';
alter table shopy_nf_order add freight int (11) DEFAULT '0';

alter table shopy_nf_account_log add remark VARCHAR (255) DEFAULT '';


update shopy_nf_product set uid = 1;


alter table shopy_nf_order_item_pre add sum_dealer_profit int (11) DEFAULT '0';
alter table shopy_nf_order_item add sum_dealer_profit int (11) DEFAULT '0';
alter table shopy_nf_account_log add op_uid int (11) DEFAULT '0';
alter table shopy_nf_account_log add op_name varchar (20) DEFAULT '';


alter table shopy_nf_product add code varchar (50) DEFAULT '';
alter table shopy_nf_item add code varchar (50) DEFAULT '';

alter table shopy_nf_order_item add code varchar (50) DEFAULT '';
alter table shopy_nf_order_item_pre add code varchar (50) DEFAULT '';
alter table shopy_nf_order add print_count int (5) DEFAULT '0';


alter table shopy_nf_order_pre add `order_from` int (5) DEFAULT '1';
alter table shopy_nf_order add `order_from` int (5) DEFAULT '1';


alter table shopy_nf_item add min_limit int (11) DEFAULT '0';
alter table shopy_nf_item add lables varchar (255) DEFAULT '';

alter table shopy_nf_product_sku add code varchar (50) DEFAULT '';

alter table shopy_nf_services add out_id int (11) DEFAULT '0';

alter table shopy_nf_order add remark varchar (200) DEFAULT '';

alter table shopy_nf_category add platform tinyint (3) DEFAULT '1';
alter table shopy_nf_product add platform tinyint (3) DEFAULT '1';
alter table shopy_nf_item add platform tinyint (3) DEFAULT '1';
alter table shopy_nf_cart add platform tinyint (3) DEFAULT '1';
alter table shopy_nf_order add platform tinyint (3) DEFAULT '1';
alter table shopy_nf_order_pre add platform tinyint (3) DEFAULT '1';

alter table shopy_nf_ad add platform tinyint (3) DEFAULT '1';
alter table shopy_nf_article add platform tinyint (3) DEFAULT '1';


CREATE TABLE `shopy_nf_volunteer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(10)  NOT NULL,
`id_no` varchar(15)  NOT NULL,
`address` varchar(100)  NOT NULL,
`free_time` varchar(50)  NOT NULL,
`status` tinyint(3)  NOT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table shopy_nf_volunteer add pay_sum int (11) DEFAULT '0';
alter table shopy_nf_volunteer add remark VARCHAR (200) DEFAULT '';
alter table shopy_nf_volunteer add uid int (11) DEFAULT '0';
alter table shopy_nf_account_log add status tinyint (3) DEFAULT '0';
alter table shopy_nf_account_log add extra varchar (100) DEFAULT '0';

CREATE TABLE `shopy_nf_disabled_help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  uid int (11) DEFAULT '0',
`name` varchar(10)  NOT NULL,
`id_no` varchar(15)  NOT NULL,
`address` varchar(100)  NOT NULL,
`tel` varchar(15)  NOT NULL,
`directly_tel` varchar(15)  DEFAULT '',
`help_cat` int (11) DEFAULT '0',
`remark` VARCHAR (200) DEFAULT '',
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `shopy_nf_disabled_help_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`title` varchar(50)  NOT NULL,
`tips` varchar(100) DEFAULT '',
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

alter table shopy_nf_volunteer add `create_time` datetime DEFAULT NULL;
alter table shopy_nf_disabled_help add `create_time` datetime DEFAULT NULL;
alter table shopy_nf_disabled_help add `directly_name` varchar(10) DEFAULT '';
alter table shopy_nf_disabled_help add `status` tinyint(3) DEFAULT 1;
alter table shopy_nf_article add `status` tinyint(3) DEFAULT 1;
alter table shopy_nf_article add `from_uid` int(11) DEFAULT 0;


CREATE TABLE `shopy_nf_disabled_man` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(10)  NOT NULL,
`id_no` varchar(15)  NOT NULL,
`address` varchar(100)  NOT NULL,
`tel` varchar(15)  NOT NULL,
`directly_tel` varchar(15)  DEFAULT '',
`directly_name` varchar(10) DEFAULT '',
`content` text,
`remark` VARCHAR (200) DEFAULT '',
`status` tinyint(3) DEFAULT 1,
`create_time` datetime DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
alter table shopy_nf_disabled_man add `img` VARCHAR (100) DEFAULT '';

alter table shopy_member add `extra` VARCHAR (1000) DEFAULT '';


alter table shopy_nf_volunteer add `tel` varchar(15)  DEFAULT '';
alter table shopy_nf_volunteer add `pay_type` tinyint(3)  DEFAULT '1';

