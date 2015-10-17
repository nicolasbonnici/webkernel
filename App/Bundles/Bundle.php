<?php
namespace Library\Core\App\Bundles;


use Library\Core\App\Configuration;
use Library\Core\Bootstrap;
use Library\Core\Exception\CoreException;
use Library\Core\FileSystem\Directory;
use Library\Core\App\Bundles\Bundles;

/**
 * Bundles common layer
 *
 * Class Bundle
 * @package Library\Core\App\Bundles
 */
class Bundle
{

    /**
     * Bundles Configuration keys
     */
    const CONFIGURATION_KEY_VERSION        = 'version';
    const CONFIGURATION_KEY_VENDOR_NAME    = 'vendor';
    const CONFIGURATION_KEY_PROJECT_AUTHOR = 'author';
    const CONFIGURATION_KEY_REPOSITORY     = 'repository';
    const CONFIGURATION_KEY_PROJECT_URL    = 'url';
    const CONFIGURATION_KEY_SUPPORT_INFO   = 'support';

    /**
     * Configuration keys for the default frontend/backend template
     */
    const CONFIGURATION_KEY_TEMPLATE_FRONTEND   = 'template';
    const CONFIGURATION_KEY_TEMPLATE_BACKEND    = 'backend.template';

    /**
     * Default template name configuration value
     */
    const CONFIGURATION_KEY_TEMPLATE_NAME_DEFAULT    = 'default';

    /**
     * Generic translation keys for bundle name and description
     */
    const TRANSLATION_KEY_BUNDLE_NAME           = 'bundle_name';
    const TRANSLATION_KEY_BUNDLE_DESCRIPTION    = 'bundle_description';

    /**
     * Bundle's folder names
     * @var string
     */
    const PATH_ASSETS           = 'Assets';
    const PATH_CONTROLLERS      = 'Controllers';
    const PATH_ENTITIES         = 'Entities';
    const PATH_MODELS           = 'Models';
    const PATH_TRANSLATION      = 'Translations';
    const PATH_VIEWS            = 'Views';
    const PATH_TESTS            = 'Tests';

    /**
     * Bundle folders
     *
     * @var array
     */
    protected $aBundlePaths = array(
        self::PATH_ASSETS,
        self::PATH_ENTITIES,
        self::PATH_MODELS,
        self::PATH_TRANSLATION,
        self::PATH_VIEWS
    );

    /**
     * Bundle name
     *
     * @var string
     */
    protected $sName;

    /**
     * Bundle version
     * @var string
     */
    protected $sVersion;

    /**
     * Bundle vendor name
     *
     * @var string
     */
    protected $sVendorName;

    /**
     * Bundle author
     *
     * @var string
     */
    protected $sAuthor;

    /**
     * Bundle repository
     *
     * @var string
     */
    protected $sRepository;

    /**
     * Bundle project url
     *
     * @var string
     */
    protected $sProjectUrl;

    /**
     * Bundle support information
     *
     * @var string
     */
    protected $sSupportInformation;

    /**
     * Bundle name translation key
     *
     * @var string
     */
    protected $sDisplayName = self::TRANSLATION_KEY_BUNDLE_NAME;

    /**
     * Bundle description translation key
     * @var string
     */
    protected $sDescription = self::TRANSLATION_KEY_BUNDLE_DESCRIPTION;

    /**
     * Bundle Configuration instance
     *
     * @var Configuration
     */
    protected $oConfiguration;

    /**
     * Bundle current template Template instance
     *
     * @var Template
     */
    protected $oTemplate;

    /**
     * Tell if the bundle was properly loaded
     */
    protected $bIsloaded = false;

    /**
     * Bundle constructor
     *
     * @param string $sBundleName
     * @throws BundleException
     */
    public function __construct($sBundleName)
    {
        # Directly set the bundle name
        $this->sName = $sBundleName;

        if ($this->exists() === false) {
            # Bundle not found
            throw new BundleException(
                sprintf(
                    BundleException::getError(BundleException::ERROR_BUNDLE_NOT_FOUND),
                    $sBundleName
                ),
                BundleException::ERROR_BUNDLE_NOT_FOUND
            );
        } elseif ($this->loadBundleConfiguration() === false) {
            # Bundle configuration not found
            throw new BundleException(
                sprintf(
                    BundleException::getError(BundleException::ERROR_BUNDLE_CONFIGURATION_NOT_FOUND),
                    $sBundleName
                ),
                BundleException::ERROR_BUNDLE_CONFIGURATION_NOT_FOUND
            );
        } else {
            # At this level we have an existent bundle with loaded Configuration instance
            if ($this->isLoaded() === true) {

                # Detect if the bundle use the Template engine and if so create a new Template component instance
                $this->loadBundleTemplate();

            }
        }

    }

    /**
     * Generic bundle install process
     *
     * @return bool
     */
    public function install()
    {
        # Set configuration variables
        //  version etc..
    }

