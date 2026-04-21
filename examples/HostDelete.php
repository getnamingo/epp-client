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

    $hostDelete = $epp->hostDelete([
        'hostname' => 'ns1.test.example',
    ]);

    if (isset($hostDelete['error'])) {
        echo 'HostDelete Error: ' . $hostDelete['error'] . PHP_EOL;
        return;
    }

    echo "HostDelete result: {$hostDelete['code']}: {$hostDelete['msg']}" . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}