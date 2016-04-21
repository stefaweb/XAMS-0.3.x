<?php
    require 'gfl.php';
    gfl(_ADMIN);
    include 'include/global.php';
    include 'include/reseller_addressbook.php';

    $myAB = new Reseller_Addressbook();
    $tl =& $myAB->i18n;
    $button = gpost('button');
    $selected = gpost('selected');

    if (!empty($button))
    {
        $myAB->Assign2Object(array('id', 'name', 'ldapname',
        'acl_reseller', 'ord', 'position'));

        if ($button == $tl->get('Update'))
        {
            $i = 0;
            foreach ($myAB->name as $selection) $myAB->Update($i++);
            if (is_array($selected))
                foreach ($selected as $selection)
                    $myAB->Delete($selection);
        }
        elseif ($button == $tl->get('New')) $myAB->Add();
    }

    $overview = $myAB->LoadOverview();
    
    // Print Access Control Field
    function output_ac($i, $type)
    {
        global $tl, $overview;
        foreach (array(_ACL_NONE => $tl->get('None'), _ACL_READ => $tl->get('Read'), _ACL_WRITE => $tl->get('Read+Write')) as $k=>$elem)
        {
            $sel = (!empty($type) && $overview[$i][$type] & $k) ? ' selected="selected"' : null;
            echo "<option value=\"$k\"$sel>$elem</option>";
        }
    }

    include 'include/functions.php';
    prep_tabs('"Sites", "Users", "Aliases", "Resellers", "Customers"');
    include 'header.php';
?>
<h1><?php echo $tl->get('Reseller Addressbook Administration'); ?></h1>
<?php show_tabs(array("Sites" => "site_addressbook.php",
                      "Users" => "user_addressbook.php",
                      "Aliases" => "alias_addressbook.php",
                      "Resellers" => "reseller_addressbook.php",
                      "Customers" => "customer_addressbook.php"), "Resellers", $tl) ?>
<p>&nbsp;</p>
<?php
    if (!count($overview))
        echo $tl->get("No addressbook fields found.");
    else
    {
?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<table width="700" class="tbl_global">
    <tr>
        <th style="font-size: 9pt;"><?php echo $tl->get("Fieldname") ?></th>
        <th style="font-size: 9pt;"><?php echo $tl->get("LDAP") ?></th>
        <th style="font-size: 9pt;"><?php echo $tl->get("ACL Reseller") ?></th>
        <th style="font-size: 9pt;"><?php echo $tl->get("Move field") ?></th>
        <th style="text-align: center;"><img src="<?php echo _SKIN ?>/img/delete_red.png" height="16" width="16" title="<?php echo $tl->get("Delete") ?>"></th>
    </tr>
<?php for ($i=0; $i<count($overview); $i++) { ?> 
    <tr>
        <td class="addressbook"><input type="hidden" name="<?php echo "id[$i]"?>" value="<?php echo $overview[$i]['id']?>" />
            <input type="hidden" name="<?php echo "ord[$i]"?>" value="<?php echo $overview[$i]['ord']?>" />
            <input type="text" name="<?php echo "name[$i]"?>" size="13" maxlength="30" value="<?php echo $overview[$i]['name']?>" class="textfield" />
        </td>
        <td class="addressbook"><input type="text" name="<?php echo "ldapname[$i]"?>" size="10" maxlength="30" value="<?php echo $overview[$i]['ldapname']?>" class="textfield" /></td>
        <td class="addressbook"><select name="<?php echo "acl_reseller[$i]"?>"><?php echo output_ac($i, "acl_reseller")?></select></td>
        <td class="addressbook">
            <select name="<?php echo "position[$i]"?>" style="width: 90px;"><option></option><?php for ($i2=0; $i2<count($overview); $i2++) echo "<option value=\"". $overview[$i2]['ord']. "\">". $overview[$i2]['name']. "</option>"?></select>
        </td>
        <td style="text-align: center;">
            <input type="checkbox" name="<?php echo "selected[$i]"?>" value="<?php echo $i?>" class="button" />
        </td>

    </tr>
<?php } ?>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td style="text-align: right; padding-top: 10px;">
            <input type="submit" name="button" value="<?php echo $tl->get("Update")?>" class="button" />
        </td>
    </tr>
</table>
</form>
<?php } ?>
<p>&nbsp;</p>
<h4><?php echo $tl->get("Add new field to Addressbook")?></h4>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
<table width="700" class="tbl_global">
    <tr>
        <th style="font-size: 9pt;"><?php echo $tl->get("Fieldname")?></th>
        <th style="font-size: 9pt;"><?php echo $tl->get("LDAP")?></th>
        <th style="font-size: 9pt;"><?php echo $tl->get("ACL Reseller")?></th>
        <th style="font-size: 9pt;">&nbsp;</th>
    </tr>
    <tr>
        <td class="addressbook"><input type="text" name="name" size="15" maxlength="30" class="textfield" /></td>
        <td class="addressbook"><input type="text" name="ldapname" size="10" maxlength="30" class="textfield" /></td>
        <td class="addressbook"><select name="acl_reseller"><?php echo output_ac(0, null)?></select></td>
        <td><input type="submit" name="button" value="<?php echo $tl->get("New")?>" class="button" /></td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td  style="padding-top: 10px;"><input type="button" name="help" value="<?php echo $tl->get("Help") ?>" class="helpbutton" onclick="window.open('help.php?help=user_addressbook', '', 'scrollbars=yes, height=500, width=920');" />
        </td>
    </tr>
</table>
</form>
<?php include 'footer.php' ?>
