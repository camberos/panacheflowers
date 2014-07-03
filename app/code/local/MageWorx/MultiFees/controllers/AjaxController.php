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
require_once('Mage/Checkout/controllers/CartController.php');
class MageWorx_MultiFees_AjaxController extends Mage_Checkout_CartController //Mage_Core_Controller_Front_Action
{
    public function submitAction() {
        if (!Mage::helper('multifees')->isEnabled()) return;        
        
        $feesPost = $this->getRequest()->getPost('fee');                
        $storeId = Mage::app()->getStore()->getId();
        Mage::helper('multifees')->addFeesToCart($feesPost, $storeId, true, 1, 0);      
                
        // copy from partn::indexAction()
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $warning = Mage::getStoreConfig('sales/minimum_order/description');
                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                $cart->getCheckoutSession()->addMessage($message);
            }
        }

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);
        // end copy from partn::indexAction()
        
        $this->getLayout()
                ->getUpdate()
                //->addHandle('checkout_cart_index')
                ->addHandle('checkout_fee_submit');
        $this->loadLayoutUpdates()->_initLayoutMessages('checkout/session');
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->renderLayout();        
    }
    
}
