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
class SavvyCube_Connector_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_dbRead;

    protected $_resource;

    protected $_tableName;

    protected $_categories;

    /**
     * return module log name
     *
     * @return string
     */
    public function getErrorLog()
    {
        return 'wcube-error.log';
    }

    public function getCurrentModuleVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->SavvyCube_Connector->version;
    }

    public function getVersionData()
    {
        return array(
            new Varien_Object(
                array(
                    'id' => 'current_version',
                    'name' => 'Connector version:',
                    'version' => $this->getCurrentModuleVersion()
                )
            )
        );
    }

    public function addAdminNotification($title, $description)
    {
        /** @var Mage_AdminNotification_Model_Inbox $inbox */
        $inbox = Mage::getModel('adminNotification/inbox');
        $inbox->add(
            Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR,
            $title,
            $description
        );
    }

    /**
     * get db read adapter
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getDbRead()
    {
        if (!$this->_dbRead) {
            $this->_dbRead = $this->getResource()->getConnection('core_read');
        }

        return $this->_dbRead;
    }

    /**
     * get db resource object
     *
     * @return Mage_Core_Model_Resource
     */
    public function getResource()
    {
        if (!$this->_resource) {
            $this->_resource = Mage::getSingleton('core/resource');
        }

        return $this->_resource;
    }

    /**
     * return table name with prefix
     *
     * @param string $name table name without prefix
     *
     * @return string
     */
    public function getTableName($name)
    {
        if (!isset($this->_tableName[$name])) {
            $this->_tableName[$name] = $this->getResource()->getTableName($name);
        }

        return $this->_tableName[$name];
    }

    public function getRelativeCategoryPath($catId, $store)
    {
        $result = array();
        if (!isset($this->_categories)) {
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('name');
            $this->_categories = $collection->getItems();
        }

        $categories = $this->_categories;
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

    public function getFullCategoryPath($categoryId)
    {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $result = "";
        if ($category->getId()) {
            $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('entity_id', array('in' => $category->getPathIds()))
                ->getItems();
            foreach ($category->getPathIds() as $id) {
                if (isset($categories[$id])) {
                    $result .= $categories[$id]['name'] . "/";
                } else {
                    $result .= 'Unknown' . "/";
                }
            };
        }

        return $result;

    }
}
