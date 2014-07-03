<?php
class SecurePay_SecureFrame_Model_Currencytype
{

    public function toOptionArray()
    {
        $options = array(
            "A" => "AUD",
            "M" => "Multicurrency"
        );
        return $options;
    }
}
?>