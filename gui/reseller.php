<?php
    require 'gfl.php';
    gfl(_ADMIN);
    require 'include/global.php';
    include 'include/reseller_addressbook.php';

    include 'include/resellers.php';
    $myReseller = new Resellers();
    $tl =& $myReseller->i18n;

    $myAB = new Reseller_Addressbook();

    $id     = greq('id');
    $mode   = greq('mode');
    $button = gpost('button');
    
    if ($id)
    {
        $myReseller->Load($id);
        if (!$myReseller->isAuthLoad())
            die($tl->get('Access denied.'));
    }

    if ($button)
    {
        if ($button == $tl->get('Delete'))
        {
            $myReseller->Delete();
            header('Location: account_overview.php?info='. urlencode($myReseller->notice));
            exit;
        }

        $myReseller->Assign2Object(array('name', 'password', 'locked',
        'maxcustomers', 'maxsites', 'maxdomains', 'maxusers', 'maxaliases', 'maxquota', 'quotaunit',
        'maxsitequota', 'sitequotaunit', 'maxuserquota', 'userquotaunit', 'viruscheckin', 'viruscheckout',
        'spamcheckin', 'spamcheckout', 'sites', 'addressbook_', 'spamscore', 'highspamscore'));
        if ($myReseller->maxquota > 0) for ($i=0; $i<$myReseller->quotaunit; $i++) $myReseller->maxquota *= 1024;
        if ($myReseller->maxsitequota > 0) for ($i=0; $i<$myReseller->sitequotaunit; $i++) $myReseller->maxsitequota *= 1024;
        if ($myReseller->maxuserquota > 0) for ($i=0; $i<$myReseller->userquotaunit; $i++) $myReseller->maxuserquota *= 1024;

        $myReseller->check_formular($mode);

        if (!$myReseller->formular_errors)
        {
            switch ($button)
            {
                case $tl->get('Save'):
                    $myReseller->Add();
                    header('Location: account_overview.php?info='. urlencode($myReseller->notice));
                    exit;
                case $tl->get('Update'):
                    $myReseller->Update();
                    header('Location: account_overview.php?info='. urlencode($myReseller->notice));
                    exit;
            }
        }
    }

    if ($myReseller->maxquota)
    {
        for ($myReseller->quotaunit=0; $myReseller->quotaunit<3; $myReseller->quotaunit++)
            if ($myReseller->maxquota % 1024 == 0) $myReseller->maxquota /= 1024;
            else break;
    }
    if ($myReseller->maxsitequota)
    {
        for ($myReseller->sitequotaunit=0; $myReseller->sitequotaunit<3; $myReseller->sitequotaunit++)
            if ($myReseller->maxsitequota % 1024 == 0) $myReseller->maxsitequota /= 1024;
            else break;
    }
    if ($myReseller->maxuserquota)
    {
        for ($myReseller->userquotaunit=0; $myReseller->userquotaunit<3; $myReseller->userquotaunit++)
            if ($myReseller->maxuserquota % 1024 == 0) $myReseller->maxuserquota /= 1024;
            else break;
    }

    $button = ($mode == 'new') ? 'Save' : 'Update';

    if ($id)
        $myAB->Load($id);

    $db = new xclass();
    $sites = $db->db->getAll('SELECT id, resellerid, name FROM pm_sites WHERE resellerid = 0 OR resellerid = ?', array($id), DB_FETCHMODE_ASSOC);
    $site_select_box_size = (count($sites) > 30) ? 30 : count($sites);
   
   	// Default value of spamscore
   	if (strcmp($button, 'Save') == 0)
   	{
	   	$res = $db->db->query('SELECT spamscore, highspamscore FROM pm_preferences', NULL); 
		$donnees = mysql_fetch_array($res->_res);
		
		$spamscore = $donnees['spamscore'];
		$highspamscore = $donnees['highspamscore'];
   	}
   	else
   	{
   		$spamscore = $myReseller->spamscore;
   		$highspamscore = $myReseller->highspamscore;
   	}

    include 'header.php';