    /**
     * Generic bundle uninstall process
     *
     * @return bool
     */
    public function uninstall()
    {
        # remove all mapped configuration
    }

    /**
     * Load bundle configuration from database
     *
     * @return bool
     */
    protected function loadBundleConfiguration()
    {
        try {
            $this->oConfiguration = new Configuration( $this->getName() );

            $this->sVersion            = $this->oConfiguration->get(self::CONFIGURATION_KEY_VERSION);
            $this->sAuthor             = $this->oConfiguration->get(self::CONFIGURATION_KEY_PROJECT_AUTHOR);
            $this->sVendorName         = $this->oConfiguration->get(self::CONFIGURATION_KEY_VENDOR_NAME);
            $this->sRepository         = $this->oConfiguration->get(self::CONFIGURATION_KEY_REPOSITORY);
            $this->sProjectUrl         = $this->oConfiguration->get(self::CONFIGURATION_KEY_PROJECT_URL);
            $this->sSupportInformation = $this->oConfiguration->get(self::CONFIGURATION_KEY_SUPPORT_INFO);

            if (is_null($this->getVersion()) === true) {
                # No version information found, this information is mandatory, bundle isn't correctly installed...0
                $this->bIsloaded = false;
            } else {
                $this->bIsloaded = true;
            }
        } catch (\Exception $oException) {
            $this->bIsloaded = false;
        }
        return $this->isLoaded();
    }

    /**
     * Retrieve default template from Configuration for the current mode if available and assign it to instance
     */
    protected function loadBundleTemplate()
    {
        # Retrieve bundle's default template from database bundle's configuration
        $sDefaultTemplate = $this->oConfiguration->get(self::CONFIGURATION_KEY_TEMPLATE_FRONTEND);
        if (is_null($sDefaultTemplate) === true) {
            # No default template in databse so the bundle doesn't use the Template engine
            $this->oTemplate = null;
        } else {
            $this->oTemplate = new Template($this);
        }
    }

    /**
     * Compute the absolute bundle path
     *
     * @return string
     */
    protected function computeBundlePath()
    {
        return Bootstrap::getPath(Bootstrap::PATH_BUNDLES) . $this->sName . DIRECTORY_SEPARATOR;
    }

    /**
     * Tell if a bundle exists
     *
     * @return bool
     */
    protected function exists()
    {
        if (is_string($this->sName) === false || empty($this->sName) === true) {
            return false;
        } else {
            return Directory::exists($this->computeBundlePath());
        }

    }

    /**
     * Generic database bundle configuration accesss
     *
     * @param $sKey
     * @return string
     */
    public function getConf($sKey)
    {
        return $this->oConfiguration->get($sKey);
    }

    /**
     * Get the full absolute path to a bundle sub folder or the bundle path itself
     *
     * @param string $sPath     NULL to retrieve the bundle path
     * @return string
     */
    public function getPath($sPath = null)
    {
        if (is_null($sPath) === true) {
            return $this->computeBundlePath();
        } elseif (is_string($sPath) === true && in_array($sPath, $this->aBundlePaths) === true) {
            return $this->computeBundlePath() . $sPath . DIRECTORY_SEPARATOR;
        }
        return null;
    }

    /**
     * Get bundle name
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * Get the bundle version (stored in database)
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->sVersion;
    }

    /**
     * Get bundle description translation key
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->sDescription;
    }

    /**
     * Get bundle name translation key
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->sDisplayName;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->sAuthor;
    }

    /**
     * @return string
     */
    public function getProjectUrl()
    {
        return $this->sProjectUrl;
    }

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->sRepository;
    }

    /**
     * @return string
     */
    public function getSupportInformation()
    {
        return $this->sSupportInformation;
    }

    /**
     * @return string
     */
    public function getVendorName()
    {
        return $this->sVendorName;
    }

    /**
     * Bundle Template instance accessor
     *
     * @return Template
     */
    public function getTemplate()
    {
        return $this->oTemplate;
    }

    /**
     * Tell if the bundle use the template engine or not
     *
     * @return bool
     */
    public function hasTemplate()
    {
        return (bool) (is_null($this->oTemplate) === false);
    }

    /**
     * Get current bundle template path
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->oTemplate->getPath();
    }

    /**
     * Tell if the Bundle was correctly loaded
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->bIsloaded;
    }

}

class BundleException extends CoreException
{

    const ERROR_BUNDLE_NOT_FOUND                = 2;
    const ERROR_BUNDLE_CONFIGURATION_NOT_FOUND  = 3;

    public static $aErrors = array(
        self::ERROR_BUNDLE_NOT_FOUND                => 'Bundle with name "%s" was not found.',
        self::ERROR_BUNDLE_CONFIGURATION_NOT_FOUND  => 'Configuration for bundle "%s" was not found.'
    );
}