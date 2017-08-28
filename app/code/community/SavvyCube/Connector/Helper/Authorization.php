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
class SavvyCube_Connector_Helper_Authorization extends Mage_Core_Helper_Abstract
{
    const TIMESTAMP_GAP = 600; # 10 min

    protected $_scRsa = null;

    protected $_rsa = null;

    protected $_cRsa = null;

    public function getActivationUrl()
    {
        $baseUrl = Mage::getStoreConfig('w_cube/settings/base_url', 0);
        $adminUrl = Mage::helper("adminhtml")
            ->getUrl("adminhtml/savvycube/activate");
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
        $this->_cRsa = null;
    }

    public function candidateSignature($session)
    {
        $currentTs = (int)Mage::getSingleton('core/date')->gmtTimestamp();
        if ($session == Mage::getStoreConfig('w_cube/settings/candidate_ts', 0)
            && $currentTs - Mage::getStoreConfig('w_cube/settings/candidate_ts', 0) < 120
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
        $currentTs = (int)Mage::getSingleton('core/date')->gmtTimestamp();
        if ($session == Mage::getStoreConfig('w_cube/settings/candidate_ts', 0)
            && $currentTs - Mage::getStoreConfig('w_cube/settings/candidate_ts', 0) < 120
        ) {
            $this->setPublicKey($this->getCandidatePublicKey());
            $this->setPrivateKey($this->getCandidatePrivateKey());
            $this->setCandidatePublicKey('');
            $this->setCandidatePrivateKey('');
            Mage::getConfig()->saveConfig('w_cube/settings/candidate_ts', 0, 'default', 0);
            $this->_rsa = null;
            $this->_cRsa = null;
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
            Mage::getStoreConfig('w_cube/settings/candidate_priv', 0)
        );
    }

    public function setCandidatePrivateKey($val)
    {
        $currentTs = (int)Mage::getSingleton('core/date')->gmtTimestamp();
        $val = Mage::helper('core')->encrypt($val);
        Mage::getConfig()->saveConfig('w_cube/settings/candidate_priv', $val, 'default', 0);
        Mage::getConfig()->saveConfig('w_cube/settings/candidate_ts', $currentTs, 'default', 0);
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
        if (!isset($this->_scRsa)) {
            $this->registerAutoloader();
            $this->_scRsa = new Crypt_RSA();
            $this->_scRsa->loadKey($this->getToken());
            $this->_scRsa->setSaltLength(128);
        }

        return $this->_scRsa;
    }

    public function getCandidateRsa()
    {
        if (!isset($this->_cRsa)) {
            $this->registerAutoloader();
            $this->_cRsa = new Crypt_RSA();
            $this->_cRsa->loadKey($this->getCandidatePrivateKey());
            $this->_cRsa->setSaltLength(128);
        }

        return $this->_cRsa;
    }

    public function getRsa()
    {
        if (!isset($this->_rsa)) {
            $this->registerAutoloader();
            $this->_rsa = new Crypt_RSA();
            $this->_rsa->loadKey($this->getPrivateKey());
            $this->_rsa->setSaltLength(128);
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

    public function verifySignature($baseStr, $sig)
    {
        return $this->getScRsa()->verify($baseStr, base64_decode($sig));
    }

    public function auth($request)
    {
        $baseUrl = Mage::getStoreConfig('w_cube/settings/base_url', 0);
        $method = strtoupper($request->getMethod());
        $url = strtolower(rtrim($baseUrl, '/') . $request->getOriginalPathInfo());
        $paramsBase = array();
        $params = $request->getParams();
        ksort($params, SORT_STRING);
        foreach ($params as $key=>$value) {
            $paramsBase[] = $key . "=" . $value;
        }

        $paramsBase = implode('&', $paramsBase);
        $nonce = $request->getHeader('SC-NONCE');
        $timestamp = $request->getHeader('SC-TIMESTAMP');
        $sig = $request->getHeader('SC-AUTHORIZATION');
        if ($nonce && $timestamp && $sig) {
            $baseStr = implode('&', array($method, $url, $paramsBase, $nonce, $timestamp));
            return $this->checkTimestamp($timestamp)
                && $this->getResource()->checkNonce($nonce)
                && $this->verifySignature($baseStr, $sig);
        }

        return False;
    }

    public function getResource()
    {
        return Mage::getResourceModel('wCube/main');
    }

    public function checkTimestamp($timestamp)
    {
        $currentTs = (int)Mage::getSingleton('core/date')->gmtTimestamp();
        return abs($currentTs - (int)$timestamp) < self::TIMESTAMP_GAP;
    }

    public function getKeyBySession($session)
    {
        $key = $this->getResource()->getKeyBySession($session);
        if ($key)
            return $this->cleanKey($key);
        return False;
    }

    public function cleanKey($key)
    {
        return $this->getRsa()->decrypt(base64_decode($key));
    }


}
