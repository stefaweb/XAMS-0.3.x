<?php
    require_once 'gfl.php';
    gfl(_CUSTOMER);

    include_once 'include/global.php';

    include_once 'include/xclass.php';
    $db = new xclass();
    $db->i18n->LoadLngBase('system_overview');
    $tl = &$db->i18n;

    include 'include/xslclass.php';

    $CustomerID = gget('customerid');
    $ResellerID = gget('resellerid');
    $email = gpost('email');
    $mode = gget('mode');
    $site_ids = [];
    $site_id = gget('siteid');
    if ($site_id) {
        array_push($site_ids, [$site_id]);
    }
    $sql_add = null;
    $ResellerName = null;
    $errmsg = null;

    // Check for Sites owned by Customers
    if (!empty($CustomerID)) {
        array_push($site_ids, $db->db->getCol('SELECT siteid FROM pm_sites_c_customers WHERE customerid = ?', 0, [$CustomerID]));
        if (count($site_ids[0]) == 0) {
            $errmsg = $tl->get('There are no sites this customer is responsible for');
        }
    }

    // Generate SQL for Search
    function search_add(&$sql, $source, $target, $integer = false)
    {
        $s = ($integer) ? ' = %d' : " LIKE '%s'";
        if (strlen(gpost($source)) > 0) {
            if (strlen($sql) > 0) {
                $sql .= ' AND ';
            }
            $sql .= sprintf($target.$s, gpost($source));
        }
    }

    // Do we have to search anything?
    if ($mode === 'search') {
        search_add($site_sql, 'sitename', 's.name');
        search_add($site_sql, 'maxquota', 's.maxquota', true);
        search_add($site_sql, 'maxaddr', 's.maxaddr', true);
        search_add($site_sql, 'maxaliases', 's.maxaliases', true);
        search_add($user_sql, 'username', 'u.name');
        search_add($domain_sql, 'domainname', 'd.name');
        search_add($alias_sql, 'leftpart', 'a.leftpart');
        search_add($alias_sql, 'rightpart', 'a.rightpart');

        if (!empty($site_sql)) {
            array_push($site_ids, $db->db->getCol("SELECT id FROM pm_sites s WHERE $site_sql"));
        }
        if (!empty($domain_sql)) {
            array_push($site_ids, $db->db->getCol("SELECT siteid FROM pm_domains d WHERE $domain_sql"));
        }
        if (!empty($user_sql)) {
            array_push($site_ids, $db->db->getCol("SELECT siteid FROM pm_users u WHERE $user_sql"));
        }
        if (strpos($email, '@') > 0) {
            list($localpart, $domainname) = explode('@', $email);
            $sql = 'SELECT      DISTINCT s.id
                    FROM        pm_sites s
                    INNER JOIN  pm_domains d
                    ON          d.siteid = s.id
                    LEFT JOIN   pm_users u
                    ON          u.siteid = s.id
                    LEFT JOIN   pm_aliases a
                    ON          a.siteid = s.id
                    WHERE       d.name = ?
                    AND         (u.name = ? OR a.leftpart = ?)';
            array_push($site_ids, $db->db->getCol($sql, 0, [$domainname, $localpart, $localpart]));
        }

        if (!empty($alias_sql)) {
            array_push($site_ids, $db->db->getCol("SELECT siteid FROM pm_aliases a WHERE $alias_sql"));
        }

        if (!isset($site_ids[0]) || !count($site_ids[0])) {
            $errmsg = $tl->get('Nothing found.');
        }
    }

    if (!$errmsg) {
        if (isset($site_ids[0]) && count($site_ids[0]) > 0) {
            $site_ids_str = implode(', ', $site_ids[0]);
        }

        // Prepare final SQL statements (Specific for Adminis / Resellers / Customers)
        if (isCUSTOMER) {
            if (!empty($site_ids_str)) {
                $sql_add = sprintf('AND s.id IN (%s)', addslashes($site_ids_str));
            }
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
                    WHERE       scc.customerid = '.USERID."
                    $sql_add
                    GROUP BY    id
                    ORDER BY    name";
        } elseif (isRESELLER) {
            if (!empty($site_ids_str)) {
                $sql_add = sprintf('AND s.id IN (%s)', addslashes($site_ids_str));
            }
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
                    AND         r.id = '.USERID."
                    $sql_add
                    GROUP BY    id
                    ORDER BY    name";
        } elseif (isADMIN) {
            // Show only entries for specific Resellers/Customers?
            if (!empty($ResellerID)) {
                $sql_add .= 'AND r.id = '.addslashes($ResellerID);
            } elseif (!empty($site_ids_str)) {
                $sql_add = addslashes(sprintf(' AND s.id IN (%s)', $site_ids_str));
            }
            $sql = "SELECT      r.name resellername, r.id resellerid, COUNT(rsi.value) raddressbook,
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
                    $sql_add
                    GROUP BY    id
                    ORDER BY    resellername, name";
        }
        $sites = $db->db->query($sql);
    } // if (!$errmsg)

    function xmlout($indent, $str)
    {
        for ($i = 1; $i <= $indent; $i++) {
            $GLOBALS['xmldata'] .= "\t";
        }
        $GLOBALS['xmldata'] .= $str;
        $GLOBALS['xmldata'] .= "\n";
    }

    function tagout($indent, $tag, $str)
    {
        for ($i = 1; $i <= $indent; $i++) {
            $GLOBALS['xmldata'] .= "\t";
        }
        $GLOBALS['xmldata'] .= "<$tag>".$str."</$tag>";
        $GLOBALS['xmldata'] .= "\n";
    }

    function entity_vals(&$vals)
    {
        foreach ($vals as $k=>$elem) {
            $vals[$k] = htmlspecialchars($elem, ENT_COMPAT, 'UTF-8');
        }
    }

    $xmldata = '<?xml version="1.0" encoding="utf-8"?>'."\n";

    if (gget('xmloutput') == 1) {
        xmlout(0, '<?xml-stylesheet type="text/xsl" href="'._SKIN.'/xsl/system_overview.xsl"?>');
    }
        //xmlout(0, '<!DOCTYPE system SYSTEM "include/dtd/system_overview.dtd">');
        xmlout(0, sprintf('<system xams-release="%s" file-version="%s" date="%s">', _XAMS_VERSION, '0.0.9', date('Y-m-d')));

        if (gget('xmloutput') == 1) {
            xmlout(1, '<i18nfile language="'.$_SESSION['SESSION_LANGUAGE'].'">http://'.$_SERVER['HTTP_HOST'].'/i18n/'.$_SESSION['SESSION_LANGUAGE'].'/system_overview.xml</i18nfile>');
        } else {
            xmlout(1, '<i18nfile language="'.$_SESSION['SESSION_LANGUAGE'].'">file://'.realpath('i18n/'.$_SESSION['SESSION_LANGUAGE'].'/system_overview.xml').'</i18nfile>');
        }

    tagout(1, 'skindir', _SKIN);

    // Define error var for quota check
    $errorCmdQuota = 0;

    if ($errmsg) {
        tagout(1, 'info', $errmsg);
    } else {
        while ($sr = $sites->fetchRow(DB_FETCHMODE_ASSOC)) {
            entity_vals($sr);
            $sr['addrtype'] = $db->bitfield2string($sr['addrtype'], 'p,i,s,x');

        // Resellers
        if ($ResellerName != $sr['resellername']) { // Other reseller as previous iteration?
            if (!empty($ResellerName)) {
                xmlout(1, '</reseller>');
            }
            $ResellerName = $sr['resellername'];
            $ab = ($sr['raddressbook'] > 0) ? ' addressbook="true"' : null;
            $ms = " maxsites=\"$sr[ms]\"";
            $md = " maxdomains=\"$sr[md]\"";
            $mu = " maxusers=\"$sr[mu]\"";
            $ma = " maxaliases=\"$sr[ma]\"";
            if (isADMIN) {
                xmlout(1, "<reseller id=\"$sr[resellerid]\" name=\"$sr[resellername]\"$ms$md$mu$ma$ab>");
            } else {
                xmlout(1, "<reseller name=\"$sr[resellername]\"$ms$md$mu$ma$ab>");
            }
        }

        // Sites
        $ab = ($sr['saddressbook'] > 0) ? ' addressbook="true"' : null;
            $status = ($sr['sitestate'] != 'default') ? " status=\"$sr[sitestate]\"" : null;
            $mu = ($sr['maxaddr'] < 0) ? ' maxusers="NaN"' : " maxusers=\"$sr[maxaddr]\"";
            $ma = ($sr['maxaliases'] < 0) ? ' maxaliases="NaN"' : " maxaliases=\"$sr[maxaliases]\"";
            $mq = " maxquota=\"$sr[maxquota]\"";
            $muq = " maxuserquota=\"$sr[maxuserquota]\"";
            $type = " addrtype=\"$sr[addrtype]\"";
            $vci = " viruscheckin=\"$sr[viruscheckin]\"";
            $vco = " viruscheckout=\"$sr[viruscheckout]\"";
            $sci = " spamcheckin=\"$sr[spamcheckin]\"";
            $sco = " spamcheckout=\"$sr[spamcheckout]\"";

            xmlout(2, "<site id=\"$sr[id]\" name=\"$sr[name]\"$status$mu$ma$mq$muq$type$vci$vco$sci$sco$ab>");

        // Domains
        $domains = $db->db->query('SELECT d.name name, d.id id, dns.id dnsid FROM pm_domains d LEFT JOIN pm_dns dns ON d.name = dns.name WHERE siteid = ? ORDER BY d.name', $sr['id']);
            if ($domains->numRows() > 0) {
                xmlout(3, '<domains>');
                while ($dr = $domains->fetchRow(DB_FETCHMODE_ASSOC)) {
                    $zone = ($dr['dnsid']) ? " zoneid=\"$dr[dnsid]\"" : null;
                    xmlout(4, "<domain id=\"$dr[id]\"$zone>".htmlspecialchars($dr['name']).'</domain>', ENT_COMPAT, 'UTF-8');
                }
                xmlout(3, '</domains>');
            } else {
                xmlout(3, '<domains/>');
            }

        // Users
        $users = $db->db->query('SELECT id, name, usedquota, quota, addrtype, viruscheckin, viruscheckout, spamcheckin, spamcheckout, spamscore, highspamscore, relayonauth, relayoncheck, autoreply, accountstate, COUNT(value) addressbook FROM pm_users LEFT JOIN pm_user_info pui ON pui.userid = id WHERE siteid = ? GROUP BY name ORDER BY name', $sr['id']);
            if ($users->numRows() > 0) {
                xmlout(3, '<users>');

                while ($ur = $users->fetchRow(DB_FETCHMODE_ASSOC)) {
                    entity_vals($ur);
                    $ur['addrtype'] = $db->bitfield2string($ur['addrtype'], 'p,i,s,x');
                    $ab = ($ur['addressbook'] > 0) ? ' addressbook="true"' : null;
                    $status = ($sr['sitestate'] != $ur['accountstate']) ? " status=\"$ur[accountstate]\"" : null;
                    $rt = " relaytype=\"$ur[relayonauth]\"";
                    $uq = " uquota=\"$ur[usedquota]\"";
                    $q = " quota=\"$ur[quota]\"";
                    $type = " addrtype=\"$ur[addrtype]\"";
                    $vci = " viruscheckin=\"$ur[viruscheckin]\"";
                    $vco = " viruscheckout=\"$ur[viruscheckout]\"";
                    $sci = " spamcheckin=\"$sr[spamcheckin]\"";
                    $sco = " spamcheckout=\"$sr[spamcheckout]\"";
                    $ar = " autoreply=\"$ur[autoreply]\"";
                    xmlout(4, "<user id=\"$ur[id]\" name=\"$ur[name]\"$status$rt$uq$q$type$vci$vco$sci$sco$ar$ab>");
                    xmlout(4, '</user>');
                }
                xmlout(3, '</users>');
            } else {
                xmlout(3, '<users/>');
            }

        // Aliases
        $aliases = $db->db->query('SELECT id, leftpart, rightpart, bounceforward, COUNT(value) addressbook FROM pm_aliases LEFT JOIN pm_alias_info pai ON pai.aliasid = id WHERE siteid = ? GROUP BY leftpart ORDER BY leftpart', $sr['id']);
            if ($aliases->numRows() > 0) {
                xmlout(3, '<aliases>');
                while ($ar = $aliases->fetchRow(DB_FETCHMODE_ASSOC)) {
                    entity_vals($ar);
                    $ab = ($ar['addressbook'] > 0) ? ' addressbook="true"' : null;
                    $bf = ($ar['bounceforward']) ? " bounceforward=\"$ar[bounceforward]\"" : null;
                    xmlout(4, "<alias id=\"$ar[id]\" name=\"$ar[leftpart]\"$bf$ab>");
                    xmlout(5, '<targets>');
                    foreach (explode(',', $ar['rightpart']) as $k) {
                        tagout(6, 'target', $k);
                    }
                    xmlout(5, '</targets>');
                    xmlout(4, '</alias>');
                }
                xmlout(3, '</aliases>');
            } else {
                xmlout(3, '<aliases/>');
            }

            xmlout(2, '</site>');
        }
    }
    if (!empty($ResellerName)) {
        xmlout(1, '</reseller>');
    }
    xmlout(0, '</system>');

    if (gget('xmloutput') == 1) {
        header('Content-Type: text/xml; charset=UTF-8');
        header('Content-Disposition: attachment; filename=xams-export.xml');
        echo $xmldata;
    } else {
        // header('Content-Type: text/html; charset=UTF-8');
        $myXSL = &xslclass::singleton();
        $myXSL->load_xsl(_SKIN.'/xsl/system_overview.xsl');
        $trans = [
            'patterns'     => ['/XHTML Output/', '/XML Output/'],
            'replacements' => [$tl->get('XHTML Output'), $tl->get('XML Output')],
        ];
        $myXSL->xsl_replace($trans);
        $myXSL->set_xml_data($xmldata);
        $myXSL->out();
    }
