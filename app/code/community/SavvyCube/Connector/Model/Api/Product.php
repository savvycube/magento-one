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
class SavvyCube_Connector_Model_Api_Product extends SavvyCube_Connector_Model_Api_Abstract
{

    public function getMethod()
    {
        $count = (int)$this->_request['count'];
        $offset = (int)$this->_request['offset'];
        $storeId = (int)$this->_request['store'];
        $store = Mage::app()->getStore($storeId);
        $initialEnvironmentInfo = Mage::getSingleton('core/app_emulation')
            ->startEnvironmentEmulation($store->getId());

        $collection = Mage::getModel('catalog/product')
            ->getCollection();

        if ($store->getWebsiteId() != 0) {
            $collection->addWebsiteFilter();
        }

        $db = Mage::getModel('core/resource')->getConnection('core_read');

        $categoryTable = Mage::getSingleton('core/resource')
            ->getTableName('catalog/category');
        $categoryProdTable = Mage::getSingleton('core/resource')
            ->getTableName('catalog/category_product');

        $treeRoot = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        $storeRoot = $store->getRootCategoryId();
        $catSubquery = $db->select()
            ->from(array('cat_prod' => $categoryProdTable))
            ->joinLeft(
                array('cat' => $categoryTable),
                'cat.entity_id = cat_prod.category_id'
            )
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns(
                array(
                    'created_at' => 'max(cat.created_at)',
                    'updated_at' => 'max(cat.updated_at)',
                    'categories' => 'group_concat(cat_prod.category_id separator ",")',
                    'product_id' => 'cat_prod.product_id'
                )
            )
            ->where('path like ?', "${treeRoot}/${storeRoot}%")
            ->group('cat_prod.product_id');

        $collection
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('url')
            ->addAttributeToSelect('msrp')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('url_key')
            ->joinField(
                'attribute_set_name',
                Mage::getModel('eav/entity_attribute_set')
                    ->getResource()->getMainTable(),
                'attribute_set_name',
                'attribute_set_id=attribute_set_id'
            )->setOrder('entity_id', 'ASC');

        $collection->getSelect()->limit($count, $offset);
        $collection->getSelect()
            ->joinLeft(
                array('cat_sum' => $catSubquery),
                'cat_sum.product_id = e.entity_id',
                array(
                    'max_cat_created_at' => 'cat_sum.created_at',
                    'max_cat_updated_at' => 'cat_sum.updated_at',
                    'categories' => 'cat_sum.categories'
                )
            );
        $collection->getSelect()->columns(array(
            'greatest_created' => 'GREATEST(COALESCE(cat_sum.created_at, 0), e.created_at)',
            'greatest_updated' => 'GREATEST(COALESCE(cat_sum.updated_at, 0), e.updated_at)'));

        if (isset($this->_request['from'])) {
            $collection->getSelect()
                ->where(
                    "GREATEST(COALESCE(cat_sum.updated_at, 0), e.updated_at) >= ?",
                    $this->_request['from']);
        }

        if (isset($this->_request['to'])) {
            $collection->getSelect()
                ->where(
                    "GREATEST(COALESCE(cat_sum.updated_at, 0), e.updated_at) <= ?",
                    $this->_request['to']);
        }

        $start = microtime(true);
        $products = $collection->getItems();
        $this->_queryTime += microtime(true) - $start;

        $data = array();

        foreach ($products as $id => $product) {
            $result = array(
                'entity_id' => $product->getEntityId(),
                'store_id' => $store->getId(),
                'attribute_set' => $product->getAttributeSetName(),
                'type_id' => $product->getTypeId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'status' => $product->getAttributeText('status'),
                'visibility' => $product->getAttributeText('visibility'),
                'url_key' => $product->getUrlKey(),
                'msrp' => $product->getMsrp(),
                'created_at' => $product->getGreatestCreated(),
                'updated_at' => $product->getGreatestUpdated(),

            );
            if ($product->getCategories()) {
                foreach(explode(',', $product->getCategories()) as $category)
                    $result['categories'][$category] = $this->getHelper()
                        ->getRelativeCategoryPath(
                            $category,
                            $store
                        );
            } else {
                $result['categories'] = array();
            }
            $data[$id] = $result;
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
