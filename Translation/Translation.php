<?php
namespace Library\Core\Translation;


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
    const GLOBAL_TRANSLATION_FILENAME = 'global.php';

    /**
     * @var array
     */
    protected $aTranslations = array();

    /**
     * Translation constructor
     * @param string $sLang
     * @param string $sBundleName
     */
    public function __construct($sLang, $sBundleName  = null)
    {
        $tr = array();

        require_once APP_PATH . '/Translations/' . $sLang . '/' . self::GLOBAL_TRANSLATION_FILENAME; // @see globale translation
        if (file_exists(BUNDLES_PATH . $sBundleName . '/Translations/' . $sLang . '.php')) {
            require_once BUNDLES_PATH . $sBundleName . '/Translations/' . $sLang . '.php';
        }

        /**
         * This php array come from the previous includes (ugly but fast)
         * @var array $tr
         */
        $this->build($tr);
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
     * Build all available translations
     *
     * @param array $aTranslations
     */
    private function  build(array $aTranslations)
    {
        $this->setTranslations($aTranslations);
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
    public function setTranslations(array $aTranslations)
    {
        $this->aTranslations = array_merge($this->aTranslations, $aTranslations);
        return $this;
    }
}