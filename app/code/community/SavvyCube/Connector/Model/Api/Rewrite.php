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
class SavvyCube_Connector_Model_Api_Rewrite extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $_mainTable = 'core_url_rewrite';

    protected $_order = 'main_table.url_rewrite_id';

    /**
     * Render response
     *
     * @return array
     */
    public function getMethod()
    {
        $this->_data = $this->getResult(
            $this->generateQuery()->columns($this->columnsListForGet()),
            '`main_table`.url_rewrite_id'
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
        return $this->prepareColumns(
            array(
                'url_rewrite_id',
                'store_id',
                'category_id',
                'id_path',
                'request_path',
                'target_path',
                'product_id'
            ),
            $this->_mainTable,
            'main_table'
        );
    }
}
