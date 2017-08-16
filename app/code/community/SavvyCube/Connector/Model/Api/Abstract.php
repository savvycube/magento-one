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
 * @copyright  Copyright (c) 2014 SavvyCube (http://www.savvycube.com). SavvyCube is a trademark of Webtex Solutions, LLC (http://www.webtexsoftware.com).
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class SavvyCube_Connector_Model_Api_Abstract
{
    protected $request;

    protected $queryTime = 0;

    protected $count = 0;

    protected $data = null;

    protected $mainTable = '';

    protected $parentEntity = array();

    protected $order = 'main_table.entity_id';

    public $error = null;

    /**
     * Render response
     *
     * @return array
     */
    public function getMethod()
    {
        if (!empty($this->parentEntity)) {
            $this->data = $this->getResult(
                $this->generateQuery()
                    ->joinLeft(
                        array('parent_table' => $this->parentEntity['table']),
                        "parent_table.entity_id = main_table.{$this->parentEntity['parent_fk']}"
                    )
                    ->reset(Varien_Db_Select::COLUMNS)
                    ->columns($this->columnsListForGet()),
                    'parent_table.updated_at'
            );
            return true;
        } else {
            $this->data = $this->getResult(
                $this->generateQuery()->columns($this->columnsListForGet()),
                '`main_table`.updated_at'
            );
            return true;
        }
    }

    public function generateQuery()
    {
        return $this->getHelper()->getDbRead()->select()
            ->from(array('main_table' => $this->getHelper()->getTableName($this->mainTable)))
            ->order($this->order)
            ->reset(Varien_Db_Select::COLUMNS);
    }

    /**
     * @param Zend_Db_Select $query
     * @param $dateColumn
     */
    public function applyDateLimit($query, $dateColumn)
    {
        $bind = array();

        if (isset($this->request['from'])) {
            $query->where("{$dateColumn} >= :fromDate");
            $bind[":fromDate"] = $this->request['from'];
        }
        if (isset($this->request['to'])) {
            $query->where("{$dateColumn} <= :toDate");
            $bind[":toDate"] = $this->request['to'];
        }

        $query->bind(array_merge($query->getBind(), $bind));
    }

    /**
     * init model and set $request array
     *
     * @param array $request
     *
     * @return $this
     */
    public function init($params)
    {
        $this->request = array();
        $this->request['offset'] = array_key_exists('offset', $params) ? $params['offset'] : 0;
        $this->request['count'] = array_key_exists('count', $params) ? $params['count'] : 100;
        $this->request['from'] = array_key_exists('from', $params) ? urldecode($params['from']) : null;
        $this->request['to'] = array_key_exists('to', $params) ? urldecode($params['to']) : null;
        return $this;
    }

    public function formatResponse($key)
    {
        Mage::app()->getResponse()->setHeader('sc-version',
            $this->getHelper()->getCurrentModuleVersion());
        Mage::app()->getResponse()->setHeader('sc-query-time', $this->queryTime);
        Mage::app()->getResponse()->setHeader('sc-report-count', $this->count);
        $options = 0;
        #if (defined('JSON_NUMERIC_CHECK')) {
        #    $options |= JSON_NUMERIC_CHECK;
        #}
        #if (defined('JSON_PRETTY_PRINT')) {
        #    $options |= JSON_PRETTY_PRINT;
        #}
        $data = json_encode($this->data, $options);
        list($iv, $encryptedData) = $this->getAuthHelper()->encrypt($key, $data);
        $signature = $this->getAuthHelper()->getRsa()->sign($encryptedData);
        Mage::app()->getResponse()->setHeader('Sc-Sig', base64_encode($signature));
        Mage::app()->getResponse()->setHeader('Sc-Iv', base64_encode($iv));
        Mage::app()->getResponse()->setBody($encryptedData);
    }

    protected function getResult($query, $dateColumn = false)
    {
        if ($dateColumn) {
            $this->applyDateLimit($query, $dateColumn);
        }
        $this->renderParameters($query);
        $start = microtime(true);
        $report = $this->getHelper()->getDbRead()->fetchAll($query, $query->getBind());
        $this->count = count($report);
        $this->queryTime += microtime(true) - $start;
        return $report;
    }

    /**
     * Render where condition by current request parameters
     *
     * @param Varien_Db_Select $sql select object
     */
    protected function renderParameters($sql)
    {
        if (isset($this->request['count']) && isset($this->request['offset'])) {
            $sql->limit($this->request['count'], $this->request['offset']);
        }
    }

    /**
     * @return SavvyCube_Connector_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('wCube');
    }

    protected function getAuthHelper()
    {
        return Mage::helper('wCube/authorization');
    }

    /**
     * Return columns list for getMethod select
     *
     * @return string | array
     */
    public function columnsListForGet()
    {
        return '*';
    }

    public function prepareColumns($columns, $table, $tableAlias = false, $aliases = array())
    {
        $result = array();
        $columns = array_flip($columns);
        if ($this->getHelper()->getDbRead()->isTableExists($table)) {
            $tableDescription = $this->getHelper()->getDbRead()->describeTable(
                $this->getHelper()->getTableName($table)
            );
            foreach ($tableDescription as $column) {
                if (isset($columns[$column['COLUMN_NAME']])) {
                    $result[isset($aliases[$column['COLUMN_NAME']])
                        ? $aliases[$column['COLUMN_NAME']]
                        : $column['COLUMN_NAME']]
                        = $tableAlias
                        ? "{$tableAlias}.{$column['COLUMN_NAME']}"
                        : $column['COLUMN_NAME'];
                }
            }
        }

        return $result;
    }
}
