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
class i18n4 extends i18n
{

    /**
     * Element that is currently being parsed
     * @access private
     * @var string
     */
    var $act_element = null;

    /**
     * Array that contains all finished elements (won't add further data)
     * @access private
     * @var array
     */
    var $finished_elements = array();

    /**
     * Array used to work around of a XML-Parser error which
     * causes multiple adds to the i18n-string if the xml-element
     * contains a &-char.
     * @var array
     */
    var $i18n_handler_count = array();

    // Current i18n array
    var $cur_array;

    /**
     * Handler for XML-Start-Element
     *
     * This handler is called if a new XML element starts.
     * @param obj $parser The PHP/XML-Parser-object
     * @param string $name The name of the new xml tag
     * @param array $attrs The attributes of the new xml tag
     * @access private
     */
    function startElement($parser, $name, &$attrs)
    {
        if ($name == 'MSG')
            $this->act_element = (isset($attrs['ID'])) ? $attrs['ID'] : null;
    }

    /**
     * Handler for XML-End-Element
     *
     * This handler is called if a XML element ends.
     * @param obj $parser The PHP/XML-Parser-object
     * @param string $name The name of the xml tag that ends
     * @access private
     */
    function endElement($parser, $name)
    {
        if (!strlen($this->act_element)) return;

        $this->finished_elements[] = $this->act_element;
        $this->act_element = null;
    }

    /**
     * Handler for reading data of current XML-Element
     *
     * This handler is called if a new XML element has started and
     * we have to load the data in this element.
     * @param obj $parser The PHP/XML-Parser-object
     * @param string $data The data of the xml element
     * @access private
     */
    function dataElement($parser, &$data)
    {
        if (!strlen($data) || !strlen($this->act_element)) return;

        if (!isset($this->cur_array[$this->act_element]))
            $this->cur_array[$this->act_element] = null;

        if (!in_array($this->act_element, $this->finished_elements))
            $this->cur_array[$this->act_element] .= $data;
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

            $data = implode(null, file($file));

            $this->i18n_array[$filename] = array();
            $this->cur_array =& $this->i18n_array[$filename];

            $parser = xml_parser_create();
            xml_set_object($parser, $this);
            xml_set_element_handler($parser, 'startElement', 'endElement');
            xml_set_character_data_handler($parser, 'dataElement');

            if (!xml_parse($parser, $data))
                die(sprintf('XML error: %s at line %d',
                            xml_error_string(xml_get_error_code($parser)),
                            xml_get_current_line_number($parser)));
            xml_parser_free($parser);

            $this->loaded_lng_bases[$filename] = $this->lng;

        }
    }
}

?>
