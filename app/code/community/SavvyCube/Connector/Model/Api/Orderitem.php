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
class SavvyCube_Connector_Model_Api_Orderitem extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $_mainTable = 'sales_flat_order_item';

    protected $_order = 'main_table.item_id';

    /**
     * Return columns list for getMethod select
     *
     * @return string | array
     */
    public function columnsListForGet()
    {
        return $this->prepareColumns(
            array(
                'base_discount_amount',
                'base_discount_invoiced',
                'base_discount_refunded',
                'discount_percent',
                'base_hidden_tax_amount',
                'base_hidden_tax_invoiced',
                'base_hidden_tax_refunded',
                'base_tax_amount',
                'base_tax_invoiced',
                'base_tax_refunded',
                'qty_canceled',
                'qty_invoiced',
                'qty_ordered',
                'qty_refunded',
                'qty_returned',
                'qty_shipped',
                'base_row_invoiced',
                'base_row_total',
                'base_price',
                'base_cost',
                'base_original_price',
                'weight',
                'row_weight',
                'created_at',
                'updated_at',
                'item_id',
                'order_id',
                'parent_item_id',
                'product_id',
                'quote_item_id',
                'description',
                'free_shipping',
                'is_virtual',
                'name',
                'product_type',
                'sku'
            ),
            $this->_mainTable,
            'main_table',
            array(
                'base_discount_amount' => 'discount_amount',
                'base_discount_invoiced' => 'discount_invoiced',
                'base_discount_refunded' => 'discount_refunded',
                'base_hidden_tax_amount' => 'hidden_tax_amount',
                'base_hidden_tax_invoiced' => 'hidden_tax_invoiced',
                'base_hidden_tax_refunded' => 'hidden_tax_refunded',
                'base_tax_amount' => 'tax_amount',
                'base_tax_invoiced' => 'tax_invoiced',
                'base_tax_refunded' => 'tax_refunded',
                'base_row_invoiced' => 'row_invoiced',
                'base_row_total' => 'row_total',
                'base_price' => 'price',
                'base_cost' => 'cost',
                'base_original_price' => 'original_price',
            )
        );
    }
}
