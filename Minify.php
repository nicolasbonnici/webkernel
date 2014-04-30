<?php
namespace Library\Core;

/**
 * A simple class to build Js and CSS assets
 * Minify and concatenate Javascript and Cascading Stylesheet files to optimize render time
 *
 * @todo integrer au chargement des bundles via un systeme de package et du cache, pour le moment genere juste les assets depuis le bundle sample
 * @todo gestion des exceptions
 *
 * @author Nicolas Bonnici nicolasbonnici@gmail.com
 */
class Minify
{

    /**
     * Minify settings
     * @var array
     */
    public static $aSettings =  array();

    /**
     * Reconized type that can be base64 encoded
     * @var array
     */
    public static $aMimeTypes = array(
        "js"	=> "text/javascript",
        "css"	=> "text/css",
        "htm"	=> "text/html",
        "html"	=> "text/html",
        "xml"	=> "text/xml",
        "txt"	=> "text/plain",
        "jpg"	=> "image/jpeg",
        "jpeg"	=> "image/jpeg",
        "png"	=> "image/png",
        "gif"	=> "image/gif",
        "swf"	=> "application/x-shockwave-flash",
        "ico"	=> "image/x-icon"
    );

    /**
     * Javascript source code minifier
     * @param $sJavascriptCode              The JavaScript code to minify
     * @return string
     */
    public static function js($sJavascriptCode)
    {
        $sJs = '';
        $bMaybeRegex = true;
        $i=0;
        $current_char = '';
        while ($i+1<strlen($sJavascriptCode)) {
            if ($bMaybeRegex && $sJavascriptCode[$i]=='/' && $sJavascriptCode[$i+1]!='/' && $sJavascriptCode[$i+1]!='*' && @$sJavascriptCode[$i-1]!='*') {//regex detected
                if (strlen($sJs) && $sJs[strlen($sJs)-1] === '/') $sJs .= ' ';
                do {
                    if ($sJavascriptCode[$i] == '\\') {
                        $sJs .= $sJavascriptCode[$i++];
                    } elseif ($sJavascriptCode[$i] == '[') {
                        do {
                            if ($sJavascriptCode[$i] == '\\') {
                                $sJs .= $sJavascriptCode[$i++];
                            }
                            $sJs .= $sJavascriptCode[$i++];
                        } while ($i<strlen($sJavascriptCode) && $sJavascriptCode[$i]!=']');
                    }
                    $sJs .= $sJavascriptCode[$i++];
                } while ($i<strlen($sJavascriptCode) && $sJavascriptCode[$i]!='/');
                $sJs .= $sJavascriptCode[$i++];
                $bMaybeRegex = false;
                continue;
            } elseif ($sJavascriptCode[$i]=='"' || $sJavascriptCode[$i]=="'") {//quoted string detected
                $quote = $sJavascriptCode[$i];
                do {
                    if ($sJavascriptCode[$i] == '\\') {
                        $sJs .= $sJavascriptCode[$i++];
                    }
                    $sJs .= $sJavascriptCode[$i++];
                } while ($i<strlen($sJavascriptCode) && $sJavascriptCode[$i]!=$quote);
                $sJs .= $sJavascriptCode[$i++];
                continue;
            } elseif ($sJavascriptCode[$i].$sJavascriptCode[$i+1]=='/*' && @$sJavascriptCode[$i+2]!='@') {//multi-line comment detected
                $i+=3;
                while ($i<strlen($sJavascriptCode) && $sJavascriptCode[$i-1].$sJavascriptCode[$i]!='*/') $i++;
                if ($current_char == "\n") $sJavascriptCode[$i] = "\n";
                else $sJavascriptCode[$i] = ' ';
            } elseif ($sJavascriptCode[$i].$sJavascriptCode[$i+1]=='//') {//single-line comment detected
                $i+=2;
                while ($i<strlen($sJavascriptCode) && $sJavascriptCode[$i]!="\n" && $sJavascriptCode[$i]!="\r") $i++;
            }



            $LF_needed = false;
            if (preg_match('/[\n\r\t ]/', $sJavascriptCode[$i])) {
                if (strlen($sJs) && preg_match('/[\n ]/', $sJs[strlen($sJs)-1])) {
                    if ($sJs[strlen($sJs)-1] == "\n") $LF_needed = true;
                    $sJs = mb_substr($sJs, 0, -1);
                }
                while ($i+1<strlen($sJavascriptCode) && preg_match('/[\n\r\t ]/', $sJavascriptCode[$i+1])) {
                    if (!$LF_needed && preg_match('/[\n\r]/', $sJavascriptCode[$i])) $LF_needed = true;
                    $i++;
                }
            }

            if (strlen($sJavascriptCode) <= $i+1) break;

            $current_char = $sJavascriptCode[$i];

            if ($LF_needed) $current_char = "\n";
            elseif ($current_char == "\t") $current_char = " ";
            elseif ($current_char == "\r") $current_char = "\n";

            // detect unnecessary white spaces
            if ($current_char == " ") {
                if (strlen($sJs) &&
                (
                    preg_match('/^[^(){}[\]=+\-*\/%&|!><?:~^,;"\']{2}$/', $sJs[strlen($sJs)-1].$sJavascriptCode[$i+1]) ||
                    preg_match('/^(\+\+)|(--)$/', $sJs[strlen($sJs)-1].$sJavascriptCode[$i+1]) // for example i+ ++j;
                )) $sJs .= $current_char;
            } elseif ($current_char == "\n") {
                if (strlen($sJs) &&
                (
                    preg_match('/^[^({[=+\-*%&|!><?:~^,;\/][^)}\]=+\-*%&|><?:,;\/]$/', $sJs[strlen($sJs)-1].$sJavascriptCode[$i+1]) ||
                    (strlen($sJs)>1 && preg_match('/^(\+\+)|(--)$/', $sJs[strlen($sJs)-2].$sJs[strlen($sJs)-1])) ||
                    (strlen($sJavascriptCode)>$i+2 && preg_match('/^(\+\+)|(--)$/', $sJavascriptCode[$i+1].$sJavascriptCode[$i+2])) ||
                    preg_match('/^(\+\+)|(--)$/', $sJs[strlen($sJs)-1].$sJavascriptCode[$i+1])// || // for example i+ ++j;
                )) $sJs .= $current_char;
            } else $sJs .= $current_char;

            // if the next charachter be a slash, detects if it is a divide operator or start of a regex
            if (preg_match('/[({[=+\-*\/%&|!><?:~^,;]/', $current_char)) $bMaybeRegex = true;
            elseif (!preg_match('/[\n ]/', $current_char)) $bMaybeRegex = false;

            $i++;
        }
        if ($i<strlen($sJavascriptCode) && preg_match('/[^\n\r\t ]/', $sJavascriptCode[$i])) {
            $sJs .= $sJavascriptCode[$i];
        }
        return $sJs;
    }

