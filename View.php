<?php
namespace Library\Core;

use Library\Core\Directory;
use \Library\Core\Router;

/**
 * View managment
 *
 * @dependancy \Library\Haanga
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class View
{

    /**
     *  Assets managment
     *  @var \Libraries\Core\Assets
     */
    private static $oAssetsInstance;

    /**
     * Registered client side components to load for the frontend view
     * @see app/config/assets.json
     * @var array
     */
    protected $aClientComponents = array(
        'dependancies',
        'core'
    );

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
            BUNDLES_PATH . Router::getBundle() . '/Views/',
            APP_PATH . 'Views/'
        );

        if ($bLoadAllBundleViews) {
            $oBundles = new Bundles();
            foreach ($oBundles->get() as $sBundle => $aController) {
                if ($sBundle !== Router::getBundle()) {
                    $aViewsPaths[] = BUNDLES_PATH . $sBundle . '/Views/';
                }
            }
        }

        // Setup client componetns dependancy managment
        self::$oAssetsInstance = new Assets();

        // Setup Haanga render engine
        \Haanga::configure(array(
            'template_dir' => $aViewsPaths,
            'cache_dir' => CACHE_PATH . Router::getBundle() . '/Views'
        ));
    }

    /**
     * Render request
     *
     * @param string $sTpl
     * @param integer $iStatusXHR
     * @param boolean $bToString
     */
    public function render(array $aViewParams, $sTpl, $iStatusXHR = Controller::XHR_STATUS_OK, $bToString = false)
    {
        $this->loadViewParameters($aViewParams);

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
                    'debug' => isset($this->aView["sDeBugHelper"]) ? str_replace(
                        array(
                            "\r",
                            "\r\n",
                            "\n",
                            "\t"
                        ), '', $this->load($this->aView["sDeBugHelper"], $this->aView, true)
                    ) : null
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
            if (! Directory::deleteDirectory(CACHE_PATH)) {
                throw  new AppException('Unable to clear cache folder (' . CACHE_PATH . ')');
            }
            return Directory::exists(CACHE_PATH);
        } catch (\AppException $oAppException) {
            return false;
        }
    }

    /**
     * Register a new client component package
     *
     * @see app/config/assets.json
     * @param string $sComponentName
     * @return boolean
     */
    public function registerClientComponent($sComponentName)
    {
        if (array_key_exists($sComponentName, $this->aClientComponents)) {
            return false;
        } else {
            $this->aClientComponents[] = $sComponentName;
            return true;
        }
    }


    /**
     * Build the parameters to send to the template view
     *
     * @param array $aViewParams
     * @return boolean
     */
    private function loadViewParameters(array $aViewParams = array())
    {
        foreach ($aViewParams as $key => $val) {
            $this->aView[$key] = $val;
        }
        return (count($this->aView) > 0);
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

    /**
     * Registered client components accessor
     *
     * @return array
     */
    public function getClientComponents()
    {
        return $this->aClientComponents;
    }
}
