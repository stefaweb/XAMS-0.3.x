<?php

class xslclass5 extends xslclass
{

    var $dom;

    function load_xsl($xsl_file)
    {
        $this->dom = new domDocument();
        $ret = $this->dom->load($xsl_file);
        if (!$ret)
        {
            die('Error while parsing XSL data');
        }
        return $ret;
    }

    function transform()
    {
        if ($this->transformed)
            return true;

        $doc = new domDocument();
        $doc->loadxml($this->xml_data);

        $proc = new xsltprocessor;
        $xsl = $proc->importStylesheet($this->dom);

        $this->data = $proc->transformToXml($doc);
        //unset($this->doc);
        return $this->transformed = true;
    }

}

?>
