<?php
    require 'gfl.php';

    include 'include/global.php';
    include 'include/user_templates.php';

    $myUser = new User_Templates();
    $tl =& $myUser->i18n;

    $id = greq('id');
    $mode = greq('mode');
    $button = gpost('button');
    $resellerid = gpost('resellerid');
    $customerid = gpost('customerid');

    if ($id)
    {
        $myUser->Load($id);
        if (!$myUser->isAuthLoad())
            die($tl->get('Access denied.'));
    }

    // Set the TopID to the logged in user-id (or the selected id, if admin logged in)
    $TopID = USERID;
    switch (USERT)
    {
        case _ADMIN:
            if (!empty($resellerid))
            {
                $TopID = $resellerid;
                $IDField = 'resellerid';
            }
            elseif (!empty($customerid))
            {
                $TopID = $customerid;
                $IDField = 'customerid';
            }
            else
                $IDField = 'adminid';
            break;
        case _RESELLER:
            $IDField = 'resellerid';
            break;
        case _CUSTOMER:
            $IDField = 'customerid';
    }

    if ($button)
    {
        if ($button == $tl->get("Delete"))
        {
            $myUser->Delete();
            header('Location: templates.php?info='. urlencode($myUser->notice));
            exit;
        }

        $_POST['addrtype'] = (is_array(gpost('addrtype'))) ? implode(',', gpost('addrtype')) : null;
        $myUser->Assign2Object(array('templatename', 'name', 'password', 'quotaunit', 'quota', 'addrtype',
        'viruscheckin', 'viruscheckout', 'spamcheckin', 'spamcheckout', 'spamscore', 'highspamscore', 'relayonauth', 'relayoncheck',
        'accountstate', 'addressbook_', 'mode'));

        if ($myUser->quota > 0)
            for ($i=0; $i<$myUser->quotaunit; $i++) $myUser->quota *= 1024;

        $myUser->assign($IDField, $TopID);

        $myUser->check_formular($mode);

        if (!$myUser->formular_errors)
        {
            switch ($button)
            {
                case $tl->get('Save'):
                    $myUser->Add();
                    break;
                case $tl->get('Update'):
                    $myUser->Update();
            }
            header('Location: templates.php?info='. urlencode($myUser->notice));
            exit;
        }
    }

    $button = ($mode == 'new') ? 'Save' : 'Update';

    if ($myUser->quota > 0)
        for ($myUser->quotaunit=0; $myUser->quotaunit<3; $myUser->quotaunit++)
            if ($myUser->quota % 1024 == 0) $myUser->quota /= 1024;
            else break;

    include 'header.php';
