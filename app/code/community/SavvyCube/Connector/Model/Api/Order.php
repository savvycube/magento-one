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
class SavvyCube_Connector_Model_Api_Order extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $_mainTable = 'sales_flat_order';

    /**
     * Render response
     *
     * @return array
     */
    public function getMethod()
    {
        $query = $this->generateQuery()
            ->joinLeft(
                array('payment' => $this->getHelper()
                    ->getTableName('sales/order_payment')),
                "main_table.entity_id = payment.parent_id"
            )
            ->joinLeft(
                array('st_label' => $this->getHelper()
                    ->getTableName('sales/order_status')),
                "main_table.status = st_label.status"
            );


        $this->_data =  $this->getResult(
            $query->columns($this->columnsListForGet()),
            '`main_table`.updated_at'
        );
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
                array('method'),
                $this->getHelper()->getTableName('sales/order_payment'),
                'payment',
                array('method' => 'payment_method')
            ),
            $this->prepareColumns(
                array('label'),
                $this->getHelper()->getTableName('sales/order_status'),
                'st_label',
                array('label' => 'status_label')
            ),
            $this->prepareColumns(
                array(
                    'base_discount_amount',
                    'base_discount_canceled',
                    'base_discount_invoiced',
                    'base_discount_refunded',
                    'base_shipping_discount_amount',
                    'base_hidden_tax_amount',
                    'base_hidden_tax_invoiced',
                    'base_hidden_tax_refunded',
                    'base_shipping_hidden_tax_amnt',
                    'base_shipping_hidden_tax_amount',
                    'base_tax_amount',
                    'base_tax_canceled',
                    'base_tax_invoiced',
                    'base_tax_refunded',
                    'base_shipping_tax_amount',
                    'base_shipping_tax_refunded',
                    'base_shipping_amount',
                    'base_shipping_canceled',
                    'base_shipping_invoiced',
                    'base_shipping_refunded',
                    'base_subtotal',
                    'base_subtotal_canceled',
                    'base_subtotal_invoiced',
                    'base_subtotal_refunded',
                    'base_adjustment_negative',
                    'base_adjustment_positive',
                    'base_grand_total',
                    'base_total_canceled',
                    'base_total_due',
                    'base_total_invoiced',
                    'base_total_offline_refunded',
                    'base_total_online_refunded',
                    'base_total_paid',
                    'base_total_refunded',
                    'weight',
                    'base_currency_code',
                    'base_to_global_rate',
                    'base_to_order_rate',
                    'global_currency_code',
                    'order_currency_code',
                    'store_currency_code',
                    'store_to_base_rate',
                    'store_to_order_rate',
                    'billing_address_id',
                    'shipping_address_id',
                    'entity_id',
                    'increment_id',
                    'store_id',
                    'quote_id',
                    'customer_id',
                    'coupon_code',
                    'coupon_rule_name',
                    'customer_email',
                    'customer_firstname',
                    'customer_gender',
                    'customer_group_id',
                    'customer_is_guest',
                    'customer_lastname',
                    'customer_middlename',
                    'customer_prefix',
                    'customer_suffix',
                    'customer_taxvat',
                    'discount_description',
                    'is_virtual',
                    'shipping_description',
                    'shipping_method',
                    'state',
                    'status',
                    'store_name',
                    'created_at',
                    'updated_at'
                ),
                $this->_mainTable,
                'main_table',
                array(
                    'base_discount_amount' => 'discount_amount',
                    'base_discount_canceled' => 'discount_canceled',
                    'base_discount_invoiced' => 'discount_invoiced',
                    'base_discount_refunded' => 'discount_refunded',
                    'base_shipping_discount_amount' => 'shipping_discount_amount',
                    'base_hidden_tax_amount' => 'hidden_tax_amount',
                    'base_hidden_tax_invoiced' => 'hidden_tax_invoiced',
                    'base_hidden_tax_refunded' => 'hidden_tax_refunded',
                    'base_shipping_hidden_tax_amnt' => 'shipping_hidden_tax_amnt',
                    'base_shipping_hidden_tax_amount' => 'shipping_hidden_tax_amnt',
                    'base_tax_amount' => 'tax_amount',
                    'base_tax_canceled' => 'tax_canceled',
                    'base_tax_invoiced' => 'tax_invoiced',
                    'base_tax_refunded' => 'tax_refunded',
                    'base_shipping_tax_amount' => 'shipping_tax_amount',
                    'base_shipping_tax_refunded' => 'shipping_tax_refunded',
                    'base_shipping_amount' => 'shipping_amount',
                    'base_shipping_canceled' => 'shipping_canceled',
                    'base_shipping_invoiced' => 'shipping_invoiced',
                    'base_shipping_refunded' => 'shipping_refunded',
                    'base_subtotal' => 'subtotal',
                    'base_subtotal_canceled' => 'subtotal_canceled',
                    'base_subtotal_invoiced' => 'subtotal_invoiced',
                    'base_subtotal_refunded' => 'subtotal_refunded',
                    'base_adjustment_negative' => 'adjustment_negative',
                    'base_adjustment_positive' => 'adjustment_positive',
                    'base_grand_total' => 'grand_total',
                    'base_total_canceled' => 'total_canceled',
                    'base_total_due' => 'total_due',
                    'base_total_invoiced' => 'total_invoiced',
                    'base_total_offline_refunded' => 'total_offline_refunded',
                    'base_total_online_refunded' => 'total_online_refunded',
                    'base_total_paid' => 'total_paid',
                    'base_total_refunded' => 'total_refunded'
                )
            )
        );
    }
}
