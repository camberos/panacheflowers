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

class MageWorx_MultiFees_Block_Sales_Order_Totals extends Mage_Core_Block_Abstract
{    
    public function initTotals() {
        
        $source = $this->getParentBlock()->getSource();
        $multifeesAmount = $source->getMultifeesAmount();
        if ($multifeesAmount==0) return $this;                
        $multifeesTaxAmount = $source->getMultifeesTaxAmount();               
        
        $taxInSales = Mage::helper('multifees')->getTaxInSales();
        $viewMode = array();
        if ($taxInSales==1) {
            $viewMode[] = false;
        } elseif ($taxInSales==2) {
            $viewMode[] = true;
        } elseif ($taxInSales==3) {
            $viewMode[] = false;
            $viewMode[] = true;
        }
        
        foreach ($viewMode as $inclTax) {                        
            if ($taxInSales!=3) {
                $label = $this->__('Additional Fees');
            } else {
                if ($inclTax) $label = $this->__('Additional Fees (Incl. Tax)'); else $label = $this->__('Additional Fees (Excl. Tax)');
            }
            
            $multifeesTotal = new Varien_Object(array(
                'code'      => 'multifees' . ($inclTax?'_incl_tax':'') . '_amount',
                'field'  => 'multifees_amount',
                'label'  => $label,
                'value'  => $inclTax?$multifeesAmount:$multifeesAmount-$multifeesTaxAmount,
            ));
            $this->getParentBlock()->addTotalBefore($multifeesTotal, 'grand_total');
        }
        return $this;
    }
    
}
