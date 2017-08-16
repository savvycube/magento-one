<?php

class SavvyCube_Connector_Model_Api_Abstract
class SavvyCube_Connector_Model_Baseurl extends Mage_Core_Model_Config_Data
{
    /**
     * Decrypt value after loading
     *
     */
    protected function _afterLoad()
    {
        $baseUrl = Mage::app()->getDefaultStoreView()->getBaseUrl();
        $value = (string)$this->getValue();
        if (!empty($value) && ($decrypted = Mage::helper('core')->decrypt($value))) {
            $this->setValue($decrypted);
        }
    }
}
