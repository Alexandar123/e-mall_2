<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ClaueTheme\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Main contact form block
 */
class AbstractProduct extends \Magento\Framework\View\Element\Template
{

    protected $_filesystem;

    protected $_storeManager;

    /**
     * @var Product
     */
    protected $_product = null;

    protected $_date;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context,
            $data
        );
    }

    public function getArImages($productId){
        $dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('wysiwyg/3d/'.$productId);

        $result = [];
        $files = [];
        if(is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ($files[] = readdir($dh));
                sort($files);
                foreach ($files as $file){
                    $file_parts = pathinfo($dir . $file);
                    if (isset($file_parts['extension']) && (($file_parts['extension'] == 'glb'))) {
                        $result[] = $this->getMediaUrl().'wysiwyg/3d/'.$productId.'/'.$file;
                    }
                }
                closedir($dh);
            }
        }
        return $result;
    }

    public function getMediaUrl(){
        return $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
    }

}

