<?php
$installer = $this;

$installer->startSetup();

$sql=<<<SQLTEXT
DELETE FROM qixol_banners;
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
ALTER TABLE `qixol_banners`
  CHANGE `banner_link_name` `display_zone` varchar(255) not null default '',
  DROP `is_default`,
  DROP `sort_order`,
  DROP `banner_group`,
  DROP `banner_type`,
  DROP `url` 
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
drop table qixol_banner_has_images;
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
RENAME TABLE  `magento`.`qixol_banner_images` TO  `magento`.`qixol_banner_image` ;
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
ALTER TABLE qixol_banner_image
  ADD `banner_id` INT(11) unsigned not null,
  ADD `sort_order` INT,
  ADD `url` varchar(255),
  ADD `promotion_reference` varchar(255),
  ADD `comment` varchar(255),
  ADD CONSTRAINT `FK_qixol_banner_image_banner_id` FOREIGN KEY (`banner_id`) REFERENCES `qixol_banners` (`banner_id`) ON DELETE CASCADE ON UPDATE CASCADE;		
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
drop table qixol_product_export_stat;	
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
ALTER TABLE qixol_shipping_name_map
  ADD `carrier_title` varchar(255) FIRST,
  ADD `carrier_method` varchar(255) AFTER `carrier_title`,
  CHANGE `shipping_name_map` `integration_code` varchar(255) not null default ''
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
ALTER TABLE  `qixol_customers_groups_name_map`
    CHANGE  `customer_group_name_map`  `integration_code` VARCHAR( 255 ) NOT NULL DEFAULT  '';
SQLTEXT;

$installer->run($sql);
    
$sql=<<<SQLTEXT
	DELETE FROM qixol_stickers;
SQLTEXT;

$installer->run($sql);

$sql=<<<SQLTEXT
ALTER TABLE qixol_stickers
  DROP `use_default_sticker`,
  DROP `use_default_banner_group`,
  DROP `default_banner_group`,
  DROP `unique_banner_group`,
  DROP `banner_link_name`,
  DROP `status`,
  DROP `sort_order`,
  DROP `created_time`,
  DROP `update_time`,
  ADD  `promo_reference` VARCHAR( 255 ) NOT NULL DEFAULT  '',
  ADD  `promo_type_name` VARCHAR( 255 ) NOT NULL DEFAULT  '',
  ADD  `is_default_for_type` TINYINT NOT NULL DEFAULT  '0',
  ADD  `is_system_default_for_type` TINYINT NOT NULL DEFAULT  '0';
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

$sql=<<<SQLTEXT
ALTER TABLE  `qixol_stores_name_map`
    ADD `website` VARCHAR( 255 ) NOT NULL DEFAULT  '' FIRST,
    ADD `store_group` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER `website`,
    CHANGE  `store_name_map`  `integration_code` VARCHAR( 255 ) NOT NULL DEFAULT  '';
SQLTEXT;

$installer->run($sql);
    

//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 
?>
