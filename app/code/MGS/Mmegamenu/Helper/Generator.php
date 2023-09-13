<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mmegamenu\Helper;

class Generator extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_filterProvider;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var \Magento\Framework\View\Element\BlockFactory
	 */
	protected $_blockFactory;

	/**
	 * @var \Magento\Framework\App\ResourceConnection
	 */
	protected $_resource;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $_date;

	/**
	 * @param \Magento\Framework\App\Helper\Context        $context
	 * @param \Magento\Store\Model\StoreManagerInterface   $storeManager
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
	 * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
	 * @param \Magento\Framework\App\ResourceConnection    $resource
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime  $date
	 * @param \Ves\Megamenu\Helper\Data                    $helper
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Framework\View\Element\BlockFactory $blockFactory,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Framework\Stdlib\DateTime\DateTime $date
	) {
		parent::__construct($context);
        $this->_filterProvider  = $filterProvider;
		$this->_storeManager = $storeManager;
		$this->_blockFactory = $blockFactory;
		$this->_resource     = $resource;
		$this->_date         = $date;
	}

	public function getMenuCacheHtml($menuId, $typeMenu = NULL) {
		//if (!$this->helper->getConfig('general_settings/enable_cache')) return false;
		$resource   = $this->_resource;
		$connection = $resource->getConnection();
		$store      = $this->_storeManager->getStore();
		$storeId    = $store->getId();
		$table      = $resource->getTableName('mgs_megamenu_cache');
		$select     = $connection->select()->from($table)->where('parent_id = ?', $menuId)->where('store_id = ?', $storeId);
		$row        = $connection->fetchRow($select);

		if (empty($row)) {
            if($menuId == 1){
                $html = $this->generateMenuHtml($menuId);
            }else{
                if($typeMenu == 'horizontal'){
                    $html = $this->generateHorizontalMenuHtml($menuId);
                }else {
                    $html = $this->generateVerticalMenuHtml($menuId);
                }
            }
			$data['parent_id']       = $menuId;
			$data['store_id']      = $storeId;
			$data['html']          = $html;
			$data['creation_time'] = $this->_date->gmtDate('Y-m-d H:i:s');
			$connection->insert($table, $data);
		} else {
			$timestamp   = strtotime($row['creation_time']);
			$menuDay     = date("d", $timestamp);
			$currentDate = $this->_date->gmtDate('Y-m-d H:i:s');
			$currentDay  = date("d", strtotime($currentDate));
			if ($currentDay == $menuDay) {
				$html = $row['html'];
			} else {
                if($menuId == 1){
                    $html = $this->generateMenuHtml($menuId);
                }else{
                    if($typeMenu == 'horizontal'){
                        $html = $this->generateHorizontalMenuHtml($menuId);
                    }else {
                        $html = $this->generateVerticalMenuHtml($menuId);
                    }
                }
				$connection->update($table, ['html' => $html, 'creation_time' => $currentDate], ['parent_id = ?' => $menuId, 'store_id = ?' => $storeId]);
			}
		}
		return $html;
	}

	protected function generateMenuHtml($menuId) {
		$html = $this->_blockFactory->createBlock('MGS\Mmegamenu\Block\Mmegamenu')->setMenuId($menuId)->setTemplate("MGS_Mmegamenu::cache/top_menu.phtml")->toHtml();
		return $html;
	}

	protected function generateHorizontalMenuHtml($menuId) {
		$html = $this->_blockFactory->createBlock('MGS\Mmegamenu\Block\Horizontal')->setMenuId($menuId)->setTemplate("MGS_Mmegamenu::cache/horizontal_menu.phtml")->toHtml();
		return $html;
	}

	protected function generateVerticalMenuHtml($menuId) {
		$html = $this->_blockFactory->createBlock('MGS\Mmegamenu\Block\Vertical')->setMenuId($menuId)->setTemplate("MGS_Mmegamenu::cache/vertical_menu.phtml")->toHtml();
		return $html;
	}

    public function filter($str)
	{
		$filter = $this->_filterProvider->getPageFilter();
		$html   = $filter->filter($str);
		return $html;
	}
}
