<?php
$installer = $this;

$installer->startSetup();

$sql=<<<SQLTEXT
create table {$installer->getTable('qixol_product_to_delete')}(
 entity_id int unsigned not null default 0,
 product_sku varchar(128),
 child_sku varchar(128),
 deleted_time datetime,
 primary key (entity_id)
);
		
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
create table {$installer->getTable('qixol_process_export_status_table')}(
                        id int(11) NOT NULL auto_increment, 
                        last_message varchar(255),
                        export_what varchar(32),
                        exports_start datetime,
                        exports_last_updated datetime,
                        is_finished int(1) default 0,
                        extended_message text,
                        PRIMARY KEY (id)
                      );
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_banners')}(
  `banner_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `display_zone` varchar(255) NOT NULL DEFAULT '',
  `status` smallint(6) NOT NULL DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_banner_image')}(
  `banner_image_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `banner_id` int(11) unsigned NOT NULL, 
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `promotion_reference` varchar(255) not null default '',
  `comment` varchar(255),
  `url` varchar(255),
  PRIMARY KEY (`banner_image_id`),
  CONSTRAINT `FK_qixol_banner_image_banner_id` FOREIGN KEY (`banner_id`) REFERENCES `qixol_banners` (`banner_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_promotions_type')}(
  `promotion_id` int(11) unsigned NOT NULL default 0,
  `promotion_type` varchar(255) NOT NULL DEFAULT '',
  `promotion_name` varchar(255) NOT NULL DEFAULT '',
  `promotion_text` text,
  `yourref`  varchar(255) NOT NULL DEFAULT '',
  `bundleprice` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discountpercent` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discountamount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `hascouponrestrictions` tinyint not null default 0,
  `is_everyday` tinyint not null default 0,
  `till_date` datetime not null default '0000-00-00 00:00:00',
  `from_date` datetime not null default '0000-00-00 00:00:00',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `is_for_product` tinyint not null default 0,
  PRIMARY KEY (`promotion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQLTEXT;


$installer->run($sql);

$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_promotion_has_product')}(
  `product_id` int(11) unsigned NOT NULL default 0,
  `parent_product_id` int(11) unsigned NOT NULL default 0,
  `promotion_id` int(11) unsigned NOT NULL default 0,
  `parentsku` varchar(255) NOT NULL DEFAULT '',
  `sku` varchar(255) NOT NULL DEFAULT '',
  `requiredqty` tinyint not null default 0,
  `multipleproductrestrictions` tinyint not null default 0,
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`product_id`,`parent_product_id`,`promotion_id`),
  INDEX `idx_qixol_promotion_sku`(`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQLTEXT;


$installer->run($sql);

$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_banner_box')}(
  banner_box_type varchar(64) not null default '',
  banner_box_is_active tinyint not null default 0,
  banner_box_translation_type varchar(32) not null default '',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`banner_box_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQLTEXT;


$installer->run($sql);

$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_shipping_name_map')}(
  shipping_name varchar(255) not null default '',
  carrier_title varchar(255) not null default '',
  carrier_method varchar(255) not null default '',
  integration_code varchar(255) not null default '',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`shipping_name`),
  UNIQUE KEY `shipping_name_to_shipping_name_map` (`shipping_name`,`integration_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;


$installer->run($sql);

$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_coupon_issued')}(
  entity_id int unsigned not null auto_increment,
  customer_id int unsigned not null default 0,
  coupon_code_value varchar(255) not null default '',
  coupon_valid_till datetime,
  is_used tinyint unsigned not null default 0,
  coupon_description varchar(255) not null default '',
  `created_time` datetime DEFAULT NULL,
  PRIMARY KEY (`entity_id`),
  KEY `IDX_qixol_coupon_issued_customer_id` (`entity_id`),
  CONSTRAINT `FK_qixol_coupon_issued_customer_id_CUSTOMER_ENTITY_ENTITY_ID` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;

$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_customers_groups_name_map')}(
  customer_group_name varchar(255) not null default '',
  integration_code varchar(255) not null default '',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`customer_group_name`),
  UNIQUE KEY `customer_group_name_to_integration_code` (`customer_group_name`,`integration_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;


$installer->run($sql);


$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_stores_name_map')}(
  `website` VARCHAR( 255 ) NOT NULL DEFAULT  '',
  `store_group` VARCHAR( 255 ) NOT NULL DEFAULT  '',
  store_name varchar(255) not null default '',
  integration_code varchar(255) not null default '',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`store_name`),
  UNIQUE KEY `store_name_to_integration_code` (`store_name`,`integration_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;


$installer->run($sql);


$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_stickers')}(
  `sticker_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `promo_reference` varchar(255) NOT NULL DEFAULT '',
  `promo_type_name` varchar(255) NOT NULL DEFAULT '',
  `is_default_for_type` tinyint not null default '0',
  `is_system_default_for_type` tinyint not null default '0',
  PRIMARY KEY (`sticker_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;

$installer->run($sql);


$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('qixol_cutomer_points')}(
  points_account_id int(11) unsigned NOT NULL AUTO_INCREMENT,
  customer_id int unsigned not null default 0,
  store_id smallint(5) unsigned NOT NULL DEFAULT '0',
  current_points int(5) unsigned NOT NULL DEFAULT '0',
  earned_points int unsigned not null default 0,
  spent_points int unsigned not null default 0,
  PRIMARY KEY (`points_account_id`),
  KEY `FK_catalog_category_ENTITY_STORE` (`store_id`),
  CONSTRAINT `FK_qixol_cutomer_points_customer_id_CUSTOMER_ENTITY_ENTITY_ID` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;

$installer->run($sql);


$sql=<<<SQLTEXT
    ALTER TABLE  {$installer->getTable('sales/quote_address')} ADD  `points_amount` DECIMAL( 10, 2 ) NOT NULL;
    ALTER TABLE  {$installer->getTable('sales/quote_address')} ADD  `base_points_amount` DECIMAL( 10, 2 ) NOT NULL;

    alter table  {$installer->getTable('sales/order')} add column promotion_data_applied text;
    ALTER TABLE  {$installer->getTable('sales/order')} ADD  `points_amount` DECIMAL( 10, 2 ) NOT NULL;
    ALTER TABLE  {$installer->getTable('sales/order')} ADD  `base_points_amount` DECIMAL( 10, 2 ) NOT NULL;

    ALTER TABLE  {$installer->getTable('sales/invoice')} ADD  `points_amount` DECIMAL( 10, 2 ) NOT NULL;
    ALTER TABLE  {$installer->getTable('sales/invoice')} ADD  `base_points_amount` DECIMAL( 10, 2 ) NOT NULL;
SQLTEXT;
$installer->run($sql);

$sql=<<<SQLTEXT
    insert into qixol_stickers (`filename`, `promo_type_name`, `is_default_for_type`, `is_system_default_for_type`) VALUES ('custom/stickers/bogof.png', 'BOGOF',1,1);
    INSERT INTO qixol_stickers (`filename`, `promo_type_name`, `is_default_for_type`, `is_system_default_for_type`) VALUES ('custom/stickers/bogor.png','BOGOR',1,1);
    INSERT INTO qixol_stickers (`filename`, `promo_type_name`, `is_default_for_type`, `is_system_default_for_type`) VALUES ('custom/stickers/bundle.png','BUNDLE',1,1);
    INSERT INTO qixol_stickers (`filename`, `promo_type_name`, `is_default_for_type`, `is_system_default_for_type`) VALUES ('custom/stickers/deal.png','DEAL',1,1);
    INSERT INTO qixol_stickers (`filename`, `promo_type_name`, `is_default_for_type`, `is_system_default_for_type`) VALUES ('custom/stickers/freeproduct.png','FREEPRODUCT',1,1);
    INSERT INTO qixol_stickers (`filename`, `promo_type_name`, `is_default_for_type`, `is_system_default_for_type`) VALUES ('custom/stickers/issuecoupon.png','ISSUECOUPON',1,1);
    INSERT INTO qixol_stickers (`filename`, `promo_type_name`, `is_default_for_type`, `is_system_default_for_type`) VALUES ('custom/stickers/issuepoints.png','ISSUEPOINTS',1,1);
    INSERT INTO qixol_stickers (`filename`, `promo_type_name`, `is_default_for_type`, `is_system_default_for_type`) VALUES ('custom/stickers/offers.png','MULTIPLE',1,1);
    INSERT INTO qixol_stickers (`filename`, `promo_type_name`, `is_default_for_type`, `is_system_default_for_type`) VALUES ('custom/stickers/productsreduction.png','PRODUCTSREDUCTION',1,1);
SQLTEXT;
$installer->run($sql);

//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 
?>