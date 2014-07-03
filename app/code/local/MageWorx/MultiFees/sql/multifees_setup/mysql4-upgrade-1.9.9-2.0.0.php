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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Multi Fees extension
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @author     MageWorx Dev Team
 */

/* @var $installer MageWorx_MultiFees_Model_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

// improvement of the structure of tables 

// multifees_fee_language
if (!$installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee_language'), 'fee_lang_id')) {
    $installer->run("ALTER TABLE `{$this->getTable('multifees_fee_language')}`
            DROP INDEX `mfl_fee_id`,
            DROP FOREIGN KEY `multifees_fee_language_fk`,
            CHANGE `mfl_id` `fee_lang_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
            CHANGE `mfl_fee_id` `fee_id` INT(10) UNSIGNED NOT NULL,
            CHANGE `title` `title` VARCHAR(255) NOT NULL,
            ADD `description` TEXT NOT NULL,
            ADD `customer_message_title` VARCHAR(255) NOT NULL,
            ADD `date_field_title` VARCHAR(255) NOT NULL;");
    $installer->getConnection()->addKey($installer->getTable('multifees_fee_language'), 'fee_id+store_id', array('fee_id', 'store_id'), 'unique');
    $installer->getConnection()->addConstraint('FK_MULTIFEES_FEE_LANGUAGE', $installer->getTable('multifees_fee_language'), 'fee_id', $installer->getTable('multifees_fee'), 'fee_id', 'CASCADE', 'CASCADE', true);
}

// multifees_fee_store
if (!$installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee_store'), 'fee_store_id')) {
    $installer->run("ALTER TABLE `{$this->getTable('multifees_fee_store')}`
            DROP INDEX `mfs_fee_id`,
            DROP FOREIGN KEY `multifees_fee_store_fk`,
            CHANGE `mfs_id` `fee_store_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
            CHANGE `mfs_fee_id` `fee_id` INT(10) UNSIGNED NOT NULL;");
    $installer->getConnection()->addKey($installer->getTable('multifees_fee_store'), 'fee_id+store_id', array('fee_id', 'store_id'), 'unique');
    $installer->getConnection()->addConstraint('FK_MULTIFEES_FEE_STORE', $installer->getTable('multifees_fee_store'), 'fee_id', $installer->getTable('multifees_fee'), 'fee_id', 'CASCADE', 'CASCADE', true);
}

// multifees_fee_option
if (!$installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee_option'), 'fee_id')) {
    $installer->run("ALTER TABLE `{$this->getTable('multifees_fee_option')}`         
            DROP INDEX `mfo_fee_id`,
            DROP FOREIGN KEY `multifees_fee_option_fk`,
            CHANGE `mfo_fee_id` `fee_id` INT(10) UNSIGNED NOT NULL,
            CHANGE `is_default` `is_default` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            ADD INDEX (`fee_id`);");
    $installer->getConnection()->addConstraint('FK_MULTIFEES_FEE_OPTION', $installer->getTable('multifees_fee_option'), 'fee_id', $installer->getTable('multifees_fee'), 'fee_id', 'CASCADE', 'CASCADE', true);
}
// multifees_fee_option_language
if (!$installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee_option_language'), 'fee_option_lang_id')) {
    $installer->run("ALTER TABLE `{$this->getTable('multifees_fee_option_language')}`
            CHANGE `mfol_id` `fee_option_lang_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            DROP FOREIGN KEY `multifees_fee_option_language_fk`,
            DROP INDEX `fee_option_id`;");
    $installer->getConnection()->addKey($installer->getTable('multifees_fee_option_language'), 'fee_option_id+store_id', array('fee_option_id', 'store_id'), 'unique');
    $installer->getConnection()->addConstraint('FK_MULTIFEES_FEE_OPTION_LANGUAGE', $installer->getTable('multifees_fee_option_language'), 'fee_option_id', $installer->getTable('multifees_fee_option'), 'fee_option_id', 'CASCADE', 'CASCADE', true);
}

// multifees_fee
if ($installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee'), 'apply_to')) {
    $installer->run("ALTER TABLE `{$this->getTable('multifees_fee')}` DROP `apply_to`;");
}
if ($installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee'), 'checkout_type')) {
    $installer->run("ALTER TABLE `{$this->getTable('multifees_fee')}` DROP `checkout_type`;");
}
if ($installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee'), 'checkout_method')) {
    if (!$installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee'), 'sales_methods')) {
        $installer->run("ALTER TABLE `{$this->getTable('multifees_fee')}` CHANGE `checkout_method` `sales_methods` VARCHAR(255) NOT NULL DEFAULT '';");
    } else {
        $installer->run("ALTER TABLE `{$this->getTable('multifees_fee')}` DROP `checkout_method`;");
    }
}
if (!$installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee'), 'sales_methods')) {
    $installer->run("ALTER TABLE `{$this->getTable('multifees_fee')}` ADD `sales_methods` VARCHAR(255) NOT NULL DEFAULT '' AFTER `status`;");
}
if (!$installer->getConnection()->tableColumnExists($installer->getTable('multifees_fee'), 'type')) {
    $installer->run("
        ALTER TABLE `{$this->getTable('multifees_fee')}` 
            ADD `type` TINYINT( 1 ) NOT NULL DEFAULT '1' COMMENT '1-Cart Fee,2-Payment Fee,3-Shipping Fee' AFTER `fee_id`, 
            CHANGE `input_type` `input_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1-Drop-Down,2-Radio Button,3-Checkbox,4-Hidden,5-Notice',
            CHANGE `status` `status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0-Disabled,1-Active',
            CHANGE `required` `required` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-No,1-Yes',                        
            ADD `customer_group_ids` VARCHAR(255) NOT NULL AFTER `sales_methods`,
            ADD `applied_totals` VARCHAR(255) NOT NULL DEFAULT 'subtotal',
            ADD `tax_class_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0-None,1-default',
            ADD `conditions_serialized` MEDIUMTEXT NOT NULL,
            ADD `enable_customer_message` TINYINT(1) NOT NULL DEFAULT '0',
            ADD `enable_date_field` TINYINT(1) NOT NULL DEFAULT '0',
            ADD `total_ordered` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0', 
            ADD `total_base_amount` decimal(12,4) UNSIGNED NOT NULL DEFAULT '0';
        
        UPDATE `{$this->getTable('multifees_fee')}` SET `type` = 2, `input_type` = 3 WHERE `input_type` = 4;
        UPDATE `{$this->getTable('multifees_fee')}` SET `type` = 3, `input_type` = 3 WHERE `input_type` = 5;
        UPDATE `{$this->getTable('multifees_fee')}` SET `status` = 0 WHERE `status` = 2;
        UPDATE `{$this->getTable('multifees_fee')}` SET `required` = 0 WHERE `required` = 2;
        
    ");        
}

// sales/quote_address
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'base_multifees_amount')) {
    if ($installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'base_multifees')) {
        $installer->run("ALTER TABLE `{$this->getTable('sales/quote_address')}`
                CHANGE `multifees` `multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000',
                CHANGE `base_multifees` `base_multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000';
        ");
    } else {
        $installer->run("ALTER TABLE `{$this->getTable('sales/quote_address')}` 
            ADD `multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000',
            ADD `base_multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000',
            ADD `details_multifees` text NOT NULL;
        ");
    }
}
if ($installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'multifees')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/quote_address')}` DROP `multifees`;");
}
if ($installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'base_multifees')) {
    $installer->run("ALTER TABLE `{$this->getTable('sales/quote_address')}` DROP `base_multifees`;");
}
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'multifees_tax_amount')) {    
    $installer->run("ALTER TABLE `{$installer->getTable('sales/quote_address')}` 
        ADD `multifees_tax_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `base_multifees_amount` ,
        ADD `base_multifees_tax_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `multifees_tax_amount`;");
}


// sales/order
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'multifees_amount')) {
    if ($installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'base_multifees_amount')) {
        $installer->run("ALTER TABLE `{$installer->getTable('sales/order')}` DROP  `base_multifees_amount`;");
    }
    
    if ($installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'multifees')) {
        $installer->run("ALTER TABLE `{$installer->getTable('sales/order')}` 
            CHANGE `multifees` `multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000',
            CHANGE `base_multifees` `base_multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000';");
    } else {
        $installer->run("ALTER TABLE `{$this->getTable('sales/order')}` 
            ADD `multifees_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0.0000',
            ADD `base_multifees_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0.0000',
            ADD `details_multifees` text NOT NULL;");
    }    
}
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'multifees_tax_amount')) {
    $installer->run("ALTER TABLE `{$installer->getTable('sales/order')}` 
        ADD `multifees_tax_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `base_multifees_amount` ,
        ADD `base_multifees_tax_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `multifees_tax_amount`;");
}
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'multifees_invoiced')) {
    $installer->run("ALTER TABLE `{$installer->getTable('sales/order')}` 
        ADD `multifees_invoiced` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `details_multifees`,
        ADD `base_multifees_invoiced` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `multifees_invoiced`,
        ADD `multifees_refunded` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `base_multifees_invoiced`,
        ADD `base_multifees_refunded` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `multifees_refunded`,
        ADD `multifees_canceled` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `base_multifees_refunded`,
        ADD `base_multifees_canceled` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `multifees_canceled`;");
}

// sales/invoice
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/invoice'), 'multifees_amount')) {
    if ($installer->getConnection()->tableColumnExists($installer->getTable('sales/invoice'), 'multifees')) {
        $installer->run("ALTER TABLE `{$installer->getTable('sales/invoice')}` 
            CHANGE `multifees` `multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000',
            CHANGE `base_multifees` `base_multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000';");
    } else {
        $installer->run("ALTER TABLE `{$this->getTable('sales/invoice')}` 
            ADD `multifees_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0.0000',
            ADD `base_multifees_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0.0000',
            ADD `details_multifees` text NOT NULL;");
    }
}
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/invoice'), 'multifees_tax_amount')) {
    $installer->run("ALTER TABLE `{$installer->getTable('sales/invoice')}` 
        ADD `multifees_tax_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `base_multifees_amount` ,
        ADD `base_multifees_tax_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `multifees_tax_amount`;");
}

// sales/creditmemo
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/creditmemo'), 'multifees_amount')) {
    if ($installer->getConnection()->tableColumnExists($installer->getTable('sales/creditmemo'), 'multifees')) {
        $installer->run("ALTER TABLE `{$installer->getTable('sales/creditmemo')}` 
            CHANGE `multifees` `multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000',
            CHANGE `base_multifees` `base_multifees_amount` DECIMAL( 12, 4 ) NOT NULL DEFAULT '0.0000';");
    } else {
        $installer->run("ALTER TABLE `{$this->getTable('sales/creditmemo')}` 
            ADD `multifees_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0.0000',
            ADD `base_multifees_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0.0000',
            ADD `details_multifees` text NOT NULL;");
    }
}
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales/creditmemo'), 'multifees_tax_amount')) {
    $installer->run("ALTER TABLE `{$installer->getTable('sales/creditmemo')}` 
        ADD `multifees_tax_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `base_multifees_amount` ,
        ADD `base_multifees_tax_amount` DECIMAL(12, 4) NOT NULL DEFAULT '0' AFTER `multifees_tax_amount`;");
}

// uninstall old attribute
$installer->run("DELETE FROM `{$installer->getTable('eav_attribute')}` WHERE `attribute_code` = 'additional_fees';");

$installer->endSetup();
