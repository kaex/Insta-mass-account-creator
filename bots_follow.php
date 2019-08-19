<?php
include 'InstaAccountBot.php';

use xosad\InstaAccountBot;
$i = new InstaAccountBot();

$accounts = json_decode(file_get_contents('accounts.json'), true);

if (file_exists(__DIR__ . '/follow.txt') && !empty(file(__DIR__ . '/follow.txt'))) {
    $follow = file(__DIR__ . '/follow.txt');
}else{
    $json_data =
        [
            'status' => false,
            'message' => 'follow.txt is missing'
        ];
    echo json_encode($json_data);
}
$follow = array_map('trim', $follow);

foreach ($accounts as $account)
{
    echo $i->loginAndFollow($account['username'] , $account['password'] , $account['registered_proxy'] , $follow);
}
