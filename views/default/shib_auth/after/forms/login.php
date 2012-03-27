<p><?php

echo elgg_view('output/url', array(
    'text' => elgg_echo('shib_auth:login_link_text'),
    'href' => 'shib_auth/login',
    'class' => 'elgg-button elgg-button-submit',
));

?></p>

