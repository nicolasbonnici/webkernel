<?php
namespace Library\Core\App\Bundles;

use Library\Core\Exception\CoreException;
use Library\Core\FileSystem\Directory;

/**
 * That class manage template usage for bundles
 *
 * Class TemplateAbstract
 * @package Library\Core\App\Template
 */
class Template
{

    /**
     * Template name
     * @var string
     */
    protected $sName = '';


    /**
     * Current bundle that use this template
     * @var Bundle
     */
    protected $oBundle;

    public function __construct(Bundle $oBundle)
    {

        if ($oBundle->isLoaded() === false) {
            throw new TemplateException(
                sprintf(
                    TemplateException::getError(TemplateException::ERROR_BUNDLE_NOT_LOADED),
                    $oBundle->getName()
                ),
                TemplateException::ERROR_BUNDLE_NOT_LOADED
            );
        }

        # Assign related bundle
        $this->oBundle = $oBundle;

        # Load bundle's template
        $this->load();

        if ($this->isBundleTemplateExists() === false) {
            throw new TemplateException(
                sprintf(
                    TemplateException::getError(TemplateException::ERROR_THEME_NOT_FOUND),
                    $oBundle->getName()
                ),
                TemplateException::ERROR_THEME_NOT_FOUND
            );
        }

    }

    /**
     * Load Bundle's template
     */
    protected function load()
    {
        # Load theme info
        $this->sName = $this->oBundle->getConf(Bundle::CONFIGURATION_KEY_TEMPLATE_FRONTEND);
    }

    /**
     * Compute template path
     *
     * @return string
     */
    protected function computePath()
    {
        return $this->oBundle->getPath(Bundle::PATH_VIEWS) . $this->getName() . DIRECTORY_SEPARATOR;
    }

    /**
     * Check if the bundle's template was found for the given mode
     * @return bool
     */
    protected function isBundleTemplateExists()
    {
        return (bool) (Directory::exists($this->computePath()) === true);
    }

    /**
     * Get template name
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    public function getPath()
    {
        return $this->computePath();
    }

}

class TemplateException extends CoreException
{

    const ERROR_BUNDLE_NOT_LOADED = 2;
    const ERROR_THEME_NOT_FOUND  = 3;

    public static $aErrors = array(
        self::ERROR_BUNDLE_NOT_LOADED=> 'Bundle "%s" not loaded.',
        self::ERROR_THEME_NOT_FOUND  => 'Default template not found for bundle "%s".'
    );
}