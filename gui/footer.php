<?php
    if (_DEBUGMODE) {
        $creation_time = getmt() - script_start_time;
        echo sprintf("<p/><hr/><p>Script took %1.0f ms for generation\n</p>", $creation_time);
    }
?>
</body>
</html>
