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
class SavvyCube_Connector_Helper_Authorization extends Mage_Core_Helper_Abstract
{

    const NONCE_TTL = 900; # 15 min

    const TIMESTAMP_GAP = 600; # 10 min

    const SESSION_TTL = 600; # 10 min

    protected $_sc_rsa = null;

    protected $_rsa = null;

    protected $_c_rsa = null;

    public function getActivationUrl()
    {
        $baseUrl = Mage::getStoreConfig('w_cube/settings/base_url', 0);
        $adminUrl = Mage::helper("adminhtml")
            ->getUrl("adminhtml/Savvycube/activate");
        return Mage::getStoreConfig('w_cube/settings/savvy_url')
        . "account/connect-login?"
        . "&type=m1"
        . "&url=" . urlencode($baseUrl)
        . "&admin_url=" . urlencode($adminUrl)
        . "&session=" . Mage::getStoreConfig('w_cube/settings/candidate_ts', 0)
        . "&pub=" . base64_encode($this->getCandidatePublicKey());
    }

    public function cleanCache()
    {
        Mage::getConfig()->cleanCache();
        Mage::app()->reinitStores();
    }

    public function generateKeys()
    {
        $keys = $this->getRsa()->createKey(2048);
        $this->setCandidatePublicKey($keys['publickey']);
        $this->setCandidatePrivateKey($keys['privatekey']);
        $this->_c_rsa = null;
    }

    public function candidateSignature($session)
    {
        if ($session == Mage::getStoreConfig('w_cube/settings/candidate_ts', 0)
            && time() - Mage::getStoreConfig('w_cube/settings/candidate_ts', 0) < 120
        ) {
            $rsa = $this->getCandidateRsa();
            $iv = crypt_random_string(10);
            return array(base64_encode($iv),
                base64_encode($rsa->sign($iv)));
        }
        return False;
    }

    public function promoteCandidateKeys($session)
    {
        if ($session == Mage::getStoreConfig('w_cube/settings/candidate_ts', 0)
            && time() - Mage::getStoreConfig('w_cube/settings/candidate_ts', 0) < 120
        ) {
            $this->setPublicKey($this->getCandidatePublicKey());
            $this->setPrivateKey($this->getCandidatePrivateKey());
            $this->setCandidatePublicKey('');
            $this->setCandidatePrivateKey('');
            Mage::getConfig()->saveConfig('w_cube/settings/candidate_ts', 0, 'default', 0);
            $this->_rsa = null;
            $this->_c_rsa = null;
            return True;
        }
        return False;
    }

    public function getCandidatePublicKey()
    {
        return Mage::getStoreConfig('w_cube/settings/candidate_pub', 0);
    }

    public function setCandidatePublicKey($val)
    {
        Mage::getConfig()->saveConfig('w_cube/settings/candidate_pub', $val, 'default', 0);
    }

    public function getCandidatePrivateKey()
    {
        return Mage::helper('core')->decrypt(
            Mage::getStoreConfig('w_cube/settings/candidate_priv', 0));
    }

    public function setCandidatePrivateKey($val)
    {
        $val = Mage::helper('core')->encrypt($val);
        Mage::getConfig()->saveConfig('w_cube/settings/candidate_priv', $val, 'default', 0);
        Mage::getConfig()->saveConfig('w_cube/settings/candidate_ts', time(), 'default', 0);
    }


    public function getPublicKey()
    {
        return Mage::getStoreConfig('w_cube/settings/pub', 0);
    }

    public function setPublicKey($val)
    {
        Mage::getConfig()->saveConfig('w_cube/settings/pub', $val, 'default', 0);
    }

    public function getPrivateKey()
    {
        return Mage::getStoreConfig('w_cube/settings/priv', 0);
    }

    public function setPrivateKey($val)
    {
        $val = Mage::helper('core')->encrypt($val);
        Mage::getConfig()->saveConfig('w_cube/settings/priv', $val, 'default', 0);
    }

    public function getToken()
    {
        return Mage::getStoreConfig('w_cube/settings/token', 0);
    }

    public function setToken($token)
    {
        Mage::getConfig()->saveConfig('w_cube/settings/token', $token, 'default', 0);
    }

    public function registerAutoloader()
    {
        $libDir = Mage::getBaseDir('lib');
        $autoloader = $libDir . DS . implode(DS, array('sc', 'connector', 'vendor', 'autoload.php'));
        return require_once($autoloader);
    }


    public function getScRsa()
    {
        if (!isset($this->_sc_rsa)) {
            $this->registerAutoloader();
            $this->_sc_rsa = new Crypt_RSA();
            $this->_sc_rsa->loadKey($this->getToken());
            #$this->_sc_rsa->setHash('sha256');
            $this->_sc_rsa->setSaltLength(128);
            #$this->_sc_rsa->setMGFHash('sha256');
        }

        return $this->_sc_rsa;
    }

