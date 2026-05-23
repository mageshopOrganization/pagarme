<?php

class MageShop_PagarMe_Block_Adminhtml_System_Config_Fieldset_Hub extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    private $hub_token = null;
    
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->getElementHtml();
        $urlCallback = Mage::helper('adminhtml')->getUrl('*/hub/authorization/', array());
        $html .= '<span id="pagarme-hub"></span>';
        $html .= '<script type="text/javascript">
                    // hub config
                    let config_pagarme = {
                        publicAppKey : \''.  Mage::helper('mageshop_pagarme')->getConfigData('public_app_key', 'hub') . '\',
                        redirectUrl :  \''.  $urlCallback . '\',
                        language :     \''.  Mage::helper('mageshop_pagarme')->getConfigData('language', 'hub')  . '\',
                        environment :  \''.  Mage::helper('mageshop_pagarme')->getConfigData('environment', 'hub') . '\',
                        installId:     \''.  $this->getHubToken()->getInstallId() . '\'
                    };
                    // run and create button
                    Hub(config_pagarme);
                  </script>';
        return $html;
    }
    protected function getHubToken()
    {
        if($this->hub_token === null){
            $this->hub_token = Mage::getModel('mageshop_pagarme/hub')->loadByStoreId(
                Mage::app()->getStore()->getId()
            );
        }
        return $this->hub_token;
    }
}