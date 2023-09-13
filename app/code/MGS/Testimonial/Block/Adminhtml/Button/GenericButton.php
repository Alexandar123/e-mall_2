<?php 

namespace MGS\Testimonial\Block\Adminhtml\Button;

use Magento\Backend\Block\Widget\Context;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;


class GenericButton {
        /**
         * @var Context
         */
        protected $context;
    
        /**
         * @var BlockRepositoryInterface
         */
        protected $blockRepository;
    
        /**
         * @param Context $context
         * @param BlockRepositoryInterface $blockRepository
         */
        public function __construct(
            Context $context,
            BlockRepositoryInterface $blockRepository
        ) {
            $this->context = $context;
            $this->blockRepository = $blockRepository;
        }
    
        /**
         * Return CMS block ID
         *
         * @return int|null
         */
        public function getBlockId()
        {
            try {
                return $this->blockRepository->getById(
                    $this->context->getRequest()->getParam('id')
                )->getId();
            } catch (NoSuchEntityException $e) {
            }
            return null;
        }
    
        /**
         * Generate url by route and parameters
         *
         * @param   string $route
         * @param   array $params
         * @return  string
         */
        public function getUrl($route = '', $params = [])
        {
            return $this->context->getUrlBuilder()->getUrl($route, $params);
        }
}