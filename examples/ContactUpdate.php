<?php
/**
 * Tembo EPP client test file
 *
 * Written in 2023-2025 by Taras Kondratyuk (https://namingo.org)
 * Based on xpanel/epp-bundle written in 2019 by Lilian Rudenco (info@xpanel.com)
 *
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Connection.php';

try
{
    $epp = connectEpp('generic');
    
    $params = array(
        'id' => 'tembo007',
        'type' => 'int',
        'firstname' => 'Svyatoslav',
        'lastname' => 'Petrenko',
        'companyname' => 'TOV TEMBO',
        'address1' => 'vul. Stryiska 100',
        'address2' => 'kv. 2',
        'city' => 'Lviv',
        'state' => 'Lviv',
        'postcode' => '48000',
        'country' => 'UA',
        'fullphonenumber' => '+380.7654321',
        'email' => 'test@tembo.lviv.ua',
        'authInfoPw' => 'ABCLviv@345'
    );
    $contactUpdate = $epp->contactUpdate($params);

    if (array_key_exists('error', $contactUpdate))
    {
        echo 'contactUpdate Error: ' . $contactUpdate['error'] . PHP_EOL;
    }
    else
    {
        echo 'contactUpdate Result: ' . $contactUpdate['code'] . ': ' . $contactUpdate['msg'] . PHP_EOL;
    }

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}