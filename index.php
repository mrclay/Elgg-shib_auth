<?php

/*
We initiate the login session at this URL because we know a Shibboleth 
redirect will never interfere. This allows us to store the real HTTP 
Referer without fear of picking up a URL at the IdP.

If you don't need to know which URL referrer the user to login, you
can save a redirect by sending the user directly to 'mod/shib_auth/validate/'.
*/

// start Elgg engine
require_once dirname(__FILE__) . "/../../engine/start.php";

// available via $this->loginReferer in your config object
$_SESSION['ELGG_SHIB_AUTH_REFERER'] = isset($_GET['referer'])
    ? $_GET['referer']
    : $_SERVER['HTTP_REFERER'];

forward('mod/shib_auth/validate/');