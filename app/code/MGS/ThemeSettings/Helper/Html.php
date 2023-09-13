<?php
namespace MGS\ThemeSettings\Helper;

class Html
{
    /**
     * @param string $html
     * @param string $replacement
     * @param int $start
     * @param int $end
     *
     * @return string
     */
    public static function replaceIntoHtml($html, $replacement, $start, $end)
    {
        $length = $end - $start + 1;
        return substr_replace($html, $replacement, $start, $length);
    }

    /**
     * @param string $html
     * @param int $start
     * @param int $end
     *
     * @return string
     */
    public static function cutHtmlString($html, $start, $end)
    {
        return self::replaceIntoHtml($html, '', $start, $end);
    }

    /**
     * @param string $insertCode
     * @param string $targetHtml
     *
     * @return string
     */
    public static function insertCodeBeforeBodyEnd($insertCode, $targetHtml)
    {
        return str_replace('</body>', $insertCode . "\n</body>", $targetHtml);
    }
	
    /**
     * @param string $string
     * @param string $text
     *
     * @return bool
     */
    public static function isHtmlContain($string, $text)
    {
        return strpos($string, $text) !== FALSE;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public static function checkHtml($string)
    {
        $result = self::isHtmlContain($string, '<html');
        $result = $result && self::isHtmlContain($string, '</html>');
        $result = $result && self::isHtmlContain($string, '<body');
        $result = $result && self::isHtmlContain($string, '</body>');
        return $result;
    }
}
