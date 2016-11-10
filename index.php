<?php
// index.php

/* ---------------------------------------------- Header ---------------------------------------------- */
require(dirname(__FILE__) . '/header.php');

$site = new Controller();

if(DEBUG)
{
    showMeTheValue($site->httpParams);
    showMeTheValue($site->postData);
}

if ($site->isLoggedUser())
{
    $site->showSite();
}
else
{
    $site->showLogin();
}



/* ---------------------------------------------- FOOTER ---------------------------------------------- */
