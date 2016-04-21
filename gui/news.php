<?php
    require 'gfl.php';
    gfl(_ADMIN);
    include 'include/global.php';
    include 'include/preferences.php';
    include 'include/xslclass.php';

    header('Content-Type: text/html; charset=UTF-8');
    
    $myXSL =& xslclass::singleton();
    $myXSL->load_xsl(realpath(_SKIN. '/xsl/news.xsl'));

    // Already checked today?
    function checked_today()
    {
        global $myPREFS;
        if ($myPREFS->lastnewscheck == date('Y-m-d'))
            $ret = true;
        else
        {
            $myPREFS->assign('lastnewscheck', date('Y-m-d'));
            $myPREFS->Update();
            $ret = false;
        }
        return $ret;
    }

    $lng = $_SESSION['SESSION_LANGUAGE'];
    $myPREFS = new Preferences();
    $myPREFS->Load(false);
    $file = $data = null;
    $cache_news = false;
    if (isTrue($myPREFS->onlinenews))
    {
        if (checked_today() && @filesize('cache/news-'. $lng. '.xml'))
            $file = 'cache/news-'. $lng. '.xml';
        else
        {
            $file = _XAMS_ONLINE_SERVER. '/news.php?version='. _XAMS_VERSION. '&lng='. $lng;
            $cache_news = true;
        }
        $myXSL->set_parser_base('include/dtd');
    }
    else
    {
        $file = "i18n/$lng/news.xml";
        if (!file_exists($file))
            $file = 'i18n/english/news.xml';
        $myXSL->set_parser_base($file);
    }

    $data = rfile($file);
    if ($data)
    {
        if ($cache_news)
            wfile('cache/news-'. $lng. '.xml', $data);

        $trans = array('patterns' => array("'{{skindir}}'"), 'replacements' => array(_SKIN));

        $myXSL->set_xml_data($data);
        $myXSL->xml_replace($trans);
        $myXSL->out();
    }
    else
        die("Can't read newsfile '$file'!");

?>
