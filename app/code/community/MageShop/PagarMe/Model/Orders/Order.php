<?php

class MageShop_PagarMe_Model_Orders_Order
{

    private $_helper;
    private $_data;
    private $_payment;
    private $_info;
    private $payment_method;
    private $card_credit_model;
    public function getReservedOrderId()
    {
        return $this->_payment->getOrder()->getQuote()->getReservedOrderId();
    }

    public function transaction()
    {
        $this->_data['code'] = $this->getReservedOrderId();
        return $this;
    }

    public function customer()
    {
        $quote = $this->getCheckout()->getQuote();
        $number_taxvat = $quote->getCustomerTaxvat();

        if ($this->getHelper()->cnpj_cpf($number_taxvat) == false) {
            Mage::throwException("CPF/CNPJ invalido.");
        }

        $doc = preg_replace("/[^0-9]/", "", $number_taxvat);
        $this->_data['customer'] = array(
            "name" => $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname(),
            "birth_date" => $quote->getCustomerDob() ? Mage::helper('core')->formatDate($quote->getCustomerDob(), 'medium', false) : '',
            "email" => $quote->getCustomerEmail()
        );

        if (strlen($doc) === 14) {
            $this->_data['customer']['document'] = $doc;
            $this->_data['customer']['type'] = "company";
            $this->_data['customer']['document_type'] = "CNPJ";
        } else if (strlen($doc) == 11) {
            $this->_data['customer']['document'] = $doc;
            $this->_data['customer']['type'] = "individual";
            $this->_data['customer']['document_type'] = "CPF";
        }

        return $this;
    }

    public function contacts()
    {
        $billingAddress = $this->_payment->getOrder()->getQuote()->getBillingAddress();
        $number_contact = str_replace([" ", "(", ")", "-"], "", $billingAddress->getTelephone());
        $phone = $this->getHelper()->extractDDDAndNumber($number_contact);
        if ($phone == null) {
            return false;
        }
        $this->_data['customer']['phones'] = array(
            "home_phone" => array(
                "country_code" => 55,
                "area_code" => $phone["ddd"],
                "number" => $phone["number"],
            ),
            "mobile_phone" => array(
                "country_code" => 55,
                "area_code" => $phone["ddd"],
                "number" => $phone["number"],
            )
        );
        return $this;
    }

    public function items()
    {
        $quote = $this->getCheckout()->getQuote();
        $items = $quote->getAllItems();
        $i = 0;
        foreach ($items as $item) {
            if ($item->getPrice() <= 0) {
                continue;
            }
            $this->_data['items'][$i]['description'] = $item->getName();
            $this->_data['items'][$i]['quantity'] = $item->getQty();
            $this->_data['items'][$i]['amount'] = $this->getHelper()->amount($item->getPrice());
            $this->_data['items'][$i]['code'] = $item->getId();
            $i++;
        }
        return $this;
    }

    public function address()
    {
        $quote = $this->_payment->getOrder()->getQuote(); // obtém o objeto quote do pedido
        $address = $quote->getBillingAddress(); // obtém o objeto endereço de cobrança
        $street = $address->getStreet();
        $zip_code = preg_replace("/[^0-9]/", "", $address->getPostcode());
        $line_1 = $this->getHelper()->__("%s, %s, %s", $street[0], $street[1], $street[3]);
        $this->_data['customer']['address'] = array(
            "country" => $address->getCountryId(),
            "zip_code" => $zip_code,
            "line_1" => $line_1,
            "line_2" => $street[2],
            "city" => $address->getCity(),
            "state" => $this->getHelper()->getRegionCode($address->getRegion()),
        );
        return $this;
    }


