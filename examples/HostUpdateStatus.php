<?php
/**
 * Tembo EPP client test file (Host Update Status)
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