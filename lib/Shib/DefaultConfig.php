<?php

/*
 * Default config class distributed with mod/shib_auth
 */
class Shib_DefaultConfig implements Shib_IConfig {

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
    protected $_requireShibAuthFlag = true;

    /**
     * @var Shib_Core
     */
    protected $core;

    public function getLoginPersistent()
    {
        return false;
    }

    public function getAllowAccountsWithSameEmail()
    {
        return false;
    }

    public function getRegistationDetails()
    {
        $details = new Shib_RegDetails();
        $details->name = $_SERVER['shib-fullname'];
        $details->mail = $_SERVER['shib-mail'];
        return $details;
    }

    public function getShibUsername()
    {
        return isset($_SERVER['shib-uid'])
            ? $_SERVER['shib-uid']
            : '';
    }

    public function setCore(Shib_Core $core)
    {
        $this->core = $core;
    }

    public function belongsToShibUser(ElggUser $user)
    {
        if ($this->_requireShibAuthFlag) {
            return (bool) $user->getPrivateSetting('shib_auth');
        } else {
            return true;
        }
    }

    public function postRegister(ElggUser $user)
    {
        $user->setPrivateSetting('shib_auth', '1');
        system_message(elgg_echo("registerok", array(elgg_get_site_entity()->name)));
    }

    public function postLogin(ElggUser $user)
    {
        system_message(elgg_echo('loginok'));
    }

    public function postLogout()
    {
        $this->core->removeSpCookies();
    }

    /*
     * Called if the Elgg user fails belongsToShibUser()
     *
     * @param ElggUser $user
     */
    public function onInvalidUser(ElggUser $user)
    {
        register_error("The system failed to log you in as '" . $this->getShibUsername() . "'."
            . " Please ask your site administrator for assistance.");
        forward();
    }

    /*
     * Called if is_loggedin() is true at very beginning of Shib->validate()
     */
    public function loggedInAlready()
    {
        forward('activity');
    }

    /*
     * Called if register_user() fails
     */
    public function onRegistrationFailure()
    {
        register_error("The system failed to register you as '" . $this->getShibUsername() . "'."
            . " Please ask your site administrator for assistance.");
        forward();
    }

    /*
     * Called if sniffUsername() fails to populate $this->username
     */
    public function onEmptyUsername()
    {
        register_error("Shibboleth is not correctly configured."
            . " Please ask your site administrator for assistance.");
        forward();
    }

    /*
     * During registration, called if getRegistationDetails() doesn't return a 'mail'
     */
    public function onEmptyRegistrationMail()
    {
        register_error("Shibboleth is not correctly configured to include your e-mail address.");
    }

    /*
     * During registration, called if getRegistationDetails() doesn't return a 'name'
     */
    public function onEmptyRegistrationName()
    {
        register_error("Shibboleth is not correctly configured to include your name.");
    }

    /*
     * Called before Elgg user is logged in. If you don't want the user to login,
     * redirect away...
     *
     * @param ElggUser $user
     */
    public function preLogin(ElggUser $user)
    {

    }

    /*
     * Called before Elgg's logout() is called (if user was logged in)
     *
     * @param ElggUser $user
     */
    public function preLogout(ElggUser $user)
    {

    }
}
