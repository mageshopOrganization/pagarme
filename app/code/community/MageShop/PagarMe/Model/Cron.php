<?php

// limite de execucao
set_time_limit(1800);

class MageShop_PagarMe_Model_Cron
{

    public function jobs()
    {
        $collection = Mage::getModel('mageshop_pagarme/job')
            ->getCollection()
            ->addFieldToFilter('attempts', array('lt' => 3))
            ->setPageSize(2)
            ->setOrder('created_at', 'ASC');

        if ($collection->getSize()) :

            $handler = Mage::getModel('mageshop_pagarme/orders_orderHandler');
            // Itera sobre os registros
            foreach ($collection as $job) {
                // pega o pedido do magento
                $order = Mage::getModel('sales/order')->loadByIncrementId(
                    $job->getIncrementId()
                );

                if (!$order || !$order->getId()) {
                    $job->delete();
                    continue;
                }
                
                try {
                    $response = $handler->order($job->getNotificationId());
                    $response->process($order);
                    $response->_updateCharges();
                } catch (\Exception $e) {
                    $job->setAttempts($job->getAttempts() + 1);
                    $job->setObs($e->getMessage());
                    $job->save();
                    continue;
                }
                // Exclui o registro do banco de dados
                $job->delete();
            }

        endif;
    }
}
