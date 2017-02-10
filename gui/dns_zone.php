<?php
    require 'gfl.php';
    gfl(_ADMIN);

    include 'include/global.php';
    include 'include/dns.php';
    $myDNS = new DNS();
    $db = &$myDNS->db;
    $tl = &$myDNS->i18n;

    $button = gpost('button');
    $dnsid = greq('dnsid');
    $mode = greq('mode');
    $info = gget('info');

    if ($dnsid) {
        $myDNS->Load($dnsid);
        if (!$myDNS->isAuthLoad()) {
            die($tl->get('Access denied.'));
        }
    }

    if ($button) {
        if ($button == $tl->get('Delete')) {
            $myDNS->Delete();
            header('Location: dns.php?info='.urlencode($myDNS->notice));
            exit;
        }

        $myDNS->Assign2Object(['name', 'zonetype', 'masterdns',
        'zoneadmin', 'serial', 'serialautomatic', 'ttl', 'ttl_unit', 'refresh', 'refresh_unit',
        'retry', 'retry_unit', 'expire', 'expire_unit', 'nttl', 'nttl_unit', ]);

        $myDNS->check_formular($mode);

        if (!$myDNS->formular_errors) {
            switch ($button) {
                case $tl->get('Save'):
                    $myDNS->Add();
                    $dnsid = $myDNS->id;
                    $mode = 'update';
                    break;
                case $tl->get('Update'):
                    $myDNS->Update();
                    header('Location: dns.php?info='.urlencode($myDNS->notice));
                    exit;
            }
            $myDNS->Load();
        }
    }

    $button = ($mode == 'new') ? 'Save' : 'Update';

    $time_array = [$tl->get('seconds'), $tl->get('minutes'), $tl->get('hours'), $tl->get('days'), $tl->get('weeks'), $tl->get('months')];

    include 'header.php';
