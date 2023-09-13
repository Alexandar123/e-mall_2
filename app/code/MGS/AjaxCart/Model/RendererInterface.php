<?php
namespace MGS\AjaxCart\Model;

/**
 * Interface RendererInterface
 * @package MGS\AjaxCart\Model
 */
interface RendererInterface
{
    /**
     * Render layout
     *
     * @param \Magento\Framework\View\Layout $layout
     * @return string
     */
    public function render($layout);
}
