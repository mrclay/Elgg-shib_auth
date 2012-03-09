<?php

/*
 * Class used as configuration for Shib->validate()
 */
abstract class Shib_AbstractConfig {

    /*
     * Current (or desired) Elgg username. This should be set by sniffUsername().
     *
     * @var string
     */
    public $username = '';

    /*
     * Should Elgg logins be persistent?
     *
     * @var bool
     */
    public $persistentLogins = false;

    /*
     * URL given as HTTP Referer upon beginning of login process. This is
     * populated by Shib::validate() in case your config methods need it.
     *
     * @var string
     */
    public $loginReferer = '';

    public $loginErrors = array();

    public $registrationErrors = array();

    /*
     * Must set $this->username. E.g. based on Shibboleth attributes
     *
     * E.g.
     * <code>
     * $this->username = $_SERVER['shib-uid'];
     * </code>
     *
     * @return null
     */
    abstract public function sniffUsername();

    /*
     * Must return an array w/ keys 'name', 'mail', and 'friendGuid' to be passed
     * to Elgg's register_user() function.
     *
     * E.g.
     * <code>
     * return array(
     *     'name' => $_SERVER['shib-fullName'],
     *     'mail' => $_SERVER['shib-mail'],
     *     'friendGuid' => 0, // default
     * );
     * </code>
     *
     * @return array
     */
    abstract public function getRegistationDetails();


    /*
     * Does the ElggUser found actually belong to the Shibboleth user?
     *
     * This is called after Shib->validate() finds an Elgg user that matches
     * $this->username.
     *
     * @param ElggUser $user
     * @return bool
     */
    public function belongsToShibUser(ElggUser $user)
    {
        return true;
    }

    /*
     * Called if the Elgg user fails belongsToShibUser()
     *
     * @param ElggUser $user
     */
    public function onInvalidUser(ElggUser $user)
    {
        register_error("The system failed to log you in as '" . $this->username . "'."
            . " Please ask your site administrator for assistance.");
        forward();
    }

    /*
     * Called if is_loggedin() is true at very beginning of Shib->validate()
     */
    public function loggedInAlready()
    {
        forward('pg/dashboard/');
    }

    /*
     * Called after a login (not during registration)
     *
     * @param ElggUser $user
     */
    public function postLogin(ElggUser $user)
    {
        system_message(elgg_echo('loginok'));
        forward('pg/dashboard/');
    }

    /*
     * Called after a user is registered
     *
     * @param ElggUser $user
     */
    public function postRegister(ElggUser $user)
    {
        global $CONFIG;
        system_message(sprintf(elgg_echo("registerok"), $CONFIG->sitename));
        forward('pg/dashboard/');
    }

    /*
     * Called if register_user() fails
     */
    public function onRegistrationFailure()
    {
        register_error("The system failed to register you as '" . $this->username . "'."
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
     * Called before Elgg's logout() is called (if user was logged in)
     *
     * @param ElggUser $user
     */
    public function preLogout(ElggUser $user)
    {

    }

    /*
     * Called after logout by Shib::logout() (if user was logged in)
     */
    public function postLogout()
    {
        forward();
    }
}
