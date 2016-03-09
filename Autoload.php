<?php

namespace Library\Core;


class Autoload {

    /**
     * Autoload prefixes
     * @var array
     */
    protected $aPrefixes = array(
        'Library/',
        'Library/Haanga/'
    );

    /**
     * Loaded classes for debug
     *
     * @var array
     */
    protected $aLoadedClass = array();

    /**
     * Find a class on project
     *
     * @param string $sClassName
     * @return string           The complete absolute path of the class otherwise NULL
     */
    protected function findClass($sClassName)
    {
        $sAbsoluteProjectRootPath = Bootstrap::getRootPath();
        $sComponentName = ltrim($sClassName, '\\');
        $sFileName = '';
        $sComponentNamespace = '';
        // PSR4 compliant class
        if ($lastNsPos = strripos($sClassName, '\\')) {
            $sComponentNamespace = substr($sClassName, 0, $lastNsPos);
            $sClassName = substr($sClassName, $lastNsPos + 1);

            $sFileName = str_replace('\\', DIRECTORY_SEPARATOR, $sComponentNamespace) . DIRECTORY_SEPARATOR;
        }
        $sFileName .= str_replace('_', DIRECTORY_SEPARATOR, $sClassName) . '.php';

        # Natural PSR4 resolution
        if (file_exists($sAbsoluteProjectRootPath . $sFileName) === true) {
            $this->registerLoadedClass($sClassName, $sComponentNamespace);
            return $sAbsoluteProjectRootPath . $sFileName;
        } else {
            # Try with instance prefixes
            foreach ($this->aPrefixes as $sPrefix) {
                if (file_exists($sAbsoluteProjectRootPath . $sPrefix . $sFileName) === true) {
                    $this->registerLoadedClass($sClassName, $sComponentNamespace);
                    return $sAbsoluteProjectRootPath . $sPrefix . $sFileName;
                }
            }

        }
        return null;
    }

    /**
     * Autoload any class that use namespaces (PSR-4)
     * @param $sClassName
     * @return bool         TRUE if class was loaded otherwise FALSE
     */
    public function load($sClassName)
    {
        $sFilename = $this->findClass($sClassName);
        if (is_null($sFilename) === false) {
            require $sFilename;
            return true;
        }
        return false;
    }

    /**
     * Register called class for debug purposes
     *
     * @todo also use for cache managment not only @load but persistent with memcache
     * @param string $sClassname            Class component name
     * @param string $sComponentPath        Class component path
     */
    protected function registerLoadedClass($sClassname, $sComponentPath = '')
    {
        if (strlen($sClassname) > 0) {
            $this->aLoadedClass[$sClassname . (($sComponentPath != '') ? ' (' . $sComponentPath . ')' : '')] = round(microtime(true) - FRAMEWORK_STARTED, 3);
        }
    }

    /**
     *
     * @return mixed
     */
    public function getLoadedClass()
    {
        return $this->aLoadedClass;
    }

    /**
     * Add a PSR 4 compliant prefix
     *
     * @param string $sNsPrefix
     * @param string $sRelativePath
     */
    public function addPrefix($sNsPrefix, $sRelativePath)
    {
        $this->aPrefixes[] = array($sNsPrefix, $sRelativePath);
        return $this;
    }

    public function getPrefixes()
    {
        return $this->aPrefixes;
    }

    /**
     * Register this Autoloader component instance
     *
     * @param bool $bPrepend
     * @return bool
     */
    public function register($bPrepend = false)
    {
        return spl_autoload_register(array($this, 'load'), true, $bPrepend);
    }


    /**
     * Removes this instance from the registered autoloaders
     *
     * @return bool
     */
    public function unregister()
    {
        return spl_autoload_unregister(array($this, 'load'));
    }

}