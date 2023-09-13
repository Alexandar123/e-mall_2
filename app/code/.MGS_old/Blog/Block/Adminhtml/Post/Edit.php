<?php

namespace MGS\Blog\Block\Adminhtml\Post;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'post_id';
        $this->_controller = 'adminhtml_post';
        $this->_blockGroup = 'MGS_Blog';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );
    }

    public function getHeaderText()
    {
        $post = $this->_coreRegistry->registry('current_post');
        if ($post->getId()) {
            return __("Edit Post '%1'", $this->escapeHtml($post->getTitle()));
        } else {
            return __('New New Post');
        }
    }
}
