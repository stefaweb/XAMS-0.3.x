<?php
    require 'gfl.php';

    // Customers have an own site-menu
    if (isCUSTOMER) {
        header('Location: customer_site.php?'.$_SERVER['QUERY_STRING']);
        exit;
    }

    gfl(_RESELLER);

    include 'include/global.php';
    include 'include/sites.php';
    include 'include/site_addressbook.php';
    include 'include/aliases.php';

    $myAB = new Site_Addressbook();
    $mySite = new Sites();
    $myReseller = &$mySite->myReseller;
    $tl = &$mySite->i18n;

    $id = greq('id');
    $mode = greq('mode');
    $templateid = gget('templateid');
    $info = gget('info');
    $button = gpost('button');

    if ($id) { // Load existing Site
        $mySite->Load($id);
        if (!$mySite->isAuthLoad()) {
            die($tl->get('Access denied.'));
        }
    } else { // New Site
        // Set the resellerid of this site to the userid of the currently
        // logged in reseller
        $resellerid = (isRESELLER) ? USERID : gpost('resellerid');
        $mySite->assignFormVar('resellerid', $resellerid);

        // Load and apply template if we have to do
        if ($templateid) {
            include_once 'include/site_templates.php';
            $myT = new Site_Templates();
            $myT->Load($templateid);
            if (!$myT->isAuthLoad()) {
                die($tl->get('Access denied.'));
            }

            foreach (['name', 'maxquota', 'maxuserquota', 'maxaddr',
                     'maxaliases', 'addrtype', 'viruscheckin', 'viruscheckout',
                     'spamcheckin', 'spamcheckout', 'spamscore', 'highspamscore', ] as $k) {
                if (isset($myT->$k)) {
                    $mySite->assign($k, $myT->$k);
                }
            }
            for ($i = 1; $i <= 5; $i++) {
                ${"leftpart$i"} = $myT->{"leftpart$i"};
                ${"rightpart$i"} = $myT->{"rightpart$i"};
                ${"bounceforward$i"} = $myT->{"bounceforward$i"};
            }
        }
    }

    if ($button) {
        if ($button == $tl->get('Delete')) {
            $mySite->Delete();
            $mySite->status($mySite->notice);
            exit;
        }

        $_POST['addrtype'] = (is_array(gpost('addrtype'))) ? implode(',', gpost('addrtype')) : null;
        $mySite->Assign2Object(['id', 'name', 'domainname',
        'quotaunit', 'maxquota', 'maxuserquota', 'userquotaunit', 'maxaddr', 'maxaliases',
        'addrtype', 'viruscheckin', 'viruscheckout', 'spamcheckin', 'spamcheckout', 'spamscore', 'highspamscore',
        'sitestate', 'addressbook_', 'mode', ]);

        $mySite->CalcQuotaUnits(false);
        $mySite->check_formular($mode);
        if (!$mySite->formular_errors) {
            switch ($button) {
                case $tl->get('Save'):
                case '>> '.$tl->get('New User'):
                    $mySite->Add();
                    $info = $tl->get($mySite->notice);
                    $mode = 'update';

                    $myAlias = new Aliases();
                    for ($i = 1; $i <= 5; $i++) {
                        $lp = gpost('leftpart'.$i);
                        $rp = gpost('rightpart'.$i);
                        $bf = gpost('bounceforward'.$i);
                        if ($lp && $rp) {
                            $myAlias->assign('siteid', $mySite->id);
                            $myAlias->assign('leftpart', $lp);
                            $myAlias->assign('rightpart', $rp);
                            $myAlias->assign('bounceforward', $bf);
                            $myAlias->Add();
                        }
                    }

                    if ($button == '>> '.$tl->get('New User')) {
                        header("Location: user.php?mode=new&siteid=$mySite->id");
                        exit;
                    }
                    break;
                case $tl->get('Update'):
                    $mySite->Update();
                    $info = $tl->get($mySite->notice);
                    break;
            }
            $mySite->Load();
        }
    }

    $button = ($mode == 'new') ? 'Save' : 'Update';

    $mySite->CalcQuotaUnits(true);

    // Calculate Limits
    if ($mySite->resellerid) {
        $myReseller->Load($mySite->resellerid);
    }

    // Check if logged in Reseller is over site-quota
    if (isRESELLER && $myReseller->FreeSites() == 0 && $mode == 'new') {
        $myReseller->status($tl->get('Unfortunately no more sites can be added. Your Site-Quota has reached.'));
        exit;
    }

    if ($id) {
        $myAB->Load($id);
    }

    // Get value of spamscore
    if (strcmp($mode, 'new') == 0) {
        if ($mySite->resellerid) {
            $spamscore = $mySite->spamscore;
            $highspamscore = $mySite->highspamscore;
        } else {
            $spamscore = 0;
            $highspamscore = 0;
        }
    } else {
        $res = $mySite->db->query('SELECT id, spamscore, highspamscore FROM pm_sites WHERE id = ?', $mySite->id);
        $donnees = mysql_fetch_array($res->_res);
        $spamscore = $donnees['spamscore'];
        $highspamscore = $donnees['highspamscore'];
    }

    include 'header.php';
?>
<h1><?php echo $tl->get('Site Management'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<?php if ($mySite->formular_errors) {
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
                    <input type="hidden" name="id" value="<?php echo $mySite->id ?>" />

                    <?php if ($templateid) {
    ?>
                    <input type="hidden" name="leftpart1" value="<?php echo $leftpart1 ?>" />
                    <input type="hidden" name="rightpart1" value="<?php echo $rightpart1 ?>" />
                    <input type="hidden" name="bounceforward1" value="<?php echo $bounceforward1 ?>" />
                    <input type="hidden" name="leftpart2" value="<?php echo $leftpart2 ?>" />
                    <input type="hidden" name="rightpart2" value="<?php echo $rightpart2 ?>" />
                    <input type="hidden" name="bounceforward2" value="<?php echo $bounceforward2 ?>" />
                    <input type="hidden" name="leftpart3" value="<?php echo $leftpart3 ?>" />
                    <input type="hidden" name="rightpart3" value="<?php echo $rightpart3 ?>" />
                    <input type="hidden" name="bounceforward3" value="<?php echo $bounceforward3 ?>" />
                    <input type="hidden" name="leftpart4" value="<?php echo $leftpart4 ?>" />
                    <input type="hidden" name="rightpart4" value="<?php echo $rightpart4 ?>" />
                    <input type="hidden" name="bounceforward4" value="<?php echo $bounceforward4 ?>" />
                    <input type="hidden" name="leftpart5" value="<?php echo $leftpart5 ?>" />
                    <input type="hidden" name="rightpart5" value="<?php echo $rightpart5 ?>" />
                    <input type="hidden" name="bounceforward5" value="<?php echo $bounceforward5 ?>" />
                    <?php 
} ?>
                </td>
            </tr>
            <?php
                if (isADMIN) {
                    ?>
            <tr>
                <th><?php echo $tl->get('Reseller') ?></th>
                <td>
                    <?php if ($mode == 'update') {
                        ?>
                    <input type="text" value="<?php echo $mySite->resellername ?>" maxlength="100" size="50" class="textfield" disabled="disabled" />
                    <?php 
                    } else {
                        ?>
                    <select name="resellerid" id="filter" onchange="changeValue();">
                        <option id="0" value="0" data-spamscore="0" data-highspamscore="0" ></option>
                        <?php
                            foreach ($mySite->resellers as $elem) {
                                $sel = ($elem['id'] == $mySite->resellerid) ? ' selected="selected"' : null;
                                $res = $mySite->db->query('SELECT id, spamscore, highspamscore FROM pm_resellers WHERE id = ?', $elem['id']);
                                $donnees = mysql_fetch_array($res->_res);
                                echo "<option id=\"$elem[id]\" data-spamscore=\"".$donnees['spamscore'].'" data-highspamscore="'.$donnees['highspamscore']."\" value=\"$elem[id]\"$sel>$elem[name]</option>";
                            } ?>
                    </select>
                    <?php 
                    } ?>
                </td>
                <td><?php echo $mySite->show_field_property('resellerid') ?></td>
            </tr>
            <?php 
                } ?>
            <tr>
                <th><?php echo $tl->get('Sitename') ?></th>
                <td>
                    <input type="text" name="name" value="<?php echo $mySite->name ?>" maxlength="100" size="50" class="textfield"<?php if ($mode == 'update') {
                    echo ' readonly="readonly"';
                } ?> />
                </td>
                <td><?php echo $mySite->show_field_property('name') ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Domains') ?></th>
                <td>
                    <textarea name="domainname" cols="43" rows="4" class="textfield"><?php foreach ($mySite->myDomains->domainnames as $elem) {
                    echo "$elem\n";
                } ?></textarea>
                </td>
                <td><?php echo $mySite->show_field_property('domainname') ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Max. Quota')?></th>
                <td>
                    <input type="text" name="maxquota" value="<?php echo $mySite->maxquota ?>" maxlength="10" size="10" class="textfield" />
                    <select name="quotaunit">
                        <option value="0"<?php if ($mySite->quotaunit == 0) {
                    echo ' selected="selected"';
                } ?>>KB</option>
                        <option value="1"<?php if ($mySite->quotaunit == 1) {
                    echo ' selected="selected"';
                } ?>>MB</option>
                        <option value="2"<?php if ($mySite->quotaunit == 2) {
                    echo ' selected="selected"';
                } ?>>GB</option>
                    </select>
                    <input type="checkbox" name="maxquota" class="checkbox" value="-1"<?php if ($mySite->maxquota < 0) {
                    echo ' checked="checked"';
                } ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $mySite->show_field_property('maxquota') ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Max. User-Quota') ?></th>
                <td>
                    <input type="text" name="maxuserquota" value="<?php echo $mySite->maxuserquota?>" maxlength="10" size="10" class="textfield" />
                    <select name="userquotaunit">
                        <option value="0"<?php if ($mySite->userquotaunit == 0) {
                    echo ' selected="selected"';
                } ?>>KB</option>
                        <option value="1"<?php if ($mySite->userquotaunit == 1) {
                    echo ' selected="selected"';
                } ?>>MB</option>
                        <option value="2"<?php if ($mySite->userquotaunit == 2) {
                    echo ' selected="selected"';
                } ?>>GB</option>
                    </select>
                    <input type="checkbox" name="maxuserquota" class="checkbox" value="-1"<?php if ($mySite->maxuserquota < 0) {
                    echo ' checked="checked"';
                } ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $mySite->show_field_property('maxuserquota') ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Max. Users') ?></th>
                <td>
                    <input type="text" name="maxaddr" value="<?php echo $mySite->maxaddr?>" size="10" maxlength="10" class="textfield" />
                    <input type="checkbox" name="maxaddr" class="checkbox" value="-1"<?php if ($mySite->maxaddr < 0) {
                    echo ' checked="checked"';
                } ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $mySite->show_field_property('maxaddr') ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Max. Aliases') ?></th>
                <td>
                    <input type="text" name="maxaliases" value="<?php echo $mySite->maxaliases?>" size="10" maxlength="10" class="textfield" />
                    <input type="checkbox" name="maxaliases" class="checkbox" value="-1"<?php if ($mySite->maxaliases < 0) {
                    echo ' checked="checked"';
                } ?> /> <?php echo $tl->get('unlimited') ?>
                </td>
                <td><?php echo $mySite->show_field_property('maxaliases') ?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Type') ?></th>
                <td>
                    <table width="300">
                        <tr>
                            <td>
                                <input type="checkbox" name="addrtype[]" value="s" class="checkbox"<?php if ($mySite->addrtype & _SMTP) {
                    echo 'checked="checked"';
                } ?> /> SMTP
                            </td>
                            <td>
                                <input type="checkbox" name="addrtype[]" value="p" class="checkbox"<?php if ($mySite->addrtype & _POP) {
                    echo 'checked="checked"';
                } ?> /> POP3
                            </td>
                            <td>
                                <input type="checkbox" name="addrtype[]" value="i" class="checkbox"<?php if ($mySite->addrtype & _IMAP) {
                    echo 'checked="checked"';
                } ?> /> IMAP
                            </td>
                            <td>
                                <input type="checkbox" name="addrtype[]" value="x" class="checkbox"<?php if ($mySite->addrtype & _XAMS) {
                    echo 'checked="checked"';
                } ?> /> XAMS
                            </td>
                        </tr>
                    </table>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Viruscheck') ?></th>
                <td>
                    <table width="200">
                        <tr>
                            <td>
                                <input type="checkbox" name="viruscheckin" value="true" class="checkbox"<?php if (isTrue($mySite->viruscheckin)) {
                    echo 'checked="checked"';
                } ?> /> <?php echo $tl->get('Incoming') ?>
                            </td>
                            <td>
                                <input type="checkbox" name="viruscheckout" value="true" class="checkbox"<?php if (isTrue($mySite->viruscheckout)) {
                    echo 'checked="checked"';
                } ?> /> <?php echo $tl->get('Outgoing') ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Spamcheck') ?></th>
                <td>
                    <table width="200">
                        <tr>
                            <td>
                                <input type="checkbox" name="spamcheckin" value="true" class="checkbox"<?php if (isTrue($mySite->spamcheckin)) {
                    echo 'checked="checked"';
                } ?> /> <?php echo $tl->get('Incoming') ?>
                            </td>
                            <td>
                                <input type="checkbox" name="spamcheckout" value="true" class="checkbox"<?php if (isTrue($mySite->spamcheckout)) {
                    echo 'checked="checked"';
                } ?> /> <?php echo $tl->get('Outgoing') ?>
                            </td>
                        </tr>                                       
                    </table>
                </td>
		<td>&nbsp;</td>
<tr>
<th><?php echo $tl->get('Spam Score Trigger')?></th>
	<td>
	<?php echo $tl->get('Spam Score')?>: <input type="text" id ="spamscore" name="spamscore" value="<?php echo $spamscore ?>" maxlength="3" size="3" class="textfield" />
	<?php echo $tl->get('High Spam Score')?>: <input type="text" id ="highspamscore" name="highspamscore" value="<?php echo $highspamscore ?>" maxlength="3" size="3" class="textfield" />
	</td>
	<td><?php echo $mySite->show_field_property('spamscore')?><?php echo $mySite->show_field_property('highspamscore')?></td>
	    </tr>                            
            <tr>
                <th><?php echo $tl->get('Status') ?></th>
                <td>
                    <table width="410">
                        <tr>
                            <td style="width: 100px;">
                                <input type="radio" name="sitestate" class="radiobutton" value="default"<?php if ($mySite->sitestate == 'default') {
                    echo ' checked="checked"';
                } ?> /> <?php echo $tl->get('Active') ?>
                            </td>
                            <td style="width: 100px;">
                                <input type="radio" name="sitestate" class="radiobutton" value="locked"<?php if ($mySite->sitestate == 'locked') {
                    echo ' checked="checked"';
                } ?> /> <?php echo $tl->get('Locked') ?>
                            </td>
                            <td style="width: 210px;">
                                <input type="radio" name="sitestate" class="radiobutton" value="lockedbounce"<?php if ($mySite->sitestate == 'lockedbounce') {
                    echo ' checked="checked"';
                } ?> /> <?php echo $tl->get('Locked & Bounce') ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php if ($mode == 'update') {
                    ?>
            <tr>
                <th><?php echo $tl->get('Site created') ?></th>
                <td>
                    <input type="text" name="created" value="<?php echo $mySite->added?>" size="<?php echo strlen($mySite->added) ?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Site last updated') ?></th>
                <td>
                    <input type="text" name="updated" value="<?php echo $mySite->updated?>" size="<?php echo strlen($mySite->updated) ?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php 
                } ?>
            <?php include 'show_addressbook.php'; ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p></p>
                    <input type="submit" name="button" value="<?php echo $tl->get("$button") ?>" class="button" />
                    <?php if ($mode == 'new') {
                    ?>
                        <input type="submit" name="button" value=">> <?php echo $tl->get('New User') ?>" class="button" />
                    <?php 
                } else {
                    ?>
                        <input type="submit" name="button" value="<?php echo $tl->get('Delete') ?>" class="button" />
                    <?php 
                } ?>
                    <input type="reset" name="button" value="<?php echo $tl->get('Reset') ?>" class="button" />
                    <input type="button" name="help" value="<?php echo $tl->get('Help') ?>" class="helpbutton" onclick="window.open('help.php?help=site&amp;mode=<?php echo $mode ?>', '', 'scrollbars=yes, height=500, width=920');" />
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
