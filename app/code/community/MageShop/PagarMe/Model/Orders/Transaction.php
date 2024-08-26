<?php

class MageShop_PagarMe_Model_Orders_Transaction
{

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH = 'AUTH';
    const REQUEST_TYPE_ORDER = 'ORDER';
    const REQUEST_TYPE_PRE_AUTH = 'PRE_AUTH';
    const REQUEST_TYPE_CAPTURE = 'CAPTURE';
    const REQUEST_TYPE_VOID = 'VOID';

    const ACTION_TRANSACTION_ORDER = 'order';
    const ACTION_TRANSACTION_AUTH = 'authorization';
    const ACTION_TRANSACTION_CAPTURE = 'capture';
    /**
     * O uso do VOID (cancelamento) em transações de pagamento é 
     * apropriado quando você deseja anular uma autorização que foi feita anteriormente, 
     * mas que ainda não foi capturada. Aqui estão algumas situações típicas em que você pode usar VOID
     */
    const ACTION_TRANSACTION_VOID = 'void';
    const ACTION_TRANSACTION_REFUND = 'refund';

    const PENDING = 1;
    const PAID = 2;
    const CANCELED = 3;
    const REFUND = 4;
    const UNDERFINE = 5;

    protected $transaction;
    protected $_helper;
    private $info_instance;

    private $transaction_amount;
    private $transaction_paid_amount;
    private $transaction_canceled_amount;

    private $transaction_id;
    private $transaction_status;
    private $transaction_charges;
    private $operation;
    private $transaction_charge;

