<?php

namespace MGS\Fbuilder\Block\Adminhtml\System;

use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use MGS\Fbuilder\Model\ResourceModel\Section\CollectionFactory as SectionFactory;

class Delete extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    protected $sectionFactory;

    protected $backendUrl;

    protected $pageFactory;

    /**
     * @var File
     */
    protected $ioFile;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        SectionFactory $sectionFactory,
        UrlInterface $backendUrl,
        PageFactory $pageFactory,
        File $ioFile,
        Filesystem $fileSystem,
        $data = []
    ) {
        $this->sectionFactory = $sectionFactory;
        $this->backendUrl = $backendUrl;
        $this->pageFactory = $pageFactory;
        $this->ioFile = $ioFile;
        $this->fileSystem = $fileSystem;
        parent::__construct(
            $factoryElement,
            $factoryCollection,
            $escaper,
            $data
        );
    }


    /**
     * @return string
     */
    public function getElementHtml()
    {
        /**
 * @var \Magento\Backend\Block\Widget\Button $buttonBlock  
*/

        $html = '<select id="fbuilder_delete_version_id" name="groups[delete][fields][version_id][value]" size="6" multiple="multiple" style="margin-bottom: 10px; max-width: 300px">
		';
        foreach ($this->getFileList() as $file) {
            $html .= '<option value="'.$file['text'].'">'. $file['text'] .'</option>';
        }

        $html .= '</select>';

        $url = $this->backendUrl->getUrl("adminhtml/fbuilder/delete");

        $urlDeleteAll = $this->backendUrl->getUrl("adminhtml/fbuilder/deleteAll");

        $html .= '<p><button type="button" class="action-default scalable" onclick="deletePage(\''.$url.'\')" data-ui-id="widget-button-2"><span id="delete-wait-text" style="display:none">'.__('Please wait...').'</span><span id="delete-import-text">'.__('Delete').'</span></button>';

        $html .= '<button type="button" class="action-default scalable" style="margin-left: 10px" onclick="deleteAllPage(\''.$urlDeleteAll.'\')" data-ui-id="widget-button-2"><span id="delete-all-wait-text" style="display:none">'.__('Please wait...').'</span><span id="delete-all-import-text">'.__('Delete All').'</span></button></p>';

        return $html;
    }

    /**
     * Get list of all layout file in pub/media/mgs/fbuilder/restore
     *
     * @return array|null
     */
    public function getFileList()
    {
        $folderPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/mgs/fbuilder/restore/');

        $io = $this->ioFile;
        $io->setAllowCreateFolders(true);
        $io->open(['path' => $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($folderPath)]);
        try {
            return array_reverse($io->ls());
        } catch (\Exception $e) {
        }
        return null;
    }
}
