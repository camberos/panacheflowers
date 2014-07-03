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

class MageWorx_MultiFees_Model_Sales_Pdf_Total extends Mage_Sales_Model_Order_Pdf_Total_Default
{

    public function getTotalsForDisplay() {        
        $source = $this->getSource();                
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
        
        $totals = array();
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        
        foreach ($viewMode as $inclTax) {                        
            $amount = ($inclTax?$source->getMultifeesAmount():$source->getMultifeesAmount()-$source->getMultifeesTaxAmount());
            if ($amount>0) {
                $amount = $this->getOrder()->formatPriceTxt($amount);

                if ($taxInSales!=3) {
                    $label = Mage::helper('multifees')->__('Additional Fees');
                } else {
                    if ($inclTax) $label = Mage::helper('multifees')->__('Additional Fees (Incl. Tax)'); else $label = Mage::helper('multifees')->__('Additional Fees (Excl. Tax)');
                }            

                $totals[] = array (
                        'amount'    => $this->getAmountPrefix().$amount,
                        'label'     => $label . ':',
                        'font_size' => $fontSize
                    );
            }
        }
        
        return $totals;
    }
}