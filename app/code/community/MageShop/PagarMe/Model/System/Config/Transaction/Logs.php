<?php

/**
 * Class Rede_Adquirencia_Model_System_Config_Transaction_Types
 */
class MageShop_PagarMe_Model_System_Config_Transaction_Logs
{
    const MS_PAGARME_ACTION_SYSTEM = 1;
    const MS_PAGARME_ACTION_TRANSACTION = 2;
    const MS_PAGARME_ACTION_ALL = 3;
    /*
    * Function to get environment options
    * @return array
    */
    public function toOptionArray() {
      $array = [
        [
          'value' => self::MS_PAGARME_ACTION_SYSTEM,
          'label' => 'Não gerar log'
        ],
        [
          'value' => self::MS_PAGARME_ACTION_TRANSACTION,
          'label' => 'Gerar log transação'
        ],
        [
          'value' => self::MS_PAGARME_ACTION_ALL,
          'label' => 'Gerar log de todos os eventos'
        ]
      ];
      return $array;
    }
}