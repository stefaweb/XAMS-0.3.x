<?php

include_once 'include/global.php';

/**
 * XSL(T) management class
 *
 * This class is responsible for the whole xml/xsl parsing
 * and transformating management.
 * @package default
 * @access public
 */
class xslclass
{

    var $xml_data = null;
    var $xsl_data = null;
    var $transformed = false;

    function set_parser_base($dir)
    {
    }

    function load_xml($xml_file)
    {
        $xml_data = rfile($xml_file);
        if (!$xml_data)
            return false;
        $this->set_xml_data($xml_data);
    }

    function xml_replace(&$regex_array, $xml=true)
    {
        if ($xml)
            $data =& $this->xml_data;
        else
            $data =& $this->xsl_data;
        $data = preg_replace($regex_array['patterns'], $regex_array['replacements'], $data);
    }

    function xsl_replace(&$regex_array)
    {
        $this->xml_replace($regex_array, false);
    }

    function set_xml_data(&$xmldata)
    {
        $this->xml_data .= $xmldata;
    }

    function set_xsl_data(&$xsldata)
    {
        $this->xsl_data .= $xsldata;
    }

    function out()
    {
        if ($this->transform())
            echo $this->data;
        else
            echo "Can't output corrupted data";
    }

    function fout()
    {
        wfile('debug.xml', $this->xml_data);
    }

    function &factory()
    {
        list($major, $minor) = explode('.', phpversion());
        $ver = $major . $minor;
        $ver = ($ver < 50) ? '4' : '5';

        $class = 'xslclass' . $ver;
        include $class . '.php';

        if (class_exists($class))
        {
            $instance = new $class();
            return $instance;
        }
        else
        {
            die('Class definition of ' . $class . ' not found.');
        }
    }

    function &singleton()
    {
        static $instances = array();

        $signature = 'theone';

        if (!array_key_exists($signature, $instances))
        {
            $instances[$signature] = &xslclass::factory();
        }

        return $instances[$signature];
    }

}

?>
