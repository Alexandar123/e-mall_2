<?php
namespace MGS\ThemeSettings\Model\OptimizeWeb\Handle;

use MGS\ThemeSettings\Helper\Html;
use MGS\ThemeSettings\Lib\JShrink as LibJShrink;

class Js extends AbstractRegExp
{
	protected $scriptRegExp = array(
        "<script[^>]*?>.*?<\/script>"
    );
	
	/** @var array */
    protected $exceptScriptTypes = [
        'text/x-custom-template',
        'application/ld+json',
        'text/x-magento-template'
    ];
    /**
     * @param string $html
     *
     * @return $this
     */
    public function deferParsingOfJs(&$html)
    {
		$jsElements = $this->getAllJavascriptElement($html);
        /*
		$this->excludeIgnoreTagFromList($jsElements, $this->ignoreMoveFlagList);

		*/
        $firstJsTag = $this->findStartTag($html);
        $lastJsTag = $this->findEndTag($html);
		
        $firstJs = current($firstJsTag);
        $lastJs = current($lastJsTag);

        $jsElementList = array();
        foreach ($jsElements as $_jsTag) {
            $_jsElement = array(
                'start' => $_jsTag->getStart(),
                'end' => $_jsTag->getEnd(),
                'content' => $_jsTag->getContent()
            );
			
            while ($firstJs && $lastJs && $_jsTag->getStart() > $lastJs->getEnd()) {
                $firstJs = next($firstJsTag);
                $lastJs = next($lastJsTag);
            }

            if (
                $firstJs && $lastJs
                && $_jsTag->getStart() > $firstJs->getEnd()
                && $_jsTag->getEnd() < $lastJs->getStart()
            ) {
                $_jsElement['content'] = $firstJs->getContent() . $_jsElement['content'] . $lastJs->getContent();
            }
            $jsElementList[] = $_jsElement;
        }

        $resultHtml = "";
        foreach (array_reverse($jsElementList) as $_jsElement) {
            $resultHtml = $_jsElement['content'] . $resultHtml;
            $html = Html::cutHtmlString($html, $_jsElement['start'], $_jsElement['end']);
        }
        $html = Html::insertCodeBeforeBodyEnd($resultHtml, $html);
        return $this;
    }
	
	/**
     * @param string $html
     *
     * @return $this
     * @throws \Exception
     */
    public function mergeJavascriptInline(&$html)
    {
        $jsElements = $this->getAllJavascriptElementExceptTypes($html, $this->exceptScriptTypes);
        if (count($jsElements) === 0) {
            return $this;
        }
        /*$this->excludeIgnoreTagFromList($jsElements, $this->ignoreMinifyFlagList);*/
        $jsElementList = array();
        foreach ($jsElements as $_jsTag) {
            $attributes = $_jsTag->getAttributes();
            if (!array_key_exists('src', $attributes)) {
                preg_match('/^(<script[^>]*?>)(.*)(<\/script>)$/is', $_jsTag->getContent(), $matches);
                if (count($matches) === 0) {
                    continue;
                }
                $content = $matches[2];
                $minContent = $this->minifyContent($content);
                if (strlen(trim($minContent)) === 0) {
                    continue;
                }
                $jsElementList[] = array(
                    'start' => $_jsTag->getStart(),
                    'end' => $_jsTag->getEnd(),
                    'content' => $matches[1] . $minContent . $matches[3]
                );
            }
        }
        foreach (array_reverse($jsElementList) as $_jsElement) {
            $html = Html::replaceIntoHtml(
                $html, $_jsElement['content'], $_jsElement['start'], $_jsElement['end']
            );
        }
        return $this;
    }
	
	/**
     * @param string $html
     * @return array
     */
    public function getAllJavascriptElement($html, $start = null, $end = null)
    {
        $scriptSearch = "/" . join('|', $this->scriptRegExp) . "/is";
        $result = $this->getElementByRegexp($scriptSearch, $html, $start, $end);
        //$result = $this->removeCommentTags($result, $html);
        return array_values($result);
    }
	
	/**
     * @param string $html
     * @param string[] $exceptScriptTypesList
     * @param null|int $start
     * @param null|int $end
     *
     * @return Tag[]
     * @throws \Exception
     */
    public function getAllJavascriptElementExceptTypes($html, $exceptScriptTypesList, $start = null, $end = null)
    {
        $tagList = $this->getAllJavascriptElement($html, $start, $end);
        foreach ($tagList as $key => $tag) {
            $attributes = $tag->getAttributes();
            if (!array_key_exists('type', $attributes)) {
                continue;
            }
            $tagType = trim($attributes['type']);
            if (in_array($tagType, $exceptScriptTypesList)) {
                unset($tagList[$key]);
            }
        }
        return array_values($tagList);
    }
	
	/**
     * @param string $content
     *
     * @return string
     * @throws \Exception
     */
    public function minifyContent($content)
    {
        return LibJShrink::minify($content, array('flaggedComments' => false));
    }
}
