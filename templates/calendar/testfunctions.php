<?php
function writeAuth($cd, $before="", $after="") {
    echo "<li>Writing without auth...<br>";
    echo $before, $cd->write(), $after;
    echo "<li>Writing with auth = 1...<br>";
    echo $before, $cd->write(1), $after;
    echo "<li>Writing with auth = 2...<br>";
    echo $before, $cd->write(2), $after;
}
?>
