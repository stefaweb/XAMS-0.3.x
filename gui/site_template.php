<?php
    require 'gfl.php';
    gfl(_RESELLER);
    include 'include/global.php';
    include 'include/site_templates.php';

    $id = greq('id');
    $mode = greq('mode');
    $button = gpost('button');
    $resellerid = gpost('resellerid');

    $mySite = new Site_Templates();
    $tl =& $mySite->i18n;

    // Set the TopID to the logged in user-id (or the selected id, if admin logged in)
    $TopID = USERID;
    $IDField = 'resellerid';
    if (isADMIN)
    {
        if (empty($resellerid))
            $IDField = 'adminid';
        else
        {
            $TopID = $resellerid;
            $IDField = 'resellerid';
        }
    }
    $mySite->assign($IDField, $TopID);

    if ($id)
    {
        $mySite->Load($id);
        if (!$mySite->isAuthLoad())
            die($tl->get('Access denied.'));
    }

    if (!empty($button))
    {
        if ($button == $tl->get('Delete'))
        {
            $mySite->Delete();
            header('Location: templates.php?info='. urlencode($mySite->notice));
            exit;
        }

        $_POST['addrtype'] = (is_array(gpost('addrtype'))) ? implode(',', gpost('addrtype')) : null;
        $mySite->Assign2Object(array('id', 'templatename', 'name',
        'quotaunit', 'maxquota', 'maxuserquota', 'userquotaunit', 'maxaddr',
        'maxaliases', 'addrtype', 'viruscheckin', 'viruscheckout',
        'spamcheckin', 'spamcheckout', 'spamscore', 'highspamscore',
        'leftpart1', 'rightpart1', 'bounceforward1',
        'leftpart2', 'rightpart2', 'bounceforward2',
        'leftpart3', 'rightpart3', 'bounceforward3',
        'leftpart4', 'rightpart4', 'bounceforward4',
        'leftpart5', 'rightpart5', 'bounceforward5',
        'addressbook_', 'mode'));
        for ($i=0; $i<$mySite->quotaunit; $i++) $mySite->maxquota *= 1024;
        for ($i=0; $i<$mySite->userquotaunit; $i++) $mySite->maxuserquota *= 1024;

        $mySite->check_formular($mode);

        if (!$mySite->formular_errors)
        {
            switch ($button)
            {
                case $tl->get('Save'):
                    $mySite->Add();
                    break;
                case $tl->get('Update'):
                    $mySite->Update();
            }
            header('Location: templates.php?info='. urlencode($mySite->notice));
            exit;
        }
    }

    $button = ($mode == 'new') ? 'Save' : 'Update';

    if (!empty($mySite->maxquota))
        for ($mySite->quotaunit=0; $mySite->quotaunit<3; $mySite->quotaunit++)
            if ($mySite->maxquota % 1024 == 0) $mySite->maxquota /= 1024;
                else break;

    if (!empty($mySite->maxuserquota))
        for ($mySite->userquotaunit=0; $mySite->userquotaunit<3; $mySite->userquotaunit++)
            if ($mySite->maxuserquota % 1024 == 0) $mySite->maxuserquota /= 1024;
                else break;

    include 'header.php';
