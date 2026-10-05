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

/**
 * To run this demo using a second test account:
 *
 * Replace the include:
 *    require_once __DIR__ . '/Connection.php';
 *  with:
 *    require_once __DIR__ . '/Connection2.php';
 *
 * And update the connection call:
 *    $epp = connect();
 * to:
 *    $epp = connect2();
 *
 * This allows testing with two separate credentials
 * without changing the code logic.
 */

require_once __DIR__ . '/Connection.php';

try
{
    $epp = connect();

    $params = [
        'domainname' => 'test.example',
        'period'     => 1,

        // ------------------------------------------------------------------
        // [A] CONTACTS
        // ------------------------------------------------------------------
        //
        // If the registry DOES support registrant/admin/tech/billing contacts,
        // keep or adjust this block.
        //
        // If the registry does NOT use contacts, DELETE or COMMENT OUT
        // the whole section below.
        //
        'registrant' => 'tembo007',
        'contacts' => [
            'admin'   => 'tembo007',
            'tech'    => 'tembo007',
            'billing' => 'tembo007',
        ],

        // ------------------------------------------------------------------
        // AUTH-INFO PASSWORD
        // ------------------------------------------------------------------
        'authInfoPw' => 'Domainpw123@',

        // ------------------------------------------------------------------
        // ENCODED SIGNED MARK
        // ------------------------------------------------------------------
        'encodedSignedMark' => 'INSERT_HERE'
    ];

    $domainCreateSunrise = $epp->domainCreateSunrise($params);

    if (array_key_exists('error', $domainCreateSunrise))
    {
        echo 'DomainCreateSunrise Error: ' . $domainCreateSunrise['error'] . PHP_EOL;
    }
    else
    {
        echo 'DomainCreateSunrise Result: ' . $domainCreateSunrise['code'] . ': ' . $domainCreateSunrise['msg'] . PHP_EOL;
        echo 'New Domain: ' . $domainCreateSunrise['name'] . PHP_EOL;
        echo 'Created On: ' . $domainCreateSunrise['crDate'] . PHP_EOL;
        echo 'Expires On: ' . $domainCreateSunrise['exDate'] . PHP_EOL;
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