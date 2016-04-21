<?php
    require 'gfl.php';
    gfl(_CUSTOMER);
    include 'include/i18n.php';
    $tl =& i18n::singleton();
    $tl->LoadLngBase('search');
    
    include 'header.php';
?> 
<h1><?php echo $tl->get('Search'); ?></h1>
<form method="post" action="system_overview.php?mode=search">
    <div class="menu1"></div>
    <div class="menu2">
        <table width="680">
            <colgroup>
                <col width="230" />
                <col width="450" />
            </colgroup>
            <tr>
                <td colspan="2">
                    <h3><?php echo $tl->get("Site specific")?></h3>
                </td>
            </tr>
            <tr>
                <th style="padding-left: 6px;"><?php echo $tl->get("Sitename")?></th>
                <td>
                    <input type="text" name="sitename" maxlength="100" size="50" class="textfield" />
                </td>
            </tr>
            <tr>
                <th style="padding-left: 6px;"><?php echo $tl->get("Max. Quota in KB (each User)")?></th>
                <td>
                    <input type="text" name="maxquota" maxlength="10" size="10" class="textfield" />
                </td>
            </tr>
            <tr>
                <th style="padding-left: 6px;"><?php echo $tl->get("Max. Users")?></th>
                <td>
                    <input type="text" name="maxaddr" size="10" maxlength="10" class="textfield" />
                </td>
            </tr>
            <tr>
                <th style="padding-left: 6px;"><?php echo $tl->get("Max. Aliases")?></th>
                <td>
                    <input type="text" name="maxaliases" size="10" maxlength="10" class="textfield" />
                </td>
            </tr>
    
            <tr>
                <td colspan="2">
                    <p>&nbsp;</p>
                    <h3><?php echo $tl->get("Domain specific")?></h3>
                </td>
            </tr>
    
            <tr>
                <th style="padding-left: 6px;"><?php echo $tl->get("Domainname")?></th>
                <td>
                    <input type="text" name="domainname" maxlength="100" size="50" class="textfield" />
                </td>
            </tr>
    
            <tr>
                <td colspan="2">
                    <p>&nbsp;</p>
                    <h3><?php echo $tl->get("User specific")?></h3>
                </td>
            </tr>
    
            <tr>
                <th style="padding-left: 6px;"><?php echo $tl->get("Account name (E-Mail)")?></th>
                <td>
                    <input type="text" name="username" maxlength="100" size="50" class="textfield" />
                </td>
            </tr>
            <tr>
                <th style="padding-left: 6px;"><?php echo $tl->get("E-Mail Address")?></th>
                <td>
                    <input type="text" name="email" maxlength="100" size="50" class="textfield" />
                </td>
            </tr>
    
            <tr>
                <td colspan="2">
                    <p>&nbsp;</p>
                    <h3><?php echo $tl->get("Alias specific")?></h3>
                </td>
            </tr>
    
            <tr>
                <th style="padding-left: 6px;"><?php echo $tl->get("Alias Name (Left Part)")?></th>
                <td>
                    <input type="text" name="leftpart" maxlength="100" size="50" class="textfield" />
                </td>
            </tr>
            <tr>
                <th style="padding-left: 6px;"><?php echo $tl->get("Alias Target (Right Part)")?></th>
                <td>
                    <input type="text" name="rightpart" maxlength="100" size="50" class="textfield" />
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <p><?php echo $tl->get("Note: You can use the %-Sign as wildcard (e.g. Domainname: %foo%)")?></p>
                    <p>
                        <input type="submit" name="button" value="<?php echo $tl->get("Search")?>" class="button" />
                        <input type="reset" name="button" value="<?php echo $tl->get("Reset")?>" class="button" />
                        <input type="button" name="help" value="<?php echo $tl->get("Help")?>" class="helpbutton" onclick="window.open('help.php?help=search', '', 'scrollbars=yes, height=500, width=920');" />
                    </p>
                </td>
            </tr>
        </table>
    </div>
    <div class="menu3"></div>
</form>
<?php include 'footer.php' ?>
