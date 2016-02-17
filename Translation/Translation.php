<?php
namespace Library\Core\Translation;

use Library\Core\Bootstrap;
use Library\Core\Entity\Entity;
use Library\Core\FileSystem\File;
use Library\Core\FileSystem\FileSystem;
use Library\Core\Scope\BundlesScope;


/**
 * Class Translation
 * @package Library\Core\Translation
 */
class Translation
{

    /**
     * Global translation filename
     * @var string
     */
    const TRANSLATION_FOLDER_NAME = 'Translations';

    /**
     * Global translation filename
     * @var string
     */
    const GLOBAL_TRANSLATION_FILENAME = 'global.php';

    /**
     * @var string
     */
    protected $sLocale;

    /**
     * @var array
     */
    protected $aTranslations = array();

    /**
     * Translation constructor
     * @param string $sLocale
     * @param string $sBundleName
     */
    public function __construct($sLocale, $sBundleName = null)
    {
        # Set the current locale value
        $this->setLocale($sLocale);

        # Global project translation
        $tr = array();
        $sGlobalTrPath = Bootstrap::getPath(Bootstrap::PATH_APP) . FileSystem::DS . self::TRANSLATION_FOLDER_NAME
            . FileSystem::DS . $this->getLocale() . FileSystem::DS . self::GLOBAL_TRANSLATION_FILENAME;
        if (File::exists($sGlobalTrPath) === true) {
            include $sGlobalTrPath;
        }

        # This php array come from the previous includes (ugly but fastest)
        $this->addTranslations($tr);

        # Load bundle level translation, if no bundle name was provided at instance we load all available bundle translations
        if (is_null($sBundleName) === true) {
            # Load all bundles translation
            $this->loadBundles();
        } elseif (is_string($sBundleName) === true) {
            # Load bundle translation
            $this->loadByBundle($sBundleName);
        }

    }

    /**
     * Load translations for all available bundles
     */
    private function loadBundles()
    {
        $oBundles = new BundlesScope();
        foreach ($oBundles->getScope() as $sBundle => $mValue) {
            $this->loadByBundle($sBundle);
        }
    }

    /**
     * Load translation for a given bundle
     *
     * @param $sBundleName
     */
    private function loadByBundle($sBundleName)
    {
        $tr = array();
        $sTranslationPath = Bootstrap::getPath(Bootstrap::PATH_BUNDLES) . $sBundleName . FileSystem::DS
            . self::TRANSLATION_FOLDER_NAME . FileSystem::DS . $this->getLocale() . '.php';
        if (file_exists($sTranslationPath)) {
            include $sTranslationPath;
        }
        $this->addTranslations($tr);
    }

    /**
     * Retrieve a translation for a given key
     * @param string $sTranslationKey
     * @return mixed array|string       String if key was found otherwise whole available translations
     */
    public function get($sTranslationKey)
    {
        if (empty($sTranslationKey) === true || isset($this->aTranslations[$sTranslationKey]) === false) {
            return $this->getTranslations();
        }
        return $this->aTranslations[$sTranslationKey];
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        return $this->aTranslations;
    }

    /**
     * @param array $aTranslations
     * @return Translation
     */
    public function addTranslations(array $aTranslations)
    {
        $this->aTranslations = array_merge($this->aTranslations, $aTranslations);
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->sLocale;
    }

    /**
     * @param string $sLocale
     */
    public function setLocale($sLocale)
    {
        $this->sLocale = $sLocale;
    }

}