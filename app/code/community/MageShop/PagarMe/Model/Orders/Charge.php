<?php

class MageShop_PagarMe_Model_Orders_Charge{

    private $last_transaction;

    private $lastIdTransaction;

    private $amount = 0;
    private $paid_amount = 0;
    private $canceled_amount = 0;
    private $void_amount = 0;
    
    private $id;
    private $status;
    private $charge;
    private $customer;
    private $payment_method;
    private $order;
    
    /**
     * Get the value of charge
     */
    public function getCharge($key = null)
    {
        return $this->getVar($this->charge, $key);
    }

    /**
     * Set the value of charge
     */
    public function setCharge($charge): self
    {
        $this->charge = $charge;
        $this->setId($this->getCharge('id'));
        $this->setStatus($this->getCharge('status'));
        $this->setOrder($this->getCharge('order'));
        $this->setAmount($this->getCharge('amount'));
        $this->setPaidAmount($this->getCharge('paid_amount'));
        $this->setCanceledAmount($this->getCharge('canceled_amount'));
        $this->setCustomer($this->getCharge('customer'));
        $this->setLastTransaction($this->getCharge('last_transaction'));
        $this->setPaymentMethod($this->getCharge('payment_method'));
        $this->setVoidAmount( $this->getAmount() - $this->getPaidAmount());
        return $this;
    }

    /**
     * Get the value of status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     */
    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of paid_amount
     * @return integer
     */
    public function getPaidAmount()
    {
        return $this->paid_amount;
    }

    /**
     * Set the value of paid_amount
     */
    public function setPaidAmount($paid_amount): self
    {
        $this->paid_amount = $paid_amount;

        return $this;
    }

    /**
     * Get the value of amount
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the value of amount
     */
    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of lastIdTransaction
     */
    public function getLastIdTransaction()
    {
        return $this->lastIdTransaction;
    }

    /**
     * Set the value of lastIdTransaction
     */
    public function setLastIdTransaction($lastIdTransaction): self
    {
        $this->lastIdTransaction = $lastIdTransaction;

        return $this;
    }

    /**
     * Get the value of last_transaction
     */
    public function getLastTransaction($key = null)
    {
        return $this->getVar($this->last_transaction, $key);
    }

    /**
     * Set the value of last_transaction
     */
    public function setLastTransaction($last_transaction): self
    {
        $this->last_transaction = $last_transaction;
        if (isset($this->last_transaction["id"])) {
            $this->setLastIdTransaction($this->last_transaction["id"]);
        }
        return $this;
    }

    /**
     * Get the value of customer
     */
    public function getCustomer($key = null)
    {
        return $this->getVar($this->customer, $key);
    }

    /**
     * Set the value of customer
     */
    public function setCustomer($customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getVar($var, $key)
    {
        return $key !== null ? (isset($var[$key]) ? $var[$key] : null) : $var;
    }

    /**
     * Get the value of canceled_amount
     * @return integer
     */
    public function getCanceledAmount()
    {
        return $this->canceled_amount;
    }

    /**
     * Set the value of canceled_amount
     */
    public function setCanceledAmount($canceled_amount): self
    {
        $this->canceled_amount = $canceled_amount;

        return $this;
    }

    /**
     * Get the value of order
     */
    public function getOrder($key = null)
    {
        return $this->getVar($this->order, $key);
    }

    /**
     * Set the value of order
     */
    public function setOrder($order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get the value of payment_method
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * Set the value of payment_method
     */
    public function setPaymentMethod($payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    /**
     * Get the value of void_amount
     */
    public function getVoidAmount()
    {
        return $this->void_amount;
    }

    /**
     * Set the value of void_amount
     */
    public function setVoidAmount($void_amount): self
    {
        $this->void_amount = $void_amount;

        return $this;
    }
}