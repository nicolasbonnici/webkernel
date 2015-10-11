<?php
namespace Library\Core\App\Mvc\View\Assets;
use Library\Core\App\Mvc\View\Assets\Minifier\Css;
use Library\Core\App\Mvc\View\Assets\Minifier\Js;
use Library\Core\FileSystem\File;

/**
 * Minify and concatenate assets files to optimize render time
 *
 * @author Nicolas Bonnici nicolasbonnici@gmail.com
 */
class Minify
{
    /**
     * Supported type that can be base64 encoded
     * @var array
     */
    protected $aSupportedMimeTypes = array(
        "js"	=> "text/javascript",
        "css"	=> "text/css",
    );

    /**
     * Javascript source code minifier
     * @param $sJavascriptCode              The JavaScript code to minify
     * @return string
     */
    public static function js($sJavascriptCode)
    {
        return Js::minify($sJavascriptCode);
    }

    /**
     * Minify CSS asset
     *
     * @param string $sCssCode          The CSS code to minify
     * @return string
     */
    public static function css($sCssCode)
    {
        return Css::process($sCssCode);
    }

}

class MinifyException extends \Exception
{
}
