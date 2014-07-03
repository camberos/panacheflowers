<?php
class SecurePay_SecureFrame_Model_Cardtype
{
  public function toOptionArray()
  {
    $options = array(
        array('value'=>'VISA', 'label'=>'Visa'),
        array('value'=>'AMEX', 'label'=>'American Express'),
        array('value'=>'MASTERCARD', 'label'=>'MasterCard'),
        array('value'=>'DINERS', 'label'=>'Diners'),
        array('value'=>'JCB', 'label'=>'JCB')
    );
    return $options;
  }
}
?>