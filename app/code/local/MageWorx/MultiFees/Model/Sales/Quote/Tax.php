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

class MageWorx_MultiFees_Model_Sales_Quote_Tax extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    public function __construct() {
        $this->setCode('multifees');
    }
    
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        if ($address->getMultifeesAmount()==0) return $this;
        
        $helper = Mage::helper('multifees');
        // if $taxMode==3 ---> show Price Incl. Tax
        if ($helper->isEnabled() && $helper->getTaxInCart()==3 && $address->getDetailsMultifees()) {            
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $helper->__('Additional Fees (Incl. Tax)'),
                'value' => $address->getMultifeesAmount(),
                'full_info' => $address->getDetailsMultifees(),
            ));
        }
        return $this;
    }

}