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

class MageWorx_MultiFees_Block_Fee extends Mage_Core_Block_Template
{
        
    public function getCmsBlockHtml() {
        return $this->getLayout()->createBlock('cms/block')
                    ->setBlockId(Mage::getStoreConfig('mageworx_sales/multifees/static_block_for_cart_page'))
                    ->toHtml();
    }
    
    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }
    
    public function getFeeMessage($feeId, $addressId = 0) {
        $detailsFees = Mage::helper('multifees')->getQuoteDetailsMultifees($addressId);
        if (isset($detailsFees[$feeId]['message'])) {
            return $this->htmlEscape($detailsFees[$feeId]['message']);
        } else {
            return '';
        }
    }

    public function getFeeDate($feeId, $addressId = 0) {
        $detailsFees = Mage::helper('multifees')->getQuoteDetailsMultifees($addressId);
        if (isset($detailsFees[$feeId]['date'])) {
            return $detailsFees[$feeId]['date'];
        } else {
            return '';
        }
    }
    
    public function getDateFormat() {
        return Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }
 
}