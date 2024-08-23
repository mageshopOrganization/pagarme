<?php

/**
 * Class Rede_Adquirencia_Model_System_Config_Transaction_Types
 */
class MageShop_PagarMe_Model_System_Config_Transaction_Status
{
    protected $_options = array();

    const AUTHORIZED_PENDING_CAPTURE = "authorized_pending_capture";
    const NOT_AUTHORIZED = "not_authorized";
    const CAPTURED = "captured";
    const PARTIAL_CAPTURE = "partial_capture";
    const WAITING_CAPTURE = "waiting_capture";
    const REFUNDED = "refunded";
    const VOIDED = "voided";
    const PARTIAL_VOID = "partial_void";
    const ERROR_ON_VOIDING = "error_on_voiding";
    const ERROR_ON_REFUNDING = "error_on_refunding";
    const WAITING_CANCELLATION = "waiting_cancellation";
    const WITH_ERROR = "with_error";
    const FAILED = "failed";
    const PAID = "paid";
    const PENDING = "pending";
    const PENDING_REFUND = "pending_refund";
    const CHARGEDBACK = "chargedback";
    const WAITING_PAYMENT = "waiting_payment";
    const GENERATED = "generated";
    const VIEWED = "viewed";
    const UNDERPAID = "underpaid";
    const OVERPAID = "overpaid";
    const PROCESSING = "processing";
    const CANCELED = "canceled";
    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (empty($this->_options)) {
            $helper = Mage::helper('mageshop_pagarme');
            $this->_options[self::PAID] = $helper->__('Paid');
            $this->_options[self::NOT_AUTHORIZED] = $helper->__('Not authorized');
            $this->_options[self::AUTHORIZED_PENDING_CAPTURE] = $helper->__('Authorized Pending Capture');
            $this->_options[self::CAPTURED] = $helper->__('Captured');
            $this->_options[self::PARTIAL_CAPTURE] = $helper->__('Partial Capture');
            $this->_options[self::WAITING_CAPTURE] = $helper->__('Waiting Capture');
            $this->_options[self::REFUNDED] = $helper->__('Refunded');
            $this->_options[self::VOIDED] = $helper->__('Voided');
            $this->_options[self::PARTIAL_VOID] = $helper->__('Partial Void');
            $this->_options[self::ERROR_ON_VOIDING] = $helper->__('Error on Voiding');
            $this->_options[self::ERROR_ON_REFUNDING] = $helper->__('Error on Refunding');
            $this->_options[self::WAITING_CANCELLATION] = $helper->__('Waiting Cancellation');
            $this->_options[self::WITH_ERROR] = $helper->__('With Error');
            $this->_options[self::FAILED] = $helper->__('Failed');
            $this->_options[self::PENDING_REFUND] = $helper->__('Pending Refund');
            $this->_options[self::PENDING] = $helper->__('Pending');
            $this->_options[self::WAITING_PAYMENT] = $helper->__('Waiting Payment');
            $this->_options[self::GENERATED] = $helper->__('Generated');
            $this->_options[self::VIEWED] = $helper->__('Viewed');
            $this->_options[self::UNDERPAID] = $helper->__('Underpaid');
            $this->_options[self::OVERPAID] = $helper->__('Overpaid');
            $this->_options[self::PROCESSING] = $helper->__('Processing');
            $this->_options[self::CANCELED] = $helper->__('Canceled');
        }
        return $this->_options;
    }

       /**
     * Convert a status string to a formatted version
     *
     * @param string $status
     * @return string
     */
    public static function convertStatus($status)
    {
        $helper = Mage::helper('mageshop_pagarme');
        switch ($status) {
            case self::AUTHORIZED_PENDING_CAPTURE:
                return $helper->__('Authorized Pending Capture');
            case self::NOT_AUTHORIZED:
                return $helper->__('Not Authorized');
            case self::CAPTURED:
                return $helper->__('Captured');
            case self::PARTIAL_CAPTURE:
                return $helper->__('Partial Capture');
            case self::WAITING_CAPTURE:
                return $helper->__('Waiting Capture');
            case self::REFUNDED:
                return $helper->__('Refunded');
            case self::VOIDED:
                return $helper->__('Voided');
            case self::PARTIAL_VOID:
                return $helper->__('Partial Void');
            case self::ERROR_ON_VOIDING:
                return $helper->__('Error on Voiding');
            case self::ERROR_ON_REFUNDING:
                return $helper->__('Error on Refunding');
            case self::WAITING_CANCELLATION:
                return $helper->__('Waiting Cancellation');
            case self::WITH_ERROR:
                return $helper->__('With Error');
            case self::FAILED:
                return $helper->__('Failed');
            case self::PAID:
                return $helper->__('Paid');
            case self::PENDING:
                return $helper->__('Pending');
            case self::PENDING_REFUND:
                return $helper->__('Pending Refund');
            case self::CHARGEDBACK:
                return $helper->__('Chargeback');
            case self::WAITING_PAYMENT:
                return $helper->__('Waiting Payment');
            case self::GENERATED:
                return $helper->__('Generated');
            case self::VIEWED:
                return $helper->__('Viewed');
            case self::UNDERPAID:
                return $helper->__('Underpaid');
            case self::OVERPAID:
                return $helper->__('Overpaid');
            case self::PROCESSING:
                return $helper->__('Processing');
            case self::CANCELED:
                return $helper->__('Canceled');
            default:
                return $status; // Retornar o próprio status se não houver correspondência
        }
    }

}