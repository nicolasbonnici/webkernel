<?php
namespace Library\Core;

/**
 * Bundles managment
 *
 * @dependancy \Library\Haanga
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class View
{

    /**
     * Errors codes
     *
     * @var integer
     */
    const XHR_STATUS_OK = 1;
    const XHR_STATUS_ERROR = 2;
    const XHR_STATUS_ACCESS_DENIED = 3;
    const XHR_STATUS_SESSION_EXPIRED = 4;

    /**
     * View instance constructor
     *
     * @param boolean $bLoadAllBundleViews      A flag to load all bundles views path (For the CrudController)
     */
    public function __construct($bLoadAllBundleViews = false)
    {
        $sHaangaPath = LIBRARY_PATH . 'Haanga/';
        require_once $sHaangaPath . 'Haanga.php';

        $aViewsPaths = array(
            APP_PATH . 'Views/',
            BUNDLES_PATH . \Library\Core\Router::getBundle() . '/Views/'
        );

        if ($bLoadAllBundleViews && count(self::$aBundles) > 0) {
            $oBundles = new Bundles();
            die(var_dump($oBundles));
            foreach ($oBundles->get() as $sBundle => $aController) {
                if ($sBundle !== \Library\Core\Router::getBundle()) {
                    $aViewsPaths[$sBundle] = BUNDLES_PATH . $sBundle . '/Views/';
                }
            }
        }

        \Haanga::configure(array(
            'template_dir' => $aViewsPaths,
            'cache_dir' => CACHE_PATH . \Library\Core\Router::getBundle() . '/Views'
        ));
    }

    /**
     * Render request
     *
     * @param string $sTpl
     * @param integer $iStatusXHR
     * @param boolean $bToString
     */
    public function render(array $aViewParams, $sTpl, $iStatusXHR = self::XHR_STATUS_OK, $bToString = false)
    {

        if (count($aViewParams) > 0) {
            foreach ($aViewParams as $key => $val) {
                $this->aView[$key] = $val;
            }
        }

        // check if it's an XMLHTTPREQUEST
        if (Controller::isXHR()) {
            $aResponse = json_encode(
                array(
                    'status' => $iStatusXHR,
                    'content' => str_replace(array(
                        "\r",
                        "\r\n",
                        "\n",
                        "\t"
                    ), '', $this->load($sTpl, $this->aView, true)),
                    'debug' => str_replace(
                        array(
                            "\r",
                            "\r\n",
                            "\n",
                            "\t"
                        ), '', $this->load($this->aView["sDeBugHelper"], $this->aView, true)
                    )
                )
            );
            if ($bToString === true) {
                return $aResponse;
            }

            header('Content-Type: application/json');
            echo $aResponse;
            exit();
        }

        // Render the view using Haanga
        $this->load($sTpl, $this->aView, $bToString);
    }


    /**
     * Clear rendering engine cache files for each bundle's views
     *
     * @param string $bRetry
     * @throws AppException
     * @return boolean
     */
    public function clearCache($bRetry = false)
    {
        try {
            if (! Directories::deleteDirectory(CACHE_PATH)) {
                throw  new AppException('Unable to clear cache folder (' . CACHE_PATH . ')');
            }
        } catch (\AppException $oAppException) {
            return false;
        }
    }

    /**
     * Init template engine and render view
     *
     * @param string $sTpl
     * @param array $aViewParams
     * @param boolean $bToString
     */
    private function load($sTpl, $aViewParams, $bToString)
    {
        return \Haanga::load($sTpl, $aViewParams, $bToString);
    }
}
