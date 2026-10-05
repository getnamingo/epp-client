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

    $hostUpdateStatus = $epp->hostUpdateStatus([
        'hostname' => 'ns1.test.example',         // Host object name (nameserver)
        'command'  => 'add',                      // add | rem
        'status'   => 'clientUpdateProhibited',   // e.g. clientDeleteProhibited | clientUpdateProhibited
    ]);

    if (isset($hostUpdateStatus['error'])) {
        echo 'HostUpdateStatus Error: ' . $hostUpdateStatus['error'] . PHP_EOL;
        return;
    }

    echo "HostUpdateStatus result: {$hostUpdateStatus['code']}: {$hostUpdateStatus['msg']}" . PHP_EOL;

    $logout = $epp->logout();
    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}