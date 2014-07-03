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

class MageWorx_MultiFees_Model_Mysql4_Option extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct() {
        $this->_init('multifees/option', 'fee_option_id');
    }

    public function getOptions($feeId) {
        $read = $this->_getReadAdapter();
        $result = $read->fetchAssoc(
            $read->select()
                ->from($this->getMainTable())
                ->where('fee_id = ?', $feeId)
        );
        return $result;
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {
        Mage::helper('multifees')->removeOptionFile($object->getId());
        return parent::_beforeDelete($object);
    }
    
}