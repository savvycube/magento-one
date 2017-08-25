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
class SavvyCube_Connector_Model_Api_Quoteaddress extends SavvyCube_Connector_Model_Api_Abstract
{

    protected $_mainTable = 'sales_flat_quote_address';

    protected $_order = 'main_table.address_id';

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
                'base_shipping_discount_amount',
                'base_hidden_tax_amount',
                'base_shipping_hidden_tax_amnt',
                'base_shipping_hidden_tax_amount',
                'base_tax_amount',
                'base_shipping_tax_amount',
                'base_shipping_amount',
                'base_subtotal',
                'base_grand_total',
                'weight',
                'created_at',
                'updated_at',
                'customer_id',
                'quote_id',
                'address_id',
                'address_type',
                'city',
                'country_id',
                'discount_description',
                'email',
                'firstname',
                'free_shipping',
                'lastname',
                'middlename',
                'postcode',
                'prefix',
                'region',
                'region_id',
                'shipping_description',
                'shipping_method',
                'suffix'
            ),
            $this->_mainTable,
            'main_table',
            array(
                'base_discount_amount' => 'discount_amount',
                'base_shipping_discount_amount' => 'shipping_discount_amount',
                'base_hidden_tax_amount' => 'hidden_tax_amount',
                'base_shipping_hidden_tax_amnt' => 'shipping_hidden_tax_amnt',
                'base_shipping_hidden_tax_amount' => 'shipping_hidden_tax_amnt',
                'base_tax_amount' => 'tax_amount',
                'base_shipping_tax_amount' => 'shipping_tax_amount',
                'base_shipping_amount' => 'shipping_amount',
                'base_subtotal' => 'subtotal',
                'base_grand_total' => 'grand_total',
            )
        );
    }


}
