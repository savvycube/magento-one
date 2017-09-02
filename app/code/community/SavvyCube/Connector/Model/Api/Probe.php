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
class SavvyCube_Connector_Model_Api_Probe extends SavvyCube_Connector_Model_Api_Abstract
{

    protected $_categories;

    /**
     * Render response on savvycube/api/probe get query
     *
     * @return array
     */
    public function getMethod()
    {
        /** @var SavvyCube_Connector_Helper_Data $helper */
        $helper = Mage::helper('wCube');
        $currentVersion = $helper->getCurrentModuleVersion();
        $currentTimezone = Mage::app()->getDefaultStoreView()->getConfig('general/locale/timezone');

        $bottomOrderSql = $this->getHelper()->getDbRead()->select()
            ->from(array('ent' => $this->getHelper()->getTableName('sales_flat_order')))
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns('MIN(created_at) AS bottom_date');

        $bottomQuoteSql = $this->getHelper()->getDbRead()->select()
            ->from(array('ent' => $this->getHelper()->getTableName('sales_flat_quote')))
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns('MIN(created_at) AS bottom_date');

        $bottomCustomerSql = $this->getHelper()->getDbRead()->select()
            ->from(array('ent' => $this->getHelper()->getTableName('customer_entity')))
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns('MIN(created_at) AS bottom_date');

        $bottomProductSql = $this->getHelper()->getDbRead()->select()
            ->from(array('ent' => $this->getHelper()->getTableName('catalog_product_entity')))
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns('MIN(created_at) AS bottom_date');

        $start = microtime(true);
        $bottomDate = $this->getHelper()->getDbRead()->fetchOne($bottomOrderSql);
        $bottomQuoteDate = $this->getHelper()->getDbRead()->fetchOne($bottomQuoteSql);
        $bottomCustomerDate = $this->getHelper()->getDbRead()->fetchOne($bottomCustomerSql);
        $bottomProductDate = $this->getHelper()->getDbRead()->fetchOne($bottomProductSql);
        $this->_queryTime += microtime(true) - $start;

        $utcTimestamp = new DateTime(null, new DateTimeZone('UTC'));

        $this->_data =  array(
            'module_version' => $currentVersion,
            'magento_version' => Mage::getVersion(),
            'source_bottom' => min($bottomDate, $bottomQuoteDate, $bottomCustomerDate, $bottomProductDate),
            'utc_timestamp' => $utcTimestamp->format("Y-m-d H:i:s"),
            'timezone' => $currentTimezone,
            'stores' => $this->getStores(),
            'store_limits' => $this->getStoreLimits(),
            'limits' => $this->getLimits()
        );
        return true;
    }

    public function getStores()
    {
        $configValues = array(
            'base_url' => 'web/unsecure/base_url',
            'secure_base_url' => 'web/secure/base_url',
            'ga_property_id' => 'google/analytics/account',
            'ga_active' => 'google/analytics/active',
            'ga_ip_anonymization' => 'google/analytics/anonymization',
            'ga_type' => 'google/analytics/type'
        );
        $stores = Mage::app()->getStores(true, true);
        foreach ($stores as $code => $store) {
            $storeData = array();
            $storeData['store_id'] = $store->getId();
            $storeData['store_code'] = $code;
            $storeData['store_name'] = $store->getName();
            $storeData['is_default_store'] = (bool)$store->getId() ==
                $store->getWebsite()->getDefaultStore()->getId();
            $storeData['website_id'] = $store->getWebsite()->getId();
            $storeData['website_code'] = $store->getWebsite()->getCode();
            $storeData['website_name'] = $store->getWebsite()->getName();
            $storeData['is_default_website'] = (bool)$store->getWebsite()
                ->getIsDefault();
            $storeData['root_category_id'] = $store->getRootCategoryId();
            $storeData['root_category'] = $this->getCategoryName(
                $store->getRootCategoryId()
            );
            foreach($configValues as $name => $path)
                $storeData[$name] = Mage::getStoreConfig($path, $store);

            $result[$code] = $storeData;
        }

        $this->_count = count($result);
        return $result;
    }

    protected function getCategoryName($catId)
    {
        if (!isset($this->_categories[$catId])) {
            $this->_categories[$catId] =
                Mage::helper('wCube')->getFullCategoryPath($catId);
        }

        return $this->_categories[$catId];
    }

    public function getStoreLimits()
    {
        $result = array();
        $stores = Mage::app()->getStores(true, true);
        foreach ($stores as $code => $store) {
            # category
            $initialEnvironmentInfo = Mage::getSingleton('core/app_emulation')
                ->startEnvironmentEmulation($store->getId());
            $query = Mage::getModel('catalog/category')
                ->getCollection()->getSelect()
                ->reset(Varien_Db_Select::COLUMNS)
                ->columns(array('max(updated_at) as max', 'min(updated_at) as min'));
            $start = microtime(true);
            $result[$store->getCode()]['category'] = $this->getHelper()->getDbRead()->fetchRow($query);
            $this->_queryTime += microtime(true) - $start;
            Mage::getSingleton('core/app_emulation')->stopEnvironmentEmulation($initialEnvironmentInfo);
            # product
            $prodCollection = Mage::getModel('catalog/product')
                ->getCollection()->setStoreId($store->getId());
            $db = Mage::getModel('core/resource')->getConnection('core_read');

            $categoryTable = Mage::getSingleton('core/resource')
                ->getTableName('catalog/category');
            $categoryProdTable = Mage::getSingleton('core/resource')
                ->getTableName('catalog/category_product');

            $catSubquery = $db->select()
                ->from(array('cat_prod' => $categoryProdTable))
                ->joinLeft(
                    array('cat' => $categoryTable),
                    'cat.entity_id = cat_prod.category_id'
                )
                ->reset(Varien_Db_Select::COLUMNS)
                ->columns(
                    array(
                        'cat_updated_at' => 'max(cat.updated_at)',
                        'product_id' => 'cat_prod.product_id'
                    )
                )
                ->group('cat_prod.product_id');


            $prodCollection->getSelect()
                ->joinLeft(
                    array('cat_sum' => $catSubquery),
                    'cat_sum.product_id = e.entity_id',
                    array()
                );



            if ($store->getWebsiteId() != 0) {
                $website = $store->getWebsiteId();
                $prodCollection->joinTable(
                    array('website' => 'catalog/product_website'),
                    'product_id=entity_id',
                    array('website_id'),
                    "website_id = {$website}"
                );
            }

            $query = $prodCollection->getSelect()
                ->reset(Varien_Db_Select::COLUMNS)
                ->columns(
                    array(
                    'max(greatest(updated_at, cat_updated_at)) as max',
                    'min(least(updated_at, cat_updated_at)) as min')
                );

            $start = microtime(true);
            $result[$store->getCode()]['product'] = $this->getHelper()->getDbRead()->fetchRow($query);
            $this->_queryTime += microtime(true) - $start;
        }

        return $result;
    }

    public function getLimits()
    {
        $result = array();
        # customer
        $result['customer'] = $this->getLimit('customer_entity', 'updated_at');
        # order
        $result['order'] = $this->getLimit('sales_flat_order', 'updated_at');
        # quote
        $result['quote'] = $this->getQuoteLimit();
        # invoice
        $result['invoice'] = $this->getLimit('sales_flat_invoice', 'updated_at');
        #creditmemo
        $result['refund'] = $this->getLimit('sales_flat_creditmemo', 'updated_at');
        # shipment
        $result['shipment'] = $this->getLimit('sales_flat_shipment', 'updated_at');
        # url_rewrite
        $result['rewrite'] = $this->getLimit('core_url_rewrite', 'url_rewrite_id');
        # transaction
        $result['transaction'] = $this->getLimit('sales_payment_transaction', 'created_at');
        return $result;
    }

    public function getQuoteLimit()
    {
        $table = 'sales_flat_quote';
        $column = 'main_table.updated_at';
        $subQuery = $this->getHelper()->getDbRead()->select()
            ->distinct()
            ->from($this->getHelper()->getTableName('sales_flat_quote_item'))
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns('quote_id');
        $query = $this->getHelper()->getDbRead()->select()
            ->from(array('main_table' => $this->getHelper()->getTableName($table)))
            ->join(array('items' => $subQuery), 'main_table.entity_id = items.quote_id')
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns(array("max({$column}) as max", "min({$column}) as min"));
        $start = microtime(true);
        $result = $this->getHelper()->getDbRead()->fetchRow($query);
        $this->_queryTime += microtime(true) - $start;
        return $result;
    }

    public function getLimit($table, $column)
    {
        $query = $this->getHelper()->getDbRead()->select()
            ->from($this->getHelper()->getTableName($table))
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns(array("max({$column}) as max", "min({$column}) as min"));
        $start = microtime(true);
        $result = $this->getHelper()->getDbRead()->fetchRow($query);
        $this->_queryTime += microtime(true) - $start;
        return $result;
    }

    public function init($params)
    {
        $this->_request = array();
        return $this;
    }


}
