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
class SavvyCube_Connector_Model_Resource_Main extends Mage_Core_Model_Resource
{
    const NONCE_TTL = 900; # 15 min

    const SESSION_TTL = 600; # 10 min

    public function checkNonce($nonce)
    {
        $nonce = (int)$nonce;
        $nonceTable = $this->getTableName('wCube/nonce');
        $select = $this->getConnection('core_read')->select();
        $select->from($nonceTable, 'nonce')
            ->where('nonce = ?', $nonce)
            ->where('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) < ?', self::NONCE_TTL);
        $duplicate = $this->getConnection('core_read')->fetchOne($select);
        if (!$duplicate) {
            $this->getConnection('core_write')
                ->insert($nonceTable, array('nonce' => $nonce));
            return true;
        }

        return false;
    }

    public function cleanNonce()
    {
        $nonceTable = $this->getTableName('wCube/nonce');
        $this->getConnection('core_write')
            ->delete(
                $nonceTable,
                array('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) > ?' => self::NONCE_TTL)
            );
    }

    public function cleanSession()
    {
        $sessionTable = $this->getTableName('wCube/session');
        $this->getConnection('core_write')
            ->delete(
                $sessionTable,
                array('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) > ?' => self::SESSION_TTL)
            );
    }

    public function createSession($key)
    {
        $session = uniqid('session_');
        $sessionTable = $this->getTableName('wCube/session');
        $this->getConnection('core_write')
            ->insert($sessionTable, array('session' => $session, 'key' => $key));
        return $session;
    }

    public function getKeyBySession($session)
    {
        $sessionTable = $this->getTableName('wCube/session');
        $select = $this->getConnection('core_read')->select();
        $select->from($sessionTable, 'key')
            ->where('session = ?', $session)
            ->where('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) < ?', self::SESSION_TTL);
        $key = $this->getConnection('core_read')->fetchOne($select);
        if ($key)
            return $key;
        return False;
    }

}
