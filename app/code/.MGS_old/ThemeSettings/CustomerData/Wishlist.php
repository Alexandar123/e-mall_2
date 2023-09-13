<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ThemeSettings\CustomerData;

/**
 * Wishlist section
 */
class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $counter = $this->getCounterWishlist();
        return [
            'counter' => $counter,
            'countCaption' => $counter == 1 ? __('1 item') : __('%1 items', $counter),
            'items' => $counter ? $this->getItems() : [],
        ];
    }

    /**
     * @return string
     */
    protected function getCounterWishlist()
    {
        return $this->createCounterWishlist($this->wishlistHelper->getItemCount());
    }

    /**
     * Create button label based on wishlist item quantity
     *
     * @param int $count
     * @return \Magento\Framework\Phrase|null
     */
    protected function createCounterWishlist($count)
    {
        if ($count >= 1) {
            return $count;
        }
        return null;
    }
}