    public function billingAddress()
    {
        $quote = $this->_payment->getOrder()->getQuote(); // obtém o objeto quote do pedido
        $billingAddress = $quote->getBillingAddress(); // obtém o objeto endereço de cobrança
        $street = $billingAddress->getStreet();
        $line_1 = $this->getHelper()->__("%s, %s, %s", $street[0], $street[1], $street[3]);
        $zip_code = preg_replace("/[^0-9]/", "", $billingAddress->getPostcode());
        return array(
            "country" => $billingAddress->getCountryId(),
            "zip_code" => $zip_code,
            "line_1" => $line_1,
            "line_2" => $street[2],
            "city" => $billingAddress->getCity(),
            "state" => $this->getHelper()->getRegionCode($billingAddress->getRegion()),
        );
    }

    public function shippingAddress()
    {
        $quote = $this->_payment->getOrder()->getQuote(); // obtém o objeto quote do pedido
        $shippingAddress = $quote->getShippingAddress(); // obtém o objeto endereço de cobrança
        $street = $shippingAddress->getStreet();

        $shippingTitle = $this->_payment->getOrder()->getData('shipping_description');

        if ($shippingTitle == NULL || empty($shippingTitle)) {
            $shippingTitle = "Produto Virtual";
        }
        $shippingPrice = $this->_payment->getShippingAmount();
        $line_1 = $this->getHelper()->__("%s, %s, %s", $street[0], $street[1], $street[3]);
        $zip_code = preg_replace("/[^0-9]/", "", $shippingAddress->getPostcode());
        $this->_data['shipping']['address'] = array(
            "country" => $shippingAddress->getCountryId(),
            "zip_code" => $zip_code,
            "city" => $shippingAddress->getCity(),
            "state" => $this->getHelper()->getRegionCode($shippingAddress->getRegion()),
            "line_1" => $line_1,
        );
        $this->_data['shipping']["description"] = $shippingTitle;
        $this->_data['shipping']["amount"] = $this->getHelper()->amount($shippingPrice);
        return $this;
    }

    public function closed($status = false)
    {
        $this->_data['closed'] = $status;
        return $this;
    }

    public function getIp()
    {
        $this->_data['ip'] = Mage::helper('core/http')->getRemoteAddr();
        return $this;
    }

    public function getSessionId()
    {
        $this->_data['session_id'] = Mage::getSingleton('core/session')->getSessionId();
        return $this;
    }

    public function getAntifraudEnabled()
    {
        $this->_data['antifraud_enabled'] = $this->getCcModel()->getAntifraud(); // caso exista um sistema de anti fraude passe true;
        return $this;
    }
    
    public function paymentBankslip()
    {
        // Obtenha os dias de vencimento da configuração
        $expirationDays = $this->getHelper()->getConfigData("expiration_days", "mageshop_pagarme_bankslip");
        // Verifique se os dias de vencimento são um número válido
        if (is_numeric($expirationDays)) {
            $expirationDays = (int)$expirationDays;
        } else {
            $expirationDays = 0; // Defina um padrão se não for um número válido
        }
        $currentDate = new DateTime();
        // Adicione os dias de vencimento à data atual
        $currentDate->modify("+{$expirationDays} days");
        // Formate a data no formato ISO 8601
        $dueAt = $currentDate->format('Y-m-d\TH:i:s');

        $this->_data["payments"][] = array(
            "boleto" => array(
                "bank" => $this->getHelper()->getConfigData("bank", "mageshop_pagarme_bankslip"),
                "instructions" => $this->getHelper()->getConfigData("instructions", "mageshop_pagarme_bankslip"),
                "due_at" => $dueAt,
                "nosso_numero" => $this->getReservedOrderId(),
                "type" => $this->getHelper()->getConfigData("type", "mageshop_pagarme_bankslip"),
                "document_number" => $this->getReservedOrderId(),
            ),
            "payment_method" => $this->payment_method,
            "amount"         => $this->getHelper()->amount($this->getTotal()),
        );
        return $this;
    }

    public function paymentPix()
    {
        $this->_data["payments"][] = array(
            "Pix" => array(
                "expires_in" => $this->getHelper()->getConfigData("expiration_qrcode", "mageshop_pagarme_pix")
            ),
            "payment_method" => $this->payment_method,
            "amount"         => $this->getHelper()->amount($this->getTotal()),
        );
        return $this;
    }

