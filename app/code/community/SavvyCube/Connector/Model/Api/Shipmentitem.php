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
class SavvyCube_Connector_Model_Api_Shipmentitem extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $_mainTable = 'sales_flat_shipment_item';

    protected $_parentEntity = array(
        'table' => 'sales_flat_shipment',
        'parent_fk' => 'parent_id'
    );

    /**
     * Return columns list for getMethod select
     *
     * @return string | array
     */
    public function columnsListForGet()
    {
        return $this->prepareColumns(
            array(
                'entity_id',
                'order_item_id',
                'parent_id',
                'product_id',
                'qty',
                'weight',
            ),
            $this->_mainTable,
            'main_table'
        );
    }
}
