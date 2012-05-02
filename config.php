<?php

/*
 * Return your site-specific config object
 *
 * @return Shib_IConfig
 */
function shib_auth_get_config() {
    return new MyShibConfig();
}

/**
 * Override methods as necessary. getRegistationDetails() and
 * getShibUsername() are usually essential.
 */
class MyShibConfig extends Shib_DefaultConfig {

}
