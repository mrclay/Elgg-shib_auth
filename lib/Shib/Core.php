<?php

class Shib_Core {

    protected $loginReferer = '';

    /**
     * @return string
     */
    public function getLoginReferer()
    {
        return $this->loginReferer;
    }

    public function validate(Shib_IConfig $conf)
    {
        $conf->setCore($this);

        if (isset($_SESSION['ELGG_SHIB_AUTH_REFERER'])) {
            $this->loginReferer = $_SESSION['ELGG_SHIB_AUTH_REFERER'];
            unset($_SESSION['ELGG_SHIB_AUTH_REFERER']);
        }

        if (elgg_is_logged_in()) {
            $conf->loggedInAlready();
        }
        
        $shibUser = $conf->getShibUsername();

        if (empty($shibUser)) {
            return $conf->onEmptyUsername();
        }

        $user = get_user_by_username($shibUser);
        if ($user) {
            $isValid = $conf->belongsToShibUser($user);
            if ($isValid) {
                $conf->preLogin($user);
                login($user, $conf->getLoginPersistent());
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
                $conf->getAllowAccountsWithSameEmail(),
                $regDetails['friendGuid']
            );
        }
        if ($guid) {
            $user = get_user($guid); // http://reference.elgg.org/lib_2users_8php.html#893f378cc151ca0a9ca94640b18b086a
            login($user, $conf->getLoginPersistent()); // http://reference.elgg.org/sessions_8php.html#f3098385f445c6f8136ab7e4ce7819c9
            return $conf->postRegister($user);
        } else {
            // user couldn't be registered!
            return $conf->onRegistrationFailure();
        }
    }

    public function logout(Shib_IConfig $conf)
    {
        $conf->setCore($this);

        if (elgg_is_logged_in()) {
            $conf->preLogout(elgg_get_logged_in_user_entity());
            logout();
            $conf->postLogout();
        }
        forward();
    }
}
