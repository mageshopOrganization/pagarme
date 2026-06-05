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
            $message = is_array($error) && isset($error["message"]) && $error["message"] !== ''
                ? $error["message"]
                : "Falha ao processar o pagamento. Por favor, verifique os dados e tente novamente.";
            Mage::throwException($message);
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
            $this->_logGatewayError();
            Mage::throwException("Sua compra não pôde ser aprovada. Por favor, verifique os dados e tente novamente.");
        }
        return $this;
    }

    private function _logGatewayError()
    {
        $transaction = $this->getTransaction();
        if (!is_array($transaction) || empty($transaction['charges'])) {
            return;
        }

        $customer = isset($transaction['customer']) ? $transaction['customer'] : [];
        $customerInfo = sprintf(
            'customer: %s | email: %s | document: %s',
            isset($customer['name'])     ? $customer['name']     : '',
            isset($customer['email'])    ? $customer['email']    : '',
            isset($customer['document']) ? $customer['document'] : ''
        );

        $orderInfo = sprintf(
            'order_id: %s | order_code: %s',
            isset($transaction['id'])   ? $transaction['id']   : '',
            isset($transaction['code']) ? $transaction['code'] : ''
        );

        foreach ($transaction['charges'] as $charge) {
            $gwResponse = isset($charge['last_transaction']['gateway_response'])
                ? $charge['last_transaction']['gateway_response']
                : null;
            if ($gwResponse) {
                Mage::log(
                    '[PagarMe] Gateway error | ' . $orderInfo . ' | ' . $customerInfo .
                    ' | charge: '  . (isset($charge['id'])             ? $charge['id']             : '') .
                    ' | method: '  . (isset($charge['payment_method']) ? $charge['payment_method'] : '') .
                    ' | gw_code: ' . (isset($gwResponse['code'])       ? $gwResponse['code']       : '') .
                    ' | errors: '  . json_encode(isset($gwResponse['errors']) ? $gwResponse['errors'] : []),
                    Zend_Log::ERR,
                    'pagarme_errors.log'
                );
            }
        }
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
