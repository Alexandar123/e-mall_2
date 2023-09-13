<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mmegamenu\Block\Store;

/**
 * Store switcher block
 *
 * @api
 * @since 100.0.2
 */
class Switcher extends \Magento\Backend\Block\Store\Switcher
{

    protected $megamenu;

    protected $store;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \MGS\Mmegamenu\Model\ResourceModel\Mmegamenu $megamenu,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        parent::__construct($context, $websiteFactory, $storeGroupFactory, $storeFactory, $data);
        $this->megamenu = $megamenu;
        $this->store = $request;
    }


    protected function _construct()
    {
        parent::_construct();

        $this->setUseConfirm(true);
        $this->setUseAjax(true);

        $this->setShowManageStoresLink(0);

        if (!$this->hasData('switch_websites')) {
            $this->setSwitchWebsites(false);
        }
        if (!$this->hasData('switch_store_groups')) {
            $this->setSwitchStoreGroups(false);
        }
        if (!$this->hasData('switch_store_views')) {
            $this->setSwitchStoreViews(true);
        }
        $this->setDefaultSelectionName(__('All Store Views'));
    }

    public function getStoreSwitcher() {
        $megamenu_id = $this->store->getParam('id');
        $table = $this->megamenu->getTable('mgs_megamenu_store');
        $connection = $this->megamenu->getConnection();
        $sql = "SELECT `store_id`
                 FROM `$table`
                 WHERE `megamenu_id`= $megamenu_id ";
        $megamenu = $connection->fetchAssoc($sql);
        return $megamenu;

    }

    public function getStoreUpdate(){
        $megamenu_id = $this->store->getParam('id');
        $table = $this->megamenu->getTable('mgs_megamenu_update');
        $connection = $this->megamenu->getConnection();
        $sql = "SELECT `scope_id`
                 FROM `$table`
                 WHERE `megamenu_id`= $megamenu_id ";
        $megamenu = $connection->fetchAssoc($sql);
        return $megamenu;

    }
    public function getWebsiteCollection()
    {
        $collection = $this->_websiteFactory->create()->getResourceCollection();

        $websiteIds = $this->getWebsiteIds();
        if ($websiteIds !== null) {
            $collection->addIdFilter($this->getWebsiteIds());
        }

        return $collection->load();
    }

    /**
     * Get websites
     *
     * @return \Magento\Store\Model\Website[]
     */
    public function getWebsites()
    {
        $websites = $this->_storeManager->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) {
            $websites = array_intersect_key($websites, array_flip($websiteIds));
        }
        return $websites;
    }

    /**
     * Check if can switch to websites
     *
     * @return bool
     */
    public function isWebsiteSwitchEnabled()
    {
        return (bool)$this->getData('switch_websites');
    }

    /**
     * Set website variable name.
     *
     * @param string $varName
     * @return $this
     */
    public function setWebsiteVarName($varName)
    {
        $this->setData('website_var_name', $varName);
        return $this;
    }

    /**
     * Get website variable name.
     *
     * @return string
     */
    public function getWebsiteVarName()
    {
        if ($this->hasData('website_var_name')) {
            return (string)$this->getData('website_var_name');
        } else {
            return (string)$this->_defaultWebsiteVarName;
        }
    }

    /**
     * Check if current website selected.
     *
     * @param \Magento\Store\Model\Website $website
     * @return bool
     */
    public function isWebsiteSelected(\Magento\Store\Model\Website $website)
    {
        return $this->getWebsiteId() === $website->getId() && $this->getStoreId() === null;
    }

    /**
     * Return website Id.
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        if (!$this->hasData('website_id')) {
            $this->setData('website_id', (int)$this->getRequest()->getParam($this->getWebsiteVarName()));
        }
        return $this->getData('website_id');
    }

    public function getGroupCollection($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {
            $website = $this->_websiteFactory->create()->load($website);
        }
        return $website->getGroupCollection();
    }

    /**
     * Get store groups for specified website
     *
     * @param \Magento\Store\Model\Website|int $website
     * @return array
     */
    public function getStoreGroups($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {
            $website = $this->_storeManager->getWebsite($website);
        }
        return $website->getGroups();
    }

    /**
     * Check if can switch to store group
     *
     * @return bool
     */
    public function isStoreGroupSwitchEnabled()
    {
        return (bool)$this->getData('switch_store_groups');
    }

    /**
     * Sets store group variable name.
     *
     * @param string $varName
     * @return $this
     */
    public function setStoreGroupVarName($varName)
    {
        $this->setData('store_group_var_name', $varName);
        return $this;
    }

    /**
     * Return store group variable name.
     *
     * @return string
     */
    public function getStoreGroupVarName()
    {
        if ($this->hasData('store_group_var_name')) {
            return (string)$this->getData('store_group_var_name');
        } else {
            return (string)$this->_defaultStoreGroupVarName;
        }
    }

    /**
     * Is provided group selected.
     *
     * @param \Magento\Store\Model\Group $group
     * @return bool
     */
    public function isStoreGroupSelected(\Magento\Store\Model\Group $group)
    {
        return $this->getStoreGroupId() === $group->getId() && $this->getStoreGroupId() === null;
    }

    /**
     * Return store group Id.
     *
     * @return int|null
     */
    public function getStoreGroupId()
    {
        if (!$this->hasData('store_group_id')) {
            $this->setData('store_group_id', (int)$this->getRequest()->getParam($this->getStoreGroupVarName()));
        }
        return $this->getData('store_group_id');
    }

    /**
     * Return store collection.
     *
     * @param \Magento\Store\Model\Group|int $group
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStoreCollection($group)
    {
        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->_storeGroupFactory->create()->load($group);
        }
        $stores = $group->getStoreCollection();
        $_storeIds = $this->getStoreIds();
        if (!empty($_storeIds)) {
            $stores->addIdFilter($_storeIds);
        }
        return $stores;
    }


    public function getStores($group)
    {
        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->_storeManager->getGroup($group);
        }
        $stores = $group->getStores();
        if ($storeIds = $this->getStoreIds()) {
            foreach (array_keys($stores) as $storeId) {
                if (!in_array($storeId, $storeIds)) {
                    unset($stores[$storeId]);
                }
            }
        }
        return $stores;
    }

    /**
     * Return store Id.
     *
     * @return int|null
     */
    public function getStoreId()
    {
        if (!$this->hasData('store_id')) {
            $this->setData('store_id', (int)$this->getRequest()->getParam($this->getStoreVarName()));
        }
        return $this->getData('store_id');
    }

    /**
     * Check is provided store selected.
     *
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    public function isStoreSelected(\Magento\Store\Model\Store $store)
    {
        return $this->getStoreId() !== null && (int)$this->getStoreId() === (int)$store->getId();
    }

    /**
     * Check if can switch to store views
     *
     * @return bool
     */
    public function isStoreSwitchEnabled()
    {
        return (bool)$this->getData('switch_store_views');
    }

    /**
     * Sets store variable name.
     *
     * @param string $varName
     * @return $this
     */
    public function setStoreVarName($varName)
    {
        $this->setData('store_var_name', $varName);
        return $this;
    }

    /**
     * Return store variable name.
     *
     * @return mixed|string
     */
    public function getStoreVarName()
    {
        if ($this->hasData('store_var_name')) {
            return (string)$this->getData('store_var_name');
        } else {
            return (string)$this->_defaultStoreVarName;
        }
    }

    /**
     * Return switch url.
     *
     * @return string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl(
            '*/*/*',
            [
                '_current' => true,
                $this->getStoreVarName() => null,
                $this->getStoreGroupVarName() => null,
                $this->getWebsiteVarName() => null,
            ]
        );
    }

    /**
     * Checks if scope selected.
     *
     * @return bool
     */
    public function hasScopeSelected()
    {
        return $this->getStoreId() !== null || $this->getStoreGroupId() !== null || $this->getWebsiteId() !== null;
    }

    /**
     * Get current selection name
     *
     * @return string
     */
    public function getCurrentSelectionName()
    {
        if (!($name = $this->getCurrentStoreName())) {
            if (!($name = $this->getCurrentStoreGroupName())) {
                if (!($name = $this->getCurrentWebsiteName())) {
                    $name = $this->getDefaultSelectionName();
                }
            }
        }
        return $name;
    }

    /**
     * Get current website name
     *
     * @return string
     */
    public function getCurrentWebsiteName()
    {
        if ($this->getWebsiteId() !== null) {
            $website = $this->_websiteFactory->create();
            $website->load($this->getWebsiteId());
            if ($website->getId()) {
                return $website->getName();
            }
        }

        return null;
    }

    /**
     * Get current store group name
     *
     * @return string
     */
    public function getCurrentStoreGroupName()
    {
        if ($this->getStoreGroupId() !== null) {
            $group = $this->_storeGroupFactory->create();
            $group->load($this->getStoreGroupId());
            if ($group->getId()) {
                return $group->getName();
            }
        }

        return null;
    }

    /**
     * Get current store view name
     *
     * @return string
     */
    public function getCurrentStoreName()
    {
        if ($this->getStoreId() !== null) {
            $store = $this->_storeFactory->create();
            $store->load($this->getStoreId());
            if ($store->getId()) {
                return $store->getName();
            }
        }

        return null;
    }

    /**
     * Sets store ids.
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * Return store ids.
     *
     * @return array
     */
    public function getStoreIds()
    {
        return $this->_storeIds;
    }

    /**
     * Check if system is run in the single store mode.
     *
     * @return bool
     */
    public function isShow()
    {
        return !$this->_storeManager->isSingleStoreMode();
    }

    /**
     * Render block.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isShow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }

    /**
     * Return url for store switcher hint
     *
     * @return string
     */
    public function getHintUrl()
    {
        return self::HINT_URL;
    }

    /**
     * Return store switcher hint html
     *
     * @return string
     */
    public function getHintHtml()
    {
        $html = '';
        $url = $this->getHintUrl();
        if ($url) {
            $html = '<div class="admin__field-tooltip tooltip">' . '<a' . ' href="' . $this->escapeUrl(
                $url
            ) . '"' . ' onclick="this.target=\'_blank\'"' . ' title="' . __(
                'What is this?'
            ) . '"' . ' class="admin__field-tooltip-action action-help"><span>' . __(
                'What is this?'
            ) . '</span></a>' . ' </div>';
        }
        return $html;
    }

    /**
     * Get whether iframe is being used
     *
     * @return bool
     */
    public function isUsingIframe()
    {
        if ($this->hasData('is_using_iframe')) {
            return (bool)$this->getData('is_using_iframe');
        }
        return false;
    }
}
