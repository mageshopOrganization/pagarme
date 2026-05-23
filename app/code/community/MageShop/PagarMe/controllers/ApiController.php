<?php
/**
 * PagSeguro Transparente Magento
 * Notification Controller responsible for receive order update notifications from PagSeguro
 * See how to setup notification url on module's official website
 *
 * @category    MageShop
 * @package     MageShop_PagarMe
 * @author      Vitor Costa
 * @copyright   Copyright (c) 2023 Vitor Costa (https://github.com/csvitor/MageShop_PagarMe/)
 * @license     https://opensource.org/licenses/MIT MIT License
 */
class MageShop_PagarMe_ApiController extends Mage_Core_Controller_Front_Action{


    public function eventAction()
    {
        try {
            $post = new Zend_Controller_Request_Http();
            $rawbody = $post->getRawBody();
           
            $data = json_decode($rawbody, true);
           
            $orderId = isset($data['data']['order']['code']) ? $data['data']['order']['code'] : (isset($data['data']['code']) ? $data['data']['code'] : null);
            $orderIdPagarme = isset($data['data']['order']['id']) ? $data['data']['order']['id'] : (isset($data['data']['id']) ? $data['data']['id'] : null);

            if ($orderId == null || $orderIdPagarme == null){
                return $this->_forward('404');
            }

            $now = Mage::getModel('core/date')->gmtDate();
            $model = Mage::getModel('mageshop_pagarme/job')->getCollection()
            ->addFieldToFilter('notification_id', $orderIdPagarme)
            ->getFirstItem();
           
            if (!$model->getId()) {
                // O registro não existe, salve
                $model = Mage::getModel('mageshop_pagarme/job');
                $model->setIncrementId($orderId);
                $model->setNotificationId($orderIdPagarme);
                $model->setPayload(json_encode($rawbody));
                $model->setObs('WEBHOOK');
                $model->setAttempts(0);
                $model->setCreatedAt($now); // Defina a data de criação como a data e hora atual
                // Defina outros dados conforme necessário
                $model->save();
            }
            return $this->getResponse()->setBody('success');
        } catch (\Exception $e) {
            return $this->_forward('404');
        }
    }

    public function callbackAction()
    {
        $post = new Zend_Controller_Request_Http();
        $rawbody = $post->getRawBody();
        $data = json_decode($rawbody);
        $hub = Mage::getModel('mageshop_pagarme/hub')->loadByInstallId($data->install_id);
        
        if($hub || $hub->getId()){
            if($data->command == 'Uninstall'){
                $hub->delete();
                $this->createAdminNotification(
                    $this->__('Integração com Pagar.me removida'),
                    $this->__('A integração com o Pagar.me foi desinstalada com sucesso. Integração ID: %s', $hub->getInstallId()),
                    Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE
                );
            }
        }
        return $this->getResponse()->setBody('success');
    }
    protected function createAdminNotification($title, $description, $severity)
    {
        $notification = Mage::getModel('adminnotification/inbox');
        $notification->setData(array(
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'date_added' => Mage::getModel('core/date')->gmtDate(),
        ));
        $notification->save();
    }
}