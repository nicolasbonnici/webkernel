<?php
namespace Library\Core\Traits;

use Library\Core\Bootstrap;

/**
 * Toolbox on namespace manipulation
 *
 * Class NamespaceTools
 * @package Library\Core\Traits
 */
trait NamespaceTools
{
    /**
     * Compute a path from a given namespace
     *
     * @param string $sNamespace
     * @param bool $bWithClassName          Flag to include class name on the returned path
     * @return string
     */
    public function computeAbsolutePathFromNamespace($sNamespace, $bWithClassName = true)
    {
        $aNamespace = explode('\\', $sNamespace);
        if ($bWithClassName === false) {
            # Remove the Class from the namespace
            $aNamespace = array_slice($aNamespace, 0, count($aNamespace) - 1);
        }
        return Bootstrap::getRootPath() . implode(DIRECTORY_SEPARATOR, $aNamespace);
    }
}