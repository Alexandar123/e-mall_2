<?php
namespace Dynamic\OnlineComplaint\Model\ResourceModel\Complaint;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Dynamic\OnlineComplaint\Model\Complaint', 'Dynamic\OnlineComplaint\Model\ResourceModel\Complaint');
	}
}