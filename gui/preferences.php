<?php
    require 'gfl.php';
    gfl(_ADMIN);

    include 'include/global.php';

    include 'include/preferences.php';
    $myPREF = new Preferences(false);
    $tl =& $myPREF->i18n;
    $tl->LoadLngBase('login');

    $info = null;
    $button = gpost('button');

    $myPREF->Load();
    if (!empty($button))
    {
        $myPREF->Assign2Object(array('loginwelcome', 'loglines', 'onlinenews',
        'newversioncheck', 'defaultlanguage', 'spamscore', 'highspamscore'));

        $myPREF->Update();
        $info = $myPREF->notice;
    }

//    $loglevels = array($tl->get("No Logging"), $tl->get("Minimum"), $tl->get("Medium"), $tl->get("Maximum"));

    function language_list()
    {
        global $myPREF, $tl;
        $handle = opendir('i18n'); 
        while (false !== ($file = readdir($handle)))
        {
            if (!preg_match('/^\.+|^CVS$/', $file) && is_dir("i18n/$file"))
            {
                $sel = ($file == $myPREF->defaultlanguage) ? ' selected="selected"' : null;
                printf("<option value=\"%s\"%s>%s</option>\n", $file, $sel, $tl->get($file));
            } 
        }
        closedir($handle);
    }

    include 'header.php';
?>
<h1><?php echo $tl->get('Preferences'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
    <div class="menu1"></div>
    <div class="menu2">
        <table width="680" style="padding: 10px;">
            <colgroup>
                <col width="360" />
                <col width="380" />
                <col width="40" />
            </colgroup>
            <tr>
                <th><?php echo $tl->get("Welcome message at logon")?></th>
                <td>
                    <input type="text" size="30" maxlength="50" name="loginwelcome" value="<?php echo ($myPREF->loginwelcome)?>" class="textfield" /> 
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Default Lines of Event-Log")?></th>
                <td>
                    <input type="text" size="3" maxlength="3" name="loglines" value="<?php echo ($myPREF->loglines)?>" class="textfield" /> 
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Online News")?></th>
                <td>
                    <input type="checkbox" name="onlinenews" class="checkbox" value="true"<?php if (isTrue($myPREF->onlinenews)) echo ' checked="checked"' ?> /> 
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Check for new versions")?></th>
                <td>
                    <input type="checkbox" name="newversioncheck" class="checkbox" value="true"<?php if (isTrue($myPREF->newversioncheck)) echo ' checked="checked"' ?> /> 
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Default Language")?></th>
                <td>
                    <select name="defaultlanguage">
                        <?php language_list() ?>
                    </select>
                </td>
                <td>&nbsp;</td>
            </tr>
<tr>
<th><?php echo $tl->get("Spam Score Trigger")?></th>
        <td>
        <?php echo $tl->get("Spam Score")?>:
        <input type="text" name="spamscore" value="<?php echo $myPREF->spamscore?>" maxlength="3" size="3" class="textfield" />
        <?php echo $tl->get("High Spam Score")?>:
        <input type="text" name="highspamscore" value="<?php echo $myPREF->highspamscore?>" maxlength="3" size="3" class="textfield" />
        </td>
</tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <p>
                        <input type="submit" class="button" name="button" value="<?php echo $tl->get("Update")?>" /> 
                        <input type="button" name="help" value="<?php echo $tl->get("Help")?>" class="helpbutton" onclick="window.open('help.php?help=preferences', '', 'scrollbars=yes, height=500, width=920');" />
                    </p>
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
