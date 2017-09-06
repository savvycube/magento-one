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

    protected $_categories;

    public function getMethod()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $result = array();
        $count = (int)$this->_request['count'];
        $offset = (int)$this->_request['offset'];
        $storeId = (int)$this->_request['store'];
        $store = Mage::app()->getStore($storeId);

        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()->setStoreId($storeId);
        $productCollection->removeAttributeToSelect()
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
            );

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
                    'created_at' => 'max(cat.created_at)',
                    'updated_at' => 'max(cat.updated_at)',
                    'categories' => 'group_concat(cat_prod.category_id separator ",")',
                    'product_id' => 'cat_prod.product_id'
                )
            )
            ->group('cat_prod.product_id');


        $productCollection->getSelect()
            ->joinLeft(
                array('cat_sum' => $catSubquery),
                'cat_sum.product_id = e.entity_id',
                array(
                    'max_cat_created_at' => 'cat_sum.created_at',
                    'max_cat_updated_at' => 'cat_sum.updated_at',
                    'categories' => 'cat_sum.categories'
                )
            );



        if ($store->getWebsiteId() != 0) {
            $website = $store->getWebsiteId();
            $productCollection
            ->joinTable(
                array('website' => 'catalog/product_website'),
                'product_id=entity_id',
                array('website_id'),
                "website_id = {$website}"
            );
        }

        $productCollection->getSelect()->limit($count, $offset);

        $prodDate = 'e.updated_at';
        $catDate = 'cat_sum.updated_at';

        if (isset($this->_request['from'])) {
            $productCollection->getSelect()
                ->where(
                    "{$prodDate} >= ? OR {$catDate} >= ?",
                    $this->_request['from']
                );
        }

        if (isset($this->_request['to'])) {
            $productCollection->getSelect()
                ->where(
                    "{$prodDate} <= ? OR {$catDate} <= ?",
                    $this->_request['to']
                );
        }

        $start = microtime(true);
        $products = $productCollection->getItems();
        $this->_queryTime += microtime(true) - $start;

        foreach ($products as $id => $product) {
            $result[$id] = $this->processProduct(
                $product,
                $store
            );
        }

        $this->_count = count($result);
        $this->_data = $result;
        return true;
    }

    protected function processProduct($product, $store)
    {
        $result['entity_id'] = $product->getEntityId();
        $result['store_id'] = $store->getId();
        $result['attribute_set'] = $product->getAttributeSetName();
        $result['type_id'] = $product->getTypeId();
        $result['sku'] = $product->getSku();
        $result['name'] = $product->getName();
        $result['status'] = $product->getAttributeText('status');
        $result['visibility'] = $product->getAttributeText('visibility');
        $result['url_key'] = $product->getUrlKey();
        $result['msrp'] = $product->getMsrp();
        $result['created_at'] = $product->getCreatedAt();
        $result['updated_at'] = $product->getUpdatedAt();
        $result['max_cat_created_at'] = $product->getMaxCatCreatedAt();
        $result['max_cat_updated_at'] = $product->getMaxCatUpdatedAt();
        if ($product->getCategories()) {
            foreach(explode(',', $product->getCategories()) as $category)
                $result['categories'][$category] = $this->getFullCategoryPath(
                    $category,
                    $store
                );
        } else {
            $result['categories'] = array();
        }

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
