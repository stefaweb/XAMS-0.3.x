<?php
    if (count($myAB->Fields) > 0) {
        ?>
        <tr><th colspan="2"><p></p><h4><?php echo $tl->get('Addressbook') ?></h4></th></tr>
        <?php
        foreach ($myAB->Fields as $k=>$Name) {
            if ($Name['writeable'] || !empty($Name['value'])) {
                echo "<tr>\n";
                echo "<th>$k</th>\n";
                echo "<td>\n";
                if ($Name['writeable'] === false) {
                    printf('<input type="text" size="50" maxlength="255" value="%s" class="textfield" readonly="readonly" />', $Name['value']);
                } else {
                    printf('<input type="text" size="50" maxlength="255" name="addressbook_%d" value="%s" class="textfield"%s />', $Name['id'], $Name['value'], (($Name['writeable'] === false) ? ' readonly="readonly"' : null));
                }
                echo "\n</td>\n<td></td>\n";
                echo "</tr>\n";
            }
        }
    }
?>
