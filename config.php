<?php

/*
 * Return your site-specific config object
 *
 * @return Shib_AbstractConfig
 */
function shibAuth_getConfigObject() {

    require_once dirname(__FILE__) . '/Shib/DefaultConfig.php';

    return new Shib_DefaultConfig();
}

