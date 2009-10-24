<?php

# This file checks to see if the user has a right to be here, and if not, sends them to a login screen.

include('configuration/pitchfork-configuration-user.php'); 

if(!$Cfg_PassRequired) header('Location: pitchfork-application-index.php');
else header('Location: pitchfork-application-login.php');

echo 'You should be redirected to the index page imminently. If that is not happening, click <a href="pitchfork-application-index.php">here.</a>';

# Part of Pitchfork.

?>