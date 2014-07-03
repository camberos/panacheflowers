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

class MageWorx_MultiFees_Model_Observer {        
    
    
    // to display multifees block in front ***
    public function toHtmlBlockFrontBefore($observer) {
        $helper = Mage::helper('multifees');
        if (!$helper->isEnabled()) return $this;
        
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        // 0 - Custom Position, 1 - Above Crosssell, 2 - Below Crosssell, 3 - Above Coupon, 4 - Below Coupon, 5 - Above Shipping, 6 - Below Shipping;
        
        $position = $helper->isEnableCartFees()?$helper->getPositionInCart():0;
        if (($position==1 || $position==2) && $block instanceof Mage_Checkout_Block_Cart_Crosssell) {
            $this->addCartFeesBlockToCheckout($block, $transport, ($position==1?true:false));
        } elseif (($position==3 || $position==4) && $block instanceof Mage_Checkout_Block_Cart_Coupon) {
            $this->addCartFeesBlockToCheckout($block, $transport, ($position==3?true:false));
        } elseif (($position==5 || $position==6) && $block instanceof Mage_Checkout_Block_Cart_Shipping) {
            $this->addCartFeesBlockToCheckout($block, $transport, ($position==5?true:false));
        } elseif ($block instanceof Mage_Payment_Block_Form) {
            if ($helper->isEnablePaymentFees()) {
                $method = $block->getData('method');
                if ($method instanceof Mage_Payment_Model_Method_Abstract && $method->getCode()) {
                    // add payment fees
                    $html = $transport->getHtml();
                    $block->setChild('payment_fee', $block->getLayout()->createBlock('multifees/fee')->setTemplate('multifees/payment_fee.phtml')->setCode($method->getCode()));
                    $html .= $block->getChild('payment_fee')->toHtml();
                    $transport->setHtml($html);
                }
            }
        } elseif ($block instanceof Mage_Paypal_Block_Express_Review) {
            if ($helper->isEnablePaymentFees()) $this->addPaymentFeesBlockToPaypalCheckout($block, $transport);
        } elseif ($block instanceof Mage_Checkout_Block_Onepage_Shipping_Method_Available) {
            if ($helper->isEnableShippingFees()) $this->addShippingFeesBlockToCheckout($block, $transport);
            // for multishipping - show layout -> checkout_multishipping_shipping
            // for paypal_shipping - show layout -> paypal_express_review
        }
        
        return $this;
    }
    
    protected function addPaymentFeesBlockToPaypalCheckout($block, $transport) {
        if (Mage::app()->getStore()->isAdmin()) {
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } else {
            $quote = Mage::getSingleton('checkout/cart')->getQuote();
        }
        $code = $quote->getPayment()->getMethod();
        $html = $transport->getHtml();
        $block->setChild('payment_fee', $block->getLayout()->createBlock('multifees/fee')->setTemplate('multifees/payment_fee.phtml')->setCode($code)->setDisplay(true));

        // search - <div class="info-set col2-set">
        $html = preg_replace('/(\<div\s.+?class\=.+?col2-set.+?\>)/is', '\\1 [col2_set_div_mark]', $html);
        $htmlArr = explode('[col2_set_div_mark]', $html);
        if (count($htmlArr) < 2) return;
        
        foreach ($htmlArr as $i => $targetHtml) {
            if (stripos($targetHtml, 'id="shipping_method') === false) continue;
            if (stripos($targetHtml, '<div class="col-1">') === false) {
                $targetHtml = '<div class="col-1"><div class="box"><div class="box-content">' . $block->getChild('payment_fee')->toHtml() . '</div></div></div>' . $targetHtml;
            } else {
                $targetHtml = preg_replace('/(\<div[^\>]+?class\=[^\>]+?col-1[^\>]+?\>.+?)(\<\/div\>)/is', '\\1 ' . $block->getChild('payment_fee')->toHtml() . ' \\2', $targetHtml);
            }
            $htmlArr[$i] = $targetHtml;
            break;
        }
        $html = implode('', $htmlArr);
        $transport->setHtml($html);
    }
        
    protected function addShippingFeesBlockToCheckout($block, $transport) {
        if (Mage::app()->getRequest()->getRouteName()==='firecheckout' || Mage::app()->getRequest()->getActionName()!='index') {
            $html = $transport->getHtml();
            $block->setChild('shipping_wrapper', $block->getLayout()->createBlock('core/template')->setTemplate('multifees/shipping_wrapper.phtml'));
            $html .= $block->getChild('shipping_wrapper')->toHtml();
            $transport->setHtml($html);
        }
    }
        
