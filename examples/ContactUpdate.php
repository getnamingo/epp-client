<?php
/**
 * Namingo EPP Client
 *
 * (c) 2023–2026 Namingo Team (https://namingo.org)
 * Based on https://github.com/xpanel/epp-bundle by Lilian Rudenco
 *
 * MIT License
 */

require_once __DIR__ . '/Connection.php';

try
{
    $epp = connect();

    $contactUpdate = $epp->contactUpdate([
        'id'               => 'tembo007',
        'type'             => 'int',
        'firstname'        => 'Svyatoslav',
        'lastname'         => 'Petrenko',
        'companyname'      => 'TOV TEMBO',
        'address1'         => 'vul. Stryiska 100',
        'address2'         => 'kv. 2',
        'city'             => 'Lviv',
        'state'            => 'Lviv',
        'postcode'         => '48000',
        'country'          => 'UA',
        'fullphonenumber'  => '+380.7654321',
        'email'            => 'test@tembo.lviv.ua',
        'authInfoPw'       => 'ABCLviv@345',
    ]);

    if (isset($contactUpdate['error'])) {
        echo 'ContactUpdate Error: ' . $contactUpdate['error'] . PHP_EOL;
        return;
    }

    echo "ContactUpdate Result: {$contactUpdate['code']}: {$contactUpdate['msg']}" . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}