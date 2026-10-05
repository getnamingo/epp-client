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

    $domainUpdateDNSSEC = $epp->domainUpdateDNSSEC([
        'domainname'   => 'test.example',

        // Operation:
        // - add    : add a DS record
        // - rem    : remove a DS record
        // - addrem : replace existing DS record(s)
        'command'      => 'add',

        'keyTag_1'     => 33409,
        'alg_1'        => 8,
        'digestType_1' => 1,
        'digest_1'     => 'F4D6E26B3483C3D7B3EE17799B0570497FAF33BCB12B9B9CE573DDB491E16948',
    ]);

    if (isset($domainUpdateDNSSEC['error'])) {
        echo 'DomainUpdateDNSSEC Error: ' . $domainUpdateDNSSEC['error'] . PHP_EOL;
        return;
    }

    echo "DomainUpdateDNSSEC result: {$domainUpdateDNSSEC['code']}: {$domainUpdateDNSSEC['msg']}" . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}