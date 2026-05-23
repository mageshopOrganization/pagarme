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
class MageShop_PagarMe_Model_Hub extends Mage_Core_Model_Abstract
{
    /**
     * Internal constructor
     *
     * @see Varien_Object::_construct()
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageshop_pagarme/hub');
    }

    public function loadByStoreId($storeId)
    {
        $this->_getResource()->loadByStoreId($this, $storeId);
        return $this;
    }

    public function loadByInstallId($installId)
    {
        $this->_getResource()->loadByInstallId($this, $installId);
        return $this;
    }


}
