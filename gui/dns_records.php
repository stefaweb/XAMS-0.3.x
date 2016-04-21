<?php
    require 'gfl.php';
    gfl(_ADMIN);

    include 'include/global.php';
    include 'include/dns.php';
    $myDNS = new DNS();
    $db =& $myDNS->db;
    $tl =& $myDNS->i18n;

    $form = gpost('form');
    $button = gpost('button');
    $dnsid = greq('dnsid');
    $info = gget('info');

    $atype = gpost('atype');
    $aname = gpost('aname');
    $aparameter1 = gpost('aparameter1');
    $aparameter2 = gpost('aparameter2');
    $acomment = gpost('acomment');

    $rid = gpost('rid');
    $rtype = gpost('rtype');
    $rname = gpost('rname');
    $rparameter1 = gpost('rparameter1');
    $rparameter2 = gpost('rparameter2');
    $rcomment = gpost('rcomment');
    $rselected = gpost('rselected');

    if ($button == $tl->get('Back to Zone menu'))
    {
        header('Location: dns_zone.php?mode=update&dnsid='. $dnsid);
        exit;
    }

    if ($dnsid)
    {
        $myDNS->Load($dnsid);
        if (!$myDNS->isAuthLoad())
            die($tl->get('Access denied.'));
    }

    if ($form == 'zone')
    {
        if ($button == $tl->get('Update'))
        {
            if (is_array($rid))
            {
                $sth = $db->prepare('UPDATE pm_dns_records SET name = ?, type = ?, parameter1 = ?, parameter2 = ?, comment = ? WHERE id = ?');
                $sth2 = $db->prepare('DELETE FROM pm_dns_records WHERE id = ?');
                for ($i=0; $i<count($rid); $i++)
                {
                    if (isset($rselected[$i]) && $rselected[$i] == 'true')
                        $db->execute($sth2, array($rid[$i]));
                    else
                        $db->execute($sth, array($rname[$i], $rtype[$i], $rparameter1[$i], $rparameter2[$i], $rcomment[$i], $rid[$i]));
                }
                $myDNS->assign('objhaschanged', true);
                $myDNS->Update();
            }
            $myDNS->Load();
        }
    }
    elseif ($form == 'zone_records')
    {
        $myDNS->Load($dnsid);
        $db->query('INSERT INTO pm_dns_records (dnsid, name, type, parameter1, parameter2, comment) VALUES (?, ?, ?, ?, ?, ?)', array($dnsid, $aname, $atype, $aparameter1, $aparameter2, $acomment));
        $myDNS->assign('objhaschanged', true);
        $myDNS->Update();
        $myDNS->Load();
    }

    include 'header.php';
?>
<h1><?php echo $tl->get('DNS Management'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<?php if ($myDNS->formular_errors) echo '<p class="formerror">'. $tl->get('The formular was not properly filled out. Point at the question mark.'). '</p>'; ?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
    <div class="menu1"></div>
    <div class="menu2">
        <table width="680" class="tbl_global">
            <colgroup>
                <col width="230" />
                <col width="410" />
                <col width="40" />
            </colgroup>
            <tr>
                <th><?php echo $tl->get("Zone") ?></th>
                <td><?php echo $myDNS->name ?></td>
                <td></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Name")?></th>
                <td>
                    <input type="hidden" name="form" value="zone_records" />
                    <input type="hidden" name="dnsid" value="<?php echo $dnsid ?>" />
                    <input type="text" name="aname" maxlength="100" size="50" class="textfield" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Type")?></th>
                <td>
                    <select name="atype">
                        <option></option>
                        <?php
                            foreach (array('A', 'AAAA', 'CNAME', 'HINFO', 'MX', 'NS', 'PTR', 'TXT') as $k)
                            {
                                $sel = ($atype == $k) ? ' selected="selected"' : null;
                                echo "<option$sel>$k</option>";
                            }
                        ?>
                    </select>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Parameter")?> #1</th>
                <td>
                    <input type="text" name="aparameter1" maxlength="100" size="50" class="textfield" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Parameter")?> #2</th>
                <td>
                    <input type="text" name="aparameter2" maxlength="100" size="50" class="textfield" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Comment")?></th>
                <td>
                    <input type="text" name="acomment" maxlength="255" size="50" class="textfield" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" class="button" name="button" value="<?php echo $tl->get("Add") ?> &gt;&gt;" />
                    <input type="submit" class="button" name="button" value="<?php echo $tl->get("Back to Zone menu") ?>" />
                    <input type="button" name="help" value="<?php echo $tl->get("Help")?>" class="helpbutton" onclick="window.open('help.php?help=dns_zone', '', 'scrollbars=yes, height=500, width=920');" />
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>

<table width="700" border="0"><tr><td><hr /></td></tr></table>

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
                    <input type="hidden" name="form" value="zone" />
                    <input type="hidden" name="dnsid" value="<?php echo $dnsid?>" />
                </td>
            </tr>
            <tr>
                <td colspan="3">
<?php if (count($myDNS->records)) { ?>
                    <table width="670">
                        <tr>
                            <th class="addressbook"><?php echo $tl->get("Name")?></th>
                            <th class="addressbook"><?php echo $tl->get("Type")?></th>
                            <th class="addressbook"><?php echo $tl->get("Parameter")?> #1</th>
                            <th class="addressbook"><?php echo $tl->get("Parameter")?> #2</th>
                            <th class="addressbook"><?php echo $tl->get("Comment")?></th>
                            <th class="addressbook" style="color: red; padding-right: 10px;"><?php echo $tl->get("Delete")?></th>
                        </tr>
                        <?php $i = 0;
                        foreach ($myDNS->records as $elem) { ?>
                        <tr>
                            <td class="addressbook">
                                <input type="hidden" name="<?php echo "rid[$i]"?>" value="<?php echo $elem['id']?>" />
                                <input type="text" name="<?php echo "rname[$i]"?>" value="<?php echo $elem['name']?>" size="12" class="textfield" />
                            </td>
                            <td class="addressbook">
                                <select name="<?php echo "rtype[$i]"?>">
                                    <option></option>
                                    <?php
                                        foreach (array('A', 'AAAA', 'CNAME', 'HINFO', 'MX', 'NS', 'PTR', 'TXT') as $k)
                                        {
                                            $sel = ($elem['type'] == $k) ? ' selected="selected"' : null;
                                            echo "<option$sel>$k</option>";
                                        }
                                    ?>
                                </select>
                            </td>
                            <td class="addressbook"><input type="text" name="<?php echo "rparameter1[$i]"?>" value="<?php echo $elem['parameter1']?>" size="14" class="textfield" /></td>
                            <td class="addressbook"><input type="text" name="<?php echo "rparameter2[$i]"?>" value="<?php echo $elem['parameter2']?>" size="14" class="textfield" /></td>
                            <td class="addressbook"><input type="text" name="<?php echo "rcomment[$i]"?>" value="<?php echo $elem['comment']?>" size="14" class="textfield" /></td>
                            <td style="text-align: center;"><input type="checkbox" name="<?php echo "rselected[$i]"?>" value="true" class="checkbox" /></td>
                        </tr>
                        <?php $i++;} ?>
                    </table>
<?php } ?>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <?php
                        if (count($myDNS->records)) {
                    ?>
                    <p><br/></p>
                    <input type="submit" class="button" name="button" value="<?php echo $tl->get("Update") ?>" />
                    <?php
                        } else echo $tl->get("No records for this zone yet. Use the form above to add some.");
                    ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
