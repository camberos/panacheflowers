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

class MageWorx_Adminhtml_Block_Multifees_Fee_Edit_Tab_Labels extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel() {
        return $this->__('Labels');
    }
   
    public function getTabTitle() {
        return $this->__('Labels');
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
        $form->setHtmlIdPrefix('fee_');

        // Fee Name Labels
        $labels = $model->getStoreNames();        
        $fieldset = $form->addFieldset('store_fee_names_fieldset', array(
            'legend'       => $this->__('Store View Fee Names'),
            'table_class'  => 'form-list stores-tree',
        ));
        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("s_{$store->getId()}", 'text', array(
                        'name'      => 'store_fee_names['.$store->getId().']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'value'     => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }
        
        // Description Labels
        $labels = $model->getStoreDescriptions();
        $fieldset = $form->addFieldset('store_fee_descriptions_fieldset', array(
            'legend'       => $this->__('Store View Fee Descriptions'),
            'table_class'  => 'form-list stores-tree',
        ));
        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("ww_{$website->getId()}_label", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("ssg_{$group->getId()}_label", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("ss_{$store->getId()}", 'textarea', array(
                        'name'      => 'store_fee_descriptions['.$store->getId().']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'value'     => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }
        
        // Customer Message Title Labels
        $labels = $model->getStoreCustomerMessageTitles();
        $fieldset = $form->addFieldset('store_fee_customer_message_titles_fieldset', array(
            'legend'       => $this->__('Store View Fee Customer Message Titles'),
            'table_class'  => 'form-list stores-tree',
        ));
        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("www_{$website->getId()}_label", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sssg_{$group->getId()}_label", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("sss_{$store->getId()}", 'text', array(
                        'name'      => 'store_fee_customer_message_titles['.$store->getId().']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'value'     => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }
        
        // Customer Message Title Labels
        $labels = $model->getStoreDateFieldTitles();
        $fieldset = $form->addFieldset('store_fee_date_field_titles_fieldset', array(
            'legend'       => $this->__('Store View Fee Date Field Titles'),
            'table_class'  => 'form-list stores-tree',
        ));
        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("wwww_{$website->getId()}_label", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("ssssg_{$group->getId()}_label", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("ssss_{$store->getId()}", 'text', array(
                        'name'      => 'store_fee_date_field_titles['.$store->getId().']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'value'     => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
