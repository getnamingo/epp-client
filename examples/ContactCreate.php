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