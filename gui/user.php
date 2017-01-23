<?php
    require 'gfl.php';
    gfl(_CUSTOMER);
    include 'include/global.php';
    include 'include/users.php';
    include 'include/user_addressbook.php';

    $myAB = new User_Addressbook();
    $myUser = new Users();
    $tl =& $myUser->i18n;

    $id = greq('id');
    $mode = greq('mode');
    $siteid = greq('siteid');
    $templateid = gget('templateid');
    $button = gpost('button');
    $info = gget('info');

    if ($id)
    {
        $myUser->Load($id);
        if (!$myUser->isAuthLoad())
            die($tl->get('Access denied.'));
    }
    elseif ($siteid) $myUser->assign('siteid', $siteid);

    // Load and apply template if we have to do
    if (!empty($templateid))
    {
        include_once 'include/user_templates.php';
        $myT = new User_Templates();
        $myT->Load($templateid);
        if (!$myT->isAuthLoad())
            die($tl->get('Access denied.'));

        foreach (array('name', 'password', 'quota', 'relayonauth',
        'relayoncheck', 'addrtype', 'viruscheckin', 'viruscheckout',
        'spamcheckin', 'spamcheckout', 'spamscore', 'highspamscore') as $k) $myUser->assign($k, $myT->$k);
    }

    if ($button)
    {
        if ($button == $tl->get('Delete'))
        {
            $myUser->Delete();
            $myUser->status($myUser->notice);
            exit;
        }

        $_POST['addrtype'] = (is_array(gpost('addrtype'))) ? implode(',', gpost('addrtype')) : null;
        $myUser->Assign2Object(array('siteid', 'name', 'uniquename', 'id', 'password', 'quotaunit', 'quota',
        'addrtype', 'viruscheckin', 'viruscheckout', 'spamcheckin', 'spamcheckout', 'spamscore', 'highspamscore', 'relayonauth',
        'relayoncheck', 'autoreply', 'autoreplysubject', 'autoreplytext', 'accountstate', 'addressbook_', 'mode'));

        $myUser->CalcQuota(false);

        $myUser->check_formular($mode);

        if (!$myUser->formular_errors)
        {
            switch ($button)
            {
                case $tl->get('Save'):
                    $myUser->Add();
                    $info = $tl->get($myUser->notice);
                    $mode = 'update';
                    break;
                case $tl->get('Update'):
                    $myUser->Update();
                    $info = $tl->get($myUser->notice);
                    break;
            }
            $myUser->Load();
        }
    }

    $button = ($mode == 'new') ? 'Save' : 'Update';

    // Check if there can be more users added, but only if check_formular()
    // hasn't handled it (and occured an error)
    if ($mode == 'new' && !$myUser->formular_errors && $myUser->siteid)
    {
        $myUser->LoadSite();
        $err = $myUser->QC_AddUser();
        if ($err)
        {
            $err_arr = array(null,
                             'Unfortunately no more users can be added. Reseller\'s User-Quota has reached.',
                             'Unfortunately no more users can be added. The site based User-Quota has reached.',
                             'Unfortunately no more users can be added. The site based Quota has reached.',
                             'Select a Site in the drop-down menu.');
            $myUser->status($tl->get($err_arr[$err]));
            exit;
        }
    }

    $myUser->CalcQuota(true);

    if ($id) $myAB->Load($id);
    
    // Get value of spamscore
	if (strcmp($mode, 'new') == 0)
    {
    	if ($siteid)
    	{
			$spamscore = $myUser->spamscore;
			$highspamscore = $myUser->highspamscore;
    	}
    	else
    	{
    		$spamscore = 0;
    		$highspamscore = 0;
    	}
    }
    else
    {
	$spamscore = $myUser->spamscore;
	$highspamscore = $myUser->highspamscore;
    }
	
	
    include 'header.php';
?>
<h1><?php echo $tl->get('User Management'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<?php if ($myUser->formular_errors) echo '<p class="formerror"><img src="'. _SKIN. '/img/critical.png" alt="Error" height="25" width="25" />'. $tl->get('The formular was not properly filled out. Point at the question mark.'). '</p>'; ?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
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
                <th><?php echo $tl->get("Site")?></th>
                <td>
                    <?php if ($mode == 'update') { ?>
                    <input type="text" name="sitename" value="<?php echo $myUser->mySite->name?>" size="30" class="textfield" disabled="disabled" />
                    <input type="hidden" name="siteid" value="<?php echo $myUser->siteid?>" />
                    <?php } else { ?>
                    <select name="siteid" id="filter" onchange="changeValue();">
                        <option id="0" value="0" data-spamscore="0" data-highspamscore="0" ></option>
                        <?php
                            foreach ($myUser->sites as $elem)
                            {
                                $sel = ($elem['id'] == $myUser->siteid) ? ' selected="selected"' : null;
                                $res = $myUser->db->query('SELECT id, spamscore, highspamscore FROM pm_sites WHERE id = ?', $elem['id']);
                                $donnees = mysql_fetch_array($res->_res);
                                echo "<option id=\"$elem[id]\" data-spamscore=\"" . $donnees['spamscore'] . "\" data-highspamscore=\"" . $donnees['highspamscore'] . "\" value=\"$elem[id]\"$sel>$elem[name]</option>";
                            }
                        ?>
                    </select>
                    <?php } ?>
                </td>
                <td><?php echo $myUser->show_field_property('siteid')?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Account name (E-Mail)")?></th>
                <td>
                    <input type="text" name="name" value="<?php echo $myUser->name?>" maxlength="100" size="50" class="textfield"<?php if ($mode == 'update') echo " readonly=\"readonly\""?> />
                </td>
                <td><?php echo $myUser->show_field_property("name")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Unique Login name (optional)")?></th>
                <td>
                    <input type="text" name="uniquename" value="<?php echo $myUser->uniquename?>" maxlength="100" size="50" class="textfield" />
                </td>
                <td><?php echo $myUser->show_field_property("uniquename")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Password")?></th>
                <td>
                    <input class="password" type="text" name="password" value="" maxlength="100" size="30" class="textfield" />
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
		    <input type="hidden" name="quotabool" value="quotabooltrue" />
                </td>
                <td><?php echo $myUser->show_field_property("quota")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Type")?></th>
                <td>
                    <table width="300">
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
                <td><?php echo $myUser->show_field_property('addrtype')?></td>
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
                <td><?php echo $myUser->show_field_property('viruscheckin')?></td>
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
                <td><?php echo $myUser->show_field_property('viruscheckout')?></td>
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
                <td><?php echo $myUser->show_field_property('spamcheckin')?></td>
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
                <td><?php echo $myUser->show_field_property('spamcheckout')?></td>
            </tr>
<tr>
<th><?php echo $tl->get("Spam Score Trigger")?></th>
	<td>
	<?php echo $tl->get("Spam Score")?>:
	<input type="text" name="spamscore" id ="spamscore" value="<?php echo $spamscore ?>" maxlength="3" size="3" class="textfield" />
	<?php echo $tl->get("High Spam Score")?>:
	<input type="text" name="highspamscore" id ="highspamscore" value="<?php echo $highspamscore ?>" maxlength="3" size="3" class="textfield" />
	</td>
	<td><?php echo $myUser->show_field_property("spamscore")?><?php echo $myUser->show_field_property("highspamscore")?></td>
</tr>
            <tr>
                <th><?php echo $tl->get("Relay on auth")?></th>
                <td>
                    <input type="checkbox" name="relayonauth" class="checkbox" value="true"<?php if (isTrue($myUser->relayonauth)) echo ' checked="checked"'; ?> /> <?php echo $tl->get("Yes")?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Relay on check")?></th>
                <td>
                    <input type="checkbox" name="relayoncheck" class="checkbox" value="true"<?php if (isTrue($myUser->relayoncheck)) echo ' checked="checked"'; ?> /> <?php echo $tl->get("Yes")?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Auto reply")?></th>
                <td>
                    <strong><?php echo $tl->get("Subject")?>:</strong>
                    <br/>
                    <input type="text" name="autoreplysubject" value="<?php echo $myUser->autoreplysubject?>" maxlength="50" size="50" class="textfield" />

                    <br/>

                    <strong><?php echo $tl->get("Message")?>:</strong>
                    <br/>
                    <textarea name="autoreplytext" cols="40" rows="4" class="textfield"><?php echo $myUser->autoreplytext?></textarea>

                    <br/>

                    <input type="checkbox" name="autoreply" class="checkbox" value="true"<?php if (isTrue($myUser->autoreply)) echo ' checked="checked"'; ?> /> <?php echo $tl->get("On")?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Status")?></th>
                <td>
                    <table width="410">
                        <tr>
                            <td style="width: 100px;">
                                <input type="radio" name="accountstate" class="radiobutton" value="default"<?php if ($myUser->accountstate == "default") echo ' checked="checked"';?> /> <?php echo $tl->get("Active")?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="accountstate" class="radiobutton" value="locked"<?php if ($myUser->accountstate == "locked") echo ' checked="checked"';?> /> <?php echo $tl->get("Locked")?>
                            </td>
                            <td style="width: 210px;">
                                <input type="radio" name="accountstate" class="radiobutton" value="lockedbounce"<?php if ($myUser->accountstate == "lockedbounce") echo ' checked="checked"';?> /> <?php echo $tl->get("Locked & Bounce")?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php if ($mode == 'update') { ?>
            <tr>
                <th><?php echo $tl->get("Filter")?></th>
                <td>
                    <input type="button" name="filter" value="<?php echo $tl->get("Edit")?>" class="button" onclick="window.open('filter.php?userid=<?php echo $myUser->id ?>', '', 'scrollbars=yes, height=500, width=750');" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php } ?>
            <?php if ($mode == 'update') { ?>
            <tr>
                <th><?php echo $tl->get("User created")?></th>
                <td>
                    <input type="text" name="created" value="<?php echo $myUser->added?>" size="<?php echo strlen($myUser->added)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("User last updated")?></th>
                <td>
                    <input type="text" name="updated" value="<?php echo $myUser->updated?>" size="<?php echo strlen($myUser->updated)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php } ?>
            <?php include 'show_addressbook.php'; ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p></p>
                    <input type="submit" name="button" class="button" value="<?php echo $tl->get("$button")?>" />
                    <input type="submit" name="button" value="<?php echo $tl->get("Delete")?>" class="button" />
                    <input type="reset" name="button" class="button" value="<?php echo $tl->get("Reset")?>" />
                    <?php if (USERT > _USER) { ?>
                    <input type="button" name="help" value="<?php echo $tl->get("Help")?>" class="helpbutton" onclick="window.open('help.php?help=user&amp;mode=<?php echo $mode?>', '', 'scrollbars=yes, height=500, width=920');" />
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<script type="text/javascript">
function changeValue(){
	
	var option=document.getElementById('filter').value;
	var filter=document.getElementById(option)
	var spamscore=filter.getAttribute("data-spamscore")
	var highspamscore=filter.getAttribute("data-highspamscore")

	
    document.getElementById('spamscore').value=spamscore;
	document.getElementById('highspamscore').value=highspamscore;
}
</script>
<?php include 'footer.php' ?>
