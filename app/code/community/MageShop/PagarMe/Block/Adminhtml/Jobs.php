<?php

/**
 * Class MageShop_PagarMe_Block_Adminhtml_Jobs
 */
class MageShop_PagarMe_Block_Adminhtml_Jobs extends Mage_Adminhtml_Block_Template
{

    private $_jobs;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mageshop/pagarme/jobs/list.phtml');
    }

    /**
     * @return mixed|null
     */
    public function getJobs()
    {
        if (empty($this->_jobs)) {
            $this->_jobs = Mage::registry('pagarme_jobs');
        }
        return $this->_jobs;
    }
}