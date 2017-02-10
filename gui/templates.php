<?php
    require 'gfl.php';
    gfl(_CUSTOMER);
    include 'include/global.php';

    $info = gget('info');
    $button = gpost('button');
    $id = gpost('id');
    $url = null;

    include 'include/xclass.php';
    $db = new xclass();
    $tl = &$db->i18n;
    $tl->LoadLngBase('template_overview');

    if ($id != null) {
        switch (gpost('form_type')) {
            case 'site':
                if ($button == $tl->get('New')) {
                    $url = 'Location: site_template.php?mode=new';
                } elseif ($button == $tl->get('Use')) {
                    $url = sprintf('Location: site.php?mode=new&templateid=%d', $id);
                } elseif ($button == $tl->get('Edit')) {
                    $url = sprintf('Location: site_template.php?mode=update&id=%d', $id);
                }
                header($url);
                exit;
            case 'user':
                if ($button == $tl->get('New')) {
                    $url = 'Location: user_template.php?mode=new';
                } elseif ($button == $tl->get('Use')) {
                    $url = sprintf('Location: user.php?mode=new&templateid=%d', $id);
                } elseif ($button == $tl->get('Edit')) {
                    $url = sprintf('Location: user_template.php?mode=update&id=%d', $id);
                }
                header($url);
                exit;
        }
    } else {
        switch (gpost('form_type')) {
            case 'site':
                if ($button == $tl->get('New')) {
                    $url = 'Location: site_template.php?mode=new';
                    header($url);
                    exit;
                }
            case 'user':
                if ($button == $tl->get('New')) {
                    $url = 'Location: user_template.php?mode=new';
                    header($url);
                    exit;
                }
        }
    }

    $arr = [_ADMIN=>'adminid', _RESELLER=>'resellerid', _CUSTOMER=>'customerid'];
    $field = $arr[USERT];

    if (USERT != _CUSTOMER) {
        $sitet = $db->db->getAll("SELECT id, templatename FROM pm_site_templates WHERE $field = ? ORDER BY templatename", [USERID], DB_FETCHMODE_ASSOC);
    }

    $usert = $db->db->getAll("SELECT id, templatename FROM pm_user_templates WHERE $field = ? ORDER BY templatename", [USERID], DB_FETCHMODE_ASSOC);

    include 'header.php';
?>
<h1><?php echo $tl->get('Template Overview'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<div class="menu1n"></div>
<div class="menu2n">
    <table width="655" class="tbl_global">
        <tr>
    <?php
        if (USERT >= _RESELLER) {
            ?>
            <td style="width: 330px;">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
                   <table width="330">
                        <tr><th style="text-align: center;"><?php echo $tl->get('Site Templates')?></th></tr>
                        <tr>
                            <td align="left">
                                <?php
                                    if (count($sitet) > 0) {
                                        echo '<select name="id" size="15" style="width: 320px;">'."\n";
                                        foreach ($sitet as $k=>$elem) {
                                            echo "<option value=\"$elem[id]\">$elem[templatename]</option>\n";
                                        }
                                        echo '</select>';
                                    } else {
                                        echo '<div style="width: 320px; height: 15em; font-size: 12pt; background: #D5EDFF; text-align: center;">'.$tl->get('none').'</div>';
                                    } ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <input type="hidden" name="form_type" value="site" />
                                <input type="submit" name="button" value="<?php echo $tl->get('New')?>" class="button" />
                                <input type="submit" name="button" value="<?php echo $tl->get('Use')?>" class="button" />
                                <input type="submit" name="button" value="<?php echo $tl->get('Edit')?>" class="button" />
                                <input type="button" name="help" value="<?php echo $tl->get('Help')?>" class="helpbutton" onclick="window.open('help.php?help=templates', '', 'scrollbars=yes, height=500, width=920');" />
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
    <?php 
        } ?>
            <td style="width: 325px;">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
                    <table width="325">
                        <tr><th style="text-align: center;"><?php echo $tl->get('User Templates')?></th></tr>
                        <tr>
                            <td align="right">
                                <?php
                                    if (count($usert) > 0) {
                                        echo '<select name="id" size="15" style="width: 320px;">'."\n";
                                        foreach ($usert as $k=>$elem) {
                                            echo "<option value=\"$elem[id]\">$elem[templatename]</option>\n";
                                        }
                                        echo '</select>';
                                    } else {
                                        echo '<div style="width: 320px; height: 15em; font-size: 12pt; background: #D5EDFF; text-align: center;">'.$tl->get('none').'</div>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <input type="hidden" name="form_type" value="user" />
                                <input type="submit" name="button" value="<?php echo $tl->get('New')?>" class="button" />
                                <input type="submit" name="button" value="<?php echo $tl->get('Use')?>" class="button" />
                                <input type="submit" name="button" value="<?php echo $tl->get('Edit')?>" class="button" />
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
