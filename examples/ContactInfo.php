<?php

/*
 * Namingo EPP Client
 *
 * Copyright (c) 2023-2026 Taras Kondratyuk
 * Copyright (c) 2025-2026 Namingo contributors
 * Copyright (c) 2026 Terbora Ltd.
 *
 * Based in part on xpanel/epp-bundle
 * Copyright (c) 2017 Lilian Rudenco
 * https://github.com/xpanel/epp-bundle
 *
 * Licensed under the MIT License.
 * See the LICENSE file distributed with this software for the full license text.
 *
 * SPDX-License-Identifier: MIT
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

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}