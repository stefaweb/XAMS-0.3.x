<?php
    require_once 'gfl.php';
    include 'include/xslclass.php';
    include 'include/i18n.php';
    $tl = &i18n::singleton();
    $tl->LoadLngBase('menu');

    if (!extension_loaded('gd')) {
        dl('gd.so');
    }

    function gen_button($Name, $Text, $Color)
    {
        global $basedir;
        $basedir = 'cache/'._SKIN.'/img/buttons/'.$_SESSION['SESSION_LANGUAGE'];
        if (!is_dir($basedir)) {
            if (!mkdir_p($basedir, 0755)) {
                die("Your webserver requires write-access to directory '$basedir'!");
            }
        }

        foreach (['', '_h', '_p'] as $elem) {
            $pic_file_name = $basedir.'/'.$Color.'_'.$Name.$elem.'.png';
            if (!file_exists($pic_file_name)) {
                $pic_src = imagecreatefrompng(_SKIN.'/img/buttons/'.$Color.'_button'.$elem.'.png');
                if ($pic_src) {
                    $img_width = imagesx($pic_src);
                    $img_height = imagesy($pic_src);
                    if (!function_exists('ImageCreateTrueColor')) {
                        $pic_new = imagecreate(20 + 8 * strlen($Text), $img_height);
                    } else {
                        $pic_new = imagecreatetruecolor(20 + 8 * strlen($Text), $img_height);
                        imagealphablending($pic_new, false);
                        imagesavealpha($pic_new, true);
                    }
                    imagecopy($pic_new, $pic_src, 0, 0, 0, 0, 10, $img_height); // Left Edge
                    imagecopy($pic_new, $pic_src, 8 * strlen($Text) + 10, 0, $img_width - 10, 0, 10, $img_height); // Right Edge
                    imagecopyresized($pic_new, $pic_src, 10, 0, 10, 0, 8 * strlen($Text), $img_height, 1, $img_height); // Mid

                    $pic_color = imagecolorallocate($pic_new, 255, 255, 255); // Text color (white)
                    $font_size = 10;
                    $text_pos_y = round($img_height / 2 - $font_size);
                    imagestring($pic_new, 4, 10, $text_pos_y, $Text, $pic_color); // Text pixel width and height

                    if (!imagepng($pic_new, $pic_file_name)) {
                        die("Your webserver requires write-access to directory '$basedir'!");
                    }
                    imagedestroy($pic_src);
                    imagedestroy($pic_new);
                }
            }
        }
    }

    //gen_button('logout', utf8_decode($tl->get('Logout')), 'red');

    $usertypes = [null, 'user', 'customer', 'reseller', 'administrator'];
    $dbsetting = (_DEBUGMODE) ? '@'.$DATABASE_SETTINGS['DBType'] : null;
    $trans = [
        'patterns' => [
            "'{{xams-release}}'",
            "'{{language}}'",
            "'{{lngfile}}'",
            "'{{skindir}}'",
            "'{{username}}'",
            "'{{usertype}}'",
        ],
        'replacements' => [
            _XAMS_VERSION,
            $_SESSION['SESSION_LANGUAGE'],
            realpath('i18n/'.$_SESSION['SESSION_LANGUAGE'].'/menu.xml'),
            _SKIN,
            USERNAME.$dbsetting,
            $usertypes[USERT],
        ],
    ];
    $myXSL = &xslclass::singleton();
    $myXSL->load_xml('include/xml/menu.xml');
    $myXSL->load_xsl(_SKIN.'/xsl/menu.xsl');
    $myXSL->xml_replace($trans);
    $myXSL->out();
