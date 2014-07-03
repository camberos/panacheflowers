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

class MageWorx_MultiFees_Model_Sales_Order_Invoice_Total extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {   
        $order = $invoice->getOrder();
        if ($order->getMultifeesAmount() > 0 && $order->getMultifeesInvoiced() < ($order->getMultifeesAmount() - $order->getMultifeesCanceled())) {            
            $invoice->setMultifeesAmount($order->getMultifeesAmount() - $order->getMultifeesInvoiced() - $order->getMultifeesCanceled());
            $invoice->setBaseMultifeesAmount($order->getBaseMultifeesAmount()-$order->getBaseMultifeesInvoiced() - $order->getBaseMultifeesCanceled());            
            $invoice->setMultifeesTaxAmount($order->getMultifeesTaxAmount());
            $invoice->setBaseMultifeesTaxAmount($order->getBaseMultifeesTaxAmount());
            $invoice->setDetailsMultifees($order->getDetailsMultifees());            
                        
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMultifeesAmount() - $invoice->getMultifeesTaxAmount());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBaseMultifeesAmount()- $invoice->getBaseMultifeesTaxAmount());
        } else {
            $invoice->setMultifeesAmount(0);
            $invoice->setBaseMultifeesAmount(0);
            $invoice->setMultifeesTaxAmount(0);
            $invoice->setBaseMultifeesTaxAmount(0);
            $invoice->setDetailsMultifees('');
        }
        return $this;
    }
}
