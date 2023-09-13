<?php
/**
 * Index
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Controller\Add;

class Index extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : 11;
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $product = $this->productRepository->getById($productId, false, $storeId);

        $this->cart->addProduct($product, []);
        $this->cart->save();

        return $this->goBack($this->_url->getUrl('onestepcheckout'));
    }
}
