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

    $hostInfo = $epp->hostInfo([
        'hostname' => 'ns1.test.example',
    ]);

    if (isset($hostInfo['error'])) {
        echo 'HostInfo Error: ' . $hostInfo['error'] . PHP_EOL;
        return;
    }

    echo "HostInfo Result: {$hostInfo['code']}: {$hostInfo['msg']}" . PHP_EOL;
    echo 'Name: ' . ($hostInfo['name'] ?? 'unknown') . PHP_EOL;

    /**
     * Normalize status:
     * - sometimes it's a list: ['ok', 'linked']
     * - sometimes it's a map: ['ok' => '...desc...', 'linked' => '...desc...']
     */
    if (!empty($hostInfo['status'])) {
        $status = [];
        foreach ((array) $hostInfo['status'] as $k => $v) {
            $status[] = is_int($k) ? $v : "{$k}: {$v}";
        }
        echo 'Status: ' . implode(', ', $status) . PHP_EOL;
    }

    /**
     * Normalize addr:
     * - sometimes it's a list: ['8.8.8.8', '1.1.1.1']
     * - sometimes it's a map: ['v4' => '8.8.8.8', 'v6' => '2001:db8::1']
     */
    if (!empty($hostInfo['addr'])) {
        $addr = [];
        foreach ((array) $hostInfo['addr'] as $k => $v) {
            $addr[] = is_int($k) ? $v : "{$k}: {$v}";
        }
        echo 'Addr: ' . implode(', ', $addr) . PHP_EOL;
    }

    $fields = [
        'Current Registrar' => 'clID',
        'Original Registrar' => 'crID',
        'Created On' => 'crDate',
        'Updated By' => 'upID',
        'Updated On' => 'upDate',
    ];

    foreach ($fields as $label => $key) {
        if (isset($hostInfo[$key]) && $hostInfo[$key] !== '') {
            echo "{$label}: {$hostInfo[$key]}" . PHP_EOL;
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