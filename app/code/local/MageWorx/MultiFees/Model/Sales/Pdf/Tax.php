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

class MageWorx_MultiFees_Model_Sales_Pdf_Tax extends Mage_Tax_Model_Sales_Pdf_Tax
{
    public function getFullTaxInfo() {
        $fontSize       = $this->getFontSize() ? $this->getFontSize() : 7;
        $rates    = Mage::getResourceModel('sales/order_tax_collection')->loadByOrder($this->getOrder())->toArray();
        $fullInfo = Mage::getSingleton('tax/calculation')->reproduceProcess($rates['items']);
        $tax_info = array();

        if ($fullInfo) {
            foreach ($fullInfo as $info) {
                if (isset($info['hidden']) && $info['hidden']) {
                    continue;
                }

                $_amount = $info['amount'];

                foreach ($info['rates'] as $rate) {
                    $percent = $rate['percent'] ? ' (' . $rate['percent']. '%)' : '';

                    $tax_info[] = array(
                        'amount'    => $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($_amount),
                        'label'     => Mage::helper('tax')->__($rate['title']) . $percent . ':',
                        'font_size' => $fontSize
                    );
                }
            }
        }
        $taxClassAmount = $tax_info;
        return $taxClassAmount;
    }
 
}