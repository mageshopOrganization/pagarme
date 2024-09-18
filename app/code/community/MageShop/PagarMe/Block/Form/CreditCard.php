<?php

class MageShop_PagarMe_Block_Form_CreditCard extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageshop/pagarme/form/creditcard.phtml');
    }
    /**
     * Function to get cc valid months
     */
    public function getCcMonths()
    {
      $months = $this->getData('cc_months');
      if (is_null($months)) {
        $months[0] = $this->__("Month");
        $months = array_merge($months, $this->helper('mageshop_pagarme/creditCard')->getMonths());
        $this->setData('cc_months', $months);
      }
      return $months;
    }
    /**
     * Function to get cc valid years
     */
    public function getCcYears()
    {
      $years = $this->getData('cc_years');
      if ($years == null) {
        $years[0] = $this->__("Year");
        $years = $years + $this->helper('mageshop_pagarme/creditCard')->getYears();
        $this->setData('cc_years', $years);
      }
      return $years;
    }

    /**
     * Function to get installments
     * @param mixed $total
     */
    public function getInstallments($quote)
    {
      $total = $quote->getBaseGrandTotal();
      $subtotal = $quote->getSubtotal();
      $_discount_helper = Mage::helper("mageshop_pagarme/discount");
      $_interest_helper = Mage::helper("mageshop_pagarme/interest");
      $maxInstallments = $this->helper('mageshop_pagarme/creditCard')->getConfigData('installments');
      $minValueInstalment = $this->helper('mageshop_pagarme/creditCard')->getConfigData('min_installment');
      $dataInterest = $_interest_helper->getInstallmentInterest();
      $dataInterest = unserialize($dataInterest);
      $value_discount = 0;
      $percentage = 0;
      $installmentInterest = [];
      if($_discount_helper->getDiscountActiveCreditCard()){
        $percentage = $_discount_helper->getDiscountPercentageCreditCard();
        if ($_discount_helper->percentage()) {
            $value_discount = $_discount_helper->getDiscountCc($subtotal);
        }
      }
      foreach ($dataInterest as $key => $value) {
        $installmentInterest[] = $value['from_qty'];
      }
      
      if ($maxInstallments == 0) {
        $arrayInstallments[1] = "1x de R$$total á vista";
        return $arrayInstallments;
      }
      for ($i = 0; $i < $maxInstallments; $i++) {
        $times = $i + 1;
        $discounLabel = '';
        if($value_discount > 0 && $_discount_helper->getSplitOk($times)){
          $valuePortion = $this->helper('mageshop_pagarme/creditCard')->monetize(( $total - $value_discount ) / $times);
          $discounLabel = $this->__(" desconto de %s%%", $percentage);
          $valuePortion2f = number_format($valuePortion, 2, ",", ".");
        }else{
          $valuePortion = ($total / $times);
          $valuePortion2f = number_format($valuePortion, 2, ",", ".");
        }
        if ($times == 1) {
          if (!isset($installmentInterest[0]) || $installmentInterest[0] == 0 || $installmentInterest[0] == null) {
            $arrayInstallments[$times] = $this->__("1x de R$$valuePortion2f%s sem juros", $discounLabel);
          } else {
            $interest = $valuePortion * ($installmentInterest[0] / 100);
            $interest = number_format($interest, 2);
            $valuePortion = $valuePortion + $interest;
            $valuePortion2f = number_format($valuePortion, 2, ",", ".");
            $arrayInstallments[$times] = $this->__("1x de R$$valuePortion2f%s com juros", $discounLabel);
          }
        } else if (isset($installmentInterest[$i]) && $installmentInterest[$i] != 0 && $installmentInterest[$i] != null) {
          if ($valuePortion < $minValueInstalment) {
            break;
          }
          $interest = $valuePortion * ($installmentInterest[$i] / 100);
          $interest = number_format($interest, 2);
          $valuePortion = $valuePortion + $interest;
          $valuePortion2f = number_format($valuePortion, 2, ",", ".");
          $arrayInstallments[$times] = $this->__("$times" . "x de R$$valuePortion2f%s com juros", $discounLabel);
        } else {
          if ($valuePortion < $minValueInstalment) {
            break;
          }
          $arrayInstallments[$times] = $this->__("$times" . "x de R$$valuePortion2f%s sem juros", $discounLabel);
        }
      }
      return $arrayInstallments;
    }

}
