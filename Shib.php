<?php

class Shib {

    public function validate(Shib_AbstractConfig $conf)
    {
        if (isset($_SESSION['ELGG_SHIB_AUTH_REFERER'])) {
            $conf->loginReferer = $_SESSION['ELGG_SHIB_AUTH_REFERER'];
            unset($_SESSION['ELGG_SHIB_AUTH_REFERER']);
        }

        if (isloggedin()) {
            $conf->loggedInAlready();
        }

        $conf->sniffUsername();
        $shibUser = $conf->username;

        if (empty($shibUser)) {
            return $conf->onEmptyUsername();
        }

        $user = get_user_by_username($shibUser);
        if ($user) {
            $isValid = $conf->belongsToShibUser($user);
            if ($isValid) {
                login($user, $conf->persistentLogins);
                return $conf->postLogin($user);
            } else {
                return $conf->onInvalidUser($user);
            }
        }

        // create account

        $regDetails = $conf->getRegistationDetails();
        if (! isset($regDetails['friendGuid']) || ! $regDetails['friendGuid']) {
            $regDetails['friendGuid'] = 0;
        }
        if (empty($regDetails['mail']) || empty($regDetails['name'])) {
            // uh oh
            $guid = false;
            if (empty($regDetails['mail'])) {
                $conf->onEmptyRegistrationMail();
            }
            if (empty($regDetails['name'])) {
                $conf->onEmptyRegistrationName();
            }
        } else {
            // unguessable (we don't want this acct accessible via regular login form)
            $password = dechex(mt_rand()) . dechex(mt_rand()) . dechex(mt_rand())
                      . microtime(true);

            // http://reference.elgg.org/lib_2users_8php.html#bb0a317e866cf8c6c4770f6376b56df9
            $guid = register_user(
                $shibUser,
                $password,
                $regDetails['name'],
                $regDetails['mail'],
                false,
                $regDetails['friendGuid']
            );
        }
        if ($guid) {
            $user = get_user($guid); // http://reference.elgg.org/lib_2users_8php.html#893f378cc151ca0a9ca94640b18b086a
            login($user, $conf->$persistentLogins); // http://reference.elgg.org/sessions_8php.html#f3098385f445c6f8136ab7e4ce7819c9
            return $conf->postRegister($user);
        } else {
            // user couldn't be registered!
            return $conf->onRegistrationFailure();
        }
    }

    public function logout(Shib_AbstractConfig $conf)
    {
	    if (isloggedin()) {
            $conf->preLogout(get_loggedin_user());
            logout();
            $conf->postLogout();
        }
        forward();
    }
}
