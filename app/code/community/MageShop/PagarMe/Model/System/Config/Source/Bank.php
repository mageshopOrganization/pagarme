<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to web@tryideas.com.br so we can send you a copy immediately.
 *
 * @category   MageShop
 * @package    MageShop_PagarMe
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageShop_PagarMe_Model_System_Config_Source_Bank
{
    public function toOptionArray()
    {
        return array(
            array('value' => "001", 'label' => "Banco do Brasil"),
            array('value' => "033", 'label' => "Santander"),
            array('value' => "237", 'label' => "Bradesco"),
            array('value' => "341", 'label' => "Itau"),
            array('value' => "745", 'label' => "Citibank"),
            array('value' => "104", 'label' => "Caixa Econômica Federal"),
        );
    }
}