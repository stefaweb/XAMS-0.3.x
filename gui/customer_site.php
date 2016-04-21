<?php
    require 'gfl.php';
    gfl(_CUSTOMER, true);
    include 'include/global.php';
    include 'include/customers.php';
    include 'include/sites.php';
    include 'include/site_addressbook.php';

    $myAB = new Site_Addressbook();
    $mySite = new Sites();
    $tl =& $mySite->i18n;

    $id = greq('id');
    $button = gpost('button');
    $mode = 'update';
    $info = gget('info');

    if ($id)
    {
        $mySite->Load($id);
        if (!$mySite->isAuthLoad())
            die('Access denied.');
    }

    if ($button)
    {
        $mySite->Assign2Object(array('id', 'addressbook_', 'mode'));

        $mySite->Update();
        $info = $tl->get($mySite->notice);
        $mySite->Load(); // For update-field
    }    

    $mySite->CalcQuotaUnits(true);

    if ($id)
        $myAB->Load($id);

    $units = array('KB', 'MB', 'GB');

    include 'header.php';
?>
<h1><?php echo $tl->get('Site Management'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
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
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Sitename") ?></th>
                <td>
                    <?php
                        echo $mySite->name;
                    ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Domains") ?></th>
                <td>
                    <?php
                        echo implode(', ', $mySite->myDomains->domainnames);
                    ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Quota")?></th>
                <td>
                    <?php
                        echo ($mySite->maxquota < 0) ? $tl->get('unlimited') : $mySite->maxquota. ' '. $units[$mySite->quotaunit];
                    ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. User-Quota") ?></th>
                <td>
                    <?php
                        echo ($mySite->maxuserquota < 0) ? $tl->get('unlimited') : $mySite->maxuserquota. ' '. $units[$mySite->userquotaunit];
                    ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Users") ?></th>
                <td>
                    <?php
                        echo ($mySite->maxaddr < 0) ? $tl->get('unlimited') : $mySite->maxaddr;
                    ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Max. Aliases") ?></th>
                <td>
                    <?php
                        echo ($mySite->maxaliases < 0) ? $tl->get('unlimited') : $mySite->maxaliases;
                    ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Type") ?></th>
                <td>
                    <?php
                        $var = array();
                        if ($mySite->addrtype & _SMTP) $var[] = 'SMTP';
                        if ($mySite->addrtype & _POP) $var[] = 'POP3';
                        if ($mySite->addrtype & _IMAP) $var[] = 'IMAP';
                        if ($mySite->addrtype & _XAMS) $var[] = 'XAMS';
                        echo implode(', ', $var);
                    ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Viruscheck") ?></th>
                <td>
                    <?php
                        $var = array();
                        if (isTrue($mySite->viruscheckin)) $var[] = $tl->get('Incoming');
                        if (isTrue($mySite->viruscheckout)) $var[] = $tl->get('Outgoing');
                        echo implode(', ', $var);
                    ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Status") ?></th>
                <td>
                    <?php
                        $var = array('default'=>'Active', 'locked'=>'Locked', 'lockedbounce'=>'Locked &amp; Bounce');
                        echo $tl->get($var[$mySite->sitestate]) ;
                    ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php include 'show_addressbook.php'; ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" name="button" value="<?php echo $tl->get("Save") ?>" class="button" />
                    <input type="reset" name="button" value="<?php echo $tl->get("Reset") ?>" class="button" />
                    <input type="button" name="help" value="<?php echo $tl->get("Help") ?>" class="helpbutton" onclick="window.open('help.php?help=site&amp;mode=<?php echo $mode ?>', '', 'scrollbars=yes, height=500, width=920');" />
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