    protected function addCartFeesBlockToCheckout($block, $transport, $before) {
        $html = $transport->getHtml();
        if ($before) {
            $html = $block->getBlockHtml('checkout.cart.fee') . $html;
        } else {
            if ($block instanceof Mage_Checkout_Block_Cart_Crosssell) $html .= '<br/>';
            $html .= $block->getBlockHtml('checkout.cart.fee');
        }
        $transport->setHtml($html);
    }
    
    
    
    // to display multifees block in admin ***
    
    public function toHtmlBlockAdminBefore($observer) {
        if (Mage::app()->getFrontController()->getAction()->getFullActionName()=='tinybrick_orderedit_order_recalc') return $this;
        
        $helper = Mage::helper('multifees');
        if (!$helper->isEnabled()) return $this;
        
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View_History) {
            $this->addDetailsMultifeesToOrderView($block, $transport);
        } elseif ($block instanceof Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Address) {
            $this->addAdditionalFeesToOrderEdit($block, $transport);
        } elseif ($block instanceof Mage_Payment_Block_Form) {
            if ($helper->isEnablePaymentFees()) {
                $method = $block->getData('method');
                if ($method instanceof Mage_Payment_Model_Method_Abstract && $method->getCode()) {
                    // add payment fees
                    $html = $transport->getHtml();                
                    $block->setChild('payment_fee', $block->getLayout()->createBlock('mageworx/multifees_sales_order_create_fee')->setTemplate('multifees/payment_fee.phtml')->setCode($method->getCode()));
                    $html .= $block->getChild('payment_fee')->toHtml();
                    $transport->setHtml($html);
                }
            }
        } elseif ($block instanceof Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form) {
            if ($helper->isEnableShippingFees()) {
                // add shipping fees
                $html = $transport->getHtml();
                $block->setChild('multifees_shipping_wrapper', $block->getLayout()->createBlock('core/template')->setTemplate('multifees/shipping_wrapper.phtml'));
                $html .= $block->getChild('multifees_shipping_wrapper')->toHtml();
                $transport->setHtml($html);
            }
        }
        return $this;
    }        
    
    // add after 'order_history' block 'multifees_details' in order_view
    protected function addDetailsMultifeesToOrderView($block, $transport) {
        if (Mage::app()->getRequest()->getActionName()=='view' && Mage::helper('multifees')->isEnabled()) {
            $html = $transport->getHtml();
            $multifeesBlock = $block->getLayout()->getBlock('multifees_details');
            $fullInfo = $multifeesBlock->getSource()->getDetailsMultifees();
            if ($fullInfo) {
                $fullInfo = unserialize($fullInfo);
                
                // division fees by type: 1-Cart,2-Payment,3-Shipping
                $cartFees = array();
                $paymentFees = array();
                $shippingFees = array();
                
                foreach ($fullInfo as $feeId=>$fee) {
                    if (isset($fee['type'])) {
                        switch ($fee['type']) {
                            case 1:
                                $cartFees[$feeId] = $fee;
                                break;
                            case 2:
                                $paymentFees[$feeId] = $fee;
                                break;
                            case 3:
                                $shippingFees[$feeId] = $fee;
                                break;
                        }
                    }
                }
                
                if ($cartFees) $html .= $this->_addDetailsMultifeesBlockHtml($multifeesBlock->setDetailsMultifees($cartFees), 1);
                if ($paymentFees) $html .= $this->_addDetailsMultifeesBlockHtml($multifeesBlock->setDetailsMultifees($paymentFees), 2);
                if ($shippingFees) $html .= $this->_addDetailsMultifeesBlockHtml($multifeesBlock->setDetailsMultifees($shippingFees), 3);                
            }
            $transport->setHtml($html);
        }
    }
    
    //$type = 1-Cart Fee,2-Payment Fee,3-Shipping Fee
    protected function _addDetailsMultifeesBlockHtml($multifeesBlock, $type) {        
        switch ($type) {
            case 1:
                $title = Mage::helper('multifees')->__('Cart Fees');
                break;
            case 2:
                $title = Mage::helper('multifees')->__('Payment Fees');
                break;
            case 3:
                $title = Mage::helper('multifees')->__('Shipping Fees');
                break;
        }
        $html = '</fieldset></div></div>';
        $html .= '<div class="box-right entry-edit multifees-details"><div class="entry-edit-head"><h4>'.$title.'</h4></div><div><fieldset>';                        
        $html .= $multifeesBlock->toHtml();
        return $html;
    }
    
    // add after 'shipping_address' block sales_fees in order_edit
    protected function addAdditionalFeesToOrderEdit($block, $transport) {        
        if (Mage::helper('multifees')->isEnabled() && Mage::helper('multifees')->isEnableCartFees()) {            
            $blocks = Mage::app()->getRequest()->getParam('block');
            if (strpos($blocks, 'multifees')===false) {
                $html = $transport->getHtml();
                $html .= '</div></div>';
                $html .= '<div class="entry-edit">';
                $html .= '<div id="order-multifees" class="box-left">';
                $html .= $block->getBlockHtml('multifees');
                $transport->setHtml($html);
            }
        }
    }    
    
    // functional ***
    public function addressSaveBefore($observer) {
        $address = $observer->getEvent()->getQuoteAddress();
        // add estimate shipping fee (front)
        if (Mage::app()->getRequest()->getPost('is_estimate_shipping_fee', false)) {
            $feesPost = Mage::app()->getRequest()->getPost('fee', array());
            Mage::helper('multifees')->addFeesToCart($feesPost, $address->getQuote()->getStoreId(), false, 3, 0);
        } else if (Mage::app()->getRequest()->getPost('is_multishipping_fee', false)) { // add multishipping fee (front)
            $addressPost = Mage::app()->getRequest()->getPost('address', array());
            if (isset($addressPost[$address->getId()]['fee'])) {
                $feesPost = $addressPost[$address->getId()]['fee'];
                Mage::helper('multifees')->addFeesToCart($feesPost, $address->getQuote()->getStoreId(), false, 3, $address->getId());
            }
        }        
    }
    
    public function orderСreateProcessData($observer) {
        // check submit fees when admin/sales_order_create
        if (!Mage::helper('multifees')->isEnabled()) return $this;
        $post = $observer->getEvent()->getRequest();        
        if (isset($post['is_cart_fee'])) {            
            Mage::helper('multifees')->addFeesToCart(isset($post['fee'])?$post['fee']:array(), Mage::getSingleton('adminhtml/session_quote')->getStoreId(), true, 1, 0);            
        } 
//        elseif (isset($post['is_shipping_fee'])) {
//            Mage::helper('multifees')->addFeesToCart(isset($post['fee'])?$post['fee']:array(), Mage::getSingleton('adminhtml/session_quote')->getStoreId(), true, 3, 0);
//        }
        return $this;
    }
    
    public function quoteCollectTotalsAfter($observer) {
        
        // check required fees
        $helper = Mage::helper('multifees');
        if (!$helper->isEnabled() || !$helper->isEnableCartFees()) return $this;
        $quote = $observer->getEvent()->getQuote();
        
        if (Mage::app()->getStore()->isAdmin()) {
            $session = Mage::getSingleton('adminhtml/session_quote');
        } else {
            $session = Mage::getSingleton('checkout/session');
        }
        $session->setMultifeesValidationFailed(false);
        
        $requiredCartFees = $helper->getMultifees(1, 1, 2, 0, '', $quote); // required cart fees, no hidden      
        if (!$requiredCartFees) return $this;
        
        $feesData = $helper->getQuoteDetailsMultifees();
        
        foreach($requiredCartFees as $fee) {
            if (!isset($feesData[$fee->getFeeId()])) {
                if (Mage::app()->getRequest()->getModuleName()=='multifees') $quote->addMessage($helper->__('You need to select required additional fees to proceed to checkout.'), 'error');
                $quote->setData('has_error', true);
                $session->setMultifeesValidationFailed(true);
                return $this;
            }
        }
        
        return $this;
    }
    
    public function cartSaveBefore($observer) {        
        // check reorder in front
        if (!Mage::helper('multifees')->isEnabled() || Mage::app()->getFrontController()->getAction()->getFullActionName()!='sales_order_reorder') return $this;
        $order = Mage::registry('current_order');
        if (!$order)  return $this;
        $feesPost = $this->convertOrdersToFeeSubmitPost($order->getDetailsMultifees());        
        $storeId = Mage::app()->getStore()->getId();
        Mage::helper('multifees')->addFeesToCart($feesPost, $storeId, true, 0, 0);
        return $this;
    }    
    
    public function convertOrderToQuote($observer) {
        // edit or reorder in backend
        if (!Mage::helper('multifees')->isEnabled()) return $this;
        $order = $observer->getEvent()->getOrder();
        $feesPost = $this->convertOrdersToFeeSubmitPost($order->getDetailsMultifees());
        Mage::helper('multifees')->addFeesToCart($feesPost, $order->getStoreId(), true, 0, 0);
        return $this;
    }
    
    public function convertOrdersToFeeSubmitPost($feesData) {
        if ($feesData) $feesData = unserialize($feesData); else $feesData = array();
        foreach ($feesData as $feeId => $data) {
            if (!isset($data['options'])) continue;                                
            foreach ($data['options'] as $optionId=>$value) {
                $feesData[$feeId]['options'][$optionId] = $optionId;
            }
        }
        return $feesData;
    }
       
    public function paypalCart($observer) {
        $helper = Mage::helper('multifees');
        if (!$helper->isEnabled()) return $this;

        $paypalModel = $observer->getEvent()->getPaypalCart();
        $salesEntity = $paypalModel->getSalesEntity();

        if ($salesEntity instanceof Mage_Sales_Model_Order) {
            $multifeesAmount = $salesEntity->getMultifeesAmount() - $salesEntity->getMultifeesTaxAmount();
        } else {
            $address = $helper->getSalesAddress($salesEntity);
            $multifeesAmount = $address->getMultifeesAmount() - $address->getMultifeesTaxAmount();
        }
        if ($multifeesAmount > 0) $paypalModel->addItem($helper->__('Additional Fees'), 1, $multifeesAmount, 'multifees');
    }

    public function placeOrderAfter(Varien_Event_Observer $observer) {        
        // make statistics
        $order = $observer->getEvent()->getOrder();        
        $feesData = $order->getDetailsMultifees();
        if ($feesData) $feesData = unserialize($feesData); else $feesData = array();
        
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        
        
        foreach ($feesData as $feeId => $data) {
            if (!isset($data['options'])) continue;                                
            $connection->query('UPDATE `'.$tablePrefix .'multifees_fee` SET `total_base_amount` = `total_base_amount` + '. floatval($data['base_price']) .', `total_ordered` = `total_ordered` + 1 WHERE `fee_id` = ' . intval($feeId));          
           // bad variant :( 
//            $feeModel = Mage::getSingleton('multifees/fee')->load($feeId);
//            if ($feeModel && $feeModel->getId()) {
//                $feeModel->setTotalBaseAmount($feeModel->getTotalBaseAmount() + floatval($data['base_price']));
//                $feeModel->setTotalOrdered($feeModel->getTotalOrdered() + 1);
//                $feeModel->save();
//            }            
        }        

        // clear details multifees
        if (Mage::app()->getStore()->isAdmin()) {
            $session = Mage::getSingleton('adminhtml/session_quote');
        } else {
            $session = Mage::getSingleton('checkout/session');
        }        
        $session->setDetailsMultifees(null);        
    }
    
    public function orderInvoiceSaveAfter(Varien_Event_Observer $observer) {
        $invoice = $observer->getEvent()->getInvoice();        
        if ($invoice->getBaseMultifeesAmount()>0) {
            $order = $invoice->getOrder();
            $order->setBaseMultifeesInvoiced($order->getBaseMultifeesInvoiced() + $invoice->getBaseMultifeesAmount());
            $order->setMultifeesInvoiced($order->getMultifeesInvoiced() + $invoice->getMultifeesAmount());
        }
    }
    
    public function orderCreditmemoSaveAfter(Varien_Event_Observer $observer) {
        $creditmemo = $observer->getEvent()->getCreditmemo();        
        if ($creditmemo->getBaseMultifeesAmount()>0) {
            $order = $creditmemo->getOrder();
            $order->setBaseMultifeesRefunded($order->getBaseMultifeesRefunded() + $creditmemo->getBaseMultifeesAmount());
            $order->setMultifeesRefunded($order->getMultifeesRefunded() + $creditmemo->getMultifeesAmount());
            
            // make statistics
            $feesData = $creditmemo->getDetailsMultifees();
            if ($feesData) $feesData = unserialize($feesData); else $feesData = array();
            foreach ($feesData as $feeId => $data) {
                if (!isset($data['options'])) continue;
                $feeModel = Mage::getSingleton('multifees/fee')->load($feeId);
                if ($feeModel && $feeModel->getId()) {
                    $feeModel->setTotalBaseAmount($feeModel->getTotalBaseAmount() - floatval($data['base_price']));
                    $feeModel->setTotalOrdered($feeModel->getTotalOrdered() - 1);
                    $feeModel->save();
                }
            }
        }
    }
    
    public function cancelOrderAfter(Varien_Event_Observer $observer) {
        // make statistics
        $order = $observer->getEvent()->getOrder();
        if (($order->getBaseMultifeesAmount() - $order->getBaseMultifeesInvoiced() - $order->getBaseMultifeesCanceled()) > 0) {
            $order->setBaseMultifeesCanceled($order->getBaseMultifeesAmount() - $order->getBaseMultifeesInvoiced() - $order->getBaseMultifeesCanceled());
            $order->setMultifeesCanceled($order->getMultifeesAmount() - $order->getMultifeesInvoiced() - $order->getMultifeesCanceled());
            
            $feesData = $order->getDetailsMultifees();
            if ($feesData) $feesData = unserialize($feesData); else $feesData = array();
            foreach ($feesData as $feeId => $data) {
                if (!isset($data['options'])) continue;
                $feeModel = Mage::getSingleton('multifees/fee')->load($feeId);
                if ($feeModel && $feeModel->getId()) {
                    $feeModel->setTotalBaseAmount($feeModel->getTotalBaseAmount() - floatval($data['base_price']));
                    $feeModel->setTotalOrdered($feeModel->getTotalOrdered() - 1);
                    $feeModel->save();
                }
            }
        }
    }
}