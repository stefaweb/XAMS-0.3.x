<?php
    require 'gfl.php';
    gfl(_ADMIN);

    include 'include/global.php';

    $dnsid = gpost('dnsid');
    $info = gget('info');
    $button = gpost('button');

    include 'include/xclass.php';
    $db = new xclass();
    $tl =& $db->i18n;
    $tl->LoadLngBase('dns');

    if ($button == $tl->get('New'). ' >>')
    {
        header('Location: dns_zone.php?mode=new');
        exit;
    } 
    if (!empty($dnsid))
    {
        header("Location: dns_zone.php?dnsid=$dnsid");
        exit;
    }

    $domains = $db->db->getAll('SELECT name, id FROM pm_dns ORDER BY name', DB_FETCHMODE_ASSOC);

    include 'header.php';
?>
<h1><?php echo $tl->get('DNS Management'); ?></h1>
<p class="forminfo"><?php echo stripcslashes($info)?></p>
<p>
    <?php echo $tl->get('Select a Zone from below or')?> <a href="dns_zone.php?mode=new"><strong><?php echo $tl->get('create a new Zone')?></strong></a>
</p>
<hr />
<p>
    <ul>
        <?php foreach ($domains as $elem) echo "<li><a href=\"dns_zone.php?mode=update&dnsid=$elem[id]\">$elem[name]</a></li>\n"; ?>
    </ul>
</p>
<?php include 'footer.php' ?>
