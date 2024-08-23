<?php

/**
 * Class MageShop_PagarMe_Adminhtml_HubController
 */
class MageShop_PagarMe_Adminhtml_HubController extends Mage_Adminhtml_Controller_Action
{


    private $_helper = null;

    /**
     * Da acesso para qualquer usuario adm
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return true;
    }
    
    public function authorizationAction()
    {
        $authorization_code = $this->getRequest()->getParam('authorization_code');
        if(!$authorization_code){
            $this->_forward('noRoute');
            return false;
        }

        $environment = $this->config('environment') == 'dev' ? $this->config('environment') : '';

        $url = $this->__('%s%s/%s', $this->config('api_url'), $environment, 'auth/apps/access-tokens');
        $body = [
            "code" =>  $authorization_code,
            "hub_callback_url" =>  $this->getHelper()->getUrlCallback(),
            "webhook_url" =>  $this->getHelper()->getUrlWebHook(),
        ];
        $http = new MageShop_PagarMe_Service_Rest();

        $http->_header(
            [
                'accept: application/json',
                'content-type: application/json',
                'PublicAppKey: ' . $this->config('public_app_key'),
            ]
        );

        $http->url($url);
        $http->_method('POST');
        $http->_body(json_encode($body));
        $http->exec();

        if($http->success()){
            $data = json_decode($http->getResponse());
            $this->setHubInstallToken($data);
            $this->getAdminSession()->addSuccess(
                $this->__('Successfully generated the Pagar.me integration')
            );
        }else{
            $this->getAdminSession()->addError(
                $this->__('Failed to generate the Pagar.me token')
            );
        }

        $this->_redirect('adminhtml/system_config/edit', array('section' => 'payment'));

    }

    public function config($field)
    {
        return $this->getHelper()->getConfigData($field, 'hub');
    }
    public function getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('mageshop_pagarme');
        }
        return $this->_helper;
    }
    private function setHubInstallToken($data)
    {
        $storeId = Mage::app()->getStore()->getId();
        $adminUserId = Mage::getSingleton('admin/session')->getUser()->getId();
        $hub = Mage::getModel('mageshop_pagarme/hub')->loadByStoreId($storeId);
        $time = date('Y-m-d H:i:s');
        
        if(!$hub || !$hub->getId()){
            $hub = Mage::getModel('mageshop_pagarme/hub');
            $hub->setCreatedAt($time);
        }
        
        $data->access_token = Mage::helper('core')->encrypt($data->access_token);
        $data->account_public_key = Mage::helper('core')->encrypt($data->account_public_key);

        $hub->setAccessToken($data->access_token);
        $hub->setAccountPublicKey($data->account_public_key);
        $hub->setAccountId($data->account_id);
        $hub->setInstallId($data->install_id);
        $hub->setPayload(json_encode($data));
        $hub->setUser($adminUserId);
        $hub->setUpdatedAt($time);
        $hub->save();
        return $hub;

    }


    private function getAdminSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

}