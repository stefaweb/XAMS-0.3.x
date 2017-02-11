<?php
    require 'gfl.php';
    gfl(_USER);

    include 'include/global.php';
    include 'include/filters.php';

    $myFilter = new Filters();
    $tl = &$myFilter->i18n;

    $userid = greq('userid');
    //$mode = greq('mode');
    $button = gpost('button');
    $info = null;

    if ($userid) {
        $myFilter->Load($userid);
        if (!$myFilter->isAuthLoad()) {
            die($tl->get('Access denied.'));
        }
    } else {
        die('No userid given.');
    }

    $mode = ($myFilter->added) ? 'update' : 'new';

    if ($button) {
        if ($button == $tl->get('Delete')) {
            $myFilter->Delete();
            $myFilter->status($myFilter->notice);
            exit;
        }

        $myFilter->Assign2Object(['userid', 'filter', 'active', 'mode']);

        $myFilter->check_formular($mode);

        if (!$myFilter->formular_errors) {
            switch ($button) {
                case $tl->get('Save'):
                    $myFilter->Add();
                    $info = $myFilter->notice;
                    $mode = 'update';
                    break;
                case $tl->get('Update'):
                    $myFilter->Update();
                    $info = $myFilter->notice;
                    break;
            }
            $myFilter->Load();
        }
    }

    $button = ($mode == 'update') ? 'Update' : 'Save';

    include 'header.php';
?>
<h1><?php echo $tl->get('Exim-Filter Configuration'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<?php if ($myFilter->formular_errors) {
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
                    <input type="hidden" name="userid" value="<?php echo $myFilter->userid ?>" />
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Filter') ?></th>
                <td>
                    <textarea name="filter" cols="50" rows="10" class="textfield"><?php echo stripslashes($myFilter->filter) ?></textarea>
                </td>
                <td><?php echo $myFilter->show_field_property('filter') ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Active') ?></th>
                <td>
                    <input type="checkbox" name="active" class="checkbox" value="true"<?php if (isTrue($myFilter->active)) {
    echo ' checked="checked"';
} ?> /> <?php echo $tl->get('Yes') ?>
                </td>
                <td>&nbsp;</td>
            </tr>
    <?php if ($mode == 'update') {
    ?>
            <tr>
                <th><?php echo $tl->get('Filter created') ?></th>
                <td>
                    <input type="text" name="created" value="<?php echo $myFilter->added ?>" size="<?php echo strlen($myFilter->added)?>" class="textfield" disabled="disabled">
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Filter last updated') ?></th>
                <td>
                    <input type="text" name="updated" value="<?php echo $myFilter->updated ?>" size="<?php echo strlen($myFilter->updated)?>" class="textfield" disabled="disabled">
                </td>
                <td>&nbsp;</td>
            </tr>
    <?php 
} ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" name="button" class="button" value="<?php echo $tl->get("$button") ?>" />
                    <input type="submit" name="button" value="<?php echo $tl->get('Delete') ?>" class="button" />
                    <input type="reset" name="button" class="button" value="<?php echo $tl->get('Reset') ?>" />
                    <?php if (USERT > _USER) {
    ?>
                    <input type="button" name="help" value="<?php echo $tl->get('Help') ?>" class="helpbutton" onclick="window.open('help.php?help=user&amp;mode=<?php echo $mode?>', '', 'scrollbars=yes, height=500, width=920');" />
                    <?php 
} ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
