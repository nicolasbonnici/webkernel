<?php
namespace Library\Core\Scope;

/**
 * Bad practices
 */


/**
 * EntityScope component to manage Entities parameters with constraints on field support
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
class EntitiesScope extends Scope
{
    /**
     * Add a new Entity to the scope
     *
     * @param Entity $oEntity
     * @return EntityScope
     */
    public function add(\Library\Core\Orm\Entity $oEntity, array $aConstraints = array())
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

class EntitiesScopeException extends \Exception
{
}
