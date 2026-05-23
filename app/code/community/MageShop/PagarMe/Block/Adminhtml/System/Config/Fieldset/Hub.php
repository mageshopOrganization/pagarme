<?php

class MageShop_PagarMe_Block_Adminhtml_System_Config_Fieldset_Hub extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    private $hub_token = null;
    
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->getElementHtml();
        $urlCallback = Mage::helper('adminhtml')->getUrl('*/hub/authorization/', array());
        $helper = Mage::helper('mageshop_pagarme');
        $publicAppKey = $this->escapeJsString($helper->getConfigData('public_app_key', 'hub'));
        $redirectUrl  = $this->escapeJsString($urlCallback);
        $language     = $this->escapeJsString($helper->getConfigData('language', 'hub'));
        $environment  = $this->escapeJsString($helper->getConfigData('environment', 'hub'));
        $installId    = $this->escapeJsString((string) $this->getHubToken()->getInstallId());

        $html .= '<span id="pagarme-hub"></span>';
        $html .= '<script type="text/javascript">
                    // hub config
                    let config_pagarme = {
                        publicAppKey : \''.  $publicAppKey . '\',
                        redirectUrl :  \''.  $redirectUrl . '\',
                        language :     \''.  $language  . '\',
                        environment :  \''.  $environment . '\',
                        installId:     \''.  $installId . '\'
                    };
                    // run and create button
                    if (typeof Hub === "function") { Hub(config_pagarme); }
                  </script>';
        return $html;
    }

    private function escapeJsString($value)
    {
        return addcslashes((string) $value, "\\'\"\r\n\t<>");
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