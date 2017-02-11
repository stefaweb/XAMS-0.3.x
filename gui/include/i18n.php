<?php
/**
   @package default
 */

/**
 * Internationalization (i18n) management class.
 *
 * This class is responsible for the whole internationalization management.
 */
class i18n
{

    public function &factory()
    {
        list($major, $minor) = explode('.', phpversion());
        $ver = $major . $minor;
        $ver = ($ver < 50) ? '4' : '5';

        $class = 'i18n'.$ver;
        include $class.'.php';

        if (class_exists($class)) {
            $instance = new $class();
            
            return $instance;
        }
        } else {
            die('Class definition of ' . $class .' not found.');
    }

    public function &singleton()
    {
        static $instances = [];

        $signature = 'theone';

        if (!array_key_exists($signature, $instances))
        {
            $instances[$signature] = &i18n::factory();
        }

        return $instances[$signature];
    }

    /**
     * Elements this object has currently loaded
     * @var array
     */
    var $i18n_array = array();

    /**
     * Default language
     * @var string
     */
    var $lng = 'english';

    /**
     * Array of loaded language packages
     * @var array
     */
    var $loaded_lng_bases = array();

    /**
     * Get the translation of a string
     *
     * This method returns the translation of a given string.
     * @param int $id i18n-string (id) in default language
     * @return string The translated string
     * @access public
     */
    function get($id)
    {
        $ret = $id;
        foreach (array_keys($this->i18n_array) as $k)
        {
            if (isset($this->i18n_array[$k][$id]))
            {
                if ($this->i18n_array[$k][$id])
                    $ret = $this->i18n_array[$k][$id];
                break;
            }
        }
        return htmlspecialchars($ret);
    }

}
