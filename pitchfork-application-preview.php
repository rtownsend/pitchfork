<?php
/* 
 * This script will eventually allow for the preview of all sorts of
 * documents. Right now, the only thing it can do is send a slightly
 * different set of headers.
 */

$hash = $_GET['hash'];

header("Location: pitchfork-application-download.php?item=$hash&mode=stream");

# Part of Pitchfork.


?>