    public function paymentCc()
    {
        $card_credit = $this->getCheckout()->getQuote()->getMageShopPagarmeCc();
        $installments = $card_credit['mageshop_pagarme_cc_installments'];
        $card_token = $card_credit['mageshop_pagarme_cc_token'];

        if($installments == null || $installments == '' || (int) $installments <= 0) {
			Mage::throwException('Selecione uma parcela!');
		}
        
        if($card_token == null || $card_token == '') {
			Mage::throwException('Token não pode ser vazio. Por favor, atualize a página e tente novamente!');
		}

        $this->_data["payments"][] = array(
            "credit_card" => array(
                "card" => array(
                    "billing_address" => $this->billingAddress(),
                ),
                "card_token" => $card_credit['mageshop_pagarme_cc_token'],
                "operation_type" => $this->getCcModel()->getOperation(),
                "installments" => $installments,
                "statement_descriptor" => $this->getCcModel()->getHelper()->getConfigData('statement_descriptor'),
                "initiated_type" => "partial_shipment",
                "recurrence_model" => "standing_order",
            ),
            "payment_method" => $this->payment_method,
            "amount"         => $this->getHelper()->amount($this->getTotal()),
           
        );
        return $this;
    }

    public function metaData()
    {
        $this->_data["metadata"] = array(
            "platform_integration" => $this->getHelper()->getMetaPlatformIntegration()
        );
        return $this;
    }

    public function getTotal()
    {
        return $this->getCheckout()->getQuote()->getGrandTotal();
    }
    private function getDocumentOrder()
    {
        $quote = $this->getCheckout()->getQuote();
        $attrSelectedCPF = Mage::helper('mageshop_pagarme/validation')->getAttrCpfArray();
        if ($attrSelectedCPF !== null && isset($attrSelectedCPF[0], $attrSelectedCPF[1])) {
            switch ($attrSelectedCPF[0]) {
                case 'customer':
                    $number_taxvat = $quote->getCustomer()->getData($attrSelectedCPF[1]);
                    break;
                case 'billing':
                    $number_taxvat = $quote->getBillingAddress()->getData($attrSelectedCPF[1]);
                    break;
            }
        } else {
            $number_taxvat = $quote->getCustomerTaxvat();
        }
        if ($this->getHelper()->cnpj_cpf($number_taxvat) == false) {
            Mage::throwException("CPF/CNPJ invalido.");
        }
        $doc = preg_replace("/[^0-9]/", "", $number_taxvat);
        return $doc;
    }
    /**
     * Cliente que efetuou a compra
     *
     * @return object|Mage
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return MageShop_PagarMe_Helper_Data|null
     */
    public function getHelper($data = 'mageshop_pagarme')
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper($data);
        }
        return $this->_helper;
    }

     /**
     * @return MageShop_PagarMe_Model_Orders_Payment_CreditCard|null
     */
    public function getCcModel()
    {
        if (!$this->card_credit_model) {
            $this->card_credit_model = Mage::getModel('mageshop_pagarme/orders_payment_creditCard');
        }
        return $this->card_credit_model;
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
     * @param string $payment_method
     * @return self
     */
    public function setPaymentMethod($payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    /**
     * Get the value of _payment
     */
    public function getPayment()
    {
        return $this->_payment;
    }

    /**
     * Set the value of _payment
     * @param string $_payment
     * @return self
     */
    public function setPayment($_payment): self
    {
        $this->_payment = $_payment;

        return $this;
    }

    /**
     * Get the value of _info
     */
    public function getInfo()
    {
        return $this->_info;
    }

    /**
     * Set the value of _info
     */
    public function setInfo($_info): self
    {
        $this->_info = $_info;

        return $this;
    }

    /**
     * Get the value of _data
     */
    public function getData()
    {
        return $this->_data;
    }
}