?>
<h1><?php echo $tl->get('Site-Templates Management'); ?></h1>
<?php if ($mySite->formular_errors) echo '<p class="formerror"><img src="'. _SKIN. '/img/critical.png" alt="Error" height="25" width="25" />'. $tl->get('The formular was not properly filled out. Point at the question mark.'). '</p>'; ?>
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
                    <input type="hidden" name="id" value="<?php echo $mySite->id?>" />
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Template-Name")?></th>
                <td>
                    <input type="text" name="templatename" value="<?php echo $mySite->templatename?>" maxlength="100" size="50" class="textfield" />
                </td>
                <td><?php echo $mySite->show_field_property("templatename")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Sitename (e.g. xams)")?></th>
                <td>
                    <input type="text" name="name" value="<?php echo $mySite->name?>" maxlength="100" size="50" class="textfield" />
                </td>
                <td><?php echo $mySite->show_field_property("name")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Quota")?></th>
                <td>
                    <input type="text" name="maxquota" value="<?php echo $mySite->maxquota?>" maxlength="10" size="10" class="textfield" />
                    <select name="quotaunit">
                        <option value="0"<?php if ($mySite->quotaunit==0) echo ' selected="selected"'?>>KB</option>
                        <option value="1"<?php if ($mySite->quotaunit==1) echo ' selected="selected"'?>>MB</option>
                        <option value="2"<?php if ($mySite->quotaunit==2) echo ' selected="selected"'?>>GB</option>
                    </select>
                </td>
                <td><?php echo $mySite->show_field_property("maxquota")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Quota (per User)")?></th>
                <td>
                    <input type="text" name="maxuserquota" value="<?php echo $mySite->maxuserquota?>" maxlength="10" size="10" class="textfield" />
                    <select name="userquotaunit">
                        <option value="0"<?php if ($mySite->userquotaunit==0) echo ' selected="selected"'?>>KB</option>
                        <option value="1"<?php if ($mySite->userquotaunit==1) echo ' selected="selected"'?>>MB</option>
                        <option value="2"<?php if ($mySite->userquotaunit==2) echo ' selected="selected"'?>>GB</option>
                    </select>
                </td>
                <td><?php echo $mySite->show_field_property("maxuserquota")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Users")?></th>
                <td>
                    <input type="text" name="maxaddr" value="<?php echo $mySite->maxaddr?>" size="10" maxlength="10" class="textfield" />
                </td>
                <td><?php echo $mySite->show_field_property("maxaddr")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Aliases")?></th>
                <td>
                    <input type="text" name="maxaliases" value="<?php echo $mySite->maxaliases?>" size="10" maxlength="10" class="textfield" />
                </td>
                <td><?php echo $mySite->show_field_property("maxaliases")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Type")?></th>
                <td>
                    <table width="300" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <input type="checkbox" name="addrtype[]" value="s" class="checkbox" <?php if ($mySite->addrtype & _SMTP) echo 'checked="checked"'; ?> /> SMTP 
                            </td>
                            <td>
                                <input type="checkbox" name="addrtype[]" value="p" class="checkbox" <?php if ($mySite->addrtype & _POP) echo 'checked="checked"'; ?> /> POP3
                            </td>
                            <td>
                                <input type="checkbox" name="addrtype[]" value="i" class="checkbox" <?php if ($mySite->addrtype & _IMAP) echo 'checked="checked"'; ?> /> IMAP
                            </td>
                            <td>
                                <input type="checkbox" name="addrtype[]" value="x" class="checkbox" <?php if ($mySite->addrtype & _XAMS) echo 'checked="checked"'; ?> /> XAMS
                            </td>
                        </tr>
                    </table>
                </td>
                <td><?php echo $mySite->show_field_property("addrtype")?></td>
            </tr>
                <th><?php echo $tl->get("Viruscheck (Incoming)")?></th>
                <td>
                    <table width="350">
                        <tr>
                            <td style="width: 100px;">
                                <input type="radio" name="viruscheckin" class="radiobutton" value="true"<?php if (isTrue($mySite->viruscheckin)) echo ' checked="checked"';?> /> <?php echo $tl->get("On")?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="viruscheckin" class="radiobutton" value="false"<?php if (isFalse($mySite->viruscheckin)) echo ' checked="checked"';?> /> <?php echo $tl->get("Off")?>
                            </td>
                            <td style="width: 200px;">
                                <input type="radio" name="viruscheckin" class="radiobutton" value=""<?php if (!$mySite->viruscheckin) echo ' checked="checked"';?> /> <?php echo $tl->get("Site dependent")?>
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
                                <input type="radio" name="viruscheckout" class="radiobutton" value="true"<?php if (isTrue($mySite->viruscheckout)) echo ' checked="checked"';?> /> <?php echo $tl->get("On")?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="viruscheckout" class="radiobutton" value="false"<?php if (isFalse($mySite->viruscheckout)) echo ' checked="checked"';?> /> <?php echo $tl->get("Off")?>
                            </td>
                            <td style="width: 200px;">
                                <input type="radio" name="viruscheckout" class="radiobutton" value=""<?php if (!$mySite->viruscheckout) echo ' checked="checked"';?> /> <?php echo $tl->get("Site dependent")?>
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
                                <input type="radio" name="spamcheckin" class="radiobutton" value="true"<?php if (isTrue($mySite->spamcheckin)) echo ' checked="checked"';?> /> <?php echo $tl->get("On")?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="spamcheckin" class="radiobutton" value="false"<?php if (isFalse($mySite->spamcheckin)) echo ' checked="checked"';?> /> <?php echo $tl->get("Off")?>
                            </td>
                            <td style="width: 200px;">
                                <input type="radio" name="spamcheckin" class="radiobutton" value=""<?php if (!$mySite->spamcheckin) echo ' checked="checked"';?> /> <?php echo $tl->get("Site dependent")?>
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
                                <input type="radio" name="spamcheckout" class="radiobutton" value="true"<?php if (isTrue($mySite->spamcheckout)) echo ' checked="checked"';?> /> <?php echo $tl->get("On")?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="spamcheckout" class="radiobutton" value="false"<?php if (isFalse($mySite->spamcheckout)) echo ' checked="checked"';?> /> <?php echo $tl->get("Off")?>
                            </td>
                            <td style="width: 200px;">
                                <input type="radio" name="spamcheckout" class="radiobutton" value=""<?php if (!$mySite->spamcheckout) echo ' checked="checked"';?> /> <?php echo $tl->get("Site dependent")?>
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
	<input type="text" name="spamscore" value="<?php echo $mySite->spamscore?>" maxlength="3" size="3" class="textfield" />
	<?php echo $tl->get("High Spam Score")?>:
	<input type="text" name="highspamscore" value="<?php echo $mySite->highspamscore?>" maxlength="3" size="3" class="textfield" />
	</td>
	<td><?php echo $mySite->show_field_property("spamscore")?><?php echo $mySite->show_field_property("highspamscore")?></td>
