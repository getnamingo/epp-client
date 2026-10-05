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

    $domainTransfer = $epp->domainTransfer([
        'domainname' => 'test.example',      // Fully-qualified domain name to be transferred
        'years'      => 1,                   // Number of years to extend the domain upon successful transfer (usually 1)
        'authInfoPw' => 'Domainpw123@',      // Domain authorization (EPP transfer) password
        'op'         => 'request',           // Transfer operation: request | query | cancel | reject | approve
    ]);

    if (isset($domainTransfer['error'])) {
        echo 'DomainTransfer Error: ' . $domainTransfer['error'] . PHP_EOL;
        return;
    }

    if (isset($domainTransfer['code'], $domainTransfer['msg'])) {
        echo "DomainTransfer Result: {$domainTransfer['code']}: {$domainTransfer['msg']}" . PHP_EOL;
    }

    $fields = [
        'Name'                    => 'name',
        'Transfer Status'         => 'trStatus',
        'Gaining Registrar'       => 'reID',
        'Requested On'            => 'reDate',
        'Losing Registrar'        => 'acID',
        'Transfer Confirmed On'   => 'acDate',
        'New Expiration Date'     => 'exDate',
    ];

    foreach ($fields as $label => $key) {
        if (isset($domainTransfer[$key]) && $domainTransfer[$key] !== '') {
            echo "{$label}: {$domainTransfer[$key]}" . PHP_EOL;
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