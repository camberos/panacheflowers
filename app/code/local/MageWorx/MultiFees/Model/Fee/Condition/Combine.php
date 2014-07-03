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

class MageWorx_MultiFees_Model_Fee_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct() {
        parent::__construct();
        $this->setType('multifees/fee_condition_combine');
    }
   
    public function getNewChildSelectOptions() {
        $addressAttributes = array();
        $addressAttributesTmp = Mage::getModel('salesrule/rule_condition_address')->loadAttributeOptions()->getAttributeOption();        
        
        // only base_subtotal, total_qty, weight
        if (isset($addressAttributesTmp['base_subtotal'])) $addressAttributes['base_subtotal'] = $addressAttributesTmp['base_subtotal'];
        if (isset($addressAttributesTmp['total_qty'])) $addressAttributes['total_qty'] = $addressAttributesTmp['total_qty'];
        if (isset($addressAttributesTmp['weight'])) $addressAttributes['weight'] = $addressAttributesTmp['weight'];                        
        
        $attributes = array();
        foreach ($addressAttributes as $code=>$label) {
            $attributes[] = array('value'=>'salesrule/rule_condition_address|'.$code, 'label'=>$label);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'multifees/fee_condition_product_found', 'label'=>Mage::helper('salesrule')->__('Product attribute combination')),            
            array('value'=>'multifees/fee_condition_combine', 'label'=>Mage::helper('salesrule')->__('Conditions combination')),
            array('label'=>Mage::helper('salesrule')->__('Cart Attribute'), 'value'=>$attributes),
        ));

        $additional = new Varien_Object();
        Mage::dispatchEvent('salesrule_rule_condition_combine', array('additional' => $additional));
        if ($additionalConditions = $additional->getConditions()) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }

    public function validate(Varien_Object $object) {
        if (!$this->getConditions()) {
            Mage::registry('multifees_fee')->addAllQuoteItemQty($object);
            return true;
        }
        
        $all    = $this->getAggregator() === 'all';
        $true   = (bool)$this->getValue();
        $productFoundCondition = false;
        
        foreach ($this->getConditions() as $cond) {
            if ($cond instanceof MageWorx_MultiFees_Model_Fee_Condition_Product_Found) $productFoundCondition = true;
            $validated = $cond->validate($object);
            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                if (!($cond instanceof MageWorx_MultiFees_Model_Fee_Condition_Product_Found)) Mage::registry('multifees_fee')->addAllQuoteItemQty($object);
                return true;
            }
        }
        
        if ($all) {
            if (!$productFoundCondition) Mage::registry('multifees_fee')->addAllQuoteItemQty($object);
            return true;
        } else {
            return false;
        }
    }
    
}
