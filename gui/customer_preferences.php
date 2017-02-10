<?php
    require 'gfl.php';
    gfl(_CUSTOMER, true);

    include 'include/global.php';
    include 'include/customers.php';
    include 'include/customer_addressbook.php';

    $button = gpost('button');
    $info = null;
    $mode = 'update';
    $id = USERID;

    $myAB = new Customer_Addressbook();
    $myCustomer = new Customers();
    $tl = &$myCustomer->i18n;

    $myCustomer->Load($id);

    if ($button) {
        $myCustomer->Assign2Object(['password', 'addressbook_', 'mode']);

        $myCustomer->check_formular($mode);

        if (!$myCustomer->formular_errors && $button == $tl->get('Update')) {
            $myCustomer->Update();
            $info = $tl->get($myCustomer->notice);
        }
    }

    $button = 'Update';

    $myAB->Load($id);

    include 'header.php';
?>
<h1><?php echo $tl->get('Account Settings'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<?php if ($myCustomer->formular_errors) {
    echo '<p class="formerror"><img src="'._SKIN.'/img/critical.png" alt="Error" height="25" width="25" />'.$tl->get('The formular was not properly filled out. Point at the question mark.').'</p>';
} ?>
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
                    <input type="hidden" name="mode" value="<?php echo $mode?>" />
                </td>
            </tr>
            <tr>
                <th><?php echo $tl->get('Password')?></th>
                <td>
                    <input class="password" type="text" name="password" value="" maxlength="100" size="30" class="textfield" />
                </td>
                <td><?php echo $myCustomer->show_field_property('password')?></td>
            </tr>
            <?php include 'show_addressbook.php'; ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <p><br/></p>
                    <input type="submit" name="button" class="button" value="<?php echo $tl->get("$button")?>" />
                    <input type="reset" name="button" class="button" value="<?php echo $tl->get('Reset')?>" />
                </td>
            </tr>    
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
