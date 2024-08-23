<?php 

class MageShop_PagarMe_Block_Payment_Method extends Mage_Payment_Block_Info{

    private $order;
    private $transaction;
    private $charge;
    
    public function _construct()
    {
        parent::_construct();
        $this->initOrder();
    }
    public function initOrder()
    {
        $this->order();
        $this->setTransaction($this->transaction());
        $this->setCharge($this->charge());
        return $this;
    }
    public function order()
    {
        if(!$this->order){
            $this->order = Mage::registry('current_order');
        }
        return $this->order;
    }

    public function payment()
    {
        return $this->order()->getPayment();
    }
    
    public function additionalInformation($key = null)
    {
        return $this->payment()->getAdditionalInformation($key);
    }
    public function transaction()
    {
        return $this->arrayInfo($this->additionalInformation("transaction"));
    }

    public function arrayInfo($json)
    {
        return json_decode($json, true);
    }
    public function charge()
    {
        foreach ($this->getTransaction("charges") as $transaction_charge) {
            return Mage::getModel("mageshop_pagarme/orders_charge")->setCharge($transaction_charge);
        }
    }

    /**
     * Get the value of transaction
     */
    public function getTransaction($key = null)
    {
        return $key !== null ? (isset($this->transaction[$key]) ? $this->transaction[$key] : null) : $this->transaction;
    }

    /**
     * Set the value of transaction
     */
    public function setTransaction($transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get the value of charge
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * Set the value of charge
     */
    public function setCharge($charge): self
    {
        $this->charge = $charge;

        return $this;
    }
    public function success()
    {
        return (bool) $this->getCharge()->getLastTransaction("success");
    }
 
    public function getImg($name)
    {
        return $this->getSkinUrlPagarMe($this->__("mageshop/pagarme/images/%s", $name));
    }
    
    public function getSvg($name)
    {
        return $this->getSkinUrlPagarMe($this->__("mageshop/pagarme/images/%s.svg", $name));
    }

    public function getSkinUrlPagarMe($imagePath)
    {
        return Mage::getDesign()->getSkinUrl($imagePath);
    }
}