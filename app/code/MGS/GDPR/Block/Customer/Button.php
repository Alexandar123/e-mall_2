<?php

namespace MGS\GDPR\Block\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;


class Button extends \Magento\Customer\Block\Account\Dashboard{
	
	public function getAction()
    {
        return $this->getUrl('gdpr/customer/save');
    }
}
?>