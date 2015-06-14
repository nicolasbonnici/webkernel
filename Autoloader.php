<?php
namespace Core;

class Autoloader {

    protected $aNamespacePrefixes;

    /**
     * Loaded classes for debug
     *
     * @var array
     */
    protected static $aLoadedClass = array();

    /**
     * Register class autoload
     */
    public function __construct()
    {
        $this->register();
    }

    /**
     * Autoload any class that use namespaces (PSR-4)
     *
     * @param string $sClassName
     */
    public function load($sClassName)
    {
        $sFileName = $this->searchFile($sClassName);
        if (is_file($sFileName)) {
            require_once ROOT_PATH . $sFileName;
        } else {
            die(var_dump('Non trouvé ' . ROOT_PATH . $sFileName));
        }

    }

    /**
     * Register called class for debug purposes
     *
     * @param string $sClassname            Class component name
     * @param string $sComponentPath        Class component path
     */
    public static function registerLoadedClass($sClassname, $sComponentPath = '')
    {
        if (strlen($sClassname) > 0) {
            self::$aLoadedClass[$sClassname . (($sComponentPath != '') ? ' (' . $sComponentPath . ')' : '')] = round(microtime(true) - FRAMEWORK_STARTED, 3);
        }
    }

    /**
     * Loaded classes accessor
     * @return array
     */
    public static function getLoadedClass()
    {
        return self::$aLoadedClass;
    }

    /**
     * Search for a class
     * @return string
     */
    protected function searchFile($sClassName)
    {

        var_dump($sClassName);
        $sFileName = '';
        $sComponentNamespace = '';
        // PSR4 autoload
        $iLastNsPos = $this->detectPsr4($sClassName);
        if ($iLastNsPos > 0) {
            $sComponentNamespace = substr($sClassName, 0, $iLastNsPos);

            $sFileName = str_replace('\\', DIRECTORY_SEPARATOR, $sComponentNamespace) . DIRECTORY_SEPARATOR;
        } else {
            // PSR 0 Autoload failback
            $sClassName = substr($sClassName, $iLastNsPos + 1);
            $sFileName .= str_replace('_', DIRECTORY_SEPARATOR, $sClassName) . '.php';
        }

        return (
            (is_file(ROOT_PATH . $sFileName))
            ? ROOT_PATH . $sFileName
            : null
        );
    }

    /**
     * Detect PSR 4 class name
     * @param $sClassname
     * @return int
     */
    protected function detectPsr4($sClassname)
    {
        return strripos($sClassname, '\\');
    }

    /**
     * Register autoloading method
     *
     * @param bool $bPrepend
     * @see spl_autoload
     */
    public function register($bPrepend = false)
    {
        spl_autoload_register(array($this, 'load'), true, $bPrepend);
    }

    /**
     * @todo ?? call at __destruct()??
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'load'));
    }
}