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
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Multi Fees extension
 *
 * @category   MageWorx
 * @package    MageWorx_MultiFees
 * @author     MageWorx Dev Team
 */

class MageWorx_Adminhtml_Block_Multifees_Sales_Order_Create_Total extends Mage_Adminhtml_Block_Sales_Order_Create_Totals_Default
{
    protected $_template = 'multifees/create_totals.phtml';
    
    public function isInclTax() {
        $code = $this->getTotal()->getCode();        
        if ($code=='multifees') {
            if (Mage::helper('multifees')->getTaxInCart()==2) return true; else return false;
        } elseif ($code=='tax_multifees') {
            return true;
        }
        return true;
    }
 
}