<?php
    require 'gfl.php';
    gfl(_RESELLER);
    require 'include/global.php';

    $button = gpost('button');
    $AdminID = gpost('AdminID');
    $ResellerID = gpost('ResellerID');
    $CustomerID = gpost('CustomerID');
    $url = null;

    include 'include/xclass.php';
    $db = new xclass();
    $tl =& $db->i18n;
    $tl->LoadLngBase('account_overview');

    switch (gpost('form_type'))
    {
        case "admin":
            if     ($button == $tl->get('New')) $url = 'Location: administrator.php?mode=new';
            elseif ($button == $tl->get('Edit')) $url = sprintf('Location: administrator.php?mode=update&id=%d', $AdminID);
            header($url);
            exit;
        case "reseller":
            if     ($button == $tl->get('New')) $url = 'Location: reseller.php?mode=new';
            elseif ($button == $tl->get('Edit')) $url = sprintf('Location: reseller.php?mode=update&id=%d', $ResellerID);
            elseif ($button == $tl->get('Overview')) $url = sprintf('Location: system_overview.php?resellerid=%d', $ResellerID);
            header($url);
            exit;
        case "customer":
            if     ($button == $tl->get('New')) $url = 'Location: customer.php?mode=new';
            elseif ($button == $tl->get('Edit')) $url = sprintf('Location: customer.php?mode=update&id=%d', $CustomerID);
            elseif ($button == $tl->get('Overview')) $url = sprintf('Location: system_overview.php?customerid=%d', $CustomerID);
            header($url);
            exit;
    }

    if (isADMIN)
    {
        $result_global = $db->db->getAll('SELECT id, name, locked FROM pm_admins ORDER BY name', DB_FETCHMODE_ASSOC);
        $result_reseller = $db->db->getAll('SELECT id, name, locked FROM pm_resellers ORDER BY name', DB_FETCHMODE_ASSOC);
        $result_customer = $db->db->getAll('SELECT id, name, locked FROM pm_customers ORDER BY name', DB_FETCHMODE_ASSOC);
    }
    else
    {
        $result_customer = $db->db->getAll('SELECT id, name, locked FROM pm_customers WHERE resellerid = ? ORDER BY name', array(USERID), DB_FETCHMODE_ASSOC);
    }

    include 'header.php';
?>
<h1><?php echo $tl->get('XAMS Accounts Overview'); ?></h1>
<p class="forminfo"><?php echo stripcslashes(gget('info')) ?></p>
<div class="menu1n"></div>
<div class="menu2n">
    <table style="width: 650px;" cellpadding="0" cellspacing="0" class="tbl_global">
        <?php if (isADMIN) { ?>
        <tr>
            <td style="width: 210px; vertical-align:top;">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" style="margin:0; padding:0;">
                    <table width="210" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center">
                                <p><strong><?php echo $tl->get('Administrators') ?></strong></p>
                                <?php
                                    if (count($result_global) > 0)
                                    {
                                        echo '<select name="AdminID" size="15" style="width: 180px;">';
                                        foreach ($result_global as $elem)
                                        {
                                            $mark = (isTrue($elem['locked'])) ? ' style="color: #FF0000; font-weight: bold;"' : null;
                                            echo "<option value=\"$elem[id]\"$mark>$elem[name]</option>\n";
                                        }
                                        echo '</select>';
                                    }
                                    else
                                        echo '<div style="width:180px; height: 15em; font-size: 12pt; background: #D5EDFF;">' .$tl->get('none').'</div>';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <input type="hidden" name="form_type" value="admin" />
                                <input type="submit" name="button" value="<?php echo $tl->get('New') ?>" class="button" />
                                <input type="submit" name="button" value="<?php echo $tl->get('Edit') ?>" class="button" />
                                <input type="button" name="help" value="<?php echo $tl->get('Help') ?>" class="helpbutton" onclick="window.open('help.php?help=accounts', '', 'scrollbars=yes, height=500, width=920');" />
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
            <td style="width: 210px; vertical-align: top;">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" style="margin:0; padding:0;">
                    <table width="210">
                        <tr>
                            <td align="center">
                                <p><strong><?php echo $tl->get("Resellers") ?></strong></p>
                                <?php
                                    if (count($result_reseller) > 0)
                                    {
                                        echo '<select name="ResellerID" size="15" style="width: 180px;">';
                                        foreach ($result_reseller as $elem)
                                        {
                                            $mark = (isTrue($elem['locked'])) ? ' style="color: #FF0000; font-weight: bold;"' : null;
                                            echo "<option value=\"$elem[id]\"$mark>$elem[name]</option>\n";
                                        }
                                        echo '</select>';
                                    }
                                    else
                                        echo '<div style="width:180px; height: 15em; font-size: 12pt; background: #D5EDFF;">'.$tl->get("none").'</div>';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <input type="hidden" name="form_type" value="reseller" />
                                <input type="submit" name="button" value="<?php echo $tl->get('New') ?>" class="button" />
                                <input type="submit" name="button" value="<?php echo $tl->get('Edit') ?>" class="button" />
                                <input type="submit" name="button" value="<?php echo $tl->get('Overview') ?>" class="button" />
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
            <?php } ?>
            <td style="width: 210px; vertical-align: top;">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" style="margin:0; padding:0;">
                    <table width="210">
                        <tr>
                            <td align="center">
                                <p><strong><?php echo $tl->get("Customers") ?></strong></p>
                                <?php
                                    if (count($result_customer) > 0)
                                    {
                                        echo '<select name="CustomerID" size="15" style="width: 180px;">';
                                        foreach ($result_customer as $elem)
                                        {
                                            $mark = (isTrue($elem['locked'])) ? ' style="color: #FF0000; font-weight: bold;"' : null;
                                            echo "<option value=\"$elem[id]\"$mark>$elem[name]</option>\n";
                                        }
                                        echo '</select>';
                                    }
                                    else
                                        echo '<div style="width:180px; height: 15em; font-size: 12pt; background: #D5EDFF;">'.$tl->get("none").'</div>';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <input type="hidden" name="form_type" value="customer" />
                                <input type="submit" name="button" value="<?php echo $tl->get('New') ?>" class="button" />
                                <input type="submit" name="button" value="<?php echo $tl->get('Edit') ?>" class="button" />
                                <input type="submit" name="button" value="<?php echo $tl->get('Overview') ?>" class="button" />
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
</div>
<div class="menu3n"></div>
<?php include 'footer.php' ?>
