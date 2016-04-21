<?php
    require 'gfl.php';
    gfl(_CUSTOMER);

    include 'include/global.php';
    include 'include/aliases.php';
    include 'include/alias_addressbook.php';

    $myAB = new Alias_Addressbook();
    $myAlias = new Aliases();
    $db =& $myAlias->db;
    $tl =& $myAlias->i18n;

    $id = greq('id');
    $mode = greq('mode');
    $siteid = greq('siteid');
    $button = gpost('button');
    $info = null;

    if ($id)
    {
        $myAlias->Load($id);
        if (!$myAlias->isAuthLoad())
            die($tl->get('Access denied.'));
    }
    elseif ($siteid)
        $myAlias->assign('siteid', $siteid);

    if ($button)
    {
        $myAlias->Assign2Object(array('id', 'siteid', 'leftpart', 'rightpart',
        'bounceforward', 'blackhole', 'oldleftpart', 'addressbook_', 'mode'));

        if ($button == $tl->get('Delete')) {
            $myAlias->Delete();
            $myAlias->status($myAlias->notice);
            exit;
        }

        $myAlias->check_formular($mode);

        if (!$myAlias->formular_errors)
        {
            switch ($button)
            {
                case $tl->get('Save'):
                    $myAlias->Add();
                    $info = $myAlias->notice;
                    $mode = 'update';
                    break;
                case $tl->get('Update'):
                    $myAlias->Update();
                    $info = $myAlias->notice;
                    break;
            }
            $myAlias->Load(); // For update-field
        }
    }

    // Check if there can be more aliases added, but only if check_formular()
    // hasn't handled it (and occured an error)
    if ($mode == 'new' && !$myAlias->formular_errors && $myAlias->siteid)
    {
        $myAlias->LoadSite();
        $err = $myAlias->QC_AddAlias();
        if ($err)
        {
            $err_arr = array(null,
                             'Unfortunately no more aliases can be added. Reseller based Alias-Quota has reached.',
                             'Unfortunately no more aliases can be added. Site based Alias-Quota has reached.',
                             'Unknown error occured.');
            $myAlias->status($tl->get($err_arr[$err]));
            exit;
        }
    }

    $button = ($mode == 'new') ? 'Save' : 'Update';

    if ($id)
        $myAB->Load($id);

    include 'header.php';
?>
<h1><?php echo $tl->get('Alias Management'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<?php if ($myAlias->formular_errors) echo '<p class="formerror"><img src="'. _SKIN. '/img/critical.png" alt="Error" height="25" width="25" />'. $tl->get('The formular was not properly filled out. Point at the question mark.'). '</p>'; ?>
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
                    <input type="hidden" name="id" value="<?php echo $myAlias->id ?>" />
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Site") ?></th>
                <td>
                <?php if ($mode == 'update') { ?>
                    <input type="text" name="sitename" value="<?php echo $myAlias->mySite->name ?>" size="30" class="textfield" readonly="readonly" />
                    <input type="hidden" name="siteid" value="<?php echo $myAlias->siteid ?>" />
                    <?php } else { ?>
                    <select name="siteid">
                        <option value=""></option>
                        <?php
                            foreach ($myAlias->sites as $elem)
                            {
                                $sel = ($elem['id'] == $myAlias->siteid) ? ' selected="selected"' : null;
                                echo "<option value=\"$elem[id]\"$sel>$elem[name]</option>\n";
                            }
                        ?>
                    </select>
                    <?php } ?>
                </td>
                <td><?php echo $myAlias->show_field_property("siteid") ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Incoming Address")?></th>
                <td>
                    <input type="text" name="leftpart" value="<?php echo $myAlias->leftpart?>" maxlength="255" size="50" class="textfield" />
                </td>
                <td><?php echo $myAlias->show_field_property("leftpart") ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Forward Address") ?></th>
                <td>
                    <input type="text" name="rightpart" value="<?php echo $myAlias->rightpart?>" maxlength="2000" size="50" class="textfield" />
                </td>
                <td><?php echo $myAlias->show_field_property("rightpart") ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Bounce and Forward") ?></th>
                <td>
                    <input type="checkbox" name="bounceforward" value="true" class="checkbox"<?php if (isTrue($myAlias->bounceforward)) echo ' checked="checked"' ?>/> <?php echo $tl->get("Yes") ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Blackhole") ?></th>
                <td>
                    <input type="checkbox" name="blackhole" value="true" class="checkbox"<?php if (isTrue($myAlias->blackhole)) echo ' checked="checked"' ?>/> <?php echo $tl->get("Yes") ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php if ($mode == 'update') { ?>
            <tr>
                <th><?php echo $tl->get("Alias created") ?></th>
                <td>
                    <input type="text" name="created" value="<?php echo $myAlias->added?>" size="<?php echo strlen($myAlias->added) ?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Alias last updated")?></th>
                <td>
                    <input type="text" name="updated" value="<?php echo $myAlias->updated?>" size="<?php echo strlen($myAlias->updated) ?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php } ?>
            <?php include 'show_addressbook.php'; ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" class="button" name="button" value="<?php echo $tl->get("$button") ?>" />
                    <?php if ($button == "Update") { ?>
                    <input type="submit" name="button" value="<?php echo $tl->get("Delete")?>" class="button" />
                    <?php } ?>
                    <input type="reset" class="button" name="button" value="<?php echo $tl->get("Reset") ?>" />
                    <input type="button" name="help" value="<?php echo $tl->get("Help") ?>" class="helpbutton" onclick="window.open('help.php?help=alias&amp;mode=<?php echo $mode ?>', '', 'scrollbars=yes, height=500, width=920');" />
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
