<?php
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Content-Type: text/html; charset=UTF-8');

    include_once 'include/config.php';

    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>XAMS</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="-1" />
    <link rel="stylesheet" type="text/css" href="<?php echo _SKIN; ?>/css/xams.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo _SKIN; ?>/css/form.css" />
    <link rel="SHORTCUT ICON" href="favicon.ico" />
    <script language="javascript" type="text/javascript">
       window.onload = function(){
       if (parent.adjustIFrameSize) parent.adjustIFrameSize(window);
       }
    </script>
	<?php
        if (isset($CSS_ADD))
            echo $CSS_ADD;
    ?>
</head>
<body style="padding: 15px 5px 0px 5px;">
