<?php

class MageShop_PagarMe_Model_System_Config_Source_BatchSize
{
    public function toOptionArray()
    {
        $options = array();
        for ($i = 1; $i <= 15; $i++) {
            $options[] = array('value' => $i, 'label' => $i);
        }
        return $options;
    }
}
