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
class SavvyCube_Connector_Model_Api_Transaction extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $_mainTable = 'sales_payment_transaction';

    protected $_order = 'payment_table.entity_id';

    /**
     * Render response
     *
     * @return array
     */
    public function getMethod()
    {
        $sql = $this->getHelper()->getDbRead()->select()
            ->from(array('main_table' => $this->getHelper()->getTableName($this->_mainTable)))
            ->joinLeft(
                array('payment_table' => $this->getHelper()->getTableName('sales_flat_order_payment')),
                "main_table.payment_id = payment_table.entity_id"
            )
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns($this->columnsListForGet());

        $this->_data = $this->getResult($sql, '`main_table`.created_at');
        return true;
    }

    /**
     * Return columns list for getMethod select
     *
     * @return string | array
     */
    public function columnsListForGet()
    {
        return array_merge(
            $this->prepareColumns(
                array(
                    'parent_id',
                    'entity_id',
                    'method',
                    'last_trans_id'
                ),
                'sales_flat_order_payment',
                'payment_table',
                array(
                    'parent_id' => 'order_id'
                )
            ),
            $this->prepareColumns(
                array(
                    'transaction_id',
                    'txn_id',
                    'parrent_txn_id',
                    'txn_type',
                    'is_closed',
                    'created_at'
                ),
                $this->_mainTable,
                'main_table'
            )
        );
    }
}
