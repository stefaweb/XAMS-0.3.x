<?php
    require 'gfl.php';
    gfl(_ADMIN);
    require 'include/global.php';

    include 'include/admins.php';
    $myAdmin = new Admins();
    $tl = &$myAdmin->i18n;

    $button = gpost('button');
    $id = greq('id');
    $mode = greq('mode');
    $condition = null;

    if ($id) {
        $myAdmin->Load($id);
    }

    if ($button) {
        if ($button == $tl->get('Delete')) {
            if (gpost('name') == 'demo' && _DEMO_SERVER) {
                header('Location: account_overview.php?info='.urlencode($tl->get('You cannot delete the demo-admin!')));
                exit;
            } else {
                $myAdmin->Delete();
                header('Location: account_overview.php?info='.urlencode($myAdmin->notice));
                exit;
            }
        }

        if (gpost('name') == 'demo' && _DEMO_SERVER) { // Do not change the password of user 'demo' on demo-servers
            $_POST['password'] = null;
        }

        $myAdmin->Assign2Object(['name', 'password', 'locked']);
        $myAdmin->check_formular($mode);

        if (!$myAdmin->formular_errors) {
            switch ($button) {
                case $tl->get('Save'):
                    $myAdmin->Add();
                    header('Location: account_overview.php?info='.urlencode($myAdmin->notice));
                    exit;
                case $tl->get('Update'):
                    $myAdmin->Update();
                    header('Location: account_overview.php?info='.urlencode($myAdmin->notice));
                    exit;
            }
        }
    }

    $button = ($mode == 'new') ? 'Save' : 'Update';

    include 'header.php';
?>
<h1><?php echo $tl->get('Administrator Management'); ?></h1>
<?php if ($myAdmin->formular_errors) {
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
            <tr style="height: 0px;">
                <td colspan="3">
                    <input type="hidden" name="mode" value="<?php echo $mode ?>" />
                    <?php if ($mode == 'update') {
    ?>
                    <input type="hidden" name="id" value="<?php echo $myAdmin->id ?>" />
                    <?php 
} ?>
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Administrator Name') ?></th>
                <td>
                    <input type="text" name="name" value="<?php echo $myAdmin->name ?>" maxlength="100" size="50" class="textfield" />
                </td>
                <td><?php echo $myAdmin->show_field_property('name') ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Administrator Password') ?></th>
                <td>
                    <input class="password" type="text" name="password" value="" maxlength="100" size="20" class="textfield" />
                </td>
                <td><?php echo $myAdmin->show_field_property('password') ?></td>
            </tr>
            <?php if ($myAdmin->id != USERID) {
    ?>
            <tr>
                <th><?php echo $tl->get('Account locked') ?></th>
                <td>
                    <input type="checkbox" name="locked" class="checkbox" value="true"<?php if (isTrue($myAdmin->locked)) {
        echo ' checked="checked"';
    } ?> />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php $condition = '&condition=not_himself';
} ?>
            <?php if ($mode == 'update') {
    ?>
            <tr>
                <th><?php echo $tl->get('Admin created')?></th>
                <td>
                    <input type="text" name="created" value="<?php echo $myAdmin->added ?>" size="<?php echo strlen($myAdmin->added) ?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Admin last updated') ?></th>
                <td>
                    <input type="text" name="updated" value="<?php echo $myAdmin->updated ?>" size="<?php echo strlen($myAdmin->updated) ?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php 
} ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" name="button" value="<?php echo $tl->get($button) ?>" class="button" />
                    <?php if ($button == 'Update' && $myAdmin->id != USERID) {
    ?>
                    <input type="submit" name="button" value="<?php echo $tl->get('Delete') ?>" class="button" />
                    <?php 
} ?>
                    <input type="reset" name="button" value="<?php echo $tl->get('Reset') ?>" class="button" />
                    <input type="button" name="help" value="<?php echo $tl->get('Help') ?>" class="helpbutton" onclick="window.open('help.php?help=administrator&mode=<?php echo $mode.$condition?>', '', 'scrollbars=yes, height=500 width=920');" />
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
