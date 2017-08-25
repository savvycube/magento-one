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
class SavvyCube_Connector_SavvycubeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Authorization Helper
     *
     * @return SavvyCube_Connector_Helper_Authorization
     */
    protected function getAuthHelper()
    {
        return Mage::helper('wCube/authorization');
    }

    public function indexAction()
    {
        $this->getAuthHelper()->generateKeys();
        $this->getAuthHelper()->cleanCache();
        Mage::app()->getResponse()->setRedirect(
            $this->getAuthHelper()->getActivationUrl()
        );
    }

    public function activateAction()
    {
        $token = base64_decode($this->getRequest()->getParam('token'));
        $session = (int)$this->getRequest()->getParam('session');
        if ($this->getAuthHelper()->promoteCandidateKeys($session)) {
            $this->getAuthHelper()->setToken($token);
            $this->getAuthHelper()->cleanCache();
            $finalUrl = $this->getUrl(
                'adminhtml/system_config/edit',
                array('section' => 'w_cube')
            );
            Mage::app()->getResponse()->setRedirect($finalUrl);
        } else {
            Mage::app()->getResponse()
                ->setHeader('HTTP/1.1', '401 Unauthorized');
        }
    }
    public function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('admin/system/config/w_cube');
    }

}
