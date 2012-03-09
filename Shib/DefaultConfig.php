<?php

require_once dirname(__FILE__) . '/AbstractConfig.php';

/*
 * Default config class distributed with mod/shib_auth
 */
class Shib_DefaultConfig extends Shib_AbstractConfig {

    /*
     * If true, only users who registered via shib_auth will be able to login
     * via shib_auth.
     *
     * NOTE: Setting this to false would allow a Shibboleth user to login to
     * an existing Elgg account regardless of if they actually owned the account.
     * I.e. The Shibboleth user doesn't need to know the Elgg account's password.
     *
     * If you set this to false or remove it from your config class, be sure
     * to include logic to ensure that the Shibboleth user matches the Elgg user.
     *
     * @var bool
     */
    private $_requireShibAuthMetadata = true;

    public function getRegistationDetails()
    {
        return array(
            'name' => $_SERVER['shib-fullname'],
            'mail' => $_SERVER['shib-mail'],
            'friendGuid' => 0,
        );
    }

    public function sniffUsername()
    {
        $this->username = isset($_SERVER['shib-username'])
            ? $_SERVER['shib-username']
            : '';
    }

    public function belongsToShibUser(ElggUser $user)
    {
        if ($_requireShibAuthMetadata) {
            return (bool) $user->getPrivateSetting('shib_auth');
        } else {
            return true;
        }
    }

    public function postRegister(ElggUser $user)
    {
        $user->setPrivateSetting('shib_auth', '1');
        parent::postRegister($user);
    }

    public function postLogin(ElggUser $user)
    {
        system_message(elgg_echo('loginok'));

        // forward user to same page if coming from Elgg site
        $dest = 'pg/dashboard/';
        if (strpos($this->loginReferer, $GLOBALS['CONFIG']->url)) {
            $dest = $this->loginReferer;
        }
        forward($dest);
    }

    public function postLogout()
    {
        // deletes Elgg and local shib cookies for your SP
        setcookie('Elgg', '', time() - 86400, '/');
        foreach ($_COOKIE as $key => $val) {
            if (0 === strpos($key, '_shib')) {
                setcookie($key, '', time() - 86400, '/');
            }
        }
    }
}
