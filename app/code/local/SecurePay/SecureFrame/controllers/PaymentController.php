<?php

class SecurePay_SecureFrame_PaymentController extends Mage_Core_Controller_Front_Action {
	//Build secureframe request and display secureframe to customer.
	public function redirectAction() {
		$model = Mage::getSingleton('secureframe/standard');
		if($model->getConfigData('test_mode') == true){
			$actionUrl = "https://payment.securepay.com.au/test/v2/invoice";
		}else{
			$actionUrl = "https://payment.securepay.com.au/live/v2/invoice";
		}
		$sfRequest = $this->buildSecureFrameRequest();

		$this->loadLayout();
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','secureframe',array('template' => 'securepay/secureframe/redirect.phtml'));
        $block->setSfRequest($sfRequest);
        $block->setActionUrl($actionUrl);
		$this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
	}
	
	// Recieve result url request or callback request.
	public function responseAction() {
		if($this->getRequest()->isPost()) {
			$orderId = $_POST["refid"];
			$amount = $_POST["amount"];
			$amount = (strrpos($amount, ".") === false) ? $amount : ($amount*100); // gotcha: when performing a 3D txn, if declined, the amount seems to come back decimal formatted; this will undoubtedly break fingerprint matching!

			$txnpw = Mage::getSingleton('secureframe/standard')->getConfigData('transaction_password');

			$localfingerprint = sha1($_POST["merchant"] . '|' . $txnpw  . '|' . $orderId . '|' . $amount . '|' . $_POST["timestamp"] . '|' . $_POST["summarycode"]);
			if($localfingerprint === $_POST["fingerprint"]) {
				$validated = true;
				if($_POST["summarycode"] == "1") {
					$approved = true;
				}
			}
			
			if($validated) {
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($orderId);
				$payment = $order->getPayment();
				if($_POST['afrescode']){
					$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'SecurePay Fraudguard Result - ' . $_POST['afrestext'] . ' - ' . $_POST['afrescode']);
				}
				if($approved){
					// Payment was successful, so update the order's state, send order email and move to the success page
					if(!$payment->getLastTransId() == $orderId . '_' . $_POST["txnid"]){
						$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.', true);
						$expiry = explode("/", $_POST["expirydate"]);
						$payment->setTransactionId($orderId . '_' . $_POST["txnid"])
					            ->setPreparedMessage('SecurePay SecureFrame')
					            ->setIsTransactionClosed(0)
					            ->registerCaptureNotification($_POST["amount"] / 100);
						$order->sendNewOrderEmail();
						$order->setEmailSent(true);
						$order->save();
					}

					Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));
				}else{
					//Dont cancel order until they come back to magento, this lets them attempt payment again from within secureframe.
					if($_POST["callback"] == false){
						$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Payment was declined. Reason: ' . $_POST["restext"] . '(' . $_POST["rescode"] . ')')->save();
					}
					Mage::getSingleton('checkout/session')->setErrorMessage("Your transaction was declined.");
					Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
				}
			}
			else {
				// There is a problem in the response we got
				Mage::getSingleton('checkout/session')->setErrorMessage("Transaction result could not be read.");
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
			}
		}
		else
			Mage_Core_Controller_Varien_Action::_redirect('');
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if($order->getId()) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Customer canceled during payment.')->save();
				Mage_Core_Controller_Varien_Action::_redirect('checkout/cart');
			}
        }
	}

	public function buildSecureFrameRequest(){
		$order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
		$orderId = $order->getRealOrderId();
		$model = Mage::getSingleton('secureframe/standard');

		$merchant_id = $model->getConfigData('merchant_id');
		$amount = $order->getTotalDue() * 100;
		$txn_type = $model->getConfigData('transaction_type');
		$txnpassword = $model->getConfigData('transaction_password');
		$time = gmdate("YmdHis");
		$fingerprint = sha1($merchant_id . '|' . $txnpassword . '|' . $txn_type . '|' . $orderId . '|' . $amount . '|' . $time);
		$card_types = str_replace(',', '|', $model->getConfigData('accepted_card_types'));
		$currency_accepted = $model->getConfigData('currency_accepted');
		$shipping = $order->getShippingDescription();
		$billing_meta = "none";
		$delivery_meta = "none";
		
		$sfRequest = array(
			"merchant_id"       => $merchant_id,
			"fp_timestamp"      => $time,
			"fingerprint"       => $fingerprint,
			"bill_name"         => "transact",
			"txn_type"          => $txn_type,
			"primary_ref"       => $orderId,
			"amount"            => $amount,
		  "currency"          => ($currency_accepted === 'M') ? $order->getBaseCurrency()->getCurrencyCode() : 'AUD',
			"template"          => $model->getConfigData('template'),
			"confirmation"      => "no",
			"return_url"        => Mage::getBaseUrl() . "secureframe/payment/response?",
			"callback_url"      => Mage::getBaseUrl() . "secureframe/payment/response?callback=true",
			"return_url_target" => "parent",
			"return_url_text"   => "Continue",
			"cancel_url"        => Mage::getBaseUrl() . "secureframe/payment/cancel",
		  "card_types"        => $card_types,
			"page_style_url"    => $model->getConfigData('stylesheet_url'),
			"meta"				=> $this->getMetaData($order)
		);
		return $sfRequest;
	}
	
	public function getMetaData($order) { 
		$shipping_meta = $order->getShippingDescription();
        $billing_meta = "none";
        $delivery_meta = "none";
		
        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
        	$billing_meta = $billing->getFirstname() . " " . $billing->getLastname() . "," . $billing->getCompany() . "," .
        			$billing->getStreet(1) . " " . $billing->getCity() . "," .
        			$billing->getRegion() . " " .	$billing->getPostcode() . " " . $billing->getCountry();
        }
        
        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {

        
        	$delivery_meta = $shipping->getFirstname() . " " . $shipping->getLastname() . "," . $shipping->getCompany() . "," .
        			$shipping->getStreet(1) . " " . $shipping->getCity() . "," .
        			$shipping->getRegion() . " " .	$shipping->getPostcode() . " " . $shipping->getCountry();
        }
                
        return "cart_post_method_eq_$shipping_meta|cart_billing_address_eq_$billing_meta|cart_delivery_address_eq_$delivery_meta";
		
	}
}
