<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class SaveConfig implements ObserverInterface
{
	protected $_request;
	
	/**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;
	
	/**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;
	
	public function __construct(
		RequestInterface $request,
		 \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Framework\Stdlib\StringUtils $string
    ) {
		$this->_request = $request;
		$this->_configFactory = $configFactory;
        $this->string = $string;
    }
	
	/**
     * Get groups for save
     *
     * @return array|null
     */
    protected function _getGroupsForSave()
    {
		$groupMegamenu = $this->getRequest()->getPost('groups');
        $groups = [
			'modules_disable_output'=> [
				[
					'fields' => [
						'MGS_Mmegamenu' => [
							'value' => $this->reverseValue($groupMegamenu['general']['fields']['is_enabled']['value'])
						]
					]
				]
			]
		];
		
        return $groups;
    }
	
	/**
     * Custom save logic for section
     *
     * @return void
     */
    protected function _saveSection()
    {
        $method = '_save' . $this->string->upperCaseWords('advanced', '_', '');
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }
	
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->_request->getPost();
		$this->_saveSection();
		$section = 'advanced';
		$website = $this->_request->getParam('website');
		$store = $this->_request->getParam('store');

		$configData = [
			'section' => $section,
			'website' => $website,
			'store' => $store,
			'groups' => $this->_getGroupsForSave(),
		];
		/** @var \Magento\Config\Model\Config $configModel  */
		$configModel = $this->_configFactory->create(['data' => $configData]);
		$configModel->save();
    }
	
	public function reverseValue($value){
		if($value==1){
			return 0;
		}
		return 1;
	}
}
