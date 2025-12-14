<?php
/**
 * Tembo EPP client test file
 *
 * Written in 2023-2025 by Taras Kondratyuk (https://namingo.org)
 * Based on xpanel/epp-bundle written in 2019 by Lilian Rudenco (info@xpanel.com)
 *
 * @license MIT
 */

require_once __DIR__ . '/Connection.php';

try
{
    $epp = connect();

    $contactCreate = $epp->contactCreate([
        'id'               => 'tembo007',
        'type'             => 'int',
        'firstname'        => 'Svyatoslav',
        'lastname'         => 'Petrenko',
        'companyname'      => 'TOV TEMBO',
        'address1'         => 'vul. Stryiska 1',
        'address2'         => 'kv. 1',
        'city'             => 'Lviv',
        'state'            => 'Lviv',
        'postcode'         => '48000',
        'country'          => 'UA',
        'fullphonenumber'  => '+380.1234567',
        'email'            => 'test@tembo.ua',
        'authInfoPw'       => 'ABCLviv@345',
        // 'euType'   => 'tech',
        // 'nin_type' => 'person',
        // 'nin'      => '1234567789',
    ]);

    if (isset($contactCreate['error'])) {
        echo 'ContactCreate Error: ' . $contactCreate['error'] . PHP_EOL;
        return;
    }

    echo 'ContactCreate Result: '
        . $contactCreate['code'] . ': '
        . $contactCreate['msg'] . PHP_EOL
        . 'New Contact ID: '
        . ($contactCreate['id'] ?? 'unknown') . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}