<?php
/**
 * Namingo EPP Client
 *
 * (c) 2023–2026 Namingo Team (https://namingo.org)
 * Based on https://github.com/xpanel/epp-bundle by Lilian Rudenco
 *
 * MIT License
 */

require_once __DIR__ . '/Connection.php';

try
{
    $epp = connect();

    // Default behaviour:
    // - Replace IP: provide both currentipaddress and newipaddress
    //
    // If supported by the registry:
    // - Add only: provide newipaddress only
    // - Remove only: provide currentipaddress only
    $hostUpdate = $epp->hostUpdate([
        'hostname'          => 'ns1.test.example',
        'currentipaddress'  => '8.8.8.8',
        'newipaddress'      => '4.4.4.4',
    ]);

    if (isset($hostUpdate['error'])) {
        echo 'HostUpdate Error: ' . $hostUpdate['error'] . PHP_EOL;
        return;
    }

    echo "HostUpdate Result: {$hostUpdate['code']}: {$hostUpdate['msg']}" . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}