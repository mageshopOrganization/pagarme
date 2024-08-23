<?php

class MageShop_PagarMe_Helper_Data extends Mage_Core_Helper_Abstract
{

    const MS_API_PAGARME = 'api';
    const MS_HUBAPI_PAGARME = 'hub';
    const MS_HUBAPI_CORE = 'core';
    const MS_URL_API_PAGARME = 'api_url';
    const MS_VERSION_API_PAGARME = 'version';
    const MS_API_TYPE_API = "type_api";
    const MS_SECRET_KEY_API_PAGARME = "secret_key";
    const MS_PUBLIC_KEY_API_PAGARME = "public_key";
    const MS_URL_WEBHOOK = 'pagarme/api/event';
    const MS_URL_CALLBACK = 'pagarme/api/callback';


    const PRICE = 100;
    /**
     * Gets the configuration value by path
     *
     * @param string $path System Config Path
     *
     * @return mixed
     */
    public function getConfigData($path, $group = null)
    {
        $groupModule = $group == null ? $this->getModuleName() : $group;
        return Mage::getStoreConfig("payment/{$groupModule}/{$path}");
    }
    
    /**
     * Metadata Identificação Plataforma
     * Para toda criação de pedido, é obrigatório informar no nó de
     * @return string
     */
    public function getMetaPlatformIntegration()
    {
        return Mage::getStoreConfig("sales/mageshop_pagarme/plataform_integration");
    }
    /**
     * Get a text for option value
     *
     * @param string|int $value Method Code
     *
     * @return string|bool
     */
    public function getShippingLabel($value)
    {
        $source = Mage::getSingleton('mageshop_correios/source_postMethods');
        return $source->getOptionText($value);
    }
    public function getModuleName()
    {
        return strtolower($this->_getModuleName());
    }

    /**
     * Urls para eventos de webhooks
     *
     * @return string
     */
    public function getUrlWebHook()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . self::MS_URL_WEBHOOK;
    }
    /**
     * Url de callbacks para hub 
     *
     * @return string
     */
    public function getUrlCallback()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . self::MS_URL_CALLBACK;
    }

    /**
     * Retorna a url da api definida no config.xml
     *
     * @param string $endpoint
     * @return string
     */
    public function getUrlApi($endpoint = '')
    {
        return $this->__(
            "%s%s/%s/%s",
            $this->getUrl(),
            self::MS_HUBAPI_CORE,
            $this->version(),
            $endpoint
        );
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getFields($type = 'customer_address')
    {
        $entityType = Mage::getModel('eav/config')->getEntityType($type);
        $entityTypeId = $entityType->getEntityTypeId();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($entityTypeId);
        return $attributes->getData();
    }

    public function getAuthorization()
    {
        return $this->__("Basic %s", base64_encode($this->getSecretKey() . ':'));
    }

    public function secretKey()
    {
        return $this->isApi() ? $this->getSecretKeyConfig() : $this->appHub()->getAccessToken();
    }

    public function publicKey()
    {
        return $this->isApi() ? $this->getPublicKeyConfig() : $this->appHub()->getAccountPublicKey();
    }

    public function getSecretKey()
    {
        return Mage::helper('core')->decrypt(
            $this->secretKey()
        );
    }

    public function getPublicKey()
    {
        return Mage::helper('core')->decrypt(
            $this->publicKey()
        );
    }
    public function monetize($value)
    {
        if (empty($value)) {
            return 0.00;
        }
        if (is_float($value)) {
            return (float) number_format($value, 2, '.', '');
        }
        if (is_string($value) && strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }
        $value = floor($value * 100) / 100;
        $value = (float) number_format($value, 2, '.', '');
        return $value;
    }

    public function appHub()
    {
        $storeId = Mage::app()->getStore()->getId();
        $hub =  Mage::getModel('mageshop_pagarme/hub')->loadByStoreId($storeId);
        // Se não encontrado, carregar pela store_id 0
        if (!$hub || !$hub->getId()) {
            $hub = Mage::getModel('mageshop_pagarme/hub')->loadByStoreId(0);
        }
        return $hub;
    }
    public function getUrl()
    {
        return $this->isApi() ? $this->getConfigData(self::MS_URL_API_PAGARME, self::MS_API_PAGARME) : $this->getConfigData(self::MS_URL_API_PAGARME, self::MS_HUBAPI_PAGARME);
    }
    public function version()
    {
        return $this->isApi() ? $this->getConfigData(self::MS_VERSION_API_PAGARME, self::MS_API_PAGARME) : $this->getConfigData(self::MS_VERSION_API_PAGARME, self::MS_HUBAPI_PAGARME);
    }

    public function getSecretKeyConfig()
    {
        return $this->getConfigData(self::MS_SECRET_KEY_API_PAGARME);
    }

    public function getPublicKeyConfig()
    {
        return $this->getConfigData(self::MS_PUBLIC_KEY_API_PAGARME);
    }
    public function isApi()
    {
        return $this->typeAPI() === MageShop_PagarMe_Model_System_Config_Source_TypeApi::API;
    }
    public function isHubApi()
    {
        return $this->typeAPI() === MageShop_PagarMe_Model_System_Config_Source_TypeApi::HUBAPI;
    }
    public function typeAPI()
    {
        return $this->getConfigData(self::MS_API_TYPE_API);
    }
    /**
     * Converte para inteiro
     *
     * @param double $price
     * @return int
     */
    public function amount($price)
    {
        return (int) ($price * MageShop_PagarMe_Helper_Data::PRICE);
    }

    public function amountMagento($amount)
    {
        return (double) ($amount / MageShop_PagarMe_Helper_Data::PRICE);
    }
    public function getConfigJs()
    {
        $config = array(
            'url_app'    => $this->getConfigData(self::MS_URL_API_PAGARME, self::MS_API_PAGARME),
            'core'       => self::MS_HUBAPI_CORE,
            'version'    => $this->getConfigData(self::MS_VERSION_API_PAGARME, self::MS_API_PAGARME),
            'public_key' => $this->getPublicKey(),
            'placeorder_button' => '#onestepcheckout-place-order-button'
        );
        return json_encode($config);
    }
}
