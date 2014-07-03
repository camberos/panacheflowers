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

class MageWorx_MultiFees_Model_Sales_Quote_Total extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_collected = false;
    
    public function __construct() {
        $this->setCode('multifees');
    }
    
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $helper = Mage::helper('multifees');
        if (!$helper->isEnabled()) return $this;
        
        if ($address->getSubtotal()==0 && floatval($address->getCustomerCreditAmount())==0) return $this;
        if ($this->_collected) return $this;
        
        $baseSubtotal = floatval($address->getBaseSubtotalWithDiscount());
        $baseShipping = floatval($address->getBaseShippingAmount()); // - $address->getBaseShippingTaxAmount()
        $baseTax = floatval($address->getBaseTaxAmount());
        
        //amount
        $address->setMultifeesAmount(0);
        $address->setBaseMultifeesAmount(0);
        $address->setDetailsMultifees(null);
        
        $quote = $address->getQuote();
                
        // add payment fee (front and admin)      
        if (Mage::app()->getRequest()->getPost('is_payment_fee', false)) {
            $feesPost = Mage::app()->getRequest()->getPost('fee', array());
            $helper->addFeesToCart($feesPost, $quote->getStoreId(), false, 2, 0);
        }
        
        // add shipping fee (front and admin)   
        if (Mage::app()->getRequest()->getPost('is_shipping_fee', false)) {
            $feesPost = Mage::app()->getRequest()->getPost('fee', array());
            $helper->addFeesToCart($feesPost, $quote->getStoreId(), false, 3, 0);
        }
        
        
        $feesData = $helper->getQuoteDetailsMultifees($address->getId());
        
        // autoadd default cart fees, no hidden
        if (is_null($feesData) && $helper->isEnableCartFees()) $this->autoAddFeesByParams(1, 0, 2, 1, '', $quote, $address, $helper);
        // autoadd hidden cart fees
        if ($helper->isEnableCartFees()) $this->autoAddFeesByParams(1, 0, 1, 1, '', $quote, $address, $helper);
        // autoadd hidden shipping fees
        if ($helper->isEnableShippingFees() && $address->getShippingMethod()) $this->autoAddFeesByParams(3, 0, 1, 1, strval($address->getShippingMethod()), $quote, $address, $helper);
        
        // autoadd hidden fees
        if ($helper->isEnablePaymentFees() && $quote->getPayment()->getMethod()) {
            // autoadd default payment fees, no hidden
            if (is_null($feesData) && Mage::app()->getRequest()->getControllerName()=='express') $this->autoAddFeesByParams(2, 0, 3, 1, $quote->getPayment()->getMethod(), $quote, $address, $helper);
            // autoadd hidden payment fees
            $this->autoAddFeesByParams(2, 0, 1, 1, $quote->getPayment()->getMethod(), $quote, $address, $helper);
        }
                
        $feesData = $helper->getQuoteDetailsMultifees($address->getId());        
        
        if (!is_array($feesData) || count($feesData)==0) return $this;
                
        
        // check conditions added fees
        
        // get all possible fees
        $possibleFeesCollection = $helper->getMultifees(0, 0, 0, 0, '', $quote, $address);
        $possibleFees = array();
        foreach($possibleFeesCollection as $fee) {
            $possibleFees[$fee->getId()] = $fee;
        }
        
        $baseMultifeesAmount = 0;
        $baseMultifeesTaxAmount = 0;
        foreach ($feesData as $feeId => $data) {
            if (!isset($data['options']) || !isset($possibleFees[$feeId])) {
                unset($feesData[$feeId]);
                continue;
            }
            $baseMultifeesLeft = 0;
            $appliedTotals = $data['applied_totals'];
            foreach ($appliedTotals as $field) {
                switch ($field) {
                    case 'subtotal':                            
                        $baseMultifeesLeft += $baseSubtotal;
                        break;
                    case 'shipping':
                        $baseMultifeesLeft += $baseShipping;
                        break;
                    case 'tax':
                        $baseMultifeesLeft += $baseTax;
                        break;                       
                }
            }

            $taxClassId = $data['tax_class_id'];

            $feePrice = 0;
            $feeTax = 0;
            foreach ($data['options'] as $optionId=>$value) {
                if (isset($value['percent'])) {
                    $opBasePrice = ($baseMultifeesLeft * $value['percent']) / 100;
                } else {                    
                    $opBasePrice = $value['base_price'] * $possibleFees[$feeId]->getFoundQty($address->getId());
                }
                $opPrice = $quote->getStore()->convertPrice($opBasePrice);
                
                if ($helper->isTaxСalculationIncludesTax()) {
                    $opBaseTax = $opBasePrice - $helper->getPriceExcludeTax($opBasePrice, $quote, $taxClassId, $address);
                    $opTax = $opPrice - $helper->getPriceExcludeTax($opPrice, $quote, $taxClassId, $address);
                } else {
                    // add tax 
                    $opBasePrice += $opBaseTax = $helper->getTaxPrice($opBasePrice, $quote, $taxClassId, $address);                    
                    $opPrice += $opTax = $helper->getTaxPrice($opPrice, $quote, $taxClassId, $address);
                }
                
                //$opPrice, $opBasePrice - inclTax
                
                $feesData[$feeId]['options'][$optionId]['base_price'] = $opBasePrice;
                $feesData[$feeId]['options'][$optionId]['price'] = $opPrice;
                $feesData[$feeId]['options'][$optionId]['base_tax'] = $opBaseTax;
                $feesData[$feeId]['options'][$optionId]['tax'] = $opTax;

                $feeTax += $opBaseTax;
                $feePrice += $opBasePrice;
            }                                

            $feesData[$feeId]['base_price'] = $feePrice;
            $feesData[$feeId]['price'] = $quote->getStore()->convertPrice($feePrice);
            $feesData[$feeId]['base_tax'] = $feeTax;
            $feesData[$feeId]['tax'] = $quote->getStore()->convertPrice($feeTax);

            $baseMultifeesAmount += $feePrice;
            $baseMultifeesTaxAmount += $feeTax;

        }
        
        $multifeesAmount = $quote->getStore()->roundPrice($quote->getStore()->convertPrice($baseMultifeesAmount));
        $baseMultifeesAmount = $quote->getStore()->roundPrice($baseMultifeesAmount);
        
        $multifeesTaxAmount = $quote->getStore()->roundPrice($quote->getStore()->convertPrice($baseMultifeesTaxAmount));
        $baseMultifeesTaxAmount = $quote->getStore()->roundPrice($baseMultifeesTaxAmount);
        
        
        if ($helper->isIncludeFeeInShippingPrice()) {
            $address->setBaseShippingAmount($baseMultifeesAmount - $baseMultifeesTaxAmount + $address->getBaseShippingAmount());
            $address->setShippingAmount($multifeesAmount - $multifeesTaxAmount + $address->getShippingAmount());
            
            $address->setBaseShippingTaxAmount($baseMultifeesTaxAmount + $address->getBaseShippingTaxAmount());
            $address->setShippingTaxAmount($multifeesTaxAmount + $address->getShippingTaxAmount());
            
            $address->setBaseShippingInclTax($baseMultifeesAmount + $address->getBaseShippingInclTax());
            $address->setShippingInclTax($multifeesAmount + $address->getShippingInclTax());
            
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $baseMultifeesAmount - $baseMultifeesTaxAmount);
            $address->setGrandTotal($address->getGrandTotal() + $multifeesAmount - $multifeesTaxAmount);
            
        } else {
            $address->setBaseMultifeesAmount($baseMultifeesAmount);
            $address->setMultifeesAmount($multifeesAmount);

            $address->setBaseMultifeesTaxAmount($baseMultifeesTaxAmount);
            $address->setMultifeesTaxAmount($multifeesTaxAmount);            

            $address->setDetailsMultifees(serialize($feesData));            

            $address->setBaseTotalAmount('multifees', $baseMultifeesAmount);
            $address->setTotalAmount('multifees', $multifeesAmount);
        }
        
        $address->setBaseTaxAmount($address->getBaseTaxAmount() + $baseMultifeesTaxAmount);
        $address->setTaxAmount($address->getTaxAmount() + $multifeesTaxAmount);
        
        // emulation $this->_saveAppliedTaxes($address, $applied, $tax, $baseTax, $rate); 
        $appliedTaxes = $address->getAppliedTaxes();
        if (is_array($appliedTaxes)) {
            foreach ($appliedTaxes as $row) {
                if (isset($appliedTaxes[$row['id']]['amount'])) {
                    $appliedTaxes[$row['id']]['amount']       += $multifeesTaxAmount;
                    $appliedTaxes[$row['id']]['base_amount']  += $baseMultifeesTaxAmount;
                    break;
                }
            }
            $address->setAppliedTaxes($appliedTaxes);
        }
        
        $this->_collected = true;
        return $this;
    }
 
    //  $hidden = 0 - all, 1 - only hidden+notice, 2 - only no hidden
    public function autoAddFeesByParams($type = 1, $required = 0, $hidden = 0, $isDefault = 0, $code = '', $quote=null, $address=null, $helper) {
        $fees = $helper->getMultiFees($type, $required, $hidden==1?3:0, $isDefault, $code, $quote, $address);
        if ($fees) {
            $feesPost = array();
            foreach($fees as $fee) {
                $feeOptions = $fee->getOptions();
                if ($feeOptions) {
                    foreach ($feeOptions as $option) {
                        if ($option->getIsDefault()) {
                            $feesPost[$fee->getFeeId()]['options'][] = $option->getId();
                        }
                    }
                }
            }
            if ($feesPost) $helper->addFeesToCart($feesPost, $quote->getStoreId(), false, $type, 0, $hidden==1?1:0);
        }
    }
    
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        if (!$this->_collected) $this->collect($address);
        if ($address->getMultifeesAmount()==0) return $this;
        if ($address->getSubtotal()==0 && floatval($address->getCustomerCreditAmount())==0) return $this;
        
        $helper = Mage::helper('multifees');
        if ($helper->isEnabled() && $address->getDetailsMultifees()) {
            $taxMode = $helper->getTaxInCart();
            // if $taxMode==1,3 ---> show Price Excl. Tax
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => ($taxMode==3) ? $helper->__('Additional Fees (Excl. Tax)') : $helper->__('Additional Fees'),
                'value' =>  ($taxMode==1|| $taxMode==3) ? $address->getMultifeesAmount() - $address->getMultifeesTaxAmount(): $address->getMultifeesAmount(),
                'full_info' => $address->getDetailsMultifees(),
            ));
        }
        return $this;
    }

}