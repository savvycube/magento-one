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
class SavvyCube_Connector_Model_Api_Customer extends SavvyCube_Connector_Model_Api_Abstract
{

    private $genderText;


    public function getMethod()
    {
        $result = array();
        $customerCollection = Mage::getModel('customer/customer')
            ->getCollection();

        $count = (int)$this->request['count'];
        $offset = (int)$this->request['offset'];

        $customerCollection->removeAttributeToSelect()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('website_id')
                ->addAttributeToSelect('gender')
                ->addAttributeToSelect('updated_at')
                ->addAttributeToSelect('created_at')
                ->joinTable(array('group' => 'customer/customer_group'),
                    'customer_group_id=group_id',
                    array('customer_group' => 'customer_group_code'),
                    null,
                    'left');

        $customerCollection->getSelect()->limit($count, $offset);

        $dateColumn = 'updated_at';

        if (isset($this->request['from'])) {
            $customerCollection->addAttributeToFilter(
                $dateColumn,
                array('gteq' => $this->request['from']));
        }
        if (isset($this->request['to'])) {
            $customerCollection->addAttributeToFilter(
                $dateColumn,
                array('lt' => $this->request['to']));
        }

        $start = microtime(true);
        $customers = $customerCollection->getItems();
        $this->queryTime += microtime(true) - $start;

        foreach ($customers as $customerId => $customer) {
            $result[$customerId] = $this->parseCustomer($customer);
        }

        $this->count = count($result);
        $this->data =  $result;
        return true;
    }

    private function parseCustomer($customer)
    {

        $result['entity_id'] = $customer->getEntityId();
        $result['gender'] = $this->getGenderText($customer->getGender());
        $result['customer_group'] = $customer->getCustomerGroup();
        $result['updated_at'] = $customer->getUpdatedAt();
        $result['created_at'] = $customer->getCreatedAt();
        return $result;
    }

    private function getGenderText($genderValue) {
        if (!isset($this->genderTex[$genderValue])) {
            $this->genderText[$genderValue] =
            Mage::getResourceModel('customer/customer')
                ->getAttribute('gender')
                ->getSource()
                ->getOptionText($genderValue);
        }
        return $this->genderText[$genderValue];
    }
}