?>
<h1><?php echo $tl->get('Reseller Management'); ?></h1>
<?php if ($myReseller->formular_errors) echo '<p class="formerror"><img src="'. _SKIN. '/img/critical.png" alt="Error" height="25" width="25" />'. $tl->get('The formular was not properly filled out. Point at the question mark.'). '</p>'; ?>
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
                    <?php if ($mode == "update") { ?>
                    <input type="hidden" name="id" value="<?php echo $myReseller->id?>" />
                    <?php } ?>
                    <input type="hidden" name="button" id="1" value="" />
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Reseller Name")?></th>
                <td>
                    <input type="text" name="name" value="<?php echo $myReseller->name?>" maxlength="100" size="50" class="textfield" />
                </td>
                <td><?php echo $myReseller->show_field_property("name")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Reseller Password")?></th>
                <td>
                    <input type="password" name="password" value="" maxlength="100" size="50" class="textfield" />
                </td>
                <td><?php echo $myReseller->show_field_property("password")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Customers")?></th>
                <td>
                    <input type="text" name="maxcustomers" value="<?php echo $myReseller->maxcustomers?>" maxlength="10" size="10" class="textfield" />
                    <input type="checkbox" name="maxcustomers" class="checkbox" value="-1"<?php if ($myReseller->maxcustomers < 0) echo ' checked="checked"' ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $myReseller->show_field_property("maxcustomers")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Sites")?></th>
                <td>
                    <input type="text" name="maxsites" value="<?php echo $myReseller->maxsites?>" maxlength="10" size="10" class="textfield" />
                    <input type="checkbox" name="maxsites" class="checkbox" value="-1"<?php if ($myReseller->maxsites < 0) echo ' checked="checked"' ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $myReseller->show_field_property("maxsites")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Domains")?></th>
                <td>
                    <input type="text" name="maxdomains" value="<?php echo $myReseller->maxdomains?>" maxlength="10" size="10" class="textfield" />
                    <input type="checkbox" name="maxdomains" class="checkbox" value="-1"<?php if ($myReseller->maxdomains < 0) echo ' checked="checked"' ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $myReseller->show_field_property("maxdomains")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Users")?></th>
                <td>
                    <input type="text" name="maxusers" value="<?php echo $myReseller->maxusers?>" maxlength="10" size="10" class="textfield" />
                    <input type="checkbox" name="maxusers" class="checkbox" value="-1"<?php if ($myReseller->maxusers < 0) echo ' checked="checked"' ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $myReseller->show_field_property("maxusers")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Aliases")?></th>
                <td>
                    <input type="text" name="maxaliases" value="<?php echo $myReseller->maxaliases?>" maxlength="10" size="10" class="textfield" />
                    <input type="checkbox" name="maxaliases" class="checkbox" value="-1"<?php if ($myReseller->maxaliases < 0) echo ' checked="checked"' ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $myReseller->show_field_property("maxaliases")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Quota")?></th>
                <td>
                    <input type="text" name="maxquota" value="<?php echo $myReseller->maxquota?>" maxlength="10" size="10" class="textfield" />
                    <select name="quotaunit">
                        <option value="0"<?php if ($myReseller->quotaunit==0) echo ' selected="selected"'; ?>>KB</option>
                        <option value="1"<?php if ($myReseller->quotaunit==1) echo ' selected="selected"'; ?>>MB</option>
                        <option value="2"<?php if ($myReseller->quotaunit==2) echo ' selected="selected"'; ?>>GB</option>
                    </select>
                    <input type="checkbox" name="maxquota" class="checkbox" value="-1"<?php if ($myReseller->maxquota < 0) echo ' checked="checked"' ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $myReseller->show_field_property("maxquota")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Site-Quota")?></th>
                <td>
                    <input type="text" name="maxsitequota" value="<?php echo $myReseller->maxsitequota?>" maxlength="10" size="10" class="textfield" />
                    <select name="sitequotaunit">
                        <option value="0"<?php if ($myReseller->sitequotaunit==0) echo ' selected="selected"'; ?>>KB</option>
                        <option value="1"<?php if ($myReseller->sitequotaunit==1) echo ' selected="selected"'; ?>>MB</option>
                        <option value="2"<?php if ($myReseller->sitequotaunit==2) echo ' selected="selected"'; ?>>GB</option>
                    </select>
                    <input type="checkbox" name="maxsitequota" class="checkbox" value="-1"<?php if ($myReseller->maxsitequota < 0) echo ' checked="checked"' ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $myReseller->show_field_property("maxsitequota")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. User-Quota")?></th>
                <td>
                    <input type="text" name="maxuserquota" value="<?php echo $myReseller->maxuserquota?>" maxlength="10" size="10" class="textfield" />
                    <select name="userquotaunit">
                        <option value="0"<?php if ($myReseller->userquotaunit==0) echo ' selected="selected"'; ?>>KB</option>
                        <option value="1"<?php if ($myReseller->userquotaunit==1) echo ' selected="selected"'; ?>>MB</option>
                        <option value="2"<?php if ($myReseller->userquotaunit==2) echo ' selected="selected"'; ?>>GB</option>
                    </select>
                    <input type="checkbox" name="maxuserquota" class="checkbox" value="-1"<?php if ($myReseller->maxuserquota < 0) echo ' checked="checked"' ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $myReseller->show_field_property("maxuserquota")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Viruscheck") ?></th>
                <td>
                    <table width="200">
                        <tr>
                            <td>
                                <input type="checkbox" name="viruscheckin" value="true" class="checkbox"<?php if (isTrue($myReseller->viruscheckin)) echo 'checked="checked"' ?> /> <?php echo $tl->get('Incoming') ?>
                            </td>
                            <td>
                                <input type="checkbox" name="viruscheckout" value="true" class="checkbox"<?php if (isTrue($myReseller->viruscheckout)) echo 'checked="checked"' ?> /> <?php echo $tl->get('Outgoing') ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Spamcheck") ?></th>
                <td>
                    <table width="200">
                        <tr>
                            <td>
                                <input type="checkbox" name="spamcheckin" value="true" class="checkbox"<?php if (isTrue($myReseller->spamcheckin)) echo 'checked="checked"' ?> /> <?php echo $tl->get('Incoming') ?>
                            </td>
                            <td>
                                <input type="checkbox" name="spamcheckout" value="true" class="checkbox"<?php if (isTrue($myReseller->spamcheckout)) echo 'checked="checked"' ?> /> <?php echo $tl->get('Outgoing') ?>
                            </td>
                        </tr>
                    </table>
					<tr>
						<th><?php echo $tl->get("Spam Score Trigger")?></th>
							<td>
								<?php echo $tl->get("Spam Score")?>:
								<input type="text" name="spamscore" value="<?php echo $spamscore?>" maxlength="3" size="3" class="textfield" />
								<?php echo $tl->get("High Spam Score")?>:
								<input type="text" name="highspamscore" value="<?php echo $highspamscore?>" maxlength="3" size="3" class="textfield" />
							</td>
							<td><?php echo $myReseller->show_field_property("spamscore")?><?php echo $myReseller->show_field_property("highspamscore")?></td>
						</tr>  
                	</td>
            	</tr>
            <tr>
                <th><?php echo $tl->get("Sites (multiple selection)")?></th>
                <td style="padding-top:5px; padding-bottom:5px;">
                    <?php if (count($sites) == 0) echo $tl->get('No unassigned sites found.'); else { ?>
                    <select name="sites[]" size="<?php echo $site_select_box_size?>" style="width: 250px;" multiple="multiple">
                    <?php
                        foreach ($sites as $k=>$elem)
                        {
                            $sel = ($elem['resellerid'] == $myReseller->id) ? ' selected="selected"' : null;
                            echo "<option value=\"$elem[id]\"$sel>$elem[name]</option>\n";
                        }
                    ?>
                    </select>
                    <?php } ?>
                </td>
                <td><?php echo $myReseller->show_field_property("sites")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Account locked")?></th>
                <td>
                    <input type="checkbox" name="locked" class="checkbox" value="true"<?php if (isTrue($myReseller->locked)) echo ' checked="checked"';?> />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php if ($mode == "update") { ?>
            <tr>
                <th><?php echo $tl->get("Reseller created")?></th>
                <td>
                    <input type="text" name="created" value="<?php echo $myReseller->added?>" size="<?php echo strlen($myReseller->added)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Reseller last updated")?></th>
                <td>
                    <input type="text" name="updated" value="<?php echo $myReseller->updated?>" size="<?php echo strlen($myReseller->updated)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php } ?>
            <?php include 'show_addressbook.php'; ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" name="button" value="<?php echo $tl->get("$button")?>" class="button" />
                    <input type="submit" name="button" value="<?php echo $tl->get("Delete")?>" class="button" />
                    <input type="reset" name="button" value="<?php echo $tl->get("Reset")?>" class="button" />
                    <input type="button" name="help" value="<?php echo $tl->get("Help")?>" class="helpbutton" onclick="window.open('help.php?help=reseller&amp;mode=<?php echo $mode?>', '', 'scrollbars=yes, height=500, width=920');" />
                </td>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