    private $curl;

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $tid
     * @param $transactionType
     * @param array $transactionDetails
     * @param bool $message
     *
     * @return Mage_Sales_Model_Order_Payment_Transaction|null
     */
    public function _addTransaction(Mage_Sales_Model_Order_Payment $payment, $tid, $transactionType, array $transactionDetails = array(), $message = false)
    {
        $payment->setTransactionId($tid);
        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }
        $transaction = $payment->addTransaction($transactionType, null, false, $message);
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, ["Status" => $this->getTransaction("status")]);
        foreach ($transactionDetails as $key => $value) {
            $payment->unsetData($key);
        }
        $payment->unsLastTransId();
        if ($transaction) {
            $transaction->setMessage($message);
        }
        return $transaction;
    }

    public function _updateTransaction(Mage_Sales_Model_Order_Payment  $payment)
    {
        if ($payment->getId()) {
            $collection = Mage::getModel('sales/order_payment_transaction')->getCollection();
            $collection->setOrderFilter($payment->getOrder())
                ->addPaymentIdFilter($payment->getId())
                ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC)
                ->setOrder('transaction_id', Varien_Data_Collection::SORT_ORDER_DESC);
            foreach ($collection as $transaction) {
                $transaction->setOrderPaymentObject($payment);
                $transaction->setIsClosed(true);
                $transaction->save();
                $this->operation['parent_transaction_id'] = $transaction->getTxnId();
                $this->_addTransaction(
                    $payment,
                    $this->getTransaction('id'),
                    $this->getTypeTransaction($payment->getMethod()),
                    $this->getTransactionData(),
                    $this->_getHelper()->__("Transacion %s by <a href=\"//pagar.me\">pagar.me</a>.", "id")
                )->save();
            }
        }
    }

    public function getTransactionData()
    {
        return array(
            'is_transaction_closed' => $this->operation['is_transaction_closed'],
            'is_transaction_approved' => $this->operation['is_transaction_approved'],
            'should_close_parent_transaction' => $this->operation['should_close_parent_transaction'],
            'parent_transaction_id' => $this->operation['parent_transaction_id'],
            'anet_trans_type' => $this->operation['anet_trans_type'],
        );
    }

    public function historyTransactions($payment)
    {
        $orderId = $payment->getOrder()->getId();
        foreach ($this->getTransactionCharges() as $charge) {
            $this->transactionLog($orderId, $charge);
        }
    }

    public function transactions($payment)
    {
        $this->getTypeOperation($this->getTransaction("status"));
        $this->operation['parent_transaction_id'] = $this->getTransaction('id');

        $this->_addTransaction(
            $payment,
            $this->getTransaction('id'),
            $this->getTypeTransaction($payment->getMethod()),
            $this->getTransactionData(),
            $this->_getHelper()->__("Transacion %s by <a href=\"//pagar.me\">pagar.me</a>.", "id")
        );
        $this->historyTransactions($payment);
    }

    public function _registerTransaction($order)
    {
        foreach ($this->getTransactionCharges() as $charge) {
            $this->_registerCharge($order, $charge);
        }
    }

    public function _updateCharges()
    {
        foreach ($this->getTransactionCharges() as $charge) {
            $chargeModel = Mage::getModel('mageshop_pagarme/charge')->loadByIdCharge($charge->getId());
            if (!$chargeModel->getOrderId()) {
                continue;
            }
            $this->_updateCharge($chargeModel, $charge);
        }
    }

    public function _updateCharge($chargeModel, $charge)
    {
        $chargeModel->setAmount($charge->getAmount())
            ->setPayload(json_encode($charge->getCharge()))
            ->setStatus($charge->getStatus())
            ->setCaptureAmount($charge->getPaidAmount())
            ->setVoidAmount($charge->getVoidAmount())
            ->setChargebackAmount($charge->getCanceledAmount())
            ->setAttempts($chargeModel->getAttempts() + 1)
            ->save();
        return $chargeModel;
    }
    protected function _registerCharge($order, $charge)
    {
        $chargeModel = Mage::getModel('mageshop_pagarme/charge');
        $chargeModel->setOrderId($order->getId())
            ->setParentId($this->getTransaction('id'))
            ->setChargeId($charge->getId())
            ->setPaymentMethod($charge->getPaymentMethod())
            ->setAmount($charge->getAmount())
            ->setPayload(json_encode($charge->getCharge()))
            ->setStatus($charge->getStatus())
            ->setAttempts(0)
            ->save();
    }

    public function getTypeTransaction($method)
    {
        switch ($method) {
            case MageShop_PagarMe_Model_Method_Pix::CODE_PAYMENT:
            case MageShop_PagarMe_Model_Method_Bankslip::CODE_PAYMENT:
                $this->operation['transaction_type'] = MageShop_PagarMe_Model_Orders_Transaction::ACTION_TRANSACTION_ORDER;
                return $this->operation['transaction_type'];
            default:
                return $this->operation['transaction_type'];
        }
    }

    public function action($status)
    {
        switch ($status) {
            case  MageShop_PagarMe_Model_Orders_Transaction::PENDING;
            case  MageShop_PagarMe_Model_Orders_Transaction::PAID;
            case  MageShop_PagarMe_Model_Orders_Transaction::CANCELED;
            case  MageShop_PagarMe_Model_Orders_Transaction::REFUND;
        }
    }

    public function getStatus($status)
    {
        switch ($status) {
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::AUTHORIZED_PENDING_CAPTURE:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::WAITING_CAPTURE:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::WAITING_PAYMENT:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::VIEWED:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::PENDING:
                return MageShop_PagarMe_Model_Orders_Transaction::PENDING; // pending
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::PARTIAL_CAPTURE:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::PAID:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::CAPTURED:
                return MageShop_PagarMe_Model_Orders_Transaction::PAID; // paid
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::VOIDED:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::PARTIAL_VOID:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::ERROR_ON_VOIDING:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::ERROR_ON_REFUNDING:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::WITH_ERROR:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::NOT_AUTHORIZED:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::FAILED:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::CANCELED:
                return MageShop_PagarMe_Model_Orders_Transaction::CANCELED; // cenceled
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::PENDING_REFUND:
            case MageShop_PagarMe_Model_System_Config_Transaction_Status::REFUNDED:
                return MageShop_PagarMe_Model_Orders_Transaction::REFUND; // refund
            default:
                return MageShop_PagarMe_Model_Orders_Transaction::UNDERFINE; // underfine
        }
    }

    public function getTypeOperation($status)
    {
        switch ($this->getStatus($status)) {
            case MageShop_PagarMe_Model_Orders_Transaction::PENDING:
                $this->operation = [
                    "transaction_type" => MageShop_PagarMe_Model_Orders_Transaction::ACTION_TRANSACTION_AUTH,
                    "anet_trans_type" => MageShop_PagarMe_Model_Orders_Transaction::REQUEST_TYPE_AUTH,
                    "is_transaction_closed" => 0,
                    "is_transaction_approved" => 0,
                    "should_close_parent_transaction" => 0,
                ];
                break;
            case MageShop_PagarMe_Model_Orders_Transaction::PAID:
                $this->operation = [
                    "transaction_type" => MageShop_PagarMe_Model_Orders_Transaction::ACTION_TRANSACTION_CAPTURE,
                    "anet_trans_type" => MageShop_PagarMe_Model_Orders_Transaction::REQUEST_TYPE_AUTH_CAPTURE,
                    "is_transaction_closed" => 1,
                    "is_transaction_approved" => 1,
                    "should_close_parent_transaction" => 1,
                ];
                break;
            case MageShop_PagarMe_Model_Orders_Transaction::CANCELED:
                $this->operation = [
                    "transaction_type" => MageShop_PagarMe_Model_Orders_Transaction::ACTION_TRANSACTION_VOID,
                    "anet_trans_type" => MageShop_PagarMe_Model_Orders_Transaction::REQUEST_TYPE_VOID,
                    "is_transaction_closed" => 1,
                    "is_transaction_approved" => 0,
                    "should_close_parent_transaction" => 1,
                ];
                break;
            case MageShop_PagarMe_Model_Orders_Transaction::REFUND:
                $this->operation = [
                    "transaction_type" => MageShop_PagarMe_Model_Orders_Transaction::ACTION_TRANSACTION_REFUND,
                    "anet_trans_type" => MageShop_PagarMe_Model_Orders_Transaction::ACTION_TRANSACTION_REFUND,
                    "is_transaction_closed" => 1,
                    "is_transaction_approved" => 0,
                    "should_close_parent_transaction" => 1,
                ];
                break;
        }
    }

    public function transactionLog($orderId, $charge)
    {
        /**
         * @var MageShop_PagarMe_Model_Transaction $transaction
         * @var int $status
         **/
        $pagarmeTransaction = Mage::getModel('mageshop_pagarme/transaction')->loadByTId($charge->getLastIdTransaction());

        if (!$pagarmeTransaction->getId()) {
            $pagarmeTransaction = Mage::getModel('mageshop_pagarme/transaction');
            $pagarmeTransaction->setOrderId($orderId)
                ->setTid($charge->getLastIdTransaction())
                ->setChargeId($charge->getId())
                ->setCreatedDate(date('Y-m-d H:i:s'))
                ->setTransactionStatus($this->getTransactionStatus())
                ->setPaymentMethod($charge->getLastTransaction('transaction_type'));
        }
        $pagarmeTransaction
            ->setAmount($charge->getAmount())
            ->setCaptureAmount($charge->getPaidAmount())
            ->setChargebackAmount($charge->getCanceledAmount())
            ->setReturnMessage(json_encode($charge->getCharge()));

        $pagarmeTransaction->save();

        return $pagarmeTransaction;
    }

    public function _updateAdditionalInformation($payment)
    {
        $additionalInformation = $payment->getAdditionalInformation();
        $additionalInformation['transaction'] = json_encode($this->getTransaction(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $payment->setAdditionalInformation($additionalInformation);
        $payment->save();
        return $payment;
    }
    public function _processStatus($payment)
    {
        $order = $payment->getOrder();
        if ($this->middlerStatusChargeBack($order)) {
            return $this;
        }
        switch ($this->getStatus($this->getTransactionStatus())) {
            case MageShop_PagarMe_Model_Orders_Transaction::PENDING:
                $this->_holded($order, $this->getTransactionStatus());
                break;
            case MageShop_PagarMe_Model_Orders_Transaction::PAID:
                $this->_paid($order, $this->getTransactionStatus());
                break;
            case MageShop_PagarMe_Model_Orders_Transaction::CANCELED:
                $this->_cancelled($order, $this->getTransactionStatus());
                break;
            case MageShop_PagarMe_Model_Orders_Transaction::REFUND:
                $this->_refund($order, $this->getTransactionStatus());
                break;
        }
    }

    /**
     * @return MageShop_PagarMe_Helper_Data|null
     */
    protected function _getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('mageshop_pagarme');
        }
        return $this->_helper;
    }

    /**
     * Get the value of info_instance
     */
    public function getInfoInstance()
    {
        return $this->info_instance;
    }

    /**
     * Set the value of info_instance
     */
    public function setInfoInstance($info_instance)
    {
        $this->info_instance = $info_instance;
        return $this;
    }
    /**
     * Get the value of transaction
     */
    public function getTransaction($key = null)
    {
        if ($key !== null) {
            return isset($this->transaction[$key]) ? $this->transaction[$key] : null;
        } else {
            return $this->transaction;
        }
    }
    /**
     * Set the value of transaction
     * @param mixed $transaction
     * @return self
     */
    public function setTransaction($transaction): self
    {
        if (gettype(json_decode($transaction, true)) == 'array') {
            $transaction = json_decode($transaction, true);
        }
        $this->transaction = $transaction;

        if (isset($this->transaction["charges"])) {
            $this->setTransactionCharges($this->transaction["charges"]);
        }

        if (isset($this->transaction["status"])) {
            $this->setTransactionStatus($this->transaction["status"]);
        }

        if (isset($this->transaction["amount"])) {
            $this->setTransactionAmount($this->transaction["amount"]);
            $this->initAmountCharge();
        }
        return $this;
    }

    public function initAmountCharge()
    {
        $total_amount = 0;
        $total_paid_amount = 0;
        $total_canceled_amount = 0;
        foreach ($this->getTransactionCharges() as $charge) {
            $total_amount += $charge->getAmount();
            $total_paid_amount += $charge->getPaidAmount();
            $total_canceled_amount += $charge->getCanceledAmount();
        }

        $this->setTransactionAmount($total_amount);
        $this->setTransactionPaidAmount($total_paid_amount);
        $this->setTransactionCanceledAmount($total_canceled_amount);

        return $this;
    }
    /**
     * Get the value of transaction_id
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }
    /**
     * Set the value of transaction_id
     */
    public function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
        return $this;
    }

    /**
     * Get the value of transaction_charges
     */
    public function getTransactionCharge()
    {
        return $this->transaction_charge;
    }
    /**
     * Set the value of transaction_charges
     */
    public function setTransactionCharge($transaction_charge)
    {
        $this->transaction_charge = Mage::getModel("mageshop_pagarme/orders_charge")->setCharge($transaction_charge);
        return $this;
    }
    /**
     * Get the value of transaction_charges
     */
    public function getTransactionCharges()
    {
        return $this->transaction_charges;
    }
    /**
     * Set the value of transaction_charges
     */
    public function setTransactionCharges($transaction_charges)
    {
        $charges = [];
        foreach ($transaction_charges as $charge) {
            $charges[] = Mage::getModel("mageshop_pagarme/orders_charge")->setCharge($charge);
        }
        $this->transaction_charges = $charges;
        return $this;
    }
    /**
     * Get the value of transaction_status
     */
    public function getTransactionStatus()
    {
        return $this->transaction_status;
    }
    /**
     * Set the value of transaction_status
     */
    public function setTransactionStatus($transaction_status)
    {
        $this->transaction_status = $transaction_status;
        return $this;
    }

    public function middlerStatusChargeBack($order)
    {
        $chargeback = false;
        $status = MageShop_PagarMe_Model_System_Config_Transaction_Status::CHARGEDBACK;
        foreach ($this->getTransactionCharges() as $charge) {
            if ($charge->getStatus() === $status) {
                $chargeback = true;
            }
        }

        if ($chargeback) {
            if ($order->canInvoice() === false) {
                $this->_refund($order, $status);
            } else {
                $this->_cancelled($order, $status);
            }
        }
        return $chargeback;
    }

    protected function _paid($order, $status)
    {
        $this->_release($order);
        // Check if the order can be invoiced
        if (!$order->canInvoice()) {
            return $this;
        }
        $payment = $order->getPayment();
        $comment =  $this->getComment($status);
        $amount = $this->getTransactionPaidAmount();
        $invoice = $this->_invoice($order, $amount, $comment);

        if ($this->_canCapture($payment)) {
            $this->_resourceTransaction($invoice)->save();
        } else {
            $this->_updateTransaction($payment);
        }
        $this->_sendEmailInvoice($invoice);
        $order->setState(
            Mage_Sales_Model_Order::STATE_PROCESSING, // ou outro estado desejado
            true, // Use a configuração de status padrão para este estado
            $comment, // Comentário do status
            false // Não notifique o cliente por e-mail
        );
        $order->save();
    }

    protected function _holded($order, $status)
    {
        if ($order->getStatus() == Mage_Sales_Model_Order::STATE_HOLDED) {
            return $this;
        }
        $order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, $this->getComment($status));
        $order->setStatus(Mage_Sales_Model_Order::STATE_HOLDED);
        $order->save();
    }
    protected function _review($order, $status)
    {
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            return $this;
        }
        $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, $this->getComment($status));
        $order->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);
        $order->save();
    }
    protected function _cancelled($order, $status)
    {
        $this->_release($order);
        if (!$order->canCancel()) {
            if ($order->canCreditmemo()) {
                $this->_refund($order, $status);
            }
            return $this;
        }
        $order->cancel();
        $this->addHistoryCommentOrder($order, $this->getComment($status));
        $this->_voidTransaction($order);
        $this->_sendEmailCancel($order);
    }
    protected function _refund($order, $status)
    {
        $this->_release($order);
        if (!$order->canCreditmemo()) {
            return $this;
        }
        $amount = $this->getTransactionCanceledAmount();
        $service = Mage::getModel('sales/service_order', $order);
        $creditmemo = $service->prepareCreditmemo();

        if ((int) $amount > 0) {
            $amount = $this->_getHelper()->amountMagento($amount);
            // Defina o valor do chargeback
            $creditmemo->setBaseGrandTotal($amount);
            $creditmemo->setGrandTotal($amount);
        }
        $creditmemo->setRefundRequested(true);
        $creditmemo->setOfflineRequested(false);
        $creditmemo->register();
        $creditmemo->save();

        $this->addHistoryCommentOrder($order, $this->getComment($status));
        $this->_refundTransaction($order);
    }
    protected function _invoice($order, $amount, $comment = null)
    {
        // Create the invoice
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

        if ((int) $amount > 0) {
            $amount = $this->_getHelper()->amountMagento($amount);
            // Defina o valor da captura
            $invoice->setBaseGrandTotal($amount);
            $invoice->setGrandTotal($amount);
        }

        // Defina o caso de captura
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->addComment((string) $comment, true, true);

        // salva a fatura do pedido
        $invoice->save();

        return $invoice;
    }
    protected function _sendEmailInvoice($invoice)
    {
        // Enviar o email da fatura
        $invoice->sendEmail();
        $invoice->setEmailSent(true);
        return $invoice;
    }

    protected function _sendEmailCancel($order)
    {
        $sendEmail = false;
        if($order->getPayment()->getMethod() === MageShop_PagarMe_Model_Method_Cc::CODE_PAYMENT){
            $sendEmail = (bool) $this->_getHelper()->getConfigData('email_canceled','mageshop_pagarme_cc');
        }else if($order->getPayment()->getMethod() === MageShop_PagarMe_Model_Method_Pix::CODE_PAYMENT) {
            $sendEmail = (bool) $this->_getHelper()->getConfigData('email_canceled','mageshop_pagarme_pix');
        }else if($order->getPayment()->getMethod() === MageShop_PagarMe_Model_Method_Bankslip::CODE_PAYMENT){
            $sendEmail = (bool) $this->_getHelper()->getConfigData('email_canceled','mageshop_pagarme_bankslip');
        }
        if($sendEmail){
            // Envia o e-mail de atualização para o cliente
            $order->sendOrderUpdateEmail(true, 'Seu pedido foi cancelado.');
        }
    }
    protected function _resourceTransaction($object)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($object)
            ->addObject($object->getOrder());
        return $transactionSave;
    }

    protected function _voidTransaction($order)
    {
        $payment = $order->getPayment();
        // Atualiza o status da transação e fecha a transação
        $transactionId = $payment->getLastTransId();
        if ($transactionId) {
            $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID, $order, true);
            $payment->setLastTransId($transactionId . '-void');
            $payment->save();
        }
    }

    protected function _refundTransaction($order)
    {
        $payment = $order->getPayment();
        // Atualiza o status da transação e fecha a transação
        $transactionId = $payment->getLastTransId();
        if ($transactionId) {
            $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $order, true);
            $payment->setLastTransId($transactionId . '-refund');
            $payment->save();
        }
    }

    protected function _canCapture($payment)
    {
        return $payment->getMethod() === MageShop_PagarMe_Model_Method_Cc::CODE_PAYMENT;
    }

    public function _release($order)
    {
        if ($this->_toHold($order)) {
            if ($order->canUnhold()) {
                $order->unhold();
            }
        }
    }
    public function _toHold($order)
    {
        if ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED || $order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            return true;
        }
        return false;
    }

    public function getComment($status)
    {
        return $this->pagarmeComment("%s", $this->_getStatusMessage($status));
    }

    public function pagarmeComment($comment, ...$params)
    {
        $comment = "pagar.me: %s | " . $comment;
        return $this->_getHelper()->__($comment, $this->timeComment(), ...$params);
    }

    public function timeComment()
    {
        return Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
    }

    public function noteActionAdmin($action)
    {
        switch ($action) {
            case 'capture':
                return 'O administrador solicitou a captura do valor do pedido via pagar.me.';
            case 'void':
                return 'O administrador cancelou o pedido via pagar.me.';
            case 'refund':
                return 'O administrador solicitou o reembolso do pedido via pagar.me.';
            default:
                return 'Ação desconhecida realizada pelo administrador.';
        }
    }

    public function addHistoryCommentOrder($order, $comment)
    {
        $order->addStatusHistoryComment((string) $comment, false);
        $order->save();
        return $order;
    }

    public function _getStatusMessage($status)
    {
        switch ($status) {
            case 'paid':
                return 'Payment completed successfully.';
            case 'pending':
                return 'Payment pending. We are awaiting payment confirmation.';
            case 'authorized_pending_capture':
                return 'Transaction authorized, awaiting capture.';
            case 'failed':
                return 'Payment failed. Please try again or contact support.';
            case 'with_error':
                return 'An error occurred during payment processing. Please contact support.';
            case 'captured':
                return 'Payment successfully captured.';
            case 'waiting_capture':
                return 'Payment authorized and awaiting capture.';
            case 'canceled':
                return 'Transaction successfully canceled.';
            case 'chargedback':
                return 'The transaction has been disputed.';
            default:
                return 'Unknown status.';
        }        
    }

    /**
     * Get the value of transaction_amount
     */
    public function getTransactionAmount()
    {
        return $this->transaction_amount;
    }

    /**
     * Set the value of transaction_amount
     */
    public function setTransactionAmount($transaction_amount): self
    {
        $this->transaction_amount = $transaction_amount;

        return $this;
    }


    /**
     * Get the value of transaction_paid_amount
     * @return integer
     */
    public function getTransactionPaidAmount()
    {
        return $this->transaction_paid_amount;
    }

    /**
     * Set the value of transaction_paid_amount
     */
    public function setTransactionPaidAmount($transaction_paid_amount): self
    {
        $this->transaction_paid_amount = $transaction_paid_amount;

        return $this;
    }


    /**
     * Get the value of transaction_canceled_amount
     */
    public function getTransactionCanceledAmount()
    {
        return $this->transaction_canceled_amount;
    }

    /**
     * Set the value of transaction_canceled_amount
     */
    public function setTransactionCanceledAmount($transaction_canceled_amount): self
    {
        $this->transaction_canceled_amount = $transaction_canceled_amount;

        return $this;
    }

    public function setHeader()
    {
        // grava o pedido no yapay
        $this->rest()->_header(
            [
                'accept: application/json',
                'content-type: application/json',
                'Authorization: ' . $this->_getHelper()->getAuthorization(),
            ]
        );
    }
    /**
     * Request http
     *
     * @return MageShop_PagarMe_Service_Rest
     */
    public function rest()
    {
        if (!$this->curl || !($this->curl instanceof MageShop_PagarMe_Service_Rest)) {
            $this->curl = new MageShop_PagarMe_Service_Rest();
        }
        return $this->curl;
    }

    public function destroyRest()
    {
        $this->curl = null;
        unset($this->curl);
        return $this;
    }
}
