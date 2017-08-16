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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$nonceTable = $installer->getConnection()->newTable(
    $installer->getTable('wCube/nonce')
)->addColumn(
    'nonce',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    array('nullable' => false),
    'nonce'
)->addColumn(
    'created_at',
    Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    null,
    array('default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT),
    'Created At'
);

$installer->getConnection()->createTable($nonceTable);

$sessionTable = $installer->getConnection()->newTable(
    $installer->getTable('wCube/session')
)->addColumn(
    'session',
    Varien_Db_Ddl_Table::TYPE_VARCHAR,
    40,
    array('nullable' => false),
    'session name'
)->addColumn(
    'key',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    null,
    array('nullable' => false),
    'encryption key'
)->addColumn(
    'created_at',
    Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    null,
    array('default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT),
    'Created At'
);

$installer->getConnection()->createTable($sessionTable);

$installer->endSetup();
