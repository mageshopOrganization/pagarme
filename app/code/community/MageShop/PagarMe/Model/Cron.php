<?php

// limite de execucao
set_time_limit(1800);

class MageShop_PagarMe_Model_Cron
{

    public function jobs()
    {
        $batchSize = (int) Mage::helper('mageshop_pagarme')->getConfigData('cron_batch_size');
        if ($batchSize < 1 || $batchSize > 15) {
            $batchSize = 5;
        }

        $collection = Mage::getModel('mageshop_pagarme/job')
            ->getCollection()
            ->addFieldToFilter('attempts', array('lt' => 3))
            ->setPageSize($batchSize)
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
                    Mage::log(
                        '[Cron] Job deletado | #' . $job->getIncrementId() . ' | ' . $job->getNotificationId() . ' | pedido não encontrado no Magento',
                        Zend_Log::WARN,
                        'mageshop_pagarme.log'
                    );
                    $job->delete();
                    continue;
                }

                try {
                    $response = $handler->order($job->getNotificationId());
                    $response->process($order);
                } catch (\Throwable $e) {
                    Mage::log(
                        '[Cron] Falha tentativa ' . ($job->getAttempts() + 1) . ' | #' . $job->getIncrementId() . ' | ' . $e->getMessage(),
                        Zend_Log::ERR,
                        'mageshop_pagarme.log'
                    );
                    $job->setAttempts($job->getAttempts() + 1);
                    $job->setObs($e->getMessage());
                    $job->save();
                    continue;
                }

                $pagarmeStatus = 'unknown';
                try {
                    $pagarmeStatus = (string) $response->getTransaction('status');
                } catch (Exception $ignored) {}

                // Exclui o registro do banco de dados
                Mage::log(
                    '[Cron] Job deletado | #' . $job->getIncrementId() . ' | ' . $job->getNotificationId() . ' | Pagar.me status: ' . $pagarmeStatus,
                    Zend_Log::INFO,
                    'mageshop_pagarme.log'
                );
                $job->delete();
            }

        endif;
    }
}
