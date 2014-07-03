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

class MageWorx_Adminhtml_Block_Multifees_Fee_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel() {
        return $this->__('Fee Information');
    }
   
    public function getTabTitle() {
        return $this->__('Fee Information');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('fee_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>$this->__('Fee  Information')));

        $model = Mage::registry('multifees_fee');
        if ($model && $model->getId()) {
            $fieldset->addField('fee_id', 'hidden', array(
                'name' => 'fee_id',
            ));
        }        

        $fieldset->addField('title', 'text', array(
            'name' => 'title',
            'label' => $this->__('Fee Name'),
            'title' => $this->__('Fee Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => $this->__('Description'),
            'title' => $this->__('Description'),
            'style' => 'width: 98%; height: 100px;',
        ));

        
        $fieldset->addField('type', 'select', array(
            'name'       => 'type',
            'label'      => $this->__('Fee Type'),
            'options'    => Mage::helper('multifees')->getTypeArray(),
            'onchange'  => 'changeConditionsBasedOnType(this)',
        ));        
        
        $fieldset->addField('required', 'select', array(
            'name'      => 'required',
            'label'     => $this->__('Required'),
            'title'     => $this->__('Required'),
            'options'   => Mage::helper('multifees')->getNoYesArray()
        ));        

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => $this->__('Sort Order'),
        ));

        
        $fieldset->addField('status', 'select', array(
            'label'     => $this->__('Status'),
            'title'     => $this->__('Status'),
            'name'      => 'status',
            'options'   => Mage::helper('multifees')->getStatusArray()
        ));
        
        
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => $this->__('Store View'),
                'title'     => $this->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
            ));
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }
               
        
        
        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('salesrule')->__('NOT LOGGED IN')));

        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'name'      => 'customer_group_ids[]',
            'label'     => $this->__('Customer Groups'),
            'title'     => $this->__('Customer Groups'),
            'required'  => true,
            'values'    => $customerGroups,
        ));
        
        
        $fieldset->addField('tax_class_id', 'select', array(
            'name'       => 'tax_class_id',
            'label'      => $this->__('Tax Class'),
            'options'    => Mage::helper('multifees')->getTaxClassesArray()
        ));
        
        $fieldset->addField('enable_customer_message', 'select', array(
            'label'     => $this->__('Enable Customer Message'),
            'title'     => $this->__('Enable Customer Message'),
            'name'      => 'enable_customer_message',
            'options'   => Mage::helper('multifees')->getNoYesArray()
        ));
        
        $fieldset->addField('customer_message_title', 'text', array(
            'name' => 'customer_message_title',
            'label' => $this->__('Customer Message Title'),
            'title' => $this->__('Customer Message Title')
        ));
        
        $fieldset->addField('enable_date_field', 'select', array(
            'label'     => $this->__('Enable Date Field'),
            'title'     => $this->__('Enable Date Field'),
            'name'      => 'enable_date_field',
            'options'   => Mage::helper('multifees')->getNoYesArray()
        ));
        
        $fieldset->addField('date_field_title', 'text', array(
            'name' => 'date_field_title',
            'label' => $this->__('Date Field Title'),
            'title' => $this->__('Date Field Title')
        ));        
        
        $form->setValues($model->getData());

        $this->setForm($form);

        // field dependencies
//        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
//            ->addFieldMap($couponTypeFiled->getHtmlId(), $couponTypeFiled->getName())
//            ->addFieldMap($couponCodeFiled->getHtmlId(), $couponCodeFiled->getName())
//            ->addFieldMap($usesPerCouponFiled->getHtmlId(), $usesPerCouponFiled->getName())
//            ->addFieldDependence(
//                $couponCodeFiled->getName(),
//                $couponTypeFiled->getName(),
//                Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
//            ->addFieldDependence(
//                $usesPerCouponFiled->getName(),
//                $couponTypeFiled->getName(),
//                Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
//        );

        return parent::_prepareForm();
    }
}