</tr>  
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Alias")?> 1</th>
                <td>
                    <input type="text" name="leftpart1" value="<?php echo $mySite->leftpart1?>" size="17" maxlength="255" class="textfield" />
                    <img src="<?php echo _SKIN?>/img/right.png" width="20" height="15" alt="" title="<?php echo $tl->get("is forwarded to")?>" />
                    <input type="text" name="rightpart1" value="<?php echo $mySite->rightpart1?>" size="19" maxlength="255" class="textfield" />
                    <input type="checkbox" name="bounceforward1" value="true" class="checkbox"<?php if (isTrue($mySite->bounceforward1)) echo ' checked="checked"' ?> /> <?php echo $tl->get("B&F")?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Alias")?> 2</th>
                <td>
                    <input type="text" name="leftpart2" value="<?php echo $mySite->leftpart2?>" size="17" maxlength="255" class="textfield" />
                    <img src="<?php echo _SKIN?>/img/right.png" width="20" height="15" alt="" title="<?php echo $tl->get("is forwarded to")?>" />
                    <input type="text" name="rightpart2" value="<?php echo $mySite->rightpart2?>" size="19" maxlength="255" class="textfield" />
                    <input type="checkbox" name="bounceforward2" value="true" class="checkbox"<?php if (isTrue($mySite->bounceforward2)) echo ' checked="checked"' ?> /> <?php echo $tl->get("B&F")?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Alias")?> 3</th>
                <td>
                    <input type="text" name="leftpart3" value="<?php echo $mySite->leftpart3?>" size="17" maxlength="255" class="textfield" />
                    <img src="<?php echo _SKIN?>/img/right.png" width="20" height="15" alt="" title="<?php echo $tl->get("is forwarded to")?>" />
                    <input type="text" name="rightpart3" value="<?php echo $mySite->rightpart3?>" size="19" maxlength="255" class="textfield" />
                    <input type="checkbox" name="bounceforward3" value="true" class="checkbox"<?php if (isTrue($mySite->bounceforward3)) echo ' checked="checked"' ?> /> <?php echo $tl->get("B&F")?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Alias")?> 4</th>
                <td>
                    <input type="text" name="leftpart4" value="<?php echo $mySite->leftpart4?>" size="17" maxlength="255" class="textfield" />
                    <img src="<?php echo _SKIN?>/img/right.png" width="20" height="15" alt="" title="<?php echo $tl->get("is forwarded to")?>" />
                    <input type="text" name="rightpart4" value="<?php echo $mySite->rightpart4?>" size="19" maxlength="255" class="textfield" />
                    <input type="checkbox" name="bounceforward4" value="true" class="checkbox"<?php if (isTrue($mySite->bounceforward4)) echo ' checked="checked"' ?> /> <?php echo $tl->get("B&F")?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Alias")?> 5</th>
                <td>
                    <input type="text" name="leftpart5" value="<?php echo $mySite->leftpart5?>" size="17" maxlength="255" class="textfield" />
                    <img src="<?php echo _SKIN?>/img/right.png" width="20" height="15" alt="" title="<?php echo $tl->get("is forwarded to")?>" />
                    <input type="text" name="rightpart5" value="<?php echo $mySite->rightpart5?>" size="19" maxlength="255" class="textfield" />
                    <input type="checkbox" name="bounceforward5" value="true" class="checkbox"<?php if (isTrue($mySite->bounceforward5)) echo ' checked="checked"' ?> /> <?php echo $tl->get("B&F")?>
                </td>
                <td>&nbsp;</td>
            </tr>
    <?php if ($mode == "update") { ?>
            <tr>
                <th><?php echo $tl->get("Template created")?></th>
                <td>
                    <input type="text" name="Created" value="<?php echo $mySite->added?>" size="<?php echo strlen($mySite->added)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Template last updated")?></th>
                <td>
                    <input type="text" name="Updated" value="<?php echo $mySite->updated?>" size="<?php echo strlen($mySite->updated)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
    <?php } ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" name="button" value="<?php echo $tl->get("$button")?>" class="button" />
                    <?php if ($mode != 'new') { ?>
                    <input type="submit" name="button" value="<?php echo $tl->get("Delete")?>" class="button" />
                    <?php } ?>
                    <input type="reset" name="button" value="<?php echo $tl->get("Reset")?>" class="button" />
                    <input type="button" name="help" value="<?php echo $tl->get("Help")?>" class="helpbutton" onclick="window.open('help.php?help=site_template&amp;mode=<?php echo $mode?>', '', 'scrollbars=yes, height=500, width=920');" />
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
