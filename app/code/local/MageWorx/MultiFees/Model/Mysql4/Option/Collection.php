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

class MageWorx_MultiFees_Model_Mysql4_Option_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct() {
    	parent::_construct();
        $this->_init('multifees/option');
    }

    public function sortByPosition($sort = 'DESC') {
    	$this->getSelect()->order('main_table.position '.$sort)->order('main_table.fee_option_id '.$sort);
        return $this;
    }

    public function addFeeFilter($feeId) {
    	$this->getSelect()->where('main_table.fee_id = ?', $feeId);
        return $this;
    }

    public function addStoreLanguage($storeId = null) {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        if ($storeId!=0) {
            $this->getSelect()->joinLeft(array('language_option_default' => $this->getTable('multifees/language_option')), 'main_table.fee_option_id = language_option_default.fee_option_id AND language_option_default.store_id = 0')
                ->joinLeft(array('language_option' => $this->getTable('multifees/language_option')), 'main_table.fee_option_id = language_option.fee_option_id AND language_option.store_id = '.$storeId, array('title'=>"IF(language_option.title!='', language_option.title, language_option_default.title)"));
        } else {
            $this->getSelect()->joinLeft(array('language_option_default' => $this->getTable('multifees/language_option')), 'main_table.fee_option_id = language_option_default.fee_option_id AND language_option_default.store_id = 0', array('title'));
        }
        return $this;
    }
    
}
