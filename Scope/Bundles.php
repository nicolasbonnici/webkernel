<?php
namespace Library\Core\Scope;

/**
 * Scope Bundles component
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
class Bundle extends Scope
{
    /**
     * Add a new Entity to the scope
     *
     * @param Entity $oEntity
     * @return EntityScope
     */
    public function add(\Library\Core\Entity $oEntity, array $aConstraints = array())
    {
        $this->aScope[$oEntity::ENTITY] = array(
            'class'         => get_class($oEntity),
            'collection'    => $oEntity->computeCollectionClassName(),
            'constraints'   => $aConstraints
        );
        return $this;
    }

    /**
     * Get constraints for a scope Entity
     *
     * @param string $sEntityName
     * @return array
     */
    public function getConstraints($sEntityName)
    {
        return (
            (isset($this->aScope[$sEntityName]['constraints']) === true)
                ? $this->aScope[$sEntityName]['constraints']
                : null
        );
    }
}

class BundlesException extends \Exception
{
}
