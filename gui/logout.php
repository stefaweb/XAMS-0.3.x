<?php
    header("Content-Type: text/html; charset=UTF-8");
    require 'include/config.php';
    session_start();
    if (session_destroy())
    {
        header('Location: login.php?logout=1');
        exit;
    }
    include 'include/i18n.php';
    $tl =& i18n::singleton();
    $tl->LoadLngBase('login');
?>
<?php echo '<?xml version="1.0" encoding="utf-8"?'.">\n"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>Logout</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="<?php echo _SKIN?>/css/login.css" />
    <link rel="SHORTCUT ICON" href="favicon.ico" />
</head>
<body>
    <p class="error"><?php echo $tl->get('Logout could not been carried out! Session was not destroyed!')?></p>
</body>
</html>

