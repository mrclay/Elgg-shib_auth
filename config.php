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

    public $isMaintenanceMode = false;

    protected $_requireShibAuthFlag = false;

    protected $_businessEmailMissing = false;

    public function getAllowAccountsWithSameEmail()
    {
        return true;
    }

    public function getShibUsername()
    {
        return isset($_SERVER['glid']) ? $_SERVER['glid'] : '';
    }

    public function postRegister(ElggUser $user)
    {
        $user->setPrivateSetting('ufid', $_SERVER['ufid']);
    }

    public function postLogout()
    {
        $loc = elgg_get_site_url() . "#loggedOut";
        // non-JS option: "http://" . $_SERVER['SERVER_NAME'] . "/distance-learning/logged-out/";
        setcookie('mdlLastCourse', '', time() - 86400, '/', '.education.ufl.edu');
        $this->core->removeSpCookies();
        forward($loc);
    }

    public function getRegistationDetails()
    {
        $this->_businessEmailMissing = false;
        $glid = $_SERVER['glid'];
        $name = '';
        $mail = '';
        if (! empty($_SERVER['cn'])) {
            $name = $this->_nameFromCn($_SERVER['cn']);
        }
        if (! empty($_SERVER['mail'])) {
            $mail = $_SERVER['mail'];
        }
        if (empty($mail) || empty($name)) {
            // try LDAP
            $ldapService = new ldapRecord();
            $ldapRecord = $ldapService->getUser($glid);
            if (empty($name)) {
                $name = $ldapService->buildFullName($ldapRecord);
            }
            if (empty($mail) && isset($ldapRecord['mail'])) {
                $mail = $ldapRecord['mail'];
            }
        }
        if (empty($mail)) {
            $this->_businessEmailMissing = true;
            $mail = "$glid@ufl.edu";
        }
        return array(
            'name' => $name,
            'mail' => $mail,
            'friendGuid' => 0,
        );
    }

    protected function _nameFromCn($cn)
    {
        if (false !== strpos($cn, ',')) {
            // has comma
            list($last, $first) = explode(',', $cn, 2);
            return trim($first) . ' ' . trim($last);
        } else {
            return trim($cn);
        }
    }
}
