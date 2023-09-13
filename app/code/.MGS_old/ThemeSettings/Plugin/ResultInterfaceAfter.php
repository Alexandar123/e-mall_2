<?php
namespace MGS\ThemeSettings\Plugin;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\ResultInterface;

class ResultInterfaceAfter
{

    /** @var MGS\ThemeSettings\Model\OptimizeWeb\Handling  */
    protected $optimizeWebHandle;

    /** @var RequireJsManager  */
    protected $requireJsManager;

    /** @var MGS\ThemeSettings\Helper\Config  */
    protected $themeHelperConfig;


    /** @var MGS\ThemeSettings\Helper\Html  */
    protected $helperHtml;
	
	/** @var Psr\Log\LoggerInterface  */
	private $logger;
	
    /**
     * ProcessResponse constructor.
     * @param Config $config
     * @param MGS\ThemeSettings\Model\OptimizeWeb\Handling $optimizeWebHandle
     * @param RequireJsManager $requireJsManager
     */
    public function __construct(
        \MGS\ThemeSettings\Model\OptimizeWeb\Handling $optimizeWebHandle,
        //RequireJsManager $requireJsManager,
		\MGS\ThemeSettings\Helper\Config $themeHelperConfig,
		\MGS\ThemeSettings\Helper\Html $helperHtml,
		\Psr\Log\LoggerInterface $logger
    ) {
        $this->optimizeWebHandle = $optimizeWebHandle;
        //$this->requireJsManager = $requireJsManager;
        $this->themeHelperConfig = $themeHelperConfig;
        $this->helperHtml = $helperHtml;
		$this->logger = $logger;
    }

    /**
     * FPC will be called on afterRenderResult
     *
     * @param ResultInterface $subject
     * @param \Closure $proceed
     * @param ResponseHttp $response
     * @return \Magento\Framework\View\Result\Layout
     */
    public function aroundRenderResult(
        ResultInterface $subject,
        \Closure $proceed,
        ResponseHttp $response
    ) {
        $result = $proceed($response);
		$enable = $this->themeHelperConfig->getStoreConfig('themesettings/optimize_site/optimize_enabed');
        if (!$enable || !$this->helperHtml->checkHtml($response->getBody())) {
            return $result;
        }
        try {
            $this->optimizeWebHandle->processHtmlResponse($response);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $result;
    }
	
	
}