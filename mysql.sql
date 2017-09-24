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
