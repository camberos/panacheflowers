<?php

$installer = $this;
$installer->startSetup();

$compatible = Mage::helper('opcheckout')->getCompatibility();
if ($compatible == 1) {
    $table = $this->getTable('sales_flat_order');
} else {
    $table = $this->getTable('sales_order');
}
$this->_conn->addColumn($this->getTable('sales_flat_quote'), 'shipping_arrival_date', 'datetime');
$this->_conn->addColumn($this->getTable($table), 'shipping_arrival_date', 'datetime');
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$installer->endSetup();

