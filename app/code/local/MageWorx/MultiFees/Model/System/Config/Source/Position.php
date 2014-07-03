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

class MageWorx_MultiFees_Model_System_Config_Source_Position
{
    public function toOptionArray() {
        $helper = Mage::helper('multifees');
        return array(
            array('value' => 0, 'label' => $helper->__('Custom Position')),
            array('value' => 1, 'label' => $helper->__('Above Crosssell')),
            array('value' => 2, 'label' => $helper->__('Below Crosssell')),
            array('value' => 3, 'label' => $helper->__('Above Coupon')),
            array('value' => 4, 'label' => $helper->__('Below Coupon')),
            array('value' => 5, 'label' => $helper->__('Above Estimate Shipping')),
            array('value' => 6, 'label' => $helper->__('Below Estimate Shipping'))            
        );
        
    }
}