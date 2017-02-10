<?php
    function mas($var)
    {
        return (get_magic_quotes_gpc()) ? $var : addslashes($var);
    }

    // Get $_POST[] vars
    function gpost($var)
    {
        if (!isset($_POST[$var])) {
            return;
        }
        if (is_array($_POST[$var])) {
            foreach ($_POST[$var] as $k => $e) {
                $ret[$k] = mas($e);
            }
        } else {
            $ret = mas($_POST[$var]);
        }

        return $ret;
    }

    // Get $_GET[] vars
    function gget($var)
    {
        return (isset($_GET[$var])) ? mas($_GET[$var]) : null;
    }

    // Get $_REQUEST[] vars
    function greq($var)
    {
        return (isset($_REQUEST[$var])) ? mas($_REQUEST[$var]) : null;
    }

    // Check if checkbox is active
    function isTrue($var)
    {
        return (!empty($var) && $var[0] == 't') ? true : false;
    }

    // Check if checkbox is inactive
    function isFalse($var)
    {
        return (!empty($var) && $var[0] == 'f') ? true : false;
    }

    function rfile($file)
    {
        $ret = false;
        $fh = @fopen($file, 'r');

        if ($fh) {
            $data = null;
            while (!feof($fh)) {
                $data .= fread($fh, 1024);
            }
            fclose($fh);
            if ($data) {
                $ret = $data;
            }
        }

        return $ret;
    }

    function wfile($file, $data)
    {
        $ret = false;
        $fh = fopen($file, 'w');

        if ($fh) {
            $ret = fwrite($fh, $data);
            fclose($fh);
        }

        return $ret;
    }

    // recursive mkdir()
    function mkdir_p($target, $mode = 0777)
    {
        if (file_exists($target)) {
            return is_dir($target) || (is_link($target) && is_dir(readlink($target)));
        } else {
            return (!empty($target) && mkdir_p(substr($target, 0, (strrpos($target, '/'))), $mode)) ? (mkdir($target, $mode)) : 0;
        }
    }
