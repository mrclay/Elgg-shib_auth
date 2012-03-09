<?php

// start Elgg engine
require_once dirname(__FILE__) . "/../../../engine/start.php";

require_once dirname(__FILE__) . "/../config.php";

require_once dirname(__FILE__) . "/../Shib.php";

$shib = new Shib();

$shib->validate(shibAuth_getConfigObject());
