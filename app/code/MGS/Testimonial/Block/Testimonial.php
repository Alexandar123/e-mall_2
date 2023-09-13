<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Testimonial\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Testimonial extends Template
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(Template\Context $context, \Magento\Framework\ObjectManagerInterface $objectManager, array $data = [])
    {
        parent::__construct($context, $data);
		$this->_objectManager = $objectManager;
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Testimonial\Model\Testimonial');
	}
	public function getResourceModel() {
		return $this->_objectManager->create('MGS\Testimonial\Model\ResourceModel\Testimonial');

	}
	public function getCollection(){
		$store = $this->_storeManager->getStore();
		$model = $this->getModel();
		$collection = $model->getCollection()
			->addFieldToFilter('status', 1)
			->addStoreFilter($this->_storeManager->getStore()->getId())
			->setOrder('testimonial_id', 'DESC');
		if($this->hasData('testimonials_count')){
			$collection->setPageSize($this->getData('testimonials_count'));
		}
		foreach($collection as $key => $item) {
			$id = $item->getTestimonialId();
            $update = $this->getTestimonialByStore($store->getId(), $id);
            foreach ($update as $key =>$value) {
                $item[$key] = $value['value'];
            }
		} 
			
		return $collection;
	}
	
	public function getAvatarUrl($fileName){
		if($fileName){
			$path = 'testimonial' . '/' . $fileName;
			$avatarUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
			return $avatarUrl;
		}
		return false;
	}
	public function getTestimonialByStore($store, $testimonial_id) {
		$resource = $this->getResourceModel('MGS\Testimonial\Model\ResourceModel\Testimonial');
        $table = $resource->getTable('mgs_testimonial_update');
        $connection = $resource->getConnection();
        $sql = "SELECT `field`, `value` 
                 FROM `$table` 
                 WHERE `scope_id`= $store
                 AND `testimonial_id`= $testimonial_id ";
        $testimonial = $connection->fetchAssoc($sql);
        return $testimonial;
    }
}