?>
<h1><?php echo $tl->get('DNS Management'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<?php if ($myDNS->formular_errors) {
    echo '<p class="formerror"><img src="'._SKIN.'/img/critical.png" alt="Error" height="25" width="25" />'.$tl->get('The formular was not properly filled out. Point at the question mark.').'</p>';
} ?>
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
                    <input type="hidden" name="dnsid" value="<?php echo $dnsid?>" />
                    <input type="hidden" name="mode" value="<?php echo $mode?>" />
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Zone Type')?></th>
                <td>
                    <select name="zonetype">
                        <?php
                            foreach (['Master', 'Slave', 'Dummy'] as $zone_type) {
                                $sel = (strtolower($zone_type[0]) == $myDNS->zonetype) ? ' selected="selected"' : null;
                                echo '<option value="'.strtolower($zone_type[0])."\"$sel>$zone_type</option>";
                            }
                        ?>
                    </select>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Zone')?></th>
                <td><input type="text" name="name" value="<?php echo $myDNS->name?>" size="30" maxlength="50" class="textfield" /></td>
                <td><?php echo $myDNS->show_field_property('name')?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Primary DNS')?></th>
                <td><input type="text" name="masterdns" value="<?php echo $myDNS->masterdns?>" size="30" maxlength="50" class="textfield" /></td>
                <td><?php echo $myDNS->show_field_property('masterdns')?></td>
            </tr>
            <tr> 
                <th><?php echo $tl->get('Zone Admin (eMail)')?></th>
                <td><input type="text" name="zoneadmin" value="<?php echo $myDNS->zoneadmin?>" size="30" maxlength="50" class="textfield" /></td>
                <td><?php echo $myDNS->show_field_property('zoneadmin')?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Serial')?></th>
                <td>
                    <input type="text" name="serial" value="<?php echo $myDNS->serial?>" size="10" maxlength="10" class="textfield" />
                    <input type="checkbox" name="serialautomatic" value="true" class="checkbox"<?php if (isTrue($myDNS->serialautomatic)) {
                            echo ' checked="checked"';
                        } ?> /> <?php echo $tl->get('Automatic')?>
                </td>
                <td><?php echo $myDNS->show_field_property('serial')?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Time to Live (TTL)')?></th>
                <td>
                    <input type="text" name="ttl" value="<?php echo $myDNS->ttl?>" size="10" maxlength="10" class="textfield" />
                    <select name="ttl_unit">
                        <?php
                            $i = 0;
                            foreach ($time_array as $time_unit) {
                                $sel = ($time_unit == $time_array[$myDNS->ttl_unit]) ? ' selected="selected"' : null;
                                echo '<option value="'.$i++."\"$sel>$time_unit</option>";
                            }
                        ?>
                    </select>
                </td>
                <td><?php echo $myDNS->show_field_property('ttl')?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Refresh')?></th>
                <td>
                    <input type="text" name="refresh" value="<?php echo $myDNS->refresh?>" size="10" maxlength="10" class="textfield" />
                    <select name="refresh_unit">
                        <?php
                            $i = 0;
                            foreach ($time_array as $time_unit) {
                                $sel = ($time_unit == $time_array[$myDNS->refresh_unit]) ? ' selected="selected"' : null;
                                echo '<option value="'.$i++."\"$sel>$time_unit</option>";
                            }
                        ?>
                    </select>
                </td>
                <td><?php echo $myDNS->show_field_property('refresh')?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Retry')?></th>
                <td>
                    <input type="text" name="retry" value="<?php echo $myDNS->retry?>" size="10" maxlength="10" class="textfield" />
                    <select name="retry_unit">
                        <?php
                            $i = 0;
                            foreach ($time_array as $time_unit) {
                                $sel = ($time_unit == $time_array[$myDNS->retry_unit]) ? ' selected="selected"' : null;
                                echo '<option value="'.$i++."\"$sel>$time_unit</option>";
                            }
                        ?>
                    </select>
                </td>
                <td><?php echo $myDNS->show_field_property('retry')?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Expire')?></th>
                <td>
                    <input type="text" name="expire" value="<?php echo $myDNS->expire?>" size="10" maxlength="10" class="textfield" />
                    <select name="expire_unit">
                        <?php
                            $i = 0;
                            foreach ($time_array as $time_unit) {
                                $sel = ($time_unit == $time_array[$myDNS->expire_unit]) ? ' selected="selected"' : null;
                                echo '<option value="'.$i++."\"$sel>$time_unit</option>";
                            }
                        ?>
                    </select>
                </td>
                <td><?php echo $myDNS->show_field_property('expire')?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Negative TTL')?></th>
                <td>
                    <input type="text" name="nttl" value="<?php echo $myDNS->nttl?>" size="10" maxlength="10" class="textfield" />
                    <select name="nttl_unit">
                        <?php
                            $i = 0;
                            foreach ($time_array as $time_unit) {
                                $sel = ($time_unit == $time_array[$myDNS->nttl_unit]) ? ' selected="selected"' : null;
                                echo '<option value="'.$i++."\"$sel>$time_unit</option>";
                            }
                        ?>
                    </select>
                </td>
                <td><?php echo $myDNS->show_field_property('nttl')?></td>
            </tr>
<?php if ($mode == 'update') {
                            ?>
            <tr>
                <th><?php echo $tl->get('Zone records') ?></th>
                <td><?php echo count($myDNS->records) ?>&nbsp;&nbsp;&nbsp;<a href="dns_records.php?dnsid=<?php echo $myDNS->id ?>" class="dns_link"><font color=#2370B8><?php echo $tl->get('Edit') ?></font></a></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Zone created')?></th>
                <td>
                    <input type="text" name="created" value="<?php echo $myDNS->added?>" size="<?php echo strlen($myDNS->added)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Zone last updated')?></th>
                <td>
                    <input type="text" name="updated" value="<?php echo $myDNS->updated?>" size="<?php echo strlen($myDNS->updated)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
<?php 
                        } ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" class="button" name="button" value="<?php echo $tl->get($button)?>" />
                    <?php if ($button == 'Update') {
                            ?>
                    <input type="submit" name="button" value="<?php echo $tl->get('Delete')?>" class="button" />
                    <?php 
                        } ?>
                    <input type="reset" class="button" name="button" value="<?php echo $tl->get('Reset')?>" />
                    <input type="button" name="help" value="<?php echo $tl->get('Help')?>" class="helpbutton" onclick="window.open('help.php?help=dns_zone', '', 'scrollbars=yes, height=500, width=920');" />    
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
