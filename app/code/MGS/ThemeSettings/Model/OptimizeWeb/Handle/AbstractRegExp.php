<?php
namespace MGS\ThemeSettings\Model\OptimizeWeb\Handle;

use MGS\ThemeSettings\Model\OptimizeWeb\Handle\Raw;

abstract class AbstractRegExp
{
	protected $startTagNeedles = array(
        '/<!-{0,2}\[if[^\]]*\]\s*>(\s*<!-->)*/is',
    );
    protected $endTagNeedles = array(
        '/(<!--\s*)*<!\[endif\]-{0,2}>/is',
    );
	
    /**
     * @param string $scriptSearch
     * @param string $html
     * @param null|int $start
     * @param null $end
     *
     * @return Raw[]
     * @throws \Exception
     */
    protected function getElementByRegexp($scriptSearch, $html, $start = null, $end = null) {
        $htmlResult = preg_match_all($scriptSearch, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
		
        if (FALSE === $htmlResult) {
            throw new \Exception('preg_match_all error in RegExp\Abstract, error code: ' . preg_last_error());
        }
        $result = [];
        foreach ($matches as $match) {
            $match = $match[0];
            $position = $match[1];
            if (null !== $start && $start > $position) {
                continue;
            }
            if (null !== $end && $end < $position) {
                continue;
            }
            $result[] = $this->processResult($match[0], $position);
        }
        return $result;
    }

    /**
     * @param string $source
     * @param int $position
     *
     * @return Raw
     * @throws \Exception
     */
    protected function processResult($source, $position)
    {
        $end = $position + strlen($source) - 1;
        $result = new Raw(
            $source, $position, $end
        );
        return $result;
    }

    /**
     * @param string   $html
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     * @throws \Exception
     */
    public function findStartTag($html, $start = null, $end = null)
    {
        $result = array();
        foreach ($this->startTagNeedles as $needle) {
            $result = array_merge($result, $this->getElementByRegexp($needle, $html, $start, $end));
        }
        return array_values($result);
    }

    /**
     * @param string   $html
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     * @throws \Exception
     */
    public function findEndTag($html, $start = null, $end = null)
    {
        $result = array();
        foreach ($this->endTagNeedles as $needle) {
            $result = array_merge($result, $this->getElementByRegexp($needle, $html, $start, $end));
        }
        return array_values($result);
    }
}