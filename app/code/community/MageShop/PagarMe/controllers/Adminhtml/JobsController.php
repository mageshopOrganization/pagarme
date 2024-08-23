<?php

/**
 * Class MageShop_PagarMe_Adminhtml_JobsController
 */
class MageShop_PagarMe_Adminhtml_JobsController extends Mage_Adminhtml_Controller_Action
{
    protected $_helper = null;

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('mageshop_pagarme/mageshop_pagarme_jobs');
    }

    public function indexAction()
    {
        $this->_initJobs();

        $this->_initAction()
            ->renderLayout();
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('mageshop_pagarme/mageshop_pagarme_jobs')
            ->_title($this->__('pagar.me'))->_title($this->__('Jobs'))
            ->_addBreadcrumb($this->__('pagar.me'), $this->__('pagar.me'))
            ->_addBreadcrumb($this->__('Jobs'), $this->__('Jobs'));
        return $this;
    }

    protected function _initJobs()
    {
        $collection = Mage::getModel('mageshop_pagarme/job')
        ->getCollection()
        //->addFieldToFilter('attempts', array('lt' => 3))
        ->setPageSize(100)
        ->setOrder('created_at', 'ASC');
        Mage::register('pagarme_jobs', $collection);
    }

}