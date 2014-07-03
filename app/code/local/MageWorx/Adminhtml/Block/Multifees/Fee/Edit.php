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

class MageWorx_Adminhtml_Block_Multifees_Fee_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct() {
        $this->_objectId = 'fee_id';
        $this->_blockGroup = 'mageworx';
        $this->_controller = 'multifees_fee';
        parent::__construct();        
        $this->removeButton('reset');
        
        $this->_updateButton('delete', 'label', $this->__('Delete Fee'));
        $this->_updateButton('save', 'label', $this->__('Save Fee'));
        
        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('salesrule')->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class' => 'save'
        ), 10);
        
        
        // set last tab
        $tab = Mage::app()->getRequest()->getParam('tab');
        
        $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'back/edit/tab/'+fee_edit_tabsJsTabs.activeTab.id+'/') }
            function changeConditionsBasedOnType(el){
                if (el.value==1) {
                    $('rule_payments_fieldset').previous().hide();
                    $('rule_payments_fieldset').hide();
                    $('rule_shippings_fieldset').previous().hide();
                    $('rule_shippings_fieldset').hide();
                } else if (el.value==2) {
                    $('rule_payments_fieldset').previous().show();
                    $('rule_payments_fieldset').show();                   
                    $('rule_shippings_fieldset').previous().hide();
                    $('rule_shippings_fieldset').hide();
                } else if (el.value==3) {
                    $('rule_payments_fieldset').previous().hide();
                    $('rule_payments_fieldset').hide();                   
                    $('rule_shippings_fieldset').previous().show();
                    $('rule_shippings_fieldset').show();
                }
            }
            changeConditionsBasedOnType($('fee_type'));
            $('applied_totals').size = 3;".
            ($tab?"fee_edit_tabsJsTabs.setSkipDisplayFirstTab(); fee_edit_tabsJsTabs.showTabContent($('".$tab."'));":"");
        
    }

    public function getHeaderText() {
        $model = Mage::registry('multifees_fee');
        if ($model && $model->getId()) {            
            return $this->__("Edit Fee '%s'", $this->htmlEscape($model->getTitle()));
        } else {
            return $this->__('New Fee');
        }        
    }
    
}
