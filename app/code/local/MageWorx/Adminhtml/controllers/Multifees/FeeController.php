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

class MageWorx_Adminhtml_Multifees_FeeController extends  Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed() {
        Mage::getSingleton('admin/session')->isAllowed('media');
        Mage::getSingleton('admin/session')->isAllowed('sales/multifees');
        return $this;
    }

    public function indexAction() {
        $title = $this->__('Manage Fees');
        $this->_title($title);
        $this->loadLayout()
            ->_setActiveMenu('sales/multifees')
            ->_addBreadcrumb($title, $title)
            ->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $id = (int) $this->getRequest()->getParam('id');        
        $model = Mage::getModel('multifees/fee')->load($id);
        if ($id==0 || $model->getId()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data)->setId($id);
            }
            if ($id==0) {
                $model->setStatus(1)
                    ->setIsOnetime(1)
                    ->setCustomerMessageTitle('Gift Message:')
                    ->setDateFieldTitle('Date:')
                    ->setAppliedTotals('subtotal');
            }    
            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');            
            Mage::register('multifees_fee', $model);
            
            if ($model->getId()) {
                $title = $this->__("Edit Fee '%s'", $model->getTitle());
            } else {
                $title = $this->__('New Fee');
            }
            
            
            $this->_title($title);
            $this->loadLayout()
                ->_setActiveMenu('sales/multifees')
                ->_addBreadcrumb($title, $title);
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Fee does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newConditionHtmlAction() {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        
        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('salesrule/rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
    
    private function _getStores($loadDefault=false) {
        return Mage::getModel('core/store')
            ->getResourceCollection()
            ->setLoadDefault($loadDefault)
            ->load();
    }
    
    public function saveAction() {
        $data = $this->getRequest()->getPost();
        $feeId = (int) $this->getRequest()->getParam('fee_id');
        $error = false;

        if ($data) {
            $data = Mage::helper('multifees')->getFilter($data, false);            
            $modelFee = Mage::getSingleton('multifees/fee');
            $modelOption = Mage::getSingleton('multifees/option');
            $modelStore = Mage::getSingleton('multifees/store');
            $modelLngFee = Mage::getSingleton('multifees/language_fee');
            $modelLngOption = Mage::getSingleton('multifees/language_option');

            try {                                
                $data['sales_methods'] = '';
                if (isset($data['payments'])) $data['sales_methods'] = implode(',', $data['payments']);
                if (isset($data['shippings'])) $data['sales_methods'] = implode(',', $data['shippings']);
                if (isset($data['applied_totals'])) $data['applied_totals'] = implode(',', $data['applied_totals']);
                if (isset($data['rule']['conditions'])) $data['conditions'] = $data['rule']['conditions'];
                                
                $modelFee->setData($data);
                $modelFee->loadPost($data); // for save conditions
                
                if ($feeId) $modelFee->setId($feeId);                
                $modelFee->save();
                $feeId = $modelFee->getId();

                if ($feeId) {
                    //* save stores
                    if ($data['stores']) {
                        $stories = $modelStore->getResource()->getStories($feeId);
                        foreach ($data['stores'] as $storeId) {
                            $modelStore->loadByFeeAndStore($feeId, $storeId);
                            $feeStoreId = $modelStore->getFeeStoreId();
                            if ($feeStoreId) unset($stories[$feeStoreId]);
                            $modelStore->setFeeId($feeId)->setStoreId($storeId)->save();
                        }
                        foreach ($stories as $feeStoreId=>$value) {
                            $modelStore->load($feeStoreId)->delete();                            
                        }
                    }
                                        
                    // save default store: name, description, customer_message_title, date_field_title
                    if (isset($data['title']) && isset($data['description']) && isset($data['customer_message_title']) && isset($data['date_field_title'])) {
                        $modelLngFee->loadByFeeAndStore($feeId, 0);
                        $feeLangId = $modelLngFee->getFeeLangId();
                        $modelLngFee->setData(array(
                                'fee_id' => $feeId,
                                'store_id' => 0,
                                'title' => trim($data['title']),
                                'description' => trim($data['description']),
                                'customer_message_title' => trim($data['customer_message_title']),
                                'date_field_title' => trim($data['date_field_title'])
                            ));
                        if ($feeLangId) $modelLngFee->setFeeLangId($feeLangId);                        
                        $modelLngFee->save();
                    }
                    
                    // save no default store (labels): names, descriptions, customer_message_titles, date_field_titles                 
                    if ($data['store_fee_names'] || $data['store_fee_descriptions'] || $data['store_fee_customer_message_titles'] || $data['store_fee_date_field_titles']) {
                        foreach ($this->_getStores() as $store) {
                            $title = $data['store_fee_names'][$store->getStoreId()];
                            $description = $data['store_fee_descriptions'][$store->getStoreId()];
                            $customerMessageTitle = $data['store_fee_customer_message_titles'][$store->getStoreId()];
                            $dateFieldTitle = $data['store_fee_date_field_titles'][$store->getStoreId()];
                            
                            $modelLngFee->loadByFeeAndStore($feeId, $store->getStoreId());
                            $feeLangId = $modelLngFee->getFeeLangId();
                            if (!empty($title) || !empty($description) || !empty($customerMessageTitle) || !empty($dateFieldTitle)) {    
                                $modelLngFee->setData(array(
                                    'fee_id' => $feeId,
                                    'store_id' => $store->getStoreId(),
                                    'title' => $title,
                                    'description' => $description,
                                    'customer_message_title' => $customerMessageTitle,
                                    'date_field_title' => $dateFieldTitle
                                ));
                                if ($feeLangId) $modelLngFee->setFeeLangId($feeLangId);
                                $modelLngFee->save();
                            } else {
                                if ($feeLangId) $modelLngFee->delete();
                            }
                        }
                    }
                    
                
                    if (!isset($data['options'])) {
                        Mage::getSingleton('adminhtml/session')->addError($this->__('There are no options'));
                        throw new Exception();
                    }
                    
                    $options = $data['options'];
                    $savedOptionsIds = array_keys($modelOption->getResource()->getOptions($feeId));
                    if ($savedOptionsIds) {
                        foreach ($savedOptionsIds as $opId) {
                            if (!isset($options['title'][$opId]) || (isset($options['delete'][$opId]) && $options['delete'][$opId])) {
                                $modelOption->setId($opId)->delete();
                            }
                        }
                    }
                    
                    if ($options['title']) {
                        foreach ($options['title'] as $key => $value) {
                            if (isset($options['delete'][$key]) && $options['delete'][$key]) continue;
                            
                            $default = 0;
                            if (isset($options['default'])) {
                                foreach ($options['default'] as $def) {
                                    if ($def && $def==$key) {
                                        $default = 1;
                                    }
                                }
                            }
                            // if hidden or notice
                            if ($modelFee->getInputType()==4 || $modelFee->getInputType()==5) $default = 1;
                            
                            
                            $order = null;
                            if (isset($options['order'][$key])) {
                                $order = $options['order'][$key];
                            }
                            
                            $modelOption->setData(array(
                                'fee_id' => $feeId,
                                'price' => $options['price'][$key],
                                'price_type' => $options['price_type'][$key],
                                'is_default' => $default,
                                'position' => $order)
                            );
                            if ($savedOptionsIds && in_array($key, $savedOptionsIds)) {
                                $modelOption->setId($key);
                            }
                            $modelOption->save();
                            $optionId = $modelOption->getId();

                            
                            // Remove Img File
                            if (isset($options['image_delete'][$optionId]) && $options['image_delete'][$optionId]==$optionId) {
                                Mage::helper('multifees')->removeOptionFile($optionId);
                            }
                            // Upload Img File
                            $this->_uploadImage('file_' . $key, $optionId);
                                                        
                            foreach ($this->_getStores(true) as $store) {
                                $modelLngOption->loadByOptionAndStore($optionId, $store->getStoreId());
                                $feeOptionLangId = $modelLngOption->getFeeOptionLangId();
                                if (!empty($value[$store->getStoreId()])) {
                                    $modelLngOption->setData(array(
                                        'fee_option_id' => $optionId,
                                        'store_id' => $store->getStoreId(),
                                        'title' => trim($value[$store->getStoreId()])
                                    ));
                                    if ($feeOptionLangId) $modelLngOption->setFeeOptionLangId($feeOptionLangId);
                                    $modelLngOption->save();
                                } else {
                                    if ($feeOptionLangId) $modelLngOption->delete();
                                }
                            }
                        }
                    }
                    
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Cannot add a record Fee. Please, try again.'));
                    $error = true;
                }
                if ($error) {
                    throw new Exception();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Fee was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $feeId, 'tab'=>$this->getRequest()->getParam('tab')));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                if ($e->getMessage()) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $feeId));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find Fee to save'));
        $this->_redirect('*/*/');
    }

    private function _uploadImage($keyFile, $optionId) {
        if (isset($_FILES[$keyFile]['name']) && $_FILES[$keyFile]['name'] != '') {
            try {
                Mage::helper('multifees')->removeOptionFile($optionId);
                $uploader = new Varien_File_Uploader($keyFile);
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $uploader->save(Mage::helper('multifees')->getOptionImgPath($optionId), $_FILES[$keyFile]['name']);
            } catch (Exception $e) {
                if ($e->getMessage()) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
    }   
    
    public function massDeleteAction() {
        $feeIds = $this->getRequest()->getParam('fee');
        if (!is_array($feeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Fee(s)'));
        } else {
            try {
                foreach ($feeIds as $feeId) {
                    Mage::getModel('multifees/fee')->load($feeId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d record(s) were successfully deleted', count($feeIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massStatusAction() {
        $feeIds = $this->getRequest()->getParam('fee');

        if (!is_array($feeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Fee(s)'));
        } else {
            try {
                foreach ($feeIds as $feeId) {
                    Mage::getSingleton('multifees/fee')
                            ->load($feeId)
                            ->setStatus((int) $this->getRequest()->getParam('status'))
                            ->save();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($feeIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
}