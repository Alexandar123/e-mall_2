<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Fbuilder\Model\File;

/**
 * Core file uploader model
 *
 * @api
 * @since 100.0.2
 */
class Uploader extends \Magento\MediaStorage\Model\File\Uploader
{
    /**
     * Check protected/allowed extension
     *
     * @param  string $extension
     * @return boolean
     */
    public function checkAllowedExtension($extension)
    {
        if ($extension == 'xml') {
            return true;
        }
        //validate with protected file types
        if (!$this->_validator->isValid($extension)) {
            return false;
        }

        return parent::checkAllowedExtension($extension);
    }
}