    /**
     * Minify CSS asset
     *
     * @param string $sCssCode          The CSS code to minify
     * @param string $sPathContext      The path context
     * @return string
     */
    public static function css($sCssCode, $sPathContext)
    {
        $sCss = '';
        $i=0;
        $inside_block = false;
        $current_char = '';
        while ($i+1<strlen($sCssCode)) {
            if ($sCssCode[$i]=='"' || $sCssCode[$i]=="'") {//quoted string detected
                $sCss .= $quote = $sCssCode[$i++];
                $sFileUrl = '';
                while ($i<strlen($sCssCode) && $sCssCode[$i]!=$quote) {
                    if ($sCssCode[$i] == '\\') {
                        $sFileUrl .= $sCssCode[$i++];
                    }
                    $sFileUrl .= $sCssCode[$i++];
                }
                if (strtolower(mb_substr($sCss, -5, 4))=='url(' || strtolower(mb_substr($sCss, -9, 8)) == '@import ') {
                    $sFileUrl = self::convertRelativePublicUrl($sFileUrl, mb_substr_count($sCssCode, $sFileUrl), $sPathContext);
                }
                $sCss .= $sFileUrl;
                $sCss .= $sCssCode[$i++];
                continue;
            } elseif (strtolower(mb_substr($sCss, -4))=='url(') {//url detected
                $sFileUrl = '';
                do {
                    if ($sCssCode[$i] == '\\') {
                        $sFileUrl .= $sCssCode[$i++];
                    }
                    $sFileUrl .= $sCssCode[$i++];
                } while ($i<strlen($sCssCode) && $sCssCode[$i]!=')');
                $sFileUrl = self::convertRelativePublicUrl($sFileUrl, mb_substr_count($sCssCode, $sFileUrl), $sPathContext);
                $sCss .= $sFileUrl;
                $sCss .= $sCssCode[$i++];
                continue;
            } elseif ($sCssCode[$i].$sCssCode[$i+1]=='/*') {//css comment detected
                $i+=3;
                while ($i<strlen($sCssCode) && $sCssCode[$i-1].$sCssCode[$i]!='*/') $i++;
                if ($current_char == "\n") $sCssCode[$i] = "\n";
                else $sCssCode[$i] = ' ';
            }

            if (strlen($sCssCode) <= $i+1) break;

            $current_char = $sCssCode[$i];

            if ($inside_block && $current_char == '}') {
                $inside_block = false;
            }

            if ($current_char == '{') {
                $inside_block = true;
            }

            if (preg_match('/[\n\r\t ]/', $current_char)) $current_char = " ";

            if ($current_char == " ") {
                $pattern = $inside_block?'/^[^{};,:\n\r\t ]{2}$/':'/^[^{};,>+\n\r\t ]{2}$/';
                if (strlen($sCss) && preg_match($pattern, $sCss[strlen($sCss)-1].$sCssCode[$i+1])) {
                    $sCss .= $current_char;
                }
            } else $sCss .= $current_char;

            $i++;
        }
        if ($i<strlen($sCssCode) && preg_match('/[^\n\r\t ]/', $sCssCode[$i])) {
            $sCss .= $sCssCode[$i];
        }
        return $sCss;
    }

