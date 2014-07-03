<?php
class SecurePay_SecureFrame_Model_Txntype
{

    public function toOptionArray()
    {
        $options = array(
            "0" => "Payment",
            "2" => "Payment with FraudGuard",
            "4" => "Payment with 3D Secure",
            "6" => "Payment with FraudGuard and 3D Secure"
        );
        return $options;
    }
}
?>