<?php
    include 'include/config.php';

    $msg = 'System is down for maintenance. Please try again later.';

    include 'header.php';
?>
    <table style="margin-right: auto; margin-left: auto;">
        <tr>
            <td align="center"><a href="#" onclick="window.open('http://www.xams.org');"><img src="<?php echo _SKIN ?>/img/logo.png" width="88" height="59" title="eXtended Account Management System" alt="XAMS" /></a></td>
        </tr>
        <tr>
            <td class="version"><?php echo _XAMS_VERSION ?></td>
        </tr>
        <tr>
            <td>
                <h2><?php echo $msg ?></h2>
            </td>
        </tr>
    </table>
<?php
    include 'footer.php';
?>
