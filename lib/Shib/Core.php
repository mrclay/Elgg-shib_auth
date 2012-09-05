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

        if (!empty($_SESSION['last_forward_from'])) {
            $this->loginReferer = $_SESSION['last_forward_from'];
            unset($_SESSION['last_forward_from']);
        } elseif (!empty($_SESSION['ELGG_SHIB_AUTH_REFERER'])) {
            $this->loginReferer = $_SESSION['ELGG_SHIB_AUTH_REFERER'];
        }
        if (!empty($_SESSION['ELGG_SHIB_AUTH_REFERER'])) {
            unset($_SESSION['ELGG_SHIB_AUTH_REFERER']);
        }

        if (elgg_is_logged_in()) {
            $conf->loggedInAlready();
        }
        
        $shibUser = $conf->getShibUsername();

        if (empty($shibUser)) {
            $conf->onEmptyUsername();
            return null;
        }

        $user = get_user_by_username($shibUser);
        if ($user) {
            $isValid = $conf->belongsToShibUser($user);
            if ($isValid) {
                $conf->preLogin($user);
                login($user, $conf->getLoginPersistent());
                $conf->postLogin($user);
                $this->forwardToReferer();
            } else {
                $conf->onInvalidUser($user);
                return null;
            }
        }
        
        // create account
        
        $regDetails = $conf->getRegistationDetails();
        /* @var Shib_RegDetails $regDetails */
        if (empty($regDetails->mail) || empty($regDetails->name)) {
            // uh oh
            $guid = false;
            if (empty($regDetails->mail)) {
                $conf->onEmptyRegistrationMail();
            }
            if (empty($regDetails->name)) {
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
                $regDetails->name,
                $regDetails->mail,
                $conf->getAllowAccountsWithSameEmail(),
                $regDetails->friendGuid
            );
        }
        if ($guid) {
            $user = get_user($guid); // http://reference.elgg.org/lib_2users_8php.html#893f378cc151ca0a9ca94640b18b086a
            login($user, $conf->getLoginPersistent()); // http://reference.elgg.org/sessions_8php.html#f3098385f445c6f8136ab7e4ce7819c9
            $conf->postRegister($user);
            $this->forwardToReferer();
        } else {
            // user couldn't be registered!
            $conf->onRegistrationFailure();
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

    /**
     * @param string $referer
     * @param bool $mustBeWithinSite
     */
    public function forwardToReferer($referer = null, $mustBeWithinSite = true)
    {
        $dest = '';
        if (! $referer) {
            $referer = $this->getLoginReferer();
        }
        if ($mustBeWithinSite) {
            $pattern = '@^' . preg_quote(elgg_get_site_url(), '@') . '(.+)@';
            $pattern = str_replace(array('@^http\\:', '@^https\\:'), '@^https?\\:', $pattern);
            if (preg_match($pattern, $referer, $m)) {
                $dest = $m[1];
            }
        } else {
            $dest = $referer;
        }
        forward($dest);
    }

    public function removeSpCookies()
    {
        foreach ($_COOKIE as $key => $val) {
            if (0 === strpos($key, '_shib')) {
                setcookie($key, ini_get('session.cookie_domain'), time() - 86400, '/');
            }
        }
    }
}
