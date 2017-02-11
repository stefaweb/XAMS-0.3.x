<?php
    require 'gfl.php';
    gfl(_ADMIN);

    include 'include/global.php';
    include 'include/xclass.php';

    $tl = &$mySite->i18n;

    $button = gpost('button');
    $mode = greq('mode');
    $resellerid = greq('resellerid');
    $id = greq('id');

    if ($button) {
        header(sprintf('Location: customer.php?mode=%s&resellerid=%d&id=%d', $mode, $resellerid, $id));
        exit;
    }

    $db = new xclass();
    $db->i18n->LoadLngBase('customer_reseller');

    // Select resellers for reseller-list
    $resellers = $db->db->getAll('SELECT id, name FROM pm_resellers ORDER BY name', DB_FETCHMODE_ASSOC);

    include 'header.php';
?>
<h1><?php echo $db->i18n->get('Reseller Assignment'); ?></h1>
<?php echo $db->i18n->get('You have to assign this customer to a reseller.')?>
<p>&nbsp;</p>
<form name="reseller_formular" method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
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
                    <input type="hidden" name="mode" value="<?php echo $mode?>">
                    <input type="hidden" name="id" value="<?php echo $id?>">
                </td>
            </tr>
            <tr>
                <th><?php echo $db->i18n->get('Reseller')?></th>
                <td>
                    <select name="resellerid" size="1">
                        <option></option>
                        <?php
                            foreach ($resellers as $elem) {
                                $sel = ($elem['id'] == $resellerid) ? ' selected="selected"' : null;
                                echo "<option value=\"$elem[id]\"$sel>$elem[name]</option>";
                            }
                        ?>
                    </select>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" name="button" value="<?php echo $db->i18n->get('Next')?> &gt;&gt;" class="button">
                    <input type="button" name="help" value="<?php echo $db->i18n->get('Help')?>" class="helpbutton" onClick="window.open('help.php?help=customer_reseller&mode=<?php echo $mode?>', '', 'scrollbars=yes, height=500, width=920');">
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
