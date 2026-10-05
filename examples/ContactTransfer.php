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

try {
    $epp = connect();

    $contactTransfer = $epp->contactTransfer([
        'contactid'  => 'C-TEST123',        // <-- change to an existing contact ID
        'authInfoPw' => 'Contactpw123@',    // <-- contact authInfo pw (if required by registry)
        'op'         => 'request',          // Transfer operation: request | query | cancel | reject | approve
    ]);

    if (isset($contactTransfer['error'])) {
        echo 'ContactTransfer Error: ' . $contactTransfer['error'] . PHP_EOL;
        return;
    }

    if (isset($contactTransfer['code'], $contactTransfer['msg'])) {
        echo "ContactTransfer Result: {$contactTransfer['code']}: {$contactTransfer['msg']}" . PHP_EOL;
    }

    $fields = [
        'Contact ID'              => 'id',
        'Transfer Status'         => 'trStatus',
        'Gaining Registrar'       => 'reID',
        'Requested On'            => 'reDate',
        'Losing Registrar'        => 'acID',
        'Transfer Confirmed On'   => 'acDate',
    ];

    foreach ($fields as $label => $key) {
        if (isset($contactTransfer[$key]) && $contactTransfer[$key] !== '') {
            echo "{$label}: {$contactTransfer[$key]}" . PHP_EOL;
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