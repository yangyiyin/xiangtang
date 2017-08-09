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





