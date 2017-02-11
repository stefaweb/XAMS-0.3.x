<?php
    if (!extension_loaded('gd')) {
        dl('gd.so');
    }

    // Function used to generate buttons (Address book)
    function gen_tab($Name, $Text, $Color)
    {
        global $basedir;
        if (!is_dir($basedir)) {
            if (!mkdir_p($basedir, 0755)) {
                die("Your webserver requires write-access to directory '$basedir'!");
            }
        }
        if (!file_exists($basedir.'/'.$Color.'_'.$Name.'.png')) {
            $pic_src = imagecreatefrompng(_SKIN.'/img/tabs/'.$Color.'_tab'.'.png');
            if ($pic_src) {
                if (!function_exists('ImageCreateTrueColor')) {
                    $pic_new = imagecreate(24 + 8 * strlen($Text), 18);
                } else {
                    $pic_new = imagecreatetruecolor(24 + 8 * strlen($Text), 18);
                }
                imagecopy($pic_new, $pic_src, 0, 0, 0, 0, 11, 18); // Left Edge
                imagecopy($pic_new, $pic_src, 11 + 8 * strlen($Text), 0, 12, 0, 13, 18); // Right Edge
                imagecopyresized($pic_new, $pic_src, 11, 0, 11, 0, 8 * strlen($Text), 18, 1, 18); // Mid

                $pic_color = imagecolorallocate($pic_new, 255, 255, 255); // Text color

                imagestring($pic_new, 3, 14, 3, $Text, $pic_color); // Text position

                if (!imagepng($pic_new, $basedir.'/'.$Color.'_'.$Name.'.png')) {
                    die("Your webserver requires write-access to directory '$basedir'!");
                }
                imagedestroy($pic_src);
                imagedestroy($pic_new);
            }
        }
    }

    function show_tabs($arr, $selected, &$i18n)
    {
        global $basedir;
        $i = 0;
        foreach ($arr as $elem=>$file) {
            $elem2 = utf8_decode($i18n->get($elem));
            $col = ($elem == $selected) ? 'red' : 'blue';
            gen_tab($elem, $elem2, 'blue');
            gen_tab($elem, $elem2, 'red');
            $out = sprintf('<a href="%s"><img src="%s/%s_%s.png" alt="" onmouseover="document.images[%d].src = button[%d].src;"', $file, $basedir, $col, $elem, $i, $i);
            if ($elem != $selected) {
                $out .= sprintf(' onmouseout="document.images[%d].src = button_orig[%d].src;"', $i, $i);
            }
            echo $out.' /></a>';
            $i++;
        }
    }

    $basedir = 'cache/'._SKIN.'/img/tabs/'.$_SESSION['SESSION_LANGUAGE'];

    function prep_tabs($buttons)
    {
        global $CSS_ADD, $basedir;
        $CSS_ADD = "
<script type=\"text/javascript\">
<!--
    buttons = new Array($buttons);
    button = new Array();
    button_orig = new Array();
    for (i=0; i<=4; i++)
    {
        button[i] = new Image();
        button[i].src = \"$basedir/red_\" + buttons[i] + \".png\";
        button_orig[i] = new Image();
        button_orig[i].src = \"$basedir/blue_\" + buttons[i] + \".png\";
    }
//-->
</script>
";
    }
