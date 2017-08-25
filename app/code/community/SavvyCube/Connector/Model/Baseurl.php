<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@savvycube.com so we can send you a copy immediately.
 *
 * @category   SavvyCube
 * @package    SavvyCube_Connector
 * @copyright  Copyright (c) 2017 SavvyCube
 * SavvyCube is a trademark of Webtex Solutions, LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
                'w_cube/settings/base_url', $baseUrl, 'default', 0
            );
            Mage::getConfig()->cleanCache();
            Mage::app()->reinitStores();
        }
    }
}
