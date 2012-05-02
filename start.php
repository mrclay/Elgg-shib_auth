<?php

function shib_auth_init() {
    spl_autoload_register('shib_auth_loader');

    elgg_register_page_handler('shib_auth', 'shib_auth_page');

    elgg_register_event_handler('logout', 'user', 'shib_auth_handle_logout');

    elgg_extend_view('forms/login', 'shib_auth/after/forms/login', 501);
}

function shib_auth_page($page) {
    if (isset($page[0])) {
        if ($page[0] === 'login') {
            // We initiate the login session at this URL because we know a Shibboleth
            // redirect will never interfere. This allows us to store the real HTTP
            // Referer without fear of picking up a URL at the IdP.
            //
            // If you don't need to know which URL referrer the user to login, you
            // can save a redirect by sending the user directly to 'mod/shib_auth/validate/'.

            // available via $this->loginReferer in your config object
            if (isset($_GET['referer'])) {
                $_SESSION['ELGG_SHIB_AUTH_REFERER'] = (string) $_GET['referer'];
            } elseif (! empty($_SERVER['HTTP_REFERER'])) {
                $_SESSION['ELGG_SHIB_AUTH_REFERER'] = $_SERVER['HTTP_REFERER'];
            }

            // forward to URL protected by Shibboleth module (may run index.php or forward to IdP).
            // HTTPS is forced because IdP may not allow redirects to an insecure endpoint that's
            // not listed in Metadata
            forward(str_replace('http://', 'https://', elgg_get_site_url() . 'mod/shib_auth/validate/'));
        } elseif ($page[0] === 'logout') {
            elgg_unregister_event_handler('logout', 'user', 'shib_auth_handle_logout');
            shib_auth_execute_method('logout');
        }
    }
}

function shib_auth_execute_method($name) {
    if (! function_exists('shib_auth_get_config')) {
        require dirname(__FILE__) . '/config.php';
    }
    $core = new Shib_Core();
    $core->$name(shib_auth_get_config());
}

function shib_auth_loader($className) {
    if (0 === strpos($className, 'Shib_')) {
        $path = dirname(__FILE__) . '/lib/' . str_replace(array('_', '\\'), '/', $className) . '.php';
        if (is_readable($path)) {
            require $path;
        }
    }
}

/**
 * @param string $event
 * @param string $type
 * @param object $user
 * @return bool
 */
function shib_auth_handle_logout($event, $type, $user) {
    forward('shib_auth/logout');
}

elgg_register_event_handler('init', 'system', 'shib_auth_init');
