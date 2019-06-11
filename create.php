<?php
include 'InstaAccountBot.php';

use xosad\InstaAccountBot;

$i = new InstaAccountBot();
if (!empty(file(__DIR__ . '/proxy.txt'))) {
    foreach (file(__DIR__ . '/proxy.txt') as $proxy) {
        echo $i->createAccount(trim($proxy));
        sleep(3);
    }
} else {
    for ($n = 1; $n <= 5; $n++) {
        echo $i->createAccount();
        sleep(3);
    }
}
