<?php
    require 'gfl.php';
    gfl(_ADMIN);
    include 'include/global.php';

    include 'include/xclass.php';
    $db = new xclass();
    $tl = &$db->i18n;
    $tl->LoadLngBase('db_analyse');

    if (gget('check') == '1') {
        $res_sites = $db->db->query('SELECT s.id siteid, s.name sitename FROM pm_sites s LEFT JOIN pm_resellers r ON s.resellerid = r.id WHERE r.name IS NULL');
        $res_domains = $db->db->query('SELECT d.siteid, d.name domainname FROM pm_domains d LEFT JOIN pm_sites s ON d.siteid = s.id WHERE s.name IS NULL ORDER BY domainname');
        $res_users = $db->db->query('SELECT u.siteid, u.id userid, u.name username FROM pm_users u LEFT JOIN pm_sites s ON u.siteid = s.id WHERE s.name IS NULL ORDER BY username');
        $res_aliases = $db->db->query('SELECT a.siteid, a.leftpart FROM pm_aliases a LEFT JOIN pm_sites s ON a.siteid = s.id WHERE s.name IS NULL ORDER BY a.leftpart');

        $res_maxquota0 = $db->db->query('SELECT id siteid, name FROM pm_sites WHERE maxquota = 0 OR maxquota IS NULL');
        $res_maxaddr0 = $db->db->query('SELECT id siteid, name FROM pm_sites WHERE maxaddr = 0 OR maxaddr IS NULL');
        $res_maxaliases0 = $db->db->query('SELECT id siteid, name FROM pm_sites WHERE maxaliases = 0 OR maxaliases IS NULL');

        $res_maxquota = $db->db->query('SELECT u.id userid, u.name username, s.name sitename FROM pm_users u INNER JOIN pm_sites s ON u.siteid = s.id WHERE u.quota > s.maxquota AND s.maxquota > 0 ORDER BY username');
        $res_maxaliases = $db->db->query('SELECT s.id siteid, s.name sitename, s.maxaliases maxaliases, COUNT(a.leftpart) numaliases FROM pm_sites s LEFT JOIN pm_aliases a ON s.id = a.siteid WHERE maxaliases > 0 GROUP BY sitename ORDER BY sitename');
        $res_maxaddr = $db->db->query('SELECT s.id siteid, s.name sitename, s.maxaddr maxaddr, COUNT(u.name) numusers FROM pm_sites s LEFT JOIN pm_users u ON s.id = u.siteid WHERE maxaliases > 0 GROUP BY sitename ORDER BY sitename');
    } else {
        $admins = $db->db->getRow('SELECT COUNT(*) FROM pm_admins');
        $resellers = $db->db->getRow('SELECT COUNT(*) FROM pm_resellers');
        $customers = $db->db->getRow('SELECT COUNT(*) FROM pm_customers');
        $sites = $db->db->getRow('SELECT COUNT(*) FROM pm_sites');
        $domains = $db->db->getRow('SELECT COUNT(*) FROM pm_domains');
        $zones = $db->db->getRow('SELECT COUNT(*) FROM pm_dns');
        $users = $db->db->getRow('SELECT COUNT(*) FROM pm_users');
        $aliases = $db->db->getRow('SELECT COUNT(*) FROM pm_aliases');
        $logs = $db->db->getRow('SELECT COUNT(*) FROM pm_log');
    }

    $errors = 0;
    $warnings = 0;

    include 'header.php';
