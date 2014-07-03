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

class MageWorx_Adminhtml_Block_Multifees_Fee_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct() {
        parent::__construct();
        $this->setTemplate('multifees/options.phtml');
    }
    
    public function getTabLabel() {
        return $this->__('Options');
    }
   
    public function getTabTitle() {
        return $this->__('Options');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getPriceTypeOfForm() {
        return $this->getLayout()->createBlock('core/html_select')
            ->setData(array('id'    => 'option_price_type_{{id}}', 'class' => 'multifees-input-full'))
            ->setName('options[price_type][{{id}}]')
            ->setOptions(Mage::helper('multifees')->getPriceTypeArray())
            ->getHtml();
    }
    
    protected function _prepareLayout() {
        $this->setChild('delete_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('eav')->__('Delete'),
                            'class' => 'delete delete-option'
                        )));

        $this->setChild('add_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('eav')->__('Add Option'),
                            'class' => 'add',
                            'id' => 'add_new_option_button'
                        )));

        $this->setChild('add_image_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => '{{add_image_button}}',
                            'class' => 'add',
                            'id' => 'new-option-file-{{id}}',
                            'onclick' => 'feeOption.createFileField(this.id)'
                        )));

        return parent::_prepareLayout();
    }

    public function getDeleteButtonHtml() {
        return $this->getChildHtml('delete_button');
    }

    public function getAddNewButtonHtml() {
        return $this->getChildHtml('add_button');
    }

    public function getAddImageButtonHtml() {
        return $this->getChildHtml('add_image_button');
    }

    public function getStores() {
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                    ->getResourceCollection()
                    ->setLoadDefault(true)
                    ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }
    
    protected function _prepareForm() {
        $model = Mage::registry('multifees_fee');
        $form = new Varien_Data_Form();
        
        $fieldset = $form->addFieldset('apply_fieldset', array('legend'=>$this->__('Options Settings')));
        
        $fieldset->addField('input_type', 'select', array(
            'name'       => 'input_type',
            'label'      => $this->__('Input Type'),
            'required'   => true,            
            'options'    => Mage::helper('multifees')->getInputTypeArray(),
            'onchange'   => "feeOption.changeInputType(this.value);"
        ));
        
        
        $fieldset->addField('is_onetime', 'select', array(
            'name'       => 'is_onetime',
            'label'      => $this->__('One-time'),
            'options'    => Mage::helper('multifees')->getYesNoArray()
        ));
        
        
        $fieldset->addField('applied_totals', 'multiselect', array(
            'name'      => 'applied_totals[]',
            'label'     => $this->__('Apply Fee To').'<br/><small>'.$this->__('(percent price type only)').'</small>',
            'title'     => $this->__('Apply Fee To'),
            'values'    => $this->getAppliedTotals(),
            'size'    => '3'
        ));
        
        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }                   
    
    public function getAppliedTotals() {
        return array(
            array('value'=>'subtotal', 'label'=>Mage::helper('sales')->__('Subtotal')),
            array('value'=>'shipping', 'label'=>Mage::helper('sales')->__('Shipping & Handling')),
            array('value'=>'tax', 'label'=>Mage::helper('sales')->__('Tax'))
        );
    }
    
    public function getFromElement($elementId) {
        return $this->getForm()->getElement($elementId)->toHtml();
    }

    public function getOptionValues() {
        
        $values = array();
        
        $model = Mage::registry('multifees_fee');        
        if ($model->getId()) {
            $collection = Mage::getResourceModel('multifees/option_collection')
                ->addFeeFilter($model->getId())                
                ->sortByPosition();
            foreach ($collection as $option) {
                $value = array();
                $isDefault = $option->getIsDefault();
                if ($isDefault) {
                    $value['checked'] = 'checked="checked"';
                } else {
                    $value['checked'] = '';
                }
                
                // if hidden or notice
                if ($model->getInputType()==4 || $model->getInputType()==5) $value['disabled'] = 'disabled'; else $value['disabled'] = '';
                
                $value['id'] = $option->getId();
                $value['price'] = Mage::app()->getStore()->roundPrice($option->getPrice());
                $value['price_type'] = $option->getPriceType();
                $value['default_input_type'] = ($model->getInputType()==1 || $model->getInputType()==2)?'radio':'checkbox';
                $value['sort_order'] = $option->getPosition();
                    
                $value['image'] = Mage::helper('multifees')->getOptionImgHtml($option->getId());                
                $value['add_image_button'] = ($value['image']?$this->__('Change Image'):$this->__('Add Image'));
                
                
                $languageData = Mage::getResourceSingleton('multifees/language_option')->getAllLanguage($option->getId());
                foreach ($this->getStores() as $store) {
                    //$storeValues = $stores[$store->getStoreId()];
                    if (isset($languageData[$store->getStoreId()])) {
                        $value['store' . $store->getStoreId()] = $languageData[$store->getStoreId()];
                    } else {
                        $value['store' . $store->getStoreId()] = '';
                    }
                }
                $values[] = new Varien_Object($value);
            }
        }
        return $values;
    }
   
}
