<?php

class SavvyCube_Connector_Model_Baseurl extends Mage_Core_Model_Config_Data
{
    /**
     * Decrypt value after loading
     *
     */
    protected function _afterLoad()
    {
        if (empty($this->getValue())) {
            $baseUrl = Mage::app()->getDefaultStoreView()->getBaseUrl();
            $this->setValue($baseUrl);
            Mage::getConfig()->saveConfig(
                'w_cube/settings/base_url', $baseUrl, 'default', 0);
            Mage::getConfig()->cleanCache();
            Mage::app()->reinitStores();
        }
    }
}
