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


    public function getMethod()
    {
        $count = (int)$this->_request['count'];
        $offset = (int)$this->_request['offset'];
        $storeId = (int)$this->_request['store'];
        $store = Mage::app()->getStore($storeId);
        $data = array();
        $initialEnvironmentInfo = Mage::getSingleton('core/app_emulation')
            ->startEnvironmentEmulation($store->getId());
        $treeRoot = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        $storeRoot = $store->getRootCategoryId();
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('updated_at')
            ->addAttributeToFilter('path',
                array('like' => "$treeRoot/{$storeRoot}%")
            )
            ->setOrder('entity_id', 'ASC');
        $collection->getSelect()->limit($count, $offset);
        if (isset($this->_request['from'])) {
            $collection->getSelect()
                ->where(
                    "updated_at >= ?",
                    $this->_request['from']
                );
        }

        if (isset($this->_request['to'])) {
            $collection->getSelect()
                ->where(
                    "updated_at <= ?",
                    $this->_request['to']
                );
        }
        $start = microtime(true);
        $collection->getItems();
        $this->_queryTime += microtime(true) - $start;

        foreach($collection as $id => $category) {
            $data[$id] = array(
                'entity_id' => $category->getId(),
                'store_id' => $store->getId(),
                'name' => $this->getHelper()
                    ->getRelativeCategoryPath($category->getId(), $store),
                'full_name' => $this->getHelper()
                    ->getFullCategoryPath($category->getId()),
                'root' => $store->getRootCategoryId(),
                'created_at' => $category->getCreatedAt(),
                'updated_at' => $category->getUpdatedAt()
            );
        }
        $this->_count = count($data);
        $this->_data = $data;
        Mage::getSingleton('core/app_emulation')->stopEnvironmentEmulation($initialEnvironmentInfo);
        return true;
    }

    public function init($params)
    {
        parent::init($params);
        $this->_request['store'] = array_key_exists('store', $params) ? $params['store'] : 0;
        return $this;
    }

}
