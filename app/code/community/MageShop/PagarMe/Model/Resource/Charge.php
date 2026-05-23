<?php

/**
 * This source file is subject to the MIT License.
 * It is also available through http://opensource.org/licenses/MIT
 *
 * @category  Mageshop
 * @package   MageShop_PagarMe
 * @author    Vitor Costa <vitor@tryideas.com.br>
 * @copyright 2023 Vitor Costa
 * @license   http://opensource.org/licenses/MIT MIT
 * @link      https://github.com/csvitor/MageShop_PagarMe
 */
class MageShop_PagarMe_Model_Resource_Charge
extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     *
     * @see Mage_Core_Model_Resource_Abstract::_construct()
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageshop_pagarme/charge', 'id');
    }

    public function loadByIdCharge(MageShop_PagarMe_Model_Charge $object, $chargeId)
    {
        $adapter = $this->_getReadAdapter();
        $binds = array(':charge_id' => (string) $chargeId);
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('charge_id = :charge_id');

        $data = $adapter->fetchRow($select, $binds);
        if ($data) {
            $object->setData($data);
        } else {
            $object->setData(array());
        }
        return $this;
    }

}
