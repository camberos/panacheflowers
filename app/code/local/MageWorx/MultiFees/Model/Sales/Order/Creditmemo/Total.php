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

class MageWorx_MultiFees_Model_Sales_Order_Creditmemo_Total extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        $order = $creditmemo->getOrder();
        if ($order->getMultifeesAmount() > 0 && $order->getMultifeesRefunded() < $order->getMultifeesInvoiced()) {
            $creditmemo->setMultifeesAmount($order->getMultifeesInvoiced() - $order->getMultifeesRefunded());
            $creditmemo->setBaseMultifeesAmount($order->getBaseMultifeesInvoiced() - $order->getBaseMultifeesRefunded());
            $creditmemo->setMultifeesTaxAmount($order->getMultifeesTaxAmount());
            $creditmemo->setBaseMultifeesTaxAmount($order->getBaseMultifeesTaxAmount());
            $creditmemo->setDetailsMultifees($order->getDetailsMultifees());            
            
            $creditmemo->setTaxAmount($creditmemo->getTaxAmount()+$creditmemo->getMultifeesTaxAmount());
            $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount()+$creditmemo->getBaseMultifeesTaxAmount());
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getMultifeesAmount());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBaseMultifeesAmount());            
        } else {
            $creditmemo->setMultifeesAmount(0);
            $creditmemo->setBaseMultifeesAmount(0);
            $creditmemo->setMultifeesTaxAmount(0);
            $creditmemo->setBaseMultifeesTaxAmount(0);
            $creditmemo->setDetailsMultifees('');
        }
        return $this;
    }        
}