?>
<h1><?php echo $tl->get('Database Analyse'); ?></h1>
<?php if (gget('check') != '1') {
    ?>
<table width="680" class="tbl_global">
    <colgroup>
        <col width="174" />
        <col width="519" />
    </colgroup>
    <tr>
        <th><?php echo $tl->get('Administrators')?></th>
        <td><?php echo $admins[0]?></td>
    </tr>
    <tr>
        <th><?php echo $tl->get('Resellers')?></th>
        <td><?php echo $resellers[0]?></td>
    </tr>
    <tr>
        <th><?php echo $tl->get('Customers')?></th>
        <td><?php echo $customers[0]?></td>
    </tr>
    <tr>
        <th><?php echo $tl->get('Sites')?></th>
        <td><?php echo $sites[0]?></td>
    </tr>
    <tr>
        <th><?php echo $tl->get('Domains')?></th>
        <td><?php echo $domains[0]?></td>
    </tr>
    <tr>
        <th><?php echo $tl->get('Zones')?></th>
        <td><?php echo $zones[0]?></td>
    </tr>
    <tr>
        <th><?php echo $tl->get('Users')?></th>
        <td><?php echo $users[0]?></td>
    </tr>
    <tr>
        <th><?php echo $tl->get('Aliases')?></th>
        <td><?php echo $aliases[0]?></td>
    </tr>
    <tr>
        <th><?php echo $tl->get('Log Entries')?></th>
        <td><?php echo $logs[0]?></td>
    </tr>
</table>
<p><a href="<?php echo $_SERVER['PHP_SELF']?>?check=1"><?php echo $tl->get('Check database consistency')?></a></p>
<p>&nbsp;</p>
<?php 
} ?>
<?php if (gget('check') == '1') {
    ?>
<table width="680" class="tbl_global">
    <colgroup>
        <col width="650" />
        <col width="100" />
    </colgroup>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Sites without Reseller')?></th>
        <td class="align_result"><?php if ($res_sites->numRows() == 0) {
        echo 'OK';
    } else {
        while ($row = $res_sites->fetchRow(DB_FETCHMODE_ASSOC)) {
            echo "$row[sitename]<br />";
            $errors++;
        }
    } ?></td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Domains without Sites')?></th>
        <td class="align_result"><?php if ($res_domains->numRows() == 0) {
        echo 'OK';
    } else {
        while ($row = $res_domains->fetchRow(DB_FETCHMODE_ASSOC)) {
            echo "$row[domainname]<br />";
            $errors++;
        }
    } ?></td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Users without Sites')?></th>
        <td class="align_result"><?php if ($res_users->numRows() == 0) {
        echo 'OK';
    } else {
        while ($row = $res_users->fetchRow(DB_FETCHMODE_ASSOC)) {
            echo "$row[username]<br />";
            $errors++;
        }
    } ?></td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Aliases without Sites')?></th>
        <td class="align_result"><?php if ($res_aliases->numRows() == 0) {
        echo 'OK';
    } else {
        while ($row = $res_aliases->fetchRow(DB_FETCHMODE_ASSOC)) {
            echo "$row[leftpart]<br />";
            $errors++;
        }
    } ?></td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Users having higher Quota than valid')?></th>
        <td class="align_result"><?php if ($res_maxquota->numRows() == 0) {
        echo 'OK';
    } else {
        while ($row = $res_maxquota->fetchRow(DB_FETCHMODE_ASSOC)) {
            echo "$row[username]@$row[sitename]<br />";
            $errors++;
        }
    } ?></td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Sites which MaxQuota is not set')?></th>
        <td class="align_result"><?php if ($res_maxquota0->numRows() == 0) {
        echo 'OK';
    } else {
        while ($row = $res_maxquota0->fetchRow(DB_FETCHMODE_ASSOC)) {
            echo "$row[name]<br />";
            $warnings++;
        }
    } ?></td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Sites which MaxUsers is not set')?></th>
        <td class="align_result"><?php if ($res_maxaddr0->numRows() == 0) {
        echo 'OK';
    } else {
        while ($row = $res_maxaddr0->fetchRow(DB_FETCHMODE_ASSOC)) {
            echo "$row[name]<br />";
            $warnings++;
        }
    } ?></td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Sites which MaxAliases is not set')?></th>
        <td class="align_result"><?php if ($res_maxaliases0->numRows() == 0) {
        echo 'OK';
    } else {
        while ($row = $res_maxaliases0->fetchRow(DB_FETCHMODE_ASSOC)) {
            echo "$row[name]<br />";
            $warnings++;
        }
    } ?></td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Sites containing more aliases than valid')?></th>
        <td class="align_result">
            <?php
                $start_errors = $errors;
    while ($row = $res_maxaliases->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['numaliases'] > $row['maxaliases']) {
            echo "$row[sitename]<br />";
            $errors++;
        }
    }
    if ($start_errors == $errors) {
        echo 'OK';
    } ?>
        </td>
    </tr>
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr valign="top">
        <th class="align_head"><?php echo $tl->get('Checking for Sites containing more users than valid')?></th>
        <td class="align_result">
            <?php
                $start_errors = $errors;
    while ($row = $res_maxaddr->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['numusers'] > $row['maxaddr']) {
            echo "$row[sitename]<br />";
            $errors++;
        }
    }
    if ($start_errors == $errors) {
        echo 'OK';
    } ?>
        </td>
    </tr>
</table>
<br /><br />
<?php
    if ($errors > 0) {
        echo '<p style="color: white;">'.sprintf($tl->get('The XAMS-Database is not fully consistent (%s errors)!'), $errors).'</p>';
    }
    if ($warnings > 0) {
        echo '<p style="color: white;">'.sprintf($tl->get('You should think about limiting MaxQuota/MaxUsers/MaxAliases on Site-Level (%s warnings).'), $warnings).'</p>';
    }

    if ($errors == 0 && $warnings == 0) {
        echo '<p style="color: white;">'.$tl->get('The XAMS-Database seems to be OK.').'</p>';
    } ?>
<?php 
} ?>
<?php include 'footer.php' ?>