    /**
     * Convert file content relative file found on asset to base64
     *
     * @param string $sFileUrl          File relative path (mostly found on url() css properties)
     * @param integer $count
     * @param string $sPathContext      The path context
     * @return string
     */
    private static function convertRelativePublicUrl($sFileUrl, $count, $sPathContext)
    {
        $sFileUrl = trim($sFileUrl);

        /**
         * Check if we got a full url or if not if the absolute path concatenated with the
         * relative file url is correctly linked with a DIRECTORY_SEPARATOR to avoid broken path
         */
        if (mb_substr($sFileUrl, 0, 3) === 'htt') {
            $sFullAbsoluteFilePath = $sFileUrl;
        } elseif ((mb_substr($sPathContext, -1) !== DIRECTORY_SEPARATOR) && (mb_substr($sFileUrl, 0, 1) !== DIRECTORY_SEPARATOR)) {
            $sFullAbsoluteFilePath = $sPathContext . DIRECTORY_SEPARATOR . $sFileUrl;
        } else {
            $sFullAbsoluteFilePath = $sPathContext . $sFileUrl;
        }

        if (preg_match('@^[^/]+:@', $sFileUrl)) return $sFileUrl;

        $fileType = mb_substr(strrchr($sFileUrl, '.'), 1);
        if (isset(self::$aMimeTypes[$fileType])) {
            $mimeType = self::$aMimeTypes[$fileType];
        } elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($sFullAbsoluteFilePath);
        } else {
            $mimeType = null;
        }

        $contents = Files::getContent($sFullAbsoluteFilePath);

        if ($fileType == 'css') {
            $oldFileDir = $sPathContext;
            $sPathContext = rtrim(dirname($sFullAbsoluteFilePath), '\/').'/';
            $oldBaseUrl = $baseUrl;
            $baseUrl = 'http'.(@$_SERVER['HTTPS']?'s':'').'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/').'/'.$sPathContext;
            $contents = minify_css($contents);
            $sPathContext = $oldFileDir;
            $baseUrl = $oldBaseUrl;
        }

        $base64   = base64_encode($contents);
        return 'data:' . $mimeType . ';base64,' . $base64;
    }


}

class MinifyException extends \Exception
{
}