?>
<h1><?php echo $tl->get('User-Templates Management'); ?></h1>
<?php if ($myUser->formular_errors) echo '<p class="formerror"><img src="'. _SKIN. '/img/critical.png" alt="Error" height="25" width="25" />'. $tl->get('The formular was not properly filled out. Point at the question mark.'). '</p>'; ?>
<form method="post" autocomplete="off" action="<?php echo $_SERVER['PHP_SELF']?>">
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
                    <input type="hidden" name="mode" value="<?php echo $mode?>" />
                    <input type="hidden" name="id" value="<?php echo $myUser->id?>" />
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Template-Name")?></th>
                <td>
                    <input type="text" name="templatename" value="<?php echo $myUser->templatename?>" maxlength="100" size="50" class="textfield" />
                </td>
                <td><?php echo $myUser->show_field_property("templatename")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Account name (E-Mail)")?></th>
                <td>
                    <input type="text" name="name" value="<?php echo $myUser->name?>" maxlength="100" size="50" class="textfield" />
                </td>
                <td><?php echo $myUser->show_field_property("name")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Password")?></th>
                <td>
                    <input type="password" name="password" value="" maxlength="100" size="30" class="textfield" />
                </td>
                <td><?php echo $myUser->show_field_property("password")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Quota")?></th>
                <td>
                    <input type="text" name="quota" value="<?php echo $myUser->quota?>" size="10" maxlength="10" class="textfield" />
                    <select name="quotaunit">
                        <option value="0"<?php if ($myUser->quotaunit==0) echo ' selected="selected"'?>>KB</option>
                        <option value="1"<?php if ($myUser->quotaunit==1) echo ' selected="selected"'?>>MB</option>
                        <option value="2"<?php if ($myUser->quotaunit==2) echo ' selected="selected"'?>>GB</option>
                    </select>
                </td>
                <td><?php echo $myUser->show_field_property("quota")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Type")?></th>
                <td>
                    <table width="300" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <input type="checkbox" name="addrtype[]" class="checkbox" value="s"<?php if ($myUser->addrtype & _SMTP) echo ' checked="checked"'; ?> /> SMTP
                            </td>
                            <td>
                                <input type="checkbox" name="addrtype[]" class="checkbox" value="p"<?php if ($myUser->addrtype & _POP) echo ' checked="checked"'; ?> /> POP3
                            </td>
                            <td>
                                <input type="checkbox" name="addrtype[]" class="checkbox" value="i"<?php if ($myUser->addrtype & _IMAP) echo ' checked="checked"'; ?> /> IMAP
                            </td>
                            <td>
                                <input type="checkbox" name="addrtype[]" class="checkbox" value="x"<?php if ($myUser->addrtype & _XAMS) echo ' checked="checked"'; ?> /> XAMS
                            </td>
                        </tr>
                    </table>
                </td>
                <td><?php echo $myUser->show_field_property("addrtype")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Viruscheck (Incoming)")?></th>
                <td>
                    <table width="350">
                        <tr>
                            <td style="width: 100px;">
                                <input type="radio" name="viruscheckin" class="radiobutton" value="true"<?php if (isTrue($myUser->viruscheckin)) echo ' checked="checked"';?> /> <?php echo $tl->get("On")?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="viruscheckin" class="radiobutton" value="false"<?php if (isFalse($myUser->viruscheckin)) echo ' checked="checked"';?> /> <?php echo $tl->get("Off")?>
                            </td>
                            <td style="width: 200px;">
                                <input type="radio" name="viruscheckin" class="radiobutton" value=""<?php if (!$myUser->viruscheckin) echo ' checked="checked"';?> /> <?php echo $tl->get("Site dependent")?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Viruscheck (Outgoing)")?></th>
                <td>
                    <table width="350">
                        <tr>
                            <td style="width: 100px;">
                                <input type="radio" name="viruscheckout" class="radiobutton" value="true"<?php if (isTrue($myUser->viruscheckout)) echo ' checked="checked"';?> /> <?php echo $tl->get("On")?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="viruscheckout" class="radiobutton" value="false"<?php if (isFalse($myUser->viruscheckout)) echo ' checked="checked"';?> /> <?php echo $tl->get("Off")?>
                            </td>
                            <td style="width: 200px;">
                                <input type="radio" name="viruscheckout" class="radiobutton" value=""<?php if (!$myUser->viruscheckout) echo ' checked="checked"';?> /> <?php echo $tl->get("Site dependent")?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Spamcheck (Incoming)")?></th>
                <td>
                    <table width="350">
                        <tr>
                            <td style="width: 100px;">
                                <input type="radio" name="spamcheckin" class="radiobutton" value="true"<?php if (isTrue($myUser->spamcheckin)) echo ' checked="checked"';?> /> <?php echo $tl->get("On")?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="spamcheckin" class="radiobutton" value="false"<?php if (isFalse($myUser->spamcheckin)) echo ' checked="checked"';?> /> <?php echo $tl->get("Off")?>
                            </td>
                            <td style="width: 200px;">
                                <input type="radio" name="spamcheckin" class="radiobutton" value=""<?php if (!$myUser->spamcheckin) echo ' checked="checked"';?> /> <?php echo $tl->get("Site dependent")?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Spamcheck (Outgoing)")?></th>
                <td>
                    <table width="350">
                        <tr>
                            <td style="width: 100px;">
                                <input type="radio" name="spamcheckout" class="radiobutton" value="true"<?php if (isTrue($myUser->spamcheckout)) echo ' checked="checked"';?> /> <?php echo $tl->get("On")?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="spamcheckout" class="radiobutton" value="false"<?php if (isFalse($myUser->spamcheckout)) echo ' checked="checked"';?> /> <?php echo $tl->get("Off")?>
                            </td>
                            <td style="width: 200px;">
                                <input type="radio" name="spamcheckout" class="radiobutton" value=""<?php if (!$myUser->spamcheckout) echo ' checked="checked"';?> /> <?php echo $tl->get("Site dependent")?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>&nbsp;</td>
            </tr>
<tr>
<th><?php echo $tl->get("Spam Score Trigger")?></th>
<td>
<?php echo $tl->get("Spam Score")?>:
<input type="text" name="spamscore" value="<?php echo $myUser->spamscore?>" maxlength="3" size="3" class="textfield" />
<?php echo $tl->get("High Spam Score")?>:
<input type="text" name="highspamscore" value="<?php echo $myUser->highspamscore?>" maxlength="3" size="3" class="textfield" />
</td>
</tr>
            <tr>
                <th><?php echo $tl->get("Relay on auth")?></th>
                <td>
                    <input type="checkbox" name="relayonauth" class="checkbox" value="true"<?php if (isTrue($myUser->relayonauth)) echo ' checked="checked"'; ?> /> <?php echo $tl->get('Yes') ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Relay on check")?></th>
                <td>
                    <input type="checkbox" name="relayoncheck" class="checkbox" value="true"<?php if (isTrue($myUser->relayoncheck)) echo ' checked="checked"'; ?> /> <?php echo $tl->get('Yes') ?>
                </td>
                <td>&nbsp;</td>
            </tr>
    <?php if ($mode == "update") { ?>
            <tr>
                <th><?php echo $tl->get("Template created")?></th>
                <td>
                    <input type="text" name="created" value="<?php echo $myUser->added?>" size="<?php echo strlen($myUser->added)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Template last updated")?></th>
                <td>
                    <input type="text" name="updated" value="<?php echo $myUser->updated?>" size="<?php echo strlen($myUser->updated)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
    <?php } ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" name="button" class="button" value="<?php echo $tl->get("$button")?>" />
                    <?php if ($mode == "update") { ?>
                    <input type="submit" name="button" value="<?php echo $tl->get("Delete")?>" class="button" />
                    <?php } ?>
                    <input type="reset" name="button" class="button" value="<?php echo $tl->get("Reset")?>" />
                    <input type="button" name="help" value="<?php echo $tl->get("Help")?>" class="helpbutton" onclick="window.open('help.php?help=user_template&amp;mode=<?php echo $mode?>', '', 'scrollbars=yes, height=500, width=905');" />
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
