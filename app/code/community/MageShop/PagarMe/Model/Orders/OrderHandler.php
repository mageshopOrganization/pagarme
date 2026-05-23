<?php

class MageShop_PagarMe_Model_Orders_OrderHandler extends MageShop_PagarMe_Model_Orders_Transaction
{

    

    /**
     * Acessar a cobrança
     *
     * @expectedException _init()
     * @param string $id
     * @return self
     */
    public function charge($id)
    {
        $http = $this->rest();
        $this->setHeader();
        $http->url($this->_getHelper()->getUrlApi('charges/' . $id))
            ->_method('GET')
            ->exec();
        
        if (!$http->success()) {
            $error = $http->error();
            $error = isset($error["message"]) ? $error["message"] : "Error";
            Mage::throwException($error);
        }

        $response = $http->getResponse();
        $res = json_decode($response, true);
        $this->setTransactionCharge($res);
        $this->destroyRest();

        return $this;
    }

    public function capture($id, $amount)
    {
        $http = $this->rest();
        $this->setHeader();
        $http->url($this->_getHelper()->getUrlApi('charges/' . $id . '/capture'))
            ->_method('POST')
            ->_body(json_encode(["amount" => $amount]))
            ->exec();
        if (!$http->success()) {
            $error = $http->error();
            $error = isset($error["message"]) ? $error["message"] : "Error";
            Mage::throwException($error);
        }
        $response = $http->getResponse();
        $res = json_decode($response, true);
        $this->setTransactionCharge($res);
        $this->destroyRest();
        return $this;
    }

    public function canceled($id, $amount)
    {
        $http = $this->rest();
        $this->setHeader();

        $http->url($this->_getHelper()->getUrlApi('charges/' . $id))
        ->_method('DELETE')
        ->_body(json_encode(["amount" => $amount]))
        ->exec();

        if (!$http->success()) {
            $error = $http->error();
            $error = isset($error["message"]) ? $error["message"] : "Error";
            Mage::throwException($error);
        }
        $response = $http->getResponse();
        $res = json_decode($response, true);
        $this->setTransactionCharge($res);
        $this->destroyRest();

    }

    public function order($id)
    {
        $http = $this->rest();
        $this->setHeader();
        $http->url($this->_getHelper()->getUrlApi('orders/' . $id))
            ->_method('GET')
            ->exec();

        if (!$http->success()) {
            $error = $http->error();
            $error = isset($error["message"]) ? $error["message"] : "Error";
            Mage::throwException($error);
        }

        $response = $http->getResponse();
        $this->setTransaction($response);
        $this->destroyRest();
        return $this;
    }

    public function process($order)
    {
        $payment = $order->getPayment();
        // adiciona o response na classe
        $this->_processStatus($payment);
        $this->historyTransactions($payment);
        $this->_updateAdditionalInformation($payment);
        $this->_updateCharges();
        return $this;
    }
}
