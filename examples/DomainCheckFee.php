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

    $domainCheckFee = $epp->domainCheckFee([
        'domainname' => 'test.example',
        'currency' => 'EUR',
        'command' => 'create',
        'years' => 1,
    ]);

    if (isset($domainCheckFee['error'])) {
        echo 'domainCheckFee Error: ' . $domainCheckFee['error'] . PHP_EOL;
        return;
    }

    echo "domainCheckFee result: {$domainCheckFee['code']}: {$domainCheckFee['msg']}" . PHP_EOL;

    foreach (($domainCheckFee['domains'] ?? []) as $i => $domain) {
        $name  = $domain['name'] ?? 'unknown';
        $avail = filter_var($domain['avail'] ?? false, FILTER_VALIDATE_BOOL);
        $reason = $domain['reason'] ?? 'no reason given';

        if ($avail) {
            echo 'Domain ' . ($i + 1) . ": {$name} is available. {$reason}" . PHP_EOL;
            continue;
        }

        echo 'Domain ' . ($i + 1) . ": {$name} is not available because: {$reason}" . PHP_EOL;
    }

    $fields = [
        'Domain Name'      => 'domain',
        'Domain Fee'    => 'feeAmount',
        'Fee Currency'    => 'currency',
        'Domain Class'     => 'feeClass',
    ];

    foreach ($fields as $label => $key) {
        if (isset($domainCheckFee[$key]) && $domainCheckFee[$key] !== '') {
            echo "{$label}: {$domainCheckFee[$key]}" . PHP_EOL;
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