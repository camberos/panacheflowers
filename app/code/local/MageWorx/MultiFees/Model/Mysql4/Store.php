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

class MageWorx_MultiFees_Model_Mysql4_Store extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct() {
        $this->_init('multifees/store', 'fee_store_id');
    }
    
    public function loadByFeeAndStore($object, $feeId, $storeId) {
	$object->unsetData();
        $read = $this->_getReadAdapter();
        if ($read) {  
            $select = $read->select()
                ->from($this->getMainTable())
                ->where('fee_id = ?', $feeId)
                ->where('store_id = ?', $storeId)
                ->limit(1); 
            $data = $read->fetchRow($select);
            if ($data) $object->addData($data);
        }
    }
    
    public function getStories($feeId) {
        $read = $this->_getReadAdapter();
        $result = $read->fetchAssoc(
            $read->select()
                ->from($this->getMainTable())
                ->where('fee_id = ?', $feeId)
        );
        return $result;
    }   
    
}