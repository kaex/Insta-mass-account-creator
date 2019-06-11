<?php
include 'InstaAccountBot.php';

use xosad\InstaAccountBot;
$i = new InstaAccountBot();

$accounts = json_decode(file_get_contents('accounts.json'), true);

$follow =
    [
        'username',
        'username2',
        'username3',
    ];

foreach ($accounts as $account)
{
    var_dump($i->loginAndFollow($account['username'] , $account['password'] , $account['registered_proxy'] , $follow));
}
