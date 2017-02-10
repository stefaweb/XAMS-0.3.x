<?php
    require 'gfl.php';
    gfl(_ADMIN);
    include 'include/global.php';
    include 'include/preferences.php';
    include 'include/xslclass.php';

    $this_day = null;
    $MEPP = gpost('MEPP');
    $filter = gpost('filter');
    $delete = gpost('delete');
    $date = gpost('date');
    $del_date = gpost('del_date');
    $usertype = gpost('usertype');
    $username = gpost('username');
    $resource = gpost('resource');
    $type = gpost('type');
    $status = gpost('status');
    $message = gpost('message');
    $button = gpost('button');
    $start_pos = gpost('start_pos');

    $page = gpost('page');
    $current_page = gpost('current_page');

    $myPREFS = new Preferences();
    $myPREFS->Load(false);
    $tl = &$myPREFS->i18n;
    $db = &$myPREFS->db;
    $tl->LoadLngBase('log_overview');

    // Prepare i18n-date-stuff
    switch ($tl->get('Date Format')) {
        case 'JJ/MM/AAAA':
            $overview_date = '%d/%m/%Y';
            $filter_date = '%d/%m/%Y';
            $date_str = '%02s/%02s/%04s';
            $seperator = '/';
            break;
        case 'DD/MM/YYYY':
            $overview_date = '%d.%m.%Y';
            $filter_date = '%d.%m.%Y';
            $date_str = '%02s.%02s.%04s';
            $seperator = '.';
            break;
        default:
        case 'MM/DD/YYYY':
            $overview_date = '%m/%d/%Y';
            $filter_date = '%m/%d/%Y';
            $date_str = '%02s/%02s/%04s';
            $seperator = '/';
    }

    if (!$MEPP) {
        $MEPP = $myPREFS->loglines;
    } // Max. entries per page

    $sql = "SELECT    a.id,
                      msgtype,
                      msgstatus,
                      resource,
                      DATE_FORMAT(TIMESTAMP, '$overview_date') time_d,
                      DATE_FORMAT(TIMESTAMP, '%H:%i') time_t,
                      DATE_FORMAT(TIMESTAMP, '%e%m%Y') day,
                      SUBSTRING(message, 1, 45) message,
                      name
            FROM      pm_log a
            LEFT JOIN pm_log_message b
            ON        b.logid = a.id
            LEFT JOIN pm_logs_c_admins lca
            ON        lca.logid = a.id
            LEFT JOIN pm_logs_c_resellers lcr
            ON        lcr.logid = a.id
            LEFT JOIN pm_logs_c_customers lcc
            ON        lcc.logid = a.id
            LEFT JOIN pm_logs_c_users lcu
            ON        lcu.logid = a.id
            WHERE     1 = 1
            "; // <- otherwise I can't simply add " AND ..." lines for filtering
    if ($delete != null) {
        list($date_m, $date_d, $date_y) = explode($seperator, $del_date);
        $iso_date = sprintf('%02s-%02s-%04s', $date_y, $date_m, $date_d);
        // delete log entries first
        $del_sql = "DELETE FROM pm_log WHERE TIMESTAMP < '$iso_date'";
        $result = $db->query($del_sql);
        // clean up orphaned log stuff
        foreach (['pm_log_message', 'pm_logs_c_admins', 'pm_logs_c_resellers', 'pm_logs_c_customers', 'pm_logs_c_users']
            as $tbl_name) {
            $del_sql = "DELETE $tbl_name FROM $tbl_name LEFT JOIN pm_log ON pm_log.ID = $tbl_name.LogID WHERE pm_log.ID IS NULL";
            $result = $db->query($del_sql);
        }
    }
    if ($filter != null) {
        if ($filter == $tl->get('Reset')) {
            $date = $usertype = $username = $message = $resource = $type = $status = null;
        }

        // Add filter-options
        if ($date) {
            list($date_m, $date_d, $date_y) = explode($seperator, $date);
            $date2 = sprintf($date_str, $date_m, $date_d, $date_y);
            $sql .= " AND (DATE_FORMAT(TIMESTAMP, '$filter_date') = '$date2')";
        }

        if (isRESELLER) {
            $sql .= ' AND rid = '.USERID;
            $usertype = false;
        }

        if ($usertype) {
            $sql .= " AND $usertype > 0";
        }
        if ($username) {
            $sql .= " AND (name LIKE '$username')";
        }
        if ($resource) {
            $sql .= " AND resource = '$resource'";
        }
        if ($type) {
            $sql .= " AND msgtype = '$type'";
        }
        if ($status) {
            $sql .= " AND msgstatus = '$status'";
        }
        if ($message) {
            $sql .= " AND message LIKE '%$message%'";
        }
    }
    // Replace SQL-Parts for counting result
    $sql_count = preg_replace('/^.*FROM/s', 'SELECT COUNT(*) FROM', $sql);

    $entries = $db->getOne($sql_count);
    $pages = ceil($entries / $MEPP);

    // Change page?
    if ($button) {
        //session_register("current_page");
        if ($page - 1 != $current_page) {
            $start_pos = $MEPP * ($current_page = $page - 1);
        }
        switch ($button) {
            case '>': $start_pos = $MEPP * (++$current_page); break;
            case '>|': $start_pos = $MEPP * ($current_page = $pages - 1); break;
            case '<': $start_pos = $MEPP * (--$current_page); break;
            case '|<': $start_pos = $current_page = 0; break;
            default: $start_pos = ($MEPP * ($current_page = $page - 1));
        }
    } else {
        $current_page = 0;
    }

    if (!isset($start_pos)) {
        $start_pos = 0;
    }

    $sql .= "\nORDER BY TIMESTAMP DESC
             LIMIT $start_pos,$MEPP";

    $result = $db->query($sql);
    $UserTypes_colors = ['adminid' => null, 'resellerid' => '#FADCA7', 'customerid' => '#DADCA7', 'userid' => '#C1E1D7'];

    include 'header.php';
?>
<h1><?php echo $tl->get('Log Overview'); ?></h1>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
    <div class="menu1n"></div>
    <div class="menu2n">
        <table width="680" style="padding: 10px;">
            <colgroup>
                <col width="158" />
                <col width="133" />
                <col width="97" />
                <col width="139" />
                <col width="171" />
            </colgroup>
            <tr style="height: 0px;">
				<td colspan="5">
                    <input type="hidden" name="current_page" value="<?php echo $current_page?>" />
                </td>
			</tr>
            <tr>
                <th><?php echo $tl->get('Date (MM/DD/YYYY)')?></th>
                <td>
                    <input type="text" name="date" value="<?php echo $date?>" class="textfield" size="14" maxlength="10" />
                </td>
                <th><?php echo $tl->get('Resource')?></th>
                <td>
                    <select name="resource">
                        <?php foreach (['', 'XAMS', 'SMTP', 'POP', 'IMAP', 'PAM', 'FTP'] as $elem) {
    ?>
                        <option<?php if ($resource == $elem) {
        echo ' selected="selected"';
    } ?>><?php echo $elem?></option>
                        <?php 
} ?>
                    </select>
                </td>
		<td>
                    <input type="submit" name="filter" value="<?php echo $tl->get('Search')?>" class="button" />
                </td>
			</tr>
            <tr>
                <th><?php echo $tl->get('UserType')?></th>
                <td>
                    <select name="usertype">
                        <option />
                        <option value="adminid"<?php if ($usertype == 'adminid') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('Admin')?></option>
                        <option value="resellerid"<?php if ($usertype == 'resellerid') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('Reseller')?></option>
                        <option value="customerid"<?php if ($usertype == 'customerid') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('Customer')?></option>
                        <option value="userid"<?php if ($usertype == 'userid') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('User')?></option>
                    </select>
                </td>
                <th><?php echo $tl->get('Type')?></th>
                <td>
                    <select name="type">
                        <option />
                        <option value="Login"<?php if ($type == 'Login') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('Login')?></option>
                        <option value="Selection"<?php if ($type == 'Selection') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('Selection')?></option>
                        <option value="Update"<?php if ($type == 'Update') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('Update')?></option>
                        <option value="Insertion"<?php if ($type == 'Insertion') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('Insertion')?></option>
                        <option value="Deletion"<?php if ($type == 'Deletion') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('Deletion')?></option>
                    </select>
                </td>
				<td>
                    <input type="submit" name="filter" value="<?php echo $tl->get('Reset')?>" class="button" />
                </td>
			</tr>
            <tr>
                <th><?php echo $tl->get('Username')?></th>
                <td>
                    <input type="text" name="username" value="<?php echo $username?>" class="textfield" size="14" maxlength="255" />
                </td>
                <th><?php echo $tl->get('Status')?></th>
                <td>
                    <select name="status">
                        <option />
                        <option value="ok"<?php if ($status == 'ok') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('OK')?></option>
                        <option value="failed"<?php if ($status == 'failed') {
    echo ' selected="selected"';
}?>><?php echo $tl->get('FAILED')?></option>
                    </select>
                </td>
				<td>
                    <input type="button" name="help" value="<?php echo $tl->get('Help')?>" class="helpbutton" onclick="window.open('help.php?help=log_overview', '', 'scrollbars=yes, height=500, width=920');" />
                </td>
			</tr>
            <tr>
                <th><?php echo $tl->get('Event')?></th>
                <td colspan="4">
                    <input type="text" name="message" value="<?php echo $message?>" size="40" class="textfield" />
                </td>
            </tr>
        </table>
    </div>
    <div class="menu1n"></div>
    <div style="width: 710px"><hr /></div>
    <div class="menu2n">
	<table width="680" style="padding: 10px;">
            <colgroup>
                <col width="86'" />
                <col width="134" />
                <col width="180" />
            </colgroup>
            <tr style="height: 0px;">
			<th colspan="3"><?php echo $tl->get('Delete Log-Entries older than'); ?></th>
		</tr>
            <tr>
                <th><?php echo $tl->get('Date (MM/DD/YYYY)')?></th>
			<td>
                    <input type="text" name="del_date" value="<?php echo $del_date?>" class="textfield" size="14" maxlength="10" />
                </td>
			<td><input type="submit" name="delete" value="<?php echo $tl->get('Delete')?>" class="button" /></td>
		</tr>
        </table>
    </div>
    <div class="menu3n"></div>
    <div style="width: 710px"><hr /></div>
<?php if ($result->numRows()) {
    ?>
    <table width="710" cellspacing="0" style="padding: 10px;">
        <tr>
            <td style="width: 490px;">
                <table width="490" cellspacing="2" style="background-color: #D5EDFF; border: 2px; border-color:#CCCCCC; border-style: solid; border-spacing: 2px;">
                    <tr>
                       <td><?php echo sprintf($tl->get('Selection contains %d entries'), $entries) ?><br>
                           <?php echo sprintf($tl->get('Showing maximum %d entries per page'), $MEPP) ?><br>
                           <?php echo sprintf($tl->get('Page: %d / %d'), $current_page + 1, $pages) ?>
                       </td>
                    </tr>
                </table>
            </td>
            <td align="right">
                <?php echo $tl->get('Entries per page')?> <input type="text" name="MEPP" value="<?php echo $MEPP?>" size="3" class="textfield" />
            </td>
        </tr>
    </table>
    <div style="width: 710px"><hr /></div>
    <p>
        <input type="submit" name="button" value="|&lt;" class="button"<?php if ($current_page == 0) {
        echo ' disabled="disabled"';
    } ?> />
        <input type="submit" name="button" value="&lt;" class="button"<?php if ($current_page == 0) {
        echo ' disabled="disabled"';
    } ?> />
        <input type="submit" name="button" value="&gt;" class="button"<?php if ($current_page + 1 == $pages) {
        echo ' disabled="disabled"';
    } ?> />
        <input type="submit" name="button" value="&gt;|" class="button"<?php if ($current_page + 1 == $pages) {
        echo ' disabled="disabled"';
    } ?> />
        <input type="text" name="page" value="<?php echo $current_page + 1?>" size="2" class="textfield" />
        <input type="submit" name="button" value="<?php echo $tl->get('Refresh')?>" class="button" />
    </p>
    <table class="lienbleu" width="710" cellspacing="0">
        <colgroup>
            <col width="20" />
            <col width="80" />
            <col width="50" />
            <col width="80" />
            <col width="80" />
            <col width="90" />
            <col width="235" />
        </colgroup>
        <tr style="height: 15px;"> 
            <th>&nbsp;</th>
            <th style="font-size:8pt;"><?php echo $tl->get('Date')?></th>
            <th style="font-size:8pt;"><?php echo $tl->get('Time')?></th>
            <th style="font-size:8pt;"><?php echo $tl->get('User')?></th>
            <th style="font-size:8pt;"><?php echo $tl->get('Resource')?></th>
            <th style="font-size:8pt;"><?php echo $tl->get('Type')?></th>
            <th style="font-size:8pt;"><?php echo $tl->get('Event')?></th>
        </tr>
<?php
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
        xmlout(0, '<?xml-stylesheet type="text/xsl" href="'._SKIN.'/xsl/log_overview.xsl"?>');
    }

    xmlout(0, sprintf('<eventlog xams-release="%s" file-version="%s" date="%s">', _XAMS_VERSION, '0.0.9', date('Y-m-d')));
    xmlout(1, '<i18nfile language="'.$_SESSION['SESSION_LANGUAGE'].'">file://'.realpath('i18n/'.$_SESSION['SESSION_LANGUAGE'].'/log_overview.xml').'</i18nfile>');
    tagout(1, 'skindir', _SKIN);

    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
        entity_vals($row);
        $entry = sprintf('<logentry id="%d" date="%s" time="%s" user="%s" '.
            'usertype="%s" resource="%s" type="%s" event="%s">%s</logentry>',
            $row['id'], $row['time_d'], $row['time_t'], $row['name'],
            $usertype, $row['resource'], $row['msgstatus'], $row['msgtype'],
            $row['message']);
        xmlout(1, $entry);
    }
    xmlout(0, '</eventlog>');

    $myXSL = &xslclass::singleton();
    $myXSL->load_xsl(_SKIN.'/xsl/log_overview.xsl');
    $myXSL->set_xml_data($xmldata);
    $myXSL->transform();
    $myXSL->data = preg_replace('/^.*dtd">/s', null, $myXSL->data);
    $myXSL->out(); ?>
    </table>
    <p>
        <input type="submit" name="button" value="|&lt;" class="button"<?php if ($current_page == 0) {
        echo ' disabled="disabled"';
    } ?> />
        <input type="submit" name="button" value="&lt;" class="button"<?php if ($current_page == 0) {
        echo ' disabled="disabled"';
    } ?> />
        <input type="submit" name="button" value="&gt;" class="button"<?php if ($current_page + 1 == $pages) {
        echo ' disabled="disabled"';
    } ?> />
        <input type="submit" name="button" value="&gt;|" class="button"<?php if ($current_page + 1 == $pages) {
        echo ' disabled="disabled"';
    } ?> />
        <input type="text" name="page" value="<?php echo $current_page + 1?>" size="2" class="textfield" />
        <input type="submit" name="button" value="<?php echo $tl->get('Refresh')?>" class="button" />
    </p>
</form>
<?php 
} else {
    echo $tl->get('No log entries found');
} ?>
<?php include 'footer.php' ?>
