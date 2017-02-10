<?php
    if (!empty($_SESSION['SESSION_logged_in_user'])) {
        exit;
    }

    // Maybe the system is being maintenanced...so don't let
    // users login
    if (file_exists('maintenance')) {
        header('Location: maintenance.php');
        exit;
    }

    // Do some checks before proceeding...
    require 'startup_checks.php';

    session_start();
    include 'include/global.php';
    include 'include/login.php';

    // Set UserType to _SYSTEM - otherwise login can't load preferences
    define('USERT', _SYSTEM);

    // Load System preferences
    include 'include/preferences.php';
    $_PREFS = new Preferences();
    $_PREFS->Load(false);

    $LoggedIn = false;

    $login = gpost('login');
    $logout = gget('logout');
    $password = gpost('password');
    $user_type = gpost('user_type');

    $tl = &$_PREFS->i18n;
    $tl->LoadLngBase('login', $_PREFS->defaultlanguage);

    // Login user
    if (!empty($login) && !empty($password)) {
        $myLogin = new Login();
        $LoggedIn = $myLogin->doLogin($login, $password, $user_type);

        if ($LoggedIn) {
            $_SESSION['SESSION_logged_in_user_id'] = $myLogin->uid;
            $_SESSION['SESSION_LANGUAGE'] = strtolower(gpost('language'));
            $_SESSION['SESSION_logged_in_user_name'] = $myLogin->login;

            // This seems be required due to PHP's madness - I've seen
            // systems where the session won't be saved without that
            session_write_close();

            header('Location: index.php');
            exit;
        }
    }

    function language_list()
    {
        global $_PREFS, $tl;
        $handle = opendir('i18n');
        while (false !== ($file = readdir($handle))) {
            if (strpos($file, '.') !== 0 && is_dir("i18n/$file")) {
                $sel = ($file == $_PREFS->defaultlanguage) ? ' selected="selected"' : null;
                printf('<option value="%s"%s>%s</option>'."\n", $file, $sel, $tl->get($file));
            }
        }
        closedir($handle);
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title><?php echo _TITLE ?> - Login</title>
    <script type="text/javascript">
    <!--
        if (top != self) top.location = self.location;
    //-->
    </script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="robots" content="noindex,nofollow" />
    <link rel="stylesheet" type="text/css" href="<?php echo _SKIN ?>/css/login.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo _SKIN ?>/css/form.css" />
    <link rel="SHORTCUT ICON" href="favicon.ico" />
</head>
<body onload="document.getElementById('loginformular').<?php echo (empty($login)) ? 'login' : 'password' ?>.focus();">
<form style="width:600px; height:500px; text-align:center; background-color:#ebf2f8; margin:0 auto; border:solid #FFF"id="loginformular" method="post" action="login.php">
    <table>
        <tr>
            <td style="padding-top:50px;"><img src="<?php echo _SKIN ?>/img/logo.png" width="88" height="59" title="eXtended Account Management System" alt="XAMS" onclick="window.open('http://www.xams.org');" /></td>
        </tr>
        <tr>
            <td id="version"><?php echo _XAMS_VERSION ?></td>
        </tr>
        <tr>
            <td>
                <div id="intro"><?php echo $_PREFS->loginwelcome ?></div>
            </td>
        </tr>
        <tr>
            <td>
                <?php
                    if ($logout) {
                        echo '<p id="info">'.$tl->get('You have successfully been logged out.').'</p>';
                    } elseif (!$LoggedIn && !empty($login)) {
                        echo '<p id="error">'.$tl->get('Login failed!').'</p>';
                    } else {
                        echo '<p id="info">'.$tl->get('Cookies must be enabled past this point!').'</p>';
                    }
                ?>
                <table>
                    <tr>
                        <th><?php echo $tl->get('Login') ?></th>
                        <td><input type="text" name="login" size="25" maxlength="50" value="<?php echo empty($login) ? null : gpost('login') ?>" class="textfield" style="width: 160px;" /></td>
                    </tr>
                    <tr></tr>
                    <tr></tr>
                    <tr>
                        <th><?php echo $tl->get('Password') ?></th>
                        <td><input type="password" name="password" size="25" maxlength="32" class="textfield" style="width: 160px;" /></td>
                    </tr>
                    <tr></tr>
                    <tr></tr>
<?php if (_USER_TYPE_SELECT) {
                    ?>
                    <tr>
                        <th><?php echo $tl->get('Usertype') ?></th>
                        <td>
                            <select name="user_type">
                                <option value="admin"<?php if ($user_type == 'admin') {
                        echo ' selected="selected"';
                    } ?>><?php echo $tl->get('Administrator') ?></option>
                                <option value="reseller"<?php if ($user_type == 'reseller') {
                        echo ' selected="selected"';
                    } ?>><?php echo $tl->get('Reseller') ?></option>
                                <option value="customer"<?php if ($user_type == 'customer') {
                        echo ' selected="selected"';
                    } ?>><?php echo $tl->get('Customer') ?></option>
                                <option value="user"<?php if ($user_type == 'user') {
                        echo ' selected="selected"';
                    } ?>><?php echo $tl->get('User') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr></tr>
<?php 
                } ?>
                    <tr>
                        <th><?php echo $tl->get('Language') ?></th>
                        <td>
                            <select name="language">
                                <?php language_list() ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="right"><input type="submit" name="enter" value="<?php echo $tl->get('Enter') ?> &gt;&gt;" class="button" /></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
</body>
</html>
