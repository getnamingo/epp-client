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

    $hostCheck = $epp->hostCheck([
        'hostname' => 'ns1.test.example',
    ]);

    if (isset($hostCheck['error'])) {
        echo 'HostCheck Error: ' . $hostCheck['error'] . PHP_EOL;
        return;
    }

    echo "HostCheck result: {$hostCheck['code']}: {$hostCheck['msg']}" . PHP_EOL;

    foreach (($hostCheck['hosts'] ?? []) as $i => $host) {
        $label = $host['name'] ?? $host['id'] ?? 'unknown';
        $avail = filter_var($host['avail'] ?? false, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $avail = $avail ?? ((int)($host['avail'] ?? 0) === 1); // fallback

        if ($avail) {
            echo 'Host ' . ($i + 1) . ": {$label} is available" . PHP_EOL;
        } else {
            $reason = $host['reason'] ?? 'no reason given';
            echo 'Host ' . ($i + 1) . ": {$label} is not available because: {$reason}" . PHP_EOL;
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