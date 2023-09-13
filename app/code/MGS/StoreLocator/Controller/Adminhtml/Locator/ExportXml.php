<?php

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\StoreLocator\Controller\Adminhtml\Locator;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \MGS\StoreLocator\Controller\Adminhtml\Locator {

    /**
     * Export locator grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute() {
        $this->_view->loadLayout();
        $fileName = 'locator.xml';
        $content = $this->_view->getLayout()->getChildBlock('adminhtml.locator.grid', 'grid.export');
        return $this->_fileFactory->create(
                        $fileName, $content->getExcelFile($fileName), DirectoryList::VAR_DIR
        );
    }

}
