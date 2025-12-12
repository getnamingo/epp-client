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

    $contactInfo = $epp->contactInfo([
        'contact' => 'tembo007',
    ]);

    if (isset($contactInfo['error'])) {
        echo 'ContactInfo Error: ' . $contactInfo['error'] . PHP_EOL;
        return;
    }

    echo "ContactInfo Result: {$contactInfo['code']}: {$contactInfo['msg']}" . PHP_EOL;

    $fields = [
        'ID'                  => 'id',
        'ROID'                => 'roid',
        'Name'                => 'name',
        'Org'                 => 'org',
        'Street 1'            => 'street1',
        'Street 2'            => 'street2',
        'Street 3'            => 'street3',
        'City'                => 'city',
        'State'               => 'state',
        'Postal'              => 'postal',
        'Country'             => 'country',
        'Voice'               => 'voice',
        'Fax'                 => 'fax',
        'Email'               => 'email',
        'Current Registrar'   => 'clID',
        'Original Registrar'  => 'crID',
        'Created On'          => 'crDate',
        'Updated By'          => 'upID',
        'Updated On'          => 'upDate',
        'Password'            => 'authInfo',
    ];

    foreach ($fields as $label => $key) {
        if (isset($contactInfo[$key]) && $contactInfo[$key] !== '') {
            echo "{$label}: {$contactInfo[$key]}" . PHP_EOL;
        }
    }

    if (!empty($contactInfo['status'])) {
        foreach ((array) $contactInfo['status'] as $status) {
            echo "Status: {$status}" . PHP_EOL;
        }
    }

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}