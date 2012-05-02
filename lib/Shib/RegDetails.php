<?php

/**
 * Value object for info sniffed from shibboleth
 */
class Shib_RegDetails
{
    /**
     * @var string Elgg display name
     */
    public $name = '';

    /**
     * @var string e-mail address
     */
    public $mail = '';

    /**
     * @var int GUID of friend of new user
     */
    public $friendGuid = 0;
}
