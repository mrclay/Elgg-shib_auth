**Note:** Rename this folder to `shib_auth` in Elgg's mod directory.

# shib_auth

A basic Shibboleth authorization mod for [Elgg](http://elgg.org/). It does not yet have an admin UI.

It adds a "Login with Shibboleth" button to the login form which routes the user through a Shibboleth authentication.

## Requirements

You must be able to write a configuration class in PHP and handle an Elgg [plugin hook](http://docs.elgg.org/wiki/Plugin_Hooks). See "Setup" below for more details.

Apache with Shibboleth module, such that a directory can be protected with the following `.htaccess` rules:

    AuthType shibboleth
    ShibRequireSession On
    Require valid-user

Within that directory, Apache must expose Shibboleth attributes in `$_SERVER`

## How it works

Shibboleth users must register & login via `<elgg_URL>/shib_auth/login`. When Shibboleth users register, their username is taken from a member of `$_SERVER`, metadata is added to their account so they can be recognized as a shib_auth user in the future, and their password is set to a long random string to make their account inaccessible from other login methods.

To log out, direct users to `<elgg_URL>/shib_auth/logout`.

### Caveats

In the included Shib_DefaultConfig class (meant only for you to extend for your convenience), the shib_auth private setting is required to login existing users via shib_auth. This is to make sure that Shibboleth users cannot "break" into an existing Elgg account that happens to have the same username. Currently no UI exists to add the private setting to a non-shib user to allow them to login via shib_auth.

## Setup

 1. Place this folder in `<elgg_path>/mod`
 2. Create a configuration class which extends the Shib_DefaultConfig class. Shib_DefaultConfig should give you an idea of what hooks are available during the login & logout processes, and see the Shib_Core class to see in what order they're called.

    You'll definitely need to override getRegistationDetails() and getShibUsername() so they return the right keys from $_SERVER.

 3. In a separate plugin, implement the plugin hook `[shib_auth:fetch, config]` and in the handler return an instance of your config class.
 4. Enable this plugin.
 5. Send Shibboleth users to `<elgg_URL>/shib_auth/login` to log in or register, and to `<elgg_URL>/shib_auth/logout` to log out.
 6. Recommended: Upon logging out, direct the user to completely exit their browser. Otherwise he/she may remain logged in at the shibboleth IdP site.

### TODO

Remove the requirement that the Elgg username must match the Shibboleth-supplied username. I.e. A valid Shibboleth user could create an Elgg account with a username of their choosing. Or an existing Elgg user could choose to have their account associated with a particular Shibboleth user, allowing them to add Shibboleth as an alternative auth method.