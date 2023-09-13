<?php

namespace MGS\Fbuilder\Block\Adminhtml\System;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageFactory;
use MGS\Fbuilder\Model\ResourceModel\Section\CollectionFactory as SectionFactory;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Filesystem\Io\File;

class Restore extends \Magento\Framework\Data\Form\Element\AbstractElement
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
        $collection = $this->pageFactory->create();

        $html = '<select id="fbuilder_restore_page_id" name="groups[restore][fields][page_id][value]" class="select admin__control-select" data-ui-id="select-groups-restore-fields-page_id-value" style="width:230px; margin-right:10px">
		<option value="">'.__('Choose Page to Restore').'</option>';
        if (count($collection)>0) {
            foreach ($collection as $page) {
                if ($page->getId()) {
                    $html .= '<option value="'.$page->getId().'">'. $page->getTitle() .'</option>';
                }
            }
        }

        $html .= '</select>';

        $html .= '<select id="fbuilder_restore_version_id" name="groups[restore][fields][version_id][value]" class="select admin__control-select" data-ui-id="select-groups-restore-fields-version_id-value" style="width:230px; margin-right:10px; margin-top: 10px">
		<option value="">'.__('Choose Version to Restore').'</option>';
        foreach ($this->getFileList() as $file) {
            $html .= '<option value="'.$file['text'].'">'. $file['text'] .'</option>';
        }

        $html .= '</select>';

        $url = $this->backendUrl->getUrl("adminhtml/fbuilder/restore");

        $html .= '<button type="button" class="action-default scalable" onclick="restorePage(\''.$url.'\')" data-ui-id="widget-button-2"><span id="restore-wait-text" style="display:none">'.__('Please wait...').'</span><span id="restore-import-text">'.__('Restore').'</span></button>';

        return $html;
    }

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
