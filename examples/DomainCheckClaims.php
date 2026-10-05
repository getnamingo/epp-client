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

    $domainCheckClaims = $epp->domainCheckClaims([
        'domainname' => 'test.example',
    ]);

    if (isset($domainCheckClaims['error'])) {
        echo 'DomainCheckClaims Error: ' . $domainCheckClaims['error'] . PHP_EOL;
        return;
    }

    echo "DomainCheckClaims result: {$domainCheckClaims['code']}: {$domainCheckClaims['msg']}" . PHP_EOL;

    $fields = [
        'Domain Name'      => 'domain',
        'Domain Status'    => 'status',
        'Domain Phase'     => 'phase',
        'Domain Claim Key' => 'claimKey',
    ];

    foreach ($fields as $label => $key) {
        if (isset($domainCheckClaims[$key]) && $domainCheckClaims[$key] !== '') {
            echo "{$label}: {$domainCheckClaims[$key]}" . PHP_EOL;
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