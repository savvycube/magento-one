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
class SavvyCube_Connector_Model_Api_Customer extends SavvyCube_Connector_Model_Api_Abstract
{

    protected $_genderText;


    public function getMethod()
    {
        $result = array();
        $customerCollection = Mage::getModel('customer/customer')
            ->getCollection();

        $count = (int)$this->_request['count'];
        $offset = (int)$this->_request['offset'];

        $customerCollection->removeAttributeToSelect()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('website_id')
                ->addAttributeToSelect('gender')
                ->addAttributeToSelect('updated_at')
                ->addAttributeToSelect('created_at')
                ->joinTable(
                    array('group' => 'customer/customer_group'),
                    'customer_group_id=group_id',
                    array('customer_group' => 'customer_group_code'),
                    null,
                    'left'
                );

        $customerCollection->getSelect()->limit($count, $offset);

        $dateColumn = 'updated_at';

        if (isset($this->_request['from'])) {
            $customerCollection->addAttributeToFilter(
                $dateColumn,
                array('gteq' => $this->_request['from'])
            );
        }

        if (isset($this->_request['to'])) {
            $customerCollection->addAttributeToFilter(
                $dateColumn,
                array('lt' => $this->_request['to'])
            );
        }

        $start = microtime(true);
        $customers = $customerCollection->getItems();
        $this->_queryTime += microtime(true) - $start;

        foreach ($customers as $customerId => $customer) {
            $result[$customerId] = $this->parseCustomer($customer);
        }

        $this->_count = count($result);
        $this->_data =  $result;
        return true;
    }

    protected function parseCustomer($customer)
    {

        $result['entity_id'] = $customer->getEntityId();
        $result['gender'] = $this->getGenderText($customer->getGender());
        $result['customer_group'] = $customer->getCustomerGroup();
        $result['updated_at'] = $customer->getUpdatedAt();
        $result['created_at'] = $customer->getCreatedAt();
        return $result;
    }

    protected function getGenderText($genderValue)
    {
        if (!isset($this->genderTex[$genderValue])) {
            $this->_genderText[$genderValue] =
            Mage::getResourceModel('customer/customer')
                ->getAttribute('gender')
                ->getSource()
                ->getOptionText($genderValue);
        }

        return $this->_genderText[$genderValue];
    }
}
