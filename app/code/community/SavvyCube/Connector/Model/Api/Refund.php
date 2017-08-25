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
class SavvyCube_Connector_Model_Api_Refund extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $_mainTable = 'sales_flat_creditmemo';

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
                'base_hidden_tax_amount',
                'base_shipping_hidden_tax_amnt',
                'base_shipping_hidden_tax_amount',
                'base_tax_amount',
                'base_shipping_tax_amount',
                'base_shipping_amount',
                'base_subtotal',
                'base_adjustment_negative',
                'base_adjustment_positive',
                'base_grand_total',
                'base_currency_code',
                'base_to_global_rate',
                'base_to_order_rate',
                'global_currency_code',
                'order_currency_code',
                'store_currency_code',
                'store_to_base_rate',
                'store_to_order_rate',
                'entity_id',
                'increment_id',
                'order_id',
                'transaction_id',
                'created_at',
                'updated_at',
            ),
            $this->_mainTable,
            'main_table',
            array(
                'base_discount_amount' => 'discount_amount',
                'base_hidden_tax_amount' => 'hidden_tax_amount',
                'base_shipping_hidden_tax_amnt' => 'shipping_hidden_tax_amnt',
                'base_shipping_hidden_tax_amount' => 'shipping_hidden_tax_amnt',
                'base_tax_amount' => 'tax_amount',
                'base_shipping_tax_amount' => 'shipping_tax_amount',
                'base_shipping_amount' => 'shipping_amount',
                'base_subtotal' => 'subtotal',
                'base_adjustment_negative' => 'adjustment_negative',
                'base_adjustment_positive' => 'adjustment_positive',
                'base_grand_total' => 'grand_total',
            )
        );
    }
}
