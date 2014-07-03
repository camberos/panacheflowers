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
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Multi Fees extension
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @author     MageWorx Dev Team
 */

class MageWorx_MultiFees_Model_Fee_Condition_Product_Found extends Mage_SalesRule_Model_Rule_Condition_Product_Found
{
    public function __construct() {
        parent::__construct();
        $this->setType('multifees/fee_condition_product_found');
    }
    
    public function getNewChildSelectOptions() {
        $productAttributes = Mage::getModel('salesrule/rule_condition_product')->loadAttributeOptions()->getAttributeOption();
        $pAttributes = array();
        $iAttributes = array();
        foreach ($productAttributes as $code=>$label) {
            if (strpos($code, 'quote_item_')===0) {
                $iAttributes[] = array('value'=>'salesrule/rule_condition_product|'.$code, 'label'=>$label);
            } else {
                $pAttributes[] = array('value'=>'salesrule/rule_condition_product|'.$code, 'label'=>$label);
            }
        }

        $conditions = array(
            array('value'=>'', 'label'=>Mage::helper('rule')->__('Please choose a condition to add...')),
            array('value'=>'multifees/fee_condition_product_combine', 'label'=>Mage::helper('catalog')->__('Conditions Combination')),
            array('label'=>Mage::helper('catalog')->__('Cart Item Attribute'), 'value'=>$iAttributes),
            array('label'=>Mage::helper('catalog')->__('Product'), 'value'=>array(array('value'=>'multifees/fee_condition_product|product_type', 'label'=>Mage::helper('catalog')->__('Type')))),
            array('label'=>Mage::helper('catalog')->__('Product Attribute'), 'value'=>$pAttributes),
        );
        
        return $conditions;
    }
    
    public function validate(Varien_Object $object) {
        $all = $this->getAggregator()==='all';
        $true = (bool)$this->getValue();
        $found = false;
        $globalFound = false;
        foreach ($object->getAllItems() as $item) {
            $found = $all;
            foreach ($this->getConditions() as $cond) {
                
                // fix bad product model
                $product = $item->getProduct();
                if ($product instanceof Mage_Catalog_Model_Product && is_null($product->getData($cond->getAttribute()))) {
                    $product->load($object->getProductId());
                }
                
                $validated = $cond->validate($item);
                if (($all && !$validated) || (!$all && $validated)) {
                    $found = $validated;
                    break;
                }
            }
            
            if ($found) Mage::registry('multifees_fee')->addFoundQuoteItemQty($item, $object->getId());
            
            if (($found && $true) || (!$true && $found)) {
                $globalFound = true;
                //break;
            }
        }
        
        if ($globalFound && $true) { // found an item and we're looking for existing one
            return true;
        } elseif (!$globalFound && !$true) { // not found and we're making sure it doesn't exist
            return true;
        }
        return false;
    }
    
}
