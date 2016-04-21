<?php
/**
   @package default
 */

/**
 * Internationalization (i18n) management class
 *
 * This class is responsible for the whole internationalization management.
 * @package default
 * @access public
 */
class i18n5 extends i18n
{

    var $dom;

    function __construct()
    {
        $this->dom = new DomDocument();
    }

    /**
     * Loads one or more language package(s)
     *
     * @param string $files_csv Comma seperated list of i18n-files to load
     * @param string $lng language to use
     * @access public
     */
    function LoadLngBase($files, $lng=null)
    {
        if (isset($lng) && !empty($lng))
            $this->lng = $lng;
        elseif (isset($_SESSION['SESSION_LANGUAGE']) && !empty($_SESSION['SESSION_LANGUAGE']))
            $this->lng = $_SESSION['SESSION_LANGUAGE'];

        if (!is_array($files))
            $files = array($files);

        $files[] = 'std_menu';

        foreach ($files as $filename)
        {
            if (isset($this->loaded_lng_bases[$filename]))
                continue;

            $file = sprintf('i18n/%s/%s.xml', $this->lng, $filename);
            if (!file_exists($file))
                $file = sprintf('i18n/english/%s.xml', $filename);

            if (!file_exists($file)) continue;

            $this->dom->load($file);

            foreach ($this->dom->getElementsByTagName('msg') as $msgtag)
                $this->i18n_array[$filename][$msgtag->getAttribute('id')] = $msgtag->nodeValue;

            $this->loaded_lng_bases[$filename] = $this->lng;
        }
    }
}

?>
