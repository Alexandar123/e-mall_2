<?php
namespace MGS\ThemeSettings\Model\OptimizeWeb;

use Magento\Framework\Profiler;

class Handling
{

    protected $themeHelperConfig;

    protected $helperHtml;

    /** @var MGS\ThemeSettings\Model\OptimizeWeb\Handle\Html  */
    protected $htmlHandling = null;

    /** @var MGS\ThemeSettings\Model\OptimizeWeb\Handle\Css  */
    protected $cssHandling = null;

    /** @var MGS\ThemeSettings\Model\OptimizeWeb\Handle\Js  */
    protected $jsHandling = null;

    /** @var null|int */
    private $originalRuntimeConfiguration = null;

    /**
     * @param MGS\ThemeSettings\Helper\Config $themeHelperConfig
     * @param MGS\ThemeSettings\Helper\Html $helperHtml,
     * @param MGS\ThemeSettings\Model\OptimizeWeb\Handle\Html $htmlHandling
     * @param MGS\ThemeSettings\Model\OptimizeWeb\Handle\Css $cssHandling
     * @param MGS\ThemeSettings\Model\OptimizeWeb\Handle\Js $jsHandling
     */
    public function __construct(
		\MGS\ThemeSettings\Helper\Config $themeHelperConfig,
		\MGS\ThemeSettings\Helper\Html $helperHtml,
        \MGS\ThemeSettings\Model\OptimizeWeb\Handle\Html $htmlHandling,
        \MGS\ThemeSettings\Model\OptimizeWeb\Handle\Css $cssHandling,
        \MGS\ThemeSettings\Model\OptimizeWeb\Handle\Js $jsHandling
    ){
		$this->themeHelperConfig = $themeHelperConfig;
        $this->helperHtml = $helperHtml;
        $this->htmlHandling = $htmlHandling;
        $this->cssHandling = $cssHandling;
        $this->jsHandling = $jsHandling;
    }

    /**
     * @param \Magento\Framework\App\Response\Http $response
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function processHtmlResponse($response)
    {
        $stringHtml = $response->getBody();
        if (!$this->checkCanHandlingHtml($stringHtml)) {
            return;
        }

        $this->setTemporaryRuntimeConfiguration();
		/*
        if ($this->themeHelperConfig->getStoreConfig('themesettings/optimize_site/css_enabed')) {
            Profiler::start('defer-parsing-of-css');
            $this->cssHandling->deferParsingOfCss($stringHtml);
            Profiler::stop('defer-parsing-of-css');
        }
		*/
        if ($this->themeHelperConfig->getStoreConfig('themesettings/optimize_site/js_enabed')) {
            Profiler::start('defer-parsing-of-javascript');
            $this->jsHandling->deferParsingOfJs($stringHtml);
            Profiler::stop('defer-parsing-of-javascript');
        }
        if ($this->themeHelperConfig->getStoreConfig('themesettings/optimize_site/js_minify')) {
            Profiler::start('theme-js-minify-javascript');
            $this->jsHandling->mergeJavascriptInline($stringHtml);
            Profiler::stop('theme-js-minify-javascript');
        }
        if ($this->themeHelperConfig->getStoreConfig('themesettings/optimize_site/html_enabed')) {
            Profiler::start('optimize-website-html-minify');
            $this->htmlHandling->minifyHtml($stringHtml);
            Profiler::stop('optimize-website-html-minify');
        }

        $this->backDefaultRuntimeConfiguration();
        $response->setBody($stringHtml);
    }

    /**
     * @param string $html
     *
     * @return bool
     */
    protected function checkCanHandlingHtml($stringHtml)
    {
        if (!$this->checkModuleConfiguration()) {
            return false;
        }

        if (!$this->helperHtml->checkHtml($stringHtml)) {
            return false;
        }
        return true;
    }

	/**
     * @return bool
     */
	protected function checkModuleConfiguration(){
		$enable = $this->themeHelperConfig->getStoreConfig('themesettings/optimize_site/optimize_enabed');
		if (!$enable) {
            return false;
        }else {
			$enableJs = $this->themeHelperConfig->getStoreConfig('themesettings/optimize_site/js_enabed');
			/*$enableCss = $this->themeHelperConfig->getStoreConfig('themesettings/optimize_site/css_enabed');*/
			$enableHtml = $this->themeHelperConfig->getStoreConfig('themesettings/optimize_site/html_enabed');
			if(!$enableJs && !$enableHtml){
				return false;
			}
		}
		return true;
	}

    /**
     * @return $this
     */
    protected function setTemporaryRuntimeConfiguration()
    {
        $this->originalRuntimeConfiguration = ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', '10000000');
        return $this;
    }

    /**
     * @return $this
     */
    protected function backDefaultRuntimeConfiguration()
    {
        ini_set('pcre.backtrack_limit', $this->originalRuntimeConfiguration);
        return $this;
    }
}
