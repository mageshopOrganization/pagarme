<?php

class MageShop_PagarMe_Model_Orders_Payment extends MageShop_PagarMe_Model_Orders_Transaction
{

    private $data;
    private $response;
    public function process(Varien_Object $payment, $amount)
    {
        $payment->setAmount($amount);
        $http = $this->rest();
        $this->setHeader();
        $http->url($this->_getHelper()->getUrlApi('orders'))
            ->_method('POST')
            ->_body($this->getDataRequest())
            ->exec();

        $this->response = $this->rest()->getResponse();

        if (!$this->rest()->success()) {
            $error = $this->rest()->error();
            $error = isset($error["message"]) ? $error["message"] : "Error";
            Mage::throwException($error);
        }

        json_encode($this->response, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

        $this->getInfoInstance()->setAdditionalInformation("transaction", $this->response);

        // adiciona o response na classe
        $this->setTransaction($this->response);

        // se foi definido para criar pedidos com falhas 
        $this->createOrder();

        $this->transactions($payment);
        $this->_registerTransaction($payment->getOrder());
        // Salvar o ID do pedido Pagarme
        $payment->setPagarmeOrderId($this->getTransaction('id'));

        // Salvar o payload do pedido Pagarme
        $payment->setPagarmeOrderPayload($this->response);

        // Salvar as alterações no pagamento
        $payment->save();

        return $payment;
    }

    public function createOrder()
    {
        if ((bool) $this->_getHelper()->getConfigData('create_order')) {
            return $this;
        }
        if ($this->getStatus($this->getTransactionStatus()) === MageShop_PagarMe_Model_Orders_Transaction::CANCELED) {
            Mage::throwException("Sua compra não pôde ser aprovada. Por favor, verifique os dados e tente novamente.");
        }
        return $this;
    }

    /**
     * Get the value of data
     */
    public function getDataRequest()
    {
        return $this->data;
    }
    public function setDataRequest($data)
    {
        $this->data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $this;
    }
}
