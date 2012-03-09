<?php

// start Elgg engine
require_once dirname(__FILE__) . "/../../../engine/start.php";

if (elgg_is_active_plugin('shib_auth')) {
    shib_auth_execute_method('validate');
}
