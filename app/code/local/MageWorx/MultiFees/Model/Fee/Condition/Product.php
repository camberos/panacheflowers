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

class MageWorx_MultiFees_Model_Fee_Condition_Product extends Mage_SalesRule_Model_Rule_Condition_Product //Mage_Rule_Model_Condition_Abstract
{

    public function loadAttributeOptions() {
        $attributes = array('product_type' => Mage::helper('catalog')->__('Product Type'));
        $this->setAttributeOption($attributes);
        return $this;
    }
    
    public function getValueSelectOptions() {
        $productTypes = Mage::getSingleton('catalog/product_type')->getOptionArray();
        foreach ($productTypes as $code=>$label) {
            $values[] = array('value'=>$code, 'label'=>$label);
        }
        return $values;
    }
    
    
    public function getValueElementType() {
        return 'select';
    }    
    
    public function getInputType() {
        return 'select';        
    }    
    
    public function validate(Varien_Object $object) {
        // check product type
        if ($this->getAttribute()=='product_type') {
            if ($object->getProduct() instanceof Mage_Catalog_Model_Product) {
                $product = $object->getProduct();
            } else {
                $product = Mage::getModel('catalog/product')->load($object->getProductId());
            }
            if ($product->getTypeId()==$this->getValue()) return true; else return false;            
        }
        
        return parent::validate($object);
    }            
    
    
}
