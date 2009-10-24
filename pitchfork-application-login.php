<?php

# Pitchfork authentication page
# Test modification(s)

# Include the UI Processor...
include('includes/pitchfork-class-interface-2.php');

$auth_UI = new UserInterface();

$auth_UI -> page_title = "Login";

$auth_UI -> load_header();


if(@$_REQUEST['access'] == 'denied') $auth_UI -> special = $auth_UI -> load('password-wrong', false, true);
else $auth_UI -> special = $auth_UI -> load('password-prompt', false, true);

$auth_UI -> load('prompt-login');

$auth_UI -> load_footer();

echo $auth_UI ->ui_parsed;

# Part of Pitchfork.


?>