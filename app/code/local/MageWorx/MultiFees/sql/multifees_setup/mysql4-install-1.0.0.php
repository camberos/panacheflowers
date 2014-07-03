<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Multi Fees extension
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

$installer = $this;
$installer->startSetup();

/* @var $installer MageWorx_MultiFees_Model_Mysql4_Setup */

//$installer->installEntities();

//if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'details_multifees')) {
//    $installer->addAttribute('quote_address', 'multifees', array('type' => 'decimal'));
//    $installer->addAttribute('quote_address', 'base_multifees', array('type' => 'decimal'));
//    $installer->addAttribute('quote_address', 'details_multifees', array('type' => 'text'));
//}

//if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'details_multifees')) {
//    $installer->addAttribute('order', 'multifees', array('type' => 'decimal'));
//    $installer->addAttribute('order', 'base_multifees', array('type' => 'decimal'));
//    $installer->addAttribute('order', 'details_multifees', array('type' => 'text'));
//}

//if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/invoice'), 'details_multifees')) {
//    $installer->addAttribute('invoice', 'multifees', array('type' => 'decimal'));
//    $installer->addAttribute('invoice', 'base_multifees', array('type' => 'decimal'));
//    $installer->addAttribute('invoice', 'details_multifees', array('type' => 'text'));
//}

//if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/creditmemo'), 'details_multifees')) {
//    $installer->addAttribute('creditmemo', 'multifees', array('type' => 'decimal'));
//    $installer->addAttribute('creditmemo', 'base_multifees', array('type' => 'decimal'));
//    $installer->addAttribute('creditmemo', 'details_multifees', array('type' => 'text'));
//}

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('multifees_fee')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('multifees_fee')}` (
  `fee_id` int(10) unsigned NOT NULL auto_increment,
  `input_type` tinyint(1) unsigned default NULL,
  `required` tinyint(1) unsigned default NULL,
  `sort_order` smallint(6) unsigned default NULL,
  `status` tinyint(1) unsigned default NULL,
  PRIMARY KEY  (`fee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('multifees_fee_language')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('multifees_fee_language')}` (
  `mfl_id` int(10) unsigned NOT NULL auto_increment,
  `mfl_fee_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned default NULL,
  `title` varchar(255) default NULL,
  PRIMARY KEY  (`mfl_id`),
  KEY `mfl_fee_id` (`mfl_fee_id`),
  CONSTRAINT `multifees_fee_language_fk` FOREIGN KEY (`mfl_fee_id`) REFERENCES {$this->getTable('multifees_fee')} (`fee_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('multifees_fee_option')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('multifees_fee_option')}` (
  `fee_option_id` int(10) unsigned NOT NULL auto_increment,
  `mfo_fee_id` int(10) unsigned NOT NULL,
  `price` decimal(12,4) default NULL,
  `price_type` enum('fixed','percent') default NULL,
  `is_default` tinyint(1) unsigned default NULL,
  `position` smallint(6) unsigned default NULL,
  PRIMARY KEY  (`fee_option_id`),
  KEY `mfo_fee_id` (`mfo_fee_id`),
  CONSTRAINT `multifees_fee_option_fk` FOREIGN KEY (`mfo_fee_id`) REFERENCES {$this->getTable('multifees_fee')} (`fee_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('multifees_fee_option_language')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('multifees_fee_option_language')}` (
  `mfol_id` int(10) unsigned NOT NULL auto_increment,
  `fee_option_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned default NULL,
  `title` varchar(255) default NULL,
  PRIMARY KEY  (`mfol_id`),
  KEY `fee_option_id` (`fee_option_id`),
  CONSTRAINT `multifees_fee_option_language_fk` FOREIGN KEY (`fee_option_id`) REFERENCES {$this->getTable('multifees_fee_option')} (`fee_option_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('multifees_fee_store')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('multifees_fee_store')}` (
  `mfs_id` int(10) unsigned NOT NULL auto_increment,
  `mfs_fee_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`mfs_id`),
  KEY `mfs_fee_id` (`mfs_fee_id`),
  CONSTRAINT `multifees_fee_store_fk` FOREIGN KEY (`mfs_fee_id`) REFERENCES {$this->getTable('multifees_fee')} (`fee_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'multifees_amount')) {
    $installer->run("  
        INSERT IGNORE INTO {$this->getTable('cms/block')} (`title`, `identifier`, `content`, `creation_time`, `update_time`, `is_active`) VALUES ('Multi Fees Cart Text', 'cart-multi-fees', '', NOW(), NOW(), 1);
        INSERT IGNORE INTO {$this->getTable('cms/block_store')} (`block_id`, `store_id`) SELECT `block_id`, 0 FROM {$this->getTable('cms/block')} WHERE `identifier`='cart-multi-fees';
    ");
}   
    
$installer->endSetup();