<?php

/*
 * Return your site-specific config object
 *
 * @return Shib_IConfig
 */
function shib_auth_get_config() {
    return new Shib_DefaultConfig();
}

