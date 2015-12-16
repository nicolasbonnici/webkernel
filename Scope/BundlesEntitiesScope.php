<?php
namespace Library\Core\Scope;

use Library\Core\Bootstrap;
use Library\Core\FileSystem\Directory;
use Library\Core\Entity\Parser;

/**
 * Scope Bundles component
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
class BundlesEntitiesScope extends BundlesScope
{
    const FILTER_ENTITY_SEARCH  = 'isSearchable';
    const FILTER_ENTITY_CACHE   = 'isCacheable';
    const FILTER_ENTITY_DELETE  = 'isDeletable';
    const FILTER_ENTITY_HISTORY = 'isHistorized';

    protected $aAllowedFilters = array(
        self::FILTER_ENTITY_SEARCH,
        self::FILTER_ENTITY_CACHE,
        self::FILTER_ENTITY_DELETE,
        self::FILTER_ENTITY_HISTORY
    );

    /**
     * Current filter
     * @var string
     *
     * @todo array here
     */
    protected $sFilter = null;

    public function __construct($bGetProjectEntities = false)
    {
        parent::__construct();
        $this->build($bGetProjectEntities);
    }

    /**
     * Parse bundles entities with filter support
     */
    protected function build($bGetProjectEntities)
    {
        foreach($this->getScope() as $sBundlesName => $mFreeDimension) {
            if (Directory::exists(Bootstrap::getPath(Bootstrap::PATH_BUNDLES) . $sBundlesName . '/Entities/')) {
                $oEntityParser = new Parser(Bootstrap::getPath(Bootstrap::PATH_BUNDLES) . $sBundlesName . '/Entities/');
                $aBundleEntities = $oEntityParser->getEntities();
                if (count($aBundleEntities) > 0) {
                    /** @var \Library\Core\Entity\Entity $oEntity */
                    foreach ($aBundleEntities as $oEntity) {
                        if (is_null($this->sFilter) === true || $oEntity->$this->sFilter() === true) {
                            $this->aScope[$sBundlesName][] = $oEntity;
                        }
                    }
                } else {
                    // No Entities found or compatible with filter for this bundle so we exclude it from scope
                    $this->delete($sBundlesName);
                }
            } else {
                // This bundle has no Entities folder so we exclude it from the scope
                $this->delete($sBundlesName);
            }
        }

        // Also add project's entities when flag requested
        if ($bGetProjectEntities === true) {
            $oEntityParser = new Parser(Bootstrap::getPath(Bootstrap::PATH_APP) . 'Entities/');
            $aEntities = $oEntityParser->getEntities();
            foreach ($aEntities as $oEntity) {
                $this->aScope['app'][] = $oEntity;
            }

        }

    }

    /**
     * Set filter for bundle's entities
     *
     * @param $sFilter
     * @return BundlesEntitiesScope|null
     */
    public function setFilter($sFilter)
    {
        if (in_array($sFilter, $this->aAllowedFilters) === true) {
            $this->sFilter = $sFilter;
            return $this;
        }
        return null;
    }

    /**
     * Filter accessor
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->sFilter;
    }

}

class BundlesEntitiesScopeException extends \Exception
{
}
