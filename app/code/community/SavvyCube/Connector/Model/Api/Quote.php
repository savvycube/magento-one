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
class SavvyCube_Connector_Model_Api_Quote extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $_mainTable = 'sales_flat_quote';

    /**
     * Render response
     *
     * @return array
     */
    public function getMethod()
    {
        $this->_data = $this->getResult(
            $this->generateQuery()
                ->join(
                    array('with_items' => $this->generateSubQuery()),
                    "main_table.entity_id = with_items.quote_id"
                )
                ->reset(Varien_Db_Select::COLUMNS)
                ->columns($this->columnsListForGet()),
            'main_table.updated_at'
        );
        return true;
    }

    public function generateSubQuery()
    {
        $query = $this->getHelper()->getDbRead()->select()
            ->distinct()
            ->from($this->getHelper()->getTableName('sales_flat_quote_item'))
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns('quote_id');

        return $query;
    }

    /**
     * Return columns list for getMethod select
     *
     * @return string | array
     */
    public function columnsListForGet()
    {
        return $this->prepareColumns(
            array(
                    'base_subtotal',
                    'base_grand_total',
                    'base_currency_code',
                    'base_to_global_rate',
                    'base_to_quote_rate',
                    'global_currency_code',
                    'quote_currency_code',
                    'store_currency_code',
                    'store_to_base_rate',
                    'store_to_quote_rate',
                    'checkout_method',
                    'coupon_code',
                    'customer_email',
                    'customer_firstname',
                    'customer_id',
                    'customer_group_id',
                    'customer_is_guest',
                    'customer_lastname',
                    'customer_middlename',
                    'customer_prefix',
                    'customer_suffix',
                    'customer_taxvat',
                    'is_active',
                    'is_changed',
                    'is_virtual',
                    'entity_id',
                    'reserved_order_id',
                    'store_id',
                    'updated_at',
                    'created_at'
                ),
            $this->_mainTable,
            'main_table',
            array(
                    'base_subtotal' => 'subtotal',
                    'base_grand_total' => 'grand_total'
                )
        );
    }
}
