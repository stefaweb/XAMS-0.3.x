<?php
    require_once 'gfl.php';

   include_once 'include/global.php';
   include_once 'include/xclass.php';
   include_once 'include/preferences.php';
   $db = new xclass();
   $db->i18n->LoadLngBase('startup');
   $tl = &$db->i18n;

    $myPREFS = new Preferences();
    $myPREFS->Load(false);
    $data = null;
    // Already checked today?
    function checked_today()
    {
        global $myPREFS;
        if ($myPREFS->lastversioncheck == date('Y-m-d')) {
            $ret = true;
        } else {
            $myPREFS->assign('lastversioncheck', date('Y-m-d'));
            $myPREFS->Update();
            $ret = false;
        }

        return $ret;
    }

    if (isADMIN &&
        !preg_match('/pre/i', _XAMS_VERSION) &&
        isTrue($myPREFS->newversioncheck) &&
        !checked_today()) {
        $current_version = rfile(_XAMS_ONLINE_SERVER.'/VERSION');
        if ($current_version && version_compare($current_version, _XAMS_VERSION) == 1) {
            $data = rfile(_XAMS_ONLINE_SERVER.'/version_announcement.php?lng='.$_SESSION['SESSION_LANGUAGE']);
            echo preg_replace('/LOCAL_VERSION/s', _XAMS_VERSION, $data);
            include 'footer.php';
            exit;
        }
    }

    $sql = null;
    if (isCUSTOMER) {
        $sql = 'SELECT      r.name resellername, r.id resellerid, NULL raddressbook,
                            r.maxsites ms, r.maxdomains md, r.maxusers mu,
                            r.maxaliases ma, s.id, s.name, s.sitestate, s.maxaddr, s.maxaliases,
                            s.maxquota, s.maxuserquota, s.addrtype, s.viruscheckin, s.viruscheckout,
                            s.spamcheckin, s.spamcheckout,
                            COUNT(psi.value) saddressbook
                FROM        pm_resellers r
                INNER JOIN  pm_sites s
                ON          r.id = s.resellerid
                LEFT JOIN   pm_site_info psi
                ON          psi.siteid = s.id
                LEFT JOIN   pm_sites_c_customers scc
                ON          scc.siteid = s.id
                WHERE       scc.customerid = '.USERID.'
                GROUP BY    id
                ORDER BY    name';
    } elseif (isRESELLER) {
        $sql = 'SELECT      r.name resellername, r.id resellerid, COUNT(rsi.value) raddressbook,
                            r.maxsites ms, r.maxdomains md, r.maxusers mu,
                            r.maxaliases ma, s.id, s.name, s.sitestate, s.maxaddr, s.maxaliases,
                            s.maxquota, s.maxuserquota, s.addrtype, s.viruscheckin, s.viruscheckout,
                            s.spamcheckin, s.spamcheckout,
                            COUNT(psi.value) saddressbook
                FROM        pm_resellers r
                INNER JOIN  pm_sites s
                ON          r.id = s.resellerid
                LEFT JOIN   pm_site_info psi
                ON          psi.siteid = s.id
                LEFT JOIN   pm_reseller_info rsi
                ON          rsi.resellerid = r.id
                WHERE       s.resellerid = r.id
                AND         r.id = '.USERID.'
                GROUP BY    id
                ORDER BY    name';
    } elseif (isADMIN) {
        $sql = 'SELECT      r.name resellername, r.id resellerid, COUNT(rsi.value) raddressbook,
                            r.maxsites ms, r.maxdomains md, r.maxusers mu,
                            r.maxaliases ma, s.id, s.name, s.sitestate, s.maxaddr, s.maxaliases,
                            s.maxquota, s.maxuserquota, s.addrtype, s.viruscheckin, s.viruscheckout,
                            s.spamcheckin, s.spamcheckout,
                            COUNT(psi.value) saddressbook
                FROM        pm_resellers r
                INNER JOIN  pm_sites s
                ON          r.id = s.resellerid
                LEFT JOIN   pm_site_info psi
                ON          psi.siteid = s.id
                LEFT JOIN   pm_reseller_info rsi
                ON          rsi.resellerid = r.id
                WHERE       s.resellerid = r.id
                GROUP BY    id
                ORDER BY    resellername, name';
    }
    if ($sql) {
        $sites = $db->db->query($sql);
    }

    include 'header.php';
?>
    <h1><?php echo $tl->get('System Overview'); ?></h1>
    <table width="680" id="sysoverview_sitelist">
    <thead>
        <tr>
            <th><h4><?php echo $tl->get('Reseller'); ?></h4></th>
            <th><h4><?php echo $tl->get('Site'); ?></h4></th>
            <th style="text-align: center; colspan="2" colspan="2"><h4><?php echo $tl->get('Actions'); ?></h4></th>
        </tr>
    </thead>
    <tfoot></tfoot>
    <tbody>
    <?php
    if ($sql) {
        while ($sr = $sites->fetchRow(DB_FETCHMODE_ASSOC)) {
            printf('<tr>
        <td>%s</td>
        <td><a href="system_overview.php?siteid=%s">%s</a></td>', $sr['resellername'], $sr['id'], $sr['name']);
            printf('<td><a href="user.php?mode=new&siteid=%s">[%s]</a></td>', $sr['id'], $tl->get('Add User'));
            printf('<td><a href="alias.php?mode=new&siteid=%s">[%s]</a></td>', $sr['id'], $tl->get('Add Alias'));
        }
    }
    ?>
    </tbody>
    </table>
    <table width="680" class="tbl_global"><tr><th></th></tr></table>
    <br>
    <p>
    <a href="system_overview.php"><?php echo $tl->get('Show all Sites'); ?></a>
    </p>
<?php
    include 'footer.php';
?>
