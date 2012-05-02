<?php

interface Shib_IConfig {

    public function setCore(Shib_Core $core);

    /*
     * This should be based on Shibboleth attributes.
     *
     * @return string
     */
    public function getShibUsername();

    /*
     * Should Elgg logins be persistent?
     *
     * @return bool
     */
    public function getLoginPersistent();

    /*
     * @return bool
     */
    public function getAllowAccountsWithSameEmail();

    /*
     * @return Shib_RegDetails
     */
    public function getRegistationDetails();

    /*
     * Does the ElggUser found actually belong to the Shibboleth user?
     *
     * This is called after Shib->validate() finds an Elgg user that matches
     * $this->username.
     *
     * @param ElggUser $user
     * @return bool
     */
    public function belongsToShibUser(ElggUser $user);

    /*
     * Called if the Elgg user fails belongsToShibUser()
     *
     * @param ElggUser $user
     */
    public function onInvalidUser(ElggUser $user);


    /*
     * Called if is_loggedin() is true at very beginning of Shib->validate()
     */
    public function loggedInAlready();

    /*
     * Called after a login (not during registration)
     *
     * @param ElggUser $user
     */
    public function postLogin(ElggUser $user);

    /*
     * Called after a user is registered
     *
     * @param ElggUser $user
     */
    public function postRegister(ElggUser $user);

    /*
     * Called if register_user() fails
     */
    public function onRegistrationFailure();

    /*
     * Called if sniffUsername() fails to populate $this->username
     */
    public function onEmptyUsername();

    /*
     * During registration, called if getRegistationDetails() doesn't return a 'mail'
     */
    public function onEmptyRegistrationMail();

    /*
     * During registration, called if getRegistationDetails() doesn't return a 'name'
     */
    public function onEmptyRegistrationName();

    /*
     * Called before Elgg user is logged in. If you don't want the user to login,
     * redirect away...
     *
     * @param ElggUser $user
     */
    public function preLogin(ElggUser $user);

    /*
     * Called before Elgg's logout() is called (if user was logged in)
     *
     * @param ElggUser $user
     */
    public function preLogout(ElggUser $user);

    /*
     * Called after logout by Shib::logout() (if user was logged in)
     */
    public function postLogout();
}