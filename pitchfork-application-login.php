<?php

# Pitchfork authentication page

# Include the UI Processor...
include('includes/pitchfork-class-interface.php');

$auth_UI = new UserInterface();

$auth_UI -> set_page_title("Login");

$auth_UI -> load_header();


if($_REQUEST['access'] == 'denied') $auth_UI -> special = $auth_UI -> load('password-wrong', false);
else $auth_UI -> special = $auth_UI -> load('password-prompt', false);

$auth_UI -> load('prompt-login');

$auth_UI -> load_footer();

$auth_UI -> return_UI();

# Part of Pitchfork.


?>