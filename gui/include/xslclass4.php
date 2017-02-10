<?php

if (!extension_loaded('xslt')) {
    dl('xslt.so');
}

class xslclass4 extends xslclass
{
    public function set_parser_base($dir)
    {
        $this->xslt_base = 'file://'.realpath(dirname($dir)).'/';
    }

    public function load_xml($xml_file)
    {
        $this->set_parser_base($xml_file);
        parent::load_xml($xml_file);
    }

    public function load_xsl($xsl_file)
    {
        $xsl_data = rfile($xsl_file);
        if (!$xsl_data) {
            return false;
        }
        $this->set_xsl_data($xsl_data);
    }

    public function transform()
    {
        if ($this->transformed) {
            return true;
        }

        $this->arguments = ['/_xml' => &$this->xml_data, '/_xsl' => &$this->xsl_data];

        $xh = xslt_create();
        if (isset($this->xslt_base)) {
            xslt_set_base($xh, $this->xslt_base);
        }
        $this->data = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', null, $this->arguments);
        if (!$this->data) {
            echo "XSLT-Error: '".xslt_error($xh)."'";
        }
        xslt_free($xh);

        return $this->transformed = true;
    }
}