    public function getCandidateRsa()
    {
        if (!isset($this->_c_rsa)) {
            $this->registerAutoloader();
            $this->_c_rsa = new Crypt_RSA();
            $this->_c_rsa->loadKey($this->getCandidatePrivateKey());
            #$this->_c_rsa->setHash('sha256');
            $this->_c_rsa->setSaltLength(128);
            #$this->_c_rsa->setMGFHash('sha256');
        }

        return $this->_c_rsa;
    }

    public function getRsa()
    {
        if (!isset($this->_rsa)) {
            $this->registerAutoloader();
            $this->_rsa = new Crypt_RSA();
            $this->_rsa->loadKey($this->getPrivateKey());
            #$this->_rsa->setHash('sha256');
            $this->_rsa->setSaltLength(128);
            #$this->_rsa->setMGFHash('sha256');
        }

        return $this->_rsa;
    }

    public function encrypt($key, $data)
    {
        $this->registerAutoloader();
        $cipher = new Crypt_AES();
        $cipher->setKey($key);
        $iv = crypt_random_string($cipher->getBlockLength() >> 3);
        $cipher->setIV($iv);
        return array($iv, base64_encode($cipher->encrypt($data)));
    }

    public function verifySignature($base_str, $sig)
    {
        return $this->getScRsa()->verify($base_str, base64_decode($sig));
    }

    public function auth($request)
    {
        $baseUrl = Mage::getStoreConfig('w_cube/settings/base_url', 0);
        $method = strtoupper($request->getMethod());
        $url = strtolower(rtrim($baseUrl, '/') . $request->getOriginalPathInfo());
        $params_base = array();
        $params = $request->getParams();
        ksort($params, SORT_STRING);
        foreach ($params as $key=>$value) {
            $params_base[] = $key . "=" . $value;
        }
        $params_base = implode('&', $params_base);
        $nonce = $request->getHeader('SC-NONCE');
        $timestamp = $request->getHeader('SC-TIMESTAMP');
        $sig = $request->getHeader('SC-AUTHORIZATION');
        if ($nonce && $timestamp && $sig) {
            $base_str = implode('&', array($method, $url, $params_base, $nonce, $timestamp));
            return $this->checkTimestamp($timestamp)
                && $this->checkNonce($nonce)
                && $this->verifySignature($base_str, $sig);
        }
        return False;
    }

    public function checkTimestamp($timestamp)
    {
        return abs(time() - (int)$timestamp) < self::TIMESTAMP_GAP;
    }

    public function checkNonce($nonce)
    {
        $nonce = (int)$nonce;
        $resource = Mage::getSingleton('core/resource');
        $nonceTable = $resource->getTableName('wCube/nonce');
        $select = $resource->getConnection('core_read')->select();
        $select->from($nonceTable, 'nonce')
            ->where('nonce = ?', $nonce)
            ->where('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) < ?', self::NONCE_TTL);
        $duplicate = $resource->getConnection('core_read')->fetchOne($select);
        if (!$duplicate) {
            $resource->getConnection('core_write')
                ->insert($nonceTable, array('nonce' => $nonce));
            return true;
        }
        return false;
    }

    public function cleanNonce()
    {
        $resource = Mage::getSingleton('core/resource');
        $nonceTable = $resource->getTableName('wCube/nonce');
        $resource->getConnection('core_write')
            ->delete($nonceTable,
                array('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) > ?' => self::NONCE_TTL)
            );
    }

    public function cleanSession()
    {
        $resource = Mage::getSingleton('core/resource');
        $sessionTable = $resource->getTableName('wCube/session');
        $resource->getConnection('core_write')
            ->delete($sessionTable,
                array('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) > ?' => self::SESSION_TTL)
            );
    }

    public function createSession($key)
    {
        $session = uniqid('session_');
        $resource = Mage::getSingleton('core/resource');
        $sessionTable = $resource->getTableName('wCube/session');
        $resource->getConnection('core_write')
            ->insert($sessionTable, array('session' => $session, 'key' => $key));
        return $session;
    }

    public function getKeyBySession($session)
    {
        $resource = Mage::getSingleton('core/resource');
        $sessionTable = $resource->getTableName('wCube/session');
        $select = $resource->getConnection('core_read')->select();
        $select->from($sessionTable, 'key')
            ->where('session = ?', $session)
            ->where('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) < ?', self::SESSION_TTL);
        $key = $resource->getConnection('core_read')->fetchOne($select);
        if ($key)
            return $this->cleanKey($key);
        return False;
    }

    public function cleanKey($key)
    {
        return $this->getRsa()->decrypt(base64_decode($key));
    }


}
