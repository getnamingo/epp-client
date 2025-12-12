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

    $domainUpdateAuthinfo = $epp->domainUpdateAuthinfo([
        'domainname' => 'test.example',
        'authInfo'   => 'P@ssword123!',
    ]);

    if (isset($domainUpdateAuthinfo['error'])) {
        echo 'DomainUpdateAuthinfo Error: ' . $domainUpdateAuthinfo['error'] . PHP_EOL;
        return;
    }

    echo "DomainUpdateAuthinfo result: {$domainUpdateAuthinfo['code']}: {$domainUpdateAuthinfo['msg']}" . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}