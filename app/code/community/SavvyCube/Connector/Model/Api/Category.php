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
class SavvyCube_Connector_Model_Api_Category extends SavvyCube_Connector_Model_Api_Abstract
{

    protected $_categories;

    public function getMethod()
    {
        $result = array();
        $count = (int)$this->_request['count'];
        $offset = (int)$this->_request['offset'];
        $storeId = (int)$this->_request['store'];
        $store = Mage::app()->getStore($storeId);
        $initialEnvironmentInfo = Mage::getSingleton('core/app_emulation')->startEnvironmentEmulation($store->getId());

        $categoryCollection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('updated_at');
        if (isset($this->_request['from'])) {
            $categoryCollection->getSelect()
                ->where(
                    "updated_at >= ?",
                    $this->_request['from']
                );
        }

        if (isset($this->_request['to'])) {
            $categoryCollection->getSelect()
                ->where(
                    "updated_at <= ?",
                    $this->_request['to']
                );
        }

        $start = microtime(true);
        $categories = $categoryCollection->getItems();
        $this->_queryTime += microtime(true) - $start;

        foreach ($categories as $id => $category) {
            $result[$id] = $this->processCategory(
                $category,
                $store
            );
        }

        Mage::getSingleton('core/app_emulation')->stopEnvironmentEmulation($initialEnvironmentInfo);

        $this->_count = count($result);
        $this->_data = $result;
        return true;
    }

    protected function processCategory($category, $store)
    {
        $result['entity_id'] = $category->getEntityId();
        $result['store_id'] = $store->getId();
        $result['name'] = $this->getFullCategoryPath($category->getId(), $store);
        $result['created_at'] = $category->getCreatedAt();
        $result['updated_at'] = $category->getUpdatedAt();
        return $result;
    }

    protected function getFullCategoryPath($catId, $store)
    {
        $result = array();
        if (!isset($this->_categories[$store->getId()])) {
             $collection = Mage::getModel('catalog/category')->getCollection()
                ->setStoreId($store->getId())
                ->addAttributeToSelect('name');
             $orFilter = array();
             if ($store->getRootCategoryId() != 0) {
                 $rootCategory = $store->getRootCategoryId();
                 $orFilter[] = array('attribute' => 'path', 'like' => "1/{$rootCategory}");
                 $orFilter[] = array('attribute' => 'path', 'like' => "1/{$rootCategory}/%");
                 $orFilter[] = array('attribute' => 'parent_id', 'eq' => 0);
                 $collection->addAttributeToFilter($orFilter);
             }

             $this->_categories[$store->getId()] = $collection->getItems();
        }

        $categories = $this->_categories[$store->getId()];
        if (isset($categories[$catId])) {
            foreach ($categories[$catId]->getPathIds() as $id) {
                if (isset($categories[$id])) {
                    $result[] = $categories[$id]->getName();
                } else {
                    $result[] = 'Unknown';
                }
            }
        }

        if (isset($categories[$store->getRootCategoryId()])) {
            $rootCategory = $categories[$store->getRootCategoryId()];
            foreach ($rootCategory->getPathIds() as $id) {
                if (isset($categories[$id])) {
                    $prefix = $categories[$id]->getName();
                } else {
                    $prefix = 'Unknown';
                }

                if (!empty($result) && $result[0] == $prefix) {
                    array_shift($result);
                } else {
                    break;
                }
            }
        }

        return implode('/', $result);
    }

    public function init($params)
    {
        parent::init($params);
        $this->_request['store'] = array_key_exists('store', $params) ? $params['store'] : 0;
        return $this;
    }

}
