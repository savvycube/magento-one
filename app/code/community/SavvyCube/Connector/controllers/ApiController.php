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
class SavvyCube_Connector_ApiController extends Mage_Core_Controller_Front_Action
{


    protected function getAuthHelper()
    {
        return Mage::helper('wCube/authorization');
    }

    public function indexAction()
    {
        if (!$this->getAuthHelper()->auth($this->getRequest())) {
            $error = array('401 Unauthorized', '');
        } else {
            $session = $this->getRequest()->getHeader('Sc-Session');
            $key = $this->getAuthHelper()->getKeyBySession($session);
            if (!$key) {
                $error = array('401 Unauthorized', 'Session is missing');
            } else {
                $resource = $this->getRequest()->getActionName();
                $method = $this->getRequest()->getMethod();
                $params = $this->getRequest()->getParams();

                $method = strtolower($this->getRequest()->getMethod()) . "Method";
                $apiResource = Mage::getModel('wCube/api_' . $resource);

                if (!$apiResource) {
                    $error = array('500 Internal Server Error', 'No resource model');
                } elseif (!is_callable(array($apiResource, $method))) {
                    $error = array('500 Internal Server Error', 'Unknown method');
                } elseif ($apiResource->init($params)->$method()) {
                    $apiResource->formatResponse($key);
                } else {
                    $error = $apiResource->error;
                }
            }
        }

        if (isset($error)) {
            list ($header, $body) = $error;
            Mage::app()->getResponse()
                ->setHeader('HTTP/1.1', $header)
                ->setBody($body);
        }
    }

    public function authAction()
    {
        if (!$this->getAuthHelper()->auth($this->getRequest())) {
            Mage::app()->getResponse()
                ->setHeader('HTTP/1.1', '401 Unauthorized');
        } else {
            $this->getAuthHelper()->getResource()->cleanSession();
            $this->getAuthHelper()->getResource()->cleanNonce();
            $key = $this->getRequest()->getParam('key');
            $session = $this->getAuthHelper()->getResource()->createSession($key);
            $key = $this->getAuthHelper()->getKeyBySession($session);
            $key = base64_encode($this->getAuthHelper()->getScRsa()->encrypt($key));
            Mage::app()->getResponse()->setHeader('Sc-Session', $session);
            Mage::app()->getResponse()->setHeader('Sc-Key', $key);
        }

    }

    public function checkAction()
    {
        $session = (int)$this->getRequest()->getParam('session');
        $result = $this->getAuthHelper()->candidateSignature($session);
        if ($result) {
            list($iv, $signature) = $result;
            Mage::app()->getResponse()->setHeader('Sc-Sig', $signature);
            Mage::app()->getResponse()->setHeader('Sc-Iv', $iv);
        } else {
            Mage::app()->getResponse()
                ->setHeader('HTTP/1.1', '401 Unauthorized');
        }
    }


    /**
     * Retrieve action method name
     *
     * @param string $action
     * @return string
     */
    public function getActionMethodName($action)
    {
        if (strtolower($action) == 'auth')
            return 'authAction';
        if (strtolower($action) == 'check')
            return 'checkAction';

        return 'indexAction';
    }
}
