<?php
namespace MGS\ThemeSettings\Model\OptimizeWeb\Handle;

use MGS\ThemeSettings\Lib\HTMLMin as LibHTMLMin;

class Html
{
    /**
     * @param string $html
     *
     * @return $this
     */
    public function minifyHtml(&$html)
    {
        $result = LibHTMLMin::minify($html, array('jsCleanComments' => false));
        if (strlen($result) > 0) {
            $html = $result;
        }
        return $this;
    }
}
