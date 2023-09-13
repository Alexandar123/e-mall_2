<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config Logo image field backend model
 */
namespace MGS\ThemeSettings\Model\Config\Backend;

class WoffTwo extends \MGS\ThemeSettings\Model\Config\Backend\Font
{
    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['woff2'];
    }
}
