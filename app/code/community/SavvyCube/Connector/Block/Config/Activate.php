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
class SavvyCube_Connector_Block_Config_Activate
extends Mage_Adminhtml_Block_System_Config_Form_Field
{

  protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
  {
        $this->setElement($element);
        $url = $this->getUrl('catalog/product'); //

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Connect')
                    ->setOnClick("setLocation('".$this->getUrl('adminhtml/savvycube/index')."')")
                    ->toHtml();

        return $html;
  }
}
