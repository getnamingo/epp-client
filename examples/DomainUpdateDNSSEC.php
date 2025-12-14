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

    $domainUpdateDNSSEC = $epp->domainUpdateDNSSEC([
        'domainname'   => 'test.example',

        // Operation:
        // - add    : add a DS record
        // - rem    : remove a DS record
        // - addrem : replace existing DS record(s)
        'command'      => 'add',

        'keyTag_1'     => 33409,
        'alg_1'        => 8,
        'digestType_1' => 1,
        'digest_1'     => 'F4D6E26B3483C3D7B3EE17799B0570497FAF33BCB12B9B9CE573DDB491E16948',
    ]);

    if (isset($domainUpdateDNSSEC['error'])) {
        echo 'DomainUpdateDNSSEC Error: ' . $domainUpdateDNSSEC['error'] . PHP_EOL;
        return;
    }

    echo "DomainUpdateDNSSEC result: {$domainUpdateDNSSEC['code']}: {$domainUpdateDNSSEC['msg']}" . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}