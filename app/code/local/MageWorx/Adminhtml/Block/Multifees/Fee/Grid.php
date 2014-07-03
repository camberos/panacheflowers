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
class MageWorx_Adminhtml_Block_Multifees_Fee_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('fee_id');
        $this->setDefaultSort('id');
        $this->setDefaultDir(Varien_Data_Collection::SORT_ORDER_DESC);
	$this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('multifees/fee_collection')->addLabels(0);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $helper = Mage::helper('multifees');
        
        $this->addColumn('id', array(
            'header' => $helper->__('ID'),
            'type' => 'number',
            'width' => '80px',
            'index' => 'fee_id',
        ));
        
        $this->addColumn('name', array(
            'header' => $helper->__('Name'),
            'index' => 'title'
        ));

        $this->addColumn('type', array(
            'header' => $helper->__('Type'),
            'width' => '100px',
            'index' => 'type',
            'type' => 'options',
            'options' => $helper->getTypeArray(),
        ));
        
        
        $this->addColumn('input_type', array(
            'header' => $helper->__('Input Type'),
            'width' => '100px',
            'index' => 'input_type',
            'type' => 'options',
            'options' => $helper->getInputTypeArray(),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => $helper->__('Store View'),
                'width' => '200px',
                'index' => 'store_id',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'sortable' => false,
                'filter_condition_callback'
                => array($this, '_filterStoreCondition'),
                'align' => 'center'
            ));
        }

        $this->addColumn('required', array(
            'header' => $helper->__('Required'),
            'width' => '80px',
            'index' => 'required',
            'sortable' => false,
            'type' => 'options',
            'options' => $helper->getNoYesArray(),
            'align' => 'center'
        ));

        $this->addColumn('sort_order', array(
            'header' => $helper->__('Sort Order'),
            'type' => 'number',
            'width' => '80px',
            'index' => 'sort_order',
        ));

        
        $this->addColumn('total_ordered', array(
            'header' => $helper->__('Ordered'),
            'type' => 'number',
            'width' => '80px',
            'index' => 'total_ordered',
        ));
        
        $currencyCode = $this->getCurrentCurrencyCode();
        $this->addColumn('total_base_amount', array(
            'header' => $helper->__('Total'),
            'type' => 'currency',
            'currency_code' => $currencyCode,
            'width' => '80px',
            'index' => 'total_base_amount',
        ));
        
        
        $this->addColumn('status', array(
            'header' => $helper->__('Status'),
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => $helper->getStatusArray(),
            'align' => 'center'
        ));

        $this->addColumn('action', 
            array(
                'header'    => $helper->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $helper->__('Edit'),
                        'url'     => array('base'=>'*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
                'align' => 'center'            
        ));

        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
    public function getCurrentCurrencyCode() {
        if (is_null($this->_currentCurrencyCode)) {
            $this->_currentCurrencyCode = (count($this->_storeIds) > 0)
                ? Mage::app()->getStore(array_shift($this->_storeIds))->getBaseCurrencyCode()
                : Mage::app()->getStore()->getBaseCurrencyCode();
        }
        return $this->_currentCurrencyCode;
    }
    
    protected function _filterStoreCondition($collection, $column) {
        if (!$value = $column->getFilter()->getValue()) return;        
        $this->getCollection()->addStoreFilter($value);
    }    

    protected function _prepareMassaction() {
        $helper = Mage::helper('multifees');
        
        $this->setMassactionIdField('fee_id');
        $this->getMassactionBlock()->setFormFieldName('fee');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $helper->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $helper->__('Are you sure you want to do this?')
        ));
                
        $statuses = array();
        $statuses[''] = '';
        $array = $helper->getStatusArray();
        foreach($array as $key=>$value) {
             $statuses[$key] = $value;
        }

        $this->getMassactionBlock()->addItem('status', array(
            'label' => $helper->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        return $this;
    }

}