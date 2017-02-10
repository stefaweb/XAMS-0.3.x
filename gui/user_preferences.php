<?php
    require 'gfl.php';
    gfl(_USER, true);

    include 'include/global.php';
    include 'include/users.php';
    include 'include/user_addressbook.php';

    $myAB = new User_Addressbook();
    $myUser = new Users();
    $db = &$myUser->db;
    $tl = &$myUser->i18n;

    $id = USERID;
    $button = gpost('button');
    $mode = 'update';
    $info = null;

    $myUser->Load($id);
    if (!$myUser->isAuthLoad()) {
        die($tl->get('Access denied.'));
    }

    if ($button) {
        $myUser->Assign2Object(['password', 'autoreplysubject', 'autoreplytext', 'autoreply', 'addressbook_', 'mode']);

        $myUser->assignFormVar('id', $id);
        $myUser->check_formular($mode);

        if (!$myUser->formular_errors && $button == $tl->get('Update')) {
            $myUser->Update();
            $info = $tl->get($myUser->notice);
        }
    }

    $button = 'Update';

    $myAB->Load($id);

    include 'header.php';
?>
<h1><?php echo $tl->get('Account Settings'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info) ?></p>
<?php if ($myUser->formular_errors) {
    echo '<p class="formerror"><img src="'._SKIN.'/img/critical.png" alt="Error" height="25" width="25" />'.$tl->get('The formular was not properly filled out. Point at the question mark.').'</p>';
} ?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
    <div class="menu1"></div>
    <div class="menu2">
        <table width="680" class="tbl_global">
            <colgroup>
                <col width="230" />
                <col width="410" />
                <col width="40" />
            </colgroup>
            <tr>
                <th><?php echo $tl->get('Password')?></th>
                <td>
                    <input class="password" type="text" name="password" value="" maxlength="100" size="30" class="textfield" />
                </td>
                <td><?php echo $myUser->show_field_property('password') ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Auto reply')?></th>
                <td>
                    <strong><?php echo $tl->get('Subject')?>:</strong>
                    <br/>
                    <input type="text" name="autoreplysubject" value="<?php echo $myUser->autoreplysubject?>" maxlength="50" size="50" class="textfield" />

                    <br/>

                    <strong><?php echo $tl->get('Message')?>:</strong>
                    <br/>
                    <textarea name="autoreplytext" cols="40" rows="4" class="textfield"><?php echo $myUser->autoreplytext?></textarea>

                    <br/>

                    <input type="checkbox" name="autoreply" class="checkbox" value="true"<?php if (isTrue($myUser->autoreply)) {
    echo ' checked="checked"';
} ?> /> <?php echo $tl->get('On')?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php include 'show_addressbook.php'; ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" name="button" class="button" value="<?php echo $tl->get("$button")?>" />
                    <input type="reset" name="button" class="button" value="<?php echo $tl->get('Reset')?>" />
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
