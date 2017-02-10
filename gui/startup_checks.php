<?php

    $startup_errors = [];
    // Don't get in trouble with obsolete PHP versions
    list($major, $minor) = explode('.', phpversion());
    $ver = $major.$minor;
    if ($ver < 41) {
        $startup_errors[] = 'You need at least PHP 4.1.0 to run!';
    }

    // Check for required libraries
    if ($ver < 50) {
        if (!extension_loaded('xslt')) {
            dl('xslt.so');
        }
        if (!function_exists('xml_parser_create')) {
            $startup_errors[] = 'You need PHP with XML support installed on your system.';
        }

        if (!function_exists('xslt_create')) {
            $startup_errors[] = 'You need PHP with XSL support installed on your system.';
        }
    } else {
        if (!class_exists('DomDocument')) {
            $startup_errors[] = 'You need PHP with DOM support installed on your system.';
        }

        if (!class_exists('xsltprocessor')) {
            $startup_errors[] = 'You need PHP with XSL support installed on your system.';
        }
    }

    if (!extension_loaded('gd')) {
        dl('gd.so');
    }
    if (!function_exists('ImageCreateTrueColor') && !function_exists('ImageCreate')) {
        $startup_errors[] = 'You need PHP with GD support installed on your system.';
    }

    // Check if webserver parsed (and applied) the settings in .htaccess
    if (ini_get('register_globals')) {
        $startup_errors[] = 'Your .htaccess is not active - you need to set register_globals = Off!';
    }

    if (ini_get('allow_call_time_pass_reference')) {
        $startup_errors[] = 'Your .htaccess is not active - you need to set allow_call_time_pass_reference = Off!';
    }

    if (!ini_get('display_errors')) {
        $startup_errors[] = 'Your .htaccess is not active - you need to set display_errors = On!';
    }

    if (ini_get('magic_quotes_gpc')) {
        $startup_errors[] = 'Your .htaccess is not active - you need to set magic_quotes_gpc = Off!';
    }

    if (count($startup_errors) > 0) {
        foreach ($startup_errors as $e) {
            echo "ERROR: $e<br/>\n";
        }
        die('Too many errors to continue.');
    }
