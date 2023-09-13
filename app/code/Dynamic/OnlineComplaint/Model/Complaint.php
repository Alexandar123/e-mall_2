<?php
namespace Dynamic\OnlineComplaint\Model;

class Complaint extends \Magento\Framework\Model\AbstractModel
{

	protected function _construct()
	{
		$this->_init('Dynamic\OnlineComplaint\Model\ResourceModel\Complaint');
	}
}