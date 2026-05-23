<?php

class MageShop_PagarMe_Model_Orders_Payment_CreditCard
{
    /**
     * Dados sobre o pagamento com cartão de crédito
     *
     * @var string
     */
    const PAYMENT_METHOD = 'credit_card';
    protected $helper;
    /**
     * Indica se a transação deve ser capturada 
     * "auth_and_capture", autorizada "auth_only", ou pré autorizada "pre_auth".
     */
    const OPERATION_AUTH_CAPTURE = 'auth_and_capture';
    /**
     * Indica se a transação deve ser capturada 
     * "auth_and_capture", autorizada "auth_only", ou pré autorizada "pre_auth".
     */
    const OPERATION_AUTH = 'auth_only';
    
    public function getAntifraud()
    {
        return (bool) $this->getHelper()->getConfigData('antifraud');
    }

    public function getOperation()
    {
        switch ($this->getHelper()->getConfigData('transaction_type')) {
            case MageShop_PagarMe_Model_Method_Cc::ACTION_AUTHORIZE:
                return self::OPERATION_AUTH;
            case MageShop_PagarMe_Model_Method_Cc::ACTION_AUTHORIZE_CAPTURE:
            default:
                return self::OPERATION_AUTH_CAPTURE;
        }
    }
    public function getCardType($cardCredit)
    {
        return $this->getHelper()->getCardType($cardCredit);
    }
    public function expdate($expiryMonth, $expiryYear)
    {
        $currentDate = date('m/y'); // Obtém a data atual no formato "mm/aaaa"
        // Separa o mês e o ano da data atual
        $currentMonth = substr($currentDate, 0, 2);
        $currentYear = substr($currentDate, 3);

        if (($expiryMonth < $currentMonth && $expiryYear == $currentYear) || ($expiryMonth == $currentMonth && $expiryYear < $currentYear)) {
            // O mês do cartão de crédito é menor que o mês atual ou o ano do cartão de crédito é menor que o ano atual
            // Retorna um erro indicando que a data de validade do cartão de crédito é inválida
            Mage::throwException("A data de validade do cartão de crédito é inválida.");
        }
    }
    public function getHelper()
    {
        if (!$this->helper) {
            $this->helper = Mage::helper("mageshop_pagarme/creditCard");
        }
        return $this->helper;
    }
}
