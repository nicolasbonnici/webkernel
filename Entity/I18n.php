<?php
namespace Library\Core\Entity;

use app\Entities\Translation;
use Library\Core\Json\Json;

/**
 * Entity internationalization component
 *
 * Class EntityI18n
 * @package Library\Core\Entity
 */
abstract class I18n
{

    /**
     * Load Entity internationalization
     *
     */
    public function loadTranslation()
    {
        if ($this->isLoaded() === true) {
            $oTranslation = $this->getTranslation($this->getLocale());
            if (is_null($oTranslation) === false && $oTranslation->isLoaded() === true) {
                $oJsonTr = new Json($oTranslation->content);
                $aJsonTr = $oJsonTr->getAsArray();
                foreach ($this->getTranslatedAttributes() as $sTranslatedKey) {
                    if (isset($aJsonTr[$sTranslatedKey]) === true) {
                        $this->$sTranslatedKey = $aJsonTr[$sTranslatedKey];
                    }
                }
            }
        }
    }

    /**
     * Add a new translation for Entity
     *
     * @param mixed string|int $sKey
     * @param string $sTranslation
     * @return bool
     */
    public function setTranslation($sKey, $sTranslation)
    {
        try {
            $sLocale = $this->getLocale();
            if (
                $this->isLoaded() === true &&
                empty($sKey) === false &&
                empty($sTranslation) === false &&
                empty($sLocale) === false
            ) {
                $oTranslation = $this->getTranslation();
                if (is_null($oTranslation) === true || $oTranslation->isLoaded() !== true) {
                    # Create new translation entity
                    $oTranslation = new Translation();
                    $oJsonContent = new Json(array(
                        $sKey => $sTranslation
                    ));

                    $oTranslation->entity_class = str_replace('\\', '\\\\', $this->getChildClass());
                    $oTranslation->pk           = $this->getId();
                    $oTranslation->content      = $oJsonContent->__toString();
                    $oTranslation->locale       = $sLocale;
                    $oTranslation->lastupdate   = time();
                    $oTranslation->created      = time();
                    return $oTranslation->add();
                } else {
                    # Update found translation
                    $oJsonContent = new Json($oTranslation->content);
                    $aJsonContent = $oJsonContent->getAsArray();

                    # Add the new translation on content
                    $aUpdatedContent = array_merge($aJsonContent, array($sKey => $sTranslation));
                    $oUpdatedJson = new Json($aUpdatedContent);

                    $oTranslation->content = $oUpdatedJson->__toString();
                    $oTranslation->lastupdate   = time();
                    return $oTranslation->update();
                }
            }
            return false;
        } catch(\Exception $oException) {
            return false;
        }
    }

    /**
     * Delete the mapped Entity translation
     *
     * @return bool
     */
    public function deleteTranslation()
    {
        try {
            $oTranslation = $this->getTranslation();
            if (is_null($oTranslation) === false) {
                return $oTranslation->delete();
            }
            return true;
        } catch(\Exception  $oException) {
            return false;
        }
    }

    /**
     * Get the mapped translation for Entity instance
     *
     * @return Translation
     * @throws EntityException
     */
    protected function getTranslation()
    {
        try {
            $oTranslation = new Translation();
            $oTranslation->loadByParameters(
                array(
                    Translation::KEY_ENTITY_CLASS       => $this->getChildClass(),
                    Translation::KEY_PRIMARY_KEY        => $this->getId(),
                    Translation::KEY_COUNTRY_LANGUAGE   => $this->getLocale()
                )
            );
            return $oTranslation;
        } catch(\Exception $oException) {
            return null;
        }
    }

}