**Warning:** This codebase has been overhauled a bit without testing. Use at your own risk :)

**Note:** Rename this folder to `shib_auth` in Elgg's mod directory.

# shib_auth

A basic Shibboleth authorization mod for [Elgg](http://elgg.org/). It does not yet have an admin UI.

## Requirements

Apache with Shibboleth module, such that a directory can be protected with the following `.htaccess` rules:

    AuthType shibboleth
    ShibRequireSession On
    Require valid-user

Within that directory, Apache must expose Shibboleth attributes in `$_SERVER`

## How it works

Shibboleth users must register & login via `<elgg_URL>/shib_auth/login`. When Shibboleth users register, their username is taken from a member of `$_SERVER`, metadata is added to their account so they can be recognized as a shib_auth user in the future, and their password is set to a long random string to make their account inaccessible from other login methods.

To log out, direct users to `<elgg_URL>/shib_auth/logout`.

### Caveats

In the included Shib_DefaultConfig class, the shib_auth private setting is required to login existing users via shib_auth. This is to make sure that Shibboleth users cannot "break" into an existing Elgg account that happens to have the same username. Currently no UI exists to add the private setting to a non-shib user to allow them to login via shib_auth.

## Setup

 1. Place this folder in `<elgg_path>/mod`
 2. Edit config.php to customize your configuration class MyShibConfig, which extends the Shib_DefaultConfig class. Shib_DefaultConfig should give you an idea of what hooks are available during the login & logout processes, and see the Shib_Core class to see in what order they're called.

    You'll definitely need to override getRegistationDetails() and getShibUsername() so they return the right keys from $_SERVER.

 3. Send Shibboleth users to `<elgg_URL>/shib_auth/login` to log in or register, and to `<elgg_URL>/shib_auth/logout` to log out.
 4. You should probably recommend upon logging out that the user completely exit their browser. Otherwise they may remain logged in at the shibboleth IdP site.

### TODO

Remove the requirement that the Elgg username must match a member of $_SERVER provided by Shibboleth. I.e. A valid Shibboleth user could create an Elgg account with a username of their choosing. Or an existing Elgg user could choose to have their account associated with a particular Shibboleth attribute, allowing them to use Shibboleth as an alternative or replacement auth method.