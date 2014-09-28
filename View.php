<?php
namespace Library\Core;

/**
 * View managment
 *
 * @todo deplacer ici le chargement des assets (App)
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
            APP_PATH . 'Views/',
            BUNDLES_PATH . \Library\Core\Router::getBundle() . '/Views/'
        );

        if ($bLoadAllBundleViews) {
            $oBundles = new Bundles();
            foreach ($oBundles->get() as $sBundle => $aController) {
                if ($sBundle !== \Library\Core\Router::getBundle()) {
                    $aViewsPaths[] = BUNDLES_PATH . $sBundle . '/Views/';
                }
            }
        }

        // Setup client componetns dependancy managment
        self::$oAssetsInstance = new Assets();


        // Setup Haanga render engine
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
    public function render(array $aViewParams, $sTpl, $iStatusXHR = Controller::XHR_STATUS_OK, $bToString = false)
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
     * Build registered components
     *
     * @return array
     */
    public function buildClientComponents()
    {
        return $this->aClientComponents;
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
