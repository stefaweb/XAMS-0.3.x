<?php
    require 'gfl.php';
    gfl(_RESELLER);
    require 'include/global.php';

    $id         = greq('id');
    $mode       = greq('mode');
    $button     = gpost('button');
    $resellerid = null;

    if (!$id && $mode != 'new')
    {
        header('Location: account_overview.php');
        exit;
    }

    include 'include/customer_addressbook.php';
    include 'include/customers.php';

    $myCustomer = new Customers();
    $myReseller =& $myCustomer->myReseller;
    $myAB       = new Customer_Addressbook();
    $db =& $myCustomer->db;
    $tl =& $myCustomer->i18n;

    if ($id)
    {
        $myCustomer->Load($id);
        if (!$myCustomer->isAuthLoad())
            die($tl->get('Access denied.'));
        $resellerid = $myCustomer->resellerid;
    }
    else
    {
        // Set the resellerid of this customer to the userid of the currently
        // logged in reseller
        $resellerid = (isRESELLER) ? USERID : greq('resellerid');
    }

    // No resellerid from Load(), none from Formular or change Reseller -> Forward
    if (!$myCustomer->resellerid && !$resellerid || $button == $tl->get('Change'))
    {
        header(sprintf('Location: customer_reseller.php?mode=%s&id=%d', $mode, $id));
        exit;
    }
    elseif ($resellerid)
    {
        $myReseller->Load($resellerid);
        $myCustomer->assignFormVar('resellerid', $resellerid);
    }

    if ($button)
    {
        if ($button == $tl->get('Delete'))
        {
            $myCustomer->Delete();
            header('Location: account_overview.php?info='. urlencode($myCustomer->notice));
            exit;
        }

        $myCustomer->Assign2Object(array('name', 'password', 'locked',
        'maxusers', 'maxaliases', 'maxuserquota', 'sites', 'addressbook_'));
        $myCustomer->check_formular($mode);

        if (!$myCustomer->formular_errors)
        {
            switch ($button)
            {
                case $tl->get('Save'):
                    $myCustomer->Add();
                    header('Location: account_overview.php?info='. urlencode($myCustomer->notice));
                    exit;
                case $tl->get('Update'):
                    $myCustomer->Update();
                    header('Location: account_overview.php?info='. urlencode($myCustomer->notice));
                    exit;
            }
        }
    }

    $button = ($mode == 'new') ? 'Save' : 'Update';

    // Check if logged in Reseller can add more customers
    if ($myReseller->FreeCustomers() == 0 && $mode == 'new') {
        if (isRESELLER)
            $msg = 'Unfortunately no more customers can be added. Your Customer-Limit has reached.';
        else
            $msg = 'Unfortunately no more customers can be added. The Customer-Limit of the selected Reseller has reached.';
        $myReseller->status($tl->get($msg));
        exit;
    }

    if ($id)
        $myAB->Load($id);

    // Select all sites for sites-list
    $sites = $db->getAll('SELECT id, name FROM pm_sites WHERE resellerid = ? ORDER BY name', array($resellerid), DB_FETCHMODE_ASSOC);
    $site_select_box_size = (count($sites) > 30) ? 30 : count($sites);

    include 'header.php';
?>
<h1><?php echo $tl->get('Customer Management'); ?></h1>
<?php if ($myCustomer->formular_errors) echo '<p class="formerror"><img src="'. _SKIN. '/img/critical.png" alt="Error" height="25" width="25" />'. $tl->get('The formular was not properly filled out. Point at the question mark.'). '</p>'; ?>
<form method="post" autocomplete="off" action="<?php echo $_SERVER['PHP_SELF']?>">
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
                    <input type="hidden" name="mode" value="<?php echo $mode?>" />
                    <?php if (isADMIN) { ?>
                    <input type="hidden" name="resellerid" value="<?php echo $resellerid ?>" />
                    <?php } ?>
                    <?php if ($mode == 'update') { ?>
                    <input type="hidden" name="id" value="<?php echo $myCustomer->id?>" />
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Customer Name")?></th>
                <td>
                    <input type="text" name="name" value="<?php echo $myCustomer->name?>" maxlength="100" size="50" class="textfield" />
                </td>
                <td><?php echo $myCustomer->show_field_property("name")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Customer Password")?></th>
                <td>
                    <input type="password" name="password" value="" maxlength="100" size="20" class="textfield" />
                </td>
                <td><?php echo $myCustomer->show_field_property("password")?></td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Account locked")?></th>
                <td>
                    <input type="checkbox" name="locked" class="checkbox" value="true"<?php if (isTrue($myCustomer->locked)) echo ' checked="checked"' ?> />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php if (isADMIN) { ?>
            <tr>
                <th><?php echo $tl->get("Reseller")?></th>
                <td>
                    <input type="text" name="reseller" value="<?php echo $myReseller->name?>" maxlength="40" size="40" class="textfield" disabled="disabled" />
                    <input type="submit" name="button" value="<?php echo $tl->get('Change')?>" class="button" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php } ?>
            <tr>
                <th><?php echo $tl->get("Sites")?></th>
                
                <td style="padding-top:5px; padding-bottom:5px;">
                    <select id="sites" name="sites[]" size="<?php echo $site_select_box_size?>" multiple="multiple">
                        <?php
                            foreach ($sites as $elem)
                            {
                                $sel = (in_array($elem['id'], $myCustomer->sites)) ? ' selected="selected"' : null;
                                echo "<option value=\"$elem[id]\"$sel>$elem[name]</option>\n";
                            }
                        ?>
                    </select>
                </td>
           
                <td>&nbsp;</td>
            </tr>
            <?php if ($mode == 'update') { ?>
            <tr>
                <th><?php echo $tl->get("Customer created")?></th>
                <td>
                    <input type="text" name="created" value="<?php echo $myCustomer->added?>" size="<?php echo strlen($myCustomer->added)?>" class="textfield" disabled="disabled" />
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><?php echo $tl->get("Customer last updated")?></th>
                <td>
                    <input type="text" name="updated" value="<?php echo $myCustomer->updated?>" size="<?php echo strlen($myCustomer->updated)?>" class="textfield" disabled="disabled"  />
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php } ?>
            <?php include 'show_addressbook.php'; ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p></p>
                    <input type="submit" name="button" value="<?php echo $tl->get("$button")?>" class="button" />
                    <input type="submit" name="button" value="<?php echo $tl->get("Delete")?>" class="button" />
                    <input type="reset" name="button" value="<?php echo $tl->get("Reset")?>" class="button" />
                    <input type="button" name="help" value="<?php echo $tl->get("Help")?>" class="helpbutton" onclick="window.open('help.php?help=customer&amp;mode=<?php echo $mode?>', '', 'scrollbars=yes, height=500, width=920');" />
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<script>
	$('form').attr('autocomplete','off');
</script>
<?php include 'footer.php' ?>
