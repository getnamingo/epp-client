<?php
/**
 * Tembo EPP client test file
 *
 * Written in 2023-2025 by Taras Kondratyuk (https://namingo.org)
 * Based on xpanel/epp-bundle written in 2019 by Lilian Rudenco (info@xpanel.com)
 *
 * @license MIT
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
        echo 'Status: ';
        foreach ((array) $hostInfo['status'] as $k => $v) {
            echo is_int($k) ? "{$v}, " : "{$k}: {$v}, ";
        }
        echo PHP_EOL;
    }

    /**
     * Normalize addr:
     * - sometimes it's a list: ['8.8.8.8', '1.1.1.1']
     * - sometimes it's a map: ['v4' => '8.8.8.8', 'v6' => '2001:db8::1']
     */
    if (!empty($hostInfo['addr'])) {
        echo 'Addr: ';
        foreach ((array) $hostInfo['addr'] as $k => $v) {
            echo is_int($k) ? "{$v}, " : "{$k}: {$v}, ";
        }
        echo PHP_EOL;
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
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}