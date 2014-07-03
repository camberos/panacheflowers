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

class MageWorx_Adminhtml_Block_Multifees_Fee_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel() {
        return $this->__('Conditions');
    }
   
    public function getTabTitle() {
        return $this->__('Conditions');
    }
    
    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    protected function _prepareForm() {
        $model = Mage::registry('multifees_fee');
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        // conditions
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/multifees_fee/newConditionHtml/form/rule_conditions_fieldset'));
        
        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('salesrule')->__('Apply the rule only to cart items matching the following conditions (leave blank for all items)')
        ))->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('salesrule')->__('Apply to'),
            'title' => Mage::helper('salesrule')->__('Apply to'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        
        // Payment Methods
        $fieldset = $form->addFieldset('payments_fieldset', array('legend'=>$this->__('Apply the rule to selected payment methods (leave blank for all items)')));
        $fieldset->addField('payments', 'multiselect', array(
            'name'      => 'payments[]',
            'label'     => $this->__('Payment Methods'),
            'title'     => $this->__('Payment Methods'),
            'values'    => $this->getPaymentMethods()
        ));
        
        // Shipping Methods
        $fieldset = $form->addFieldset('shippings_fieldset', array('legend'=>$this->__('Apply the rule to selected shipping methods (leave blank for all items)')));
        $fieldset->addField('shippings', 'multiselect', array(
            'name'      => 'shippings[]',
            'label'     => $this->__('Shipping Methods'),
            'title'     => $this->__('Shipping Methods'),
            'values'    => $this->getShippingMethods()
        ));
        
        
        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    private function getShippingMethods() {
//        $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();        
//        $methods = array();
//        foreach ($carriers as $code=>$carriersModel) {
//            $title = Mage::getStoreConfig('carriers/'.$code.'/title');
//            if ($title) $methods[] = array('value'=>$code, 'label'=>$title);
//        }
//        return $methods;
        
        $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $methods = array();
        foreach ($carriers as $_code => $carrier) {
            $title = Mage::getStoreConfig("carriers/$_code/title");    
            if ($_methods = $carrier->getAllowedMethods()) {
                foreach($_methods as $_mcode => $_method) {
                    if ($_method) $methods[] = array('value' => $_code . '_' . $_mcode, 'label' => $_method);
                }
            }
        }
        return $methods;
    }

    private function getPaymentMethods() {
        $methods = Mage::getSingleton('adminhtml/system_config_source_payment_allowedmethods')->toOptionArray();
        if (isset($methods[0])) {
            unset($methods[0]);
        }
        return $methods;
        
        //$payments = Mage::getSingleton('payment/config')->getActiveMethods();
        //$methods = array();
        //foreach ($payments as $paymentCode=>$paymentModel) {
            //$methods[] = array('value'=>$paymentCode, 'label'=>Mage::getStoreConfig('payment/'.$paymentCode.'/title'));
        //}
        //return $methods;
    }

}
