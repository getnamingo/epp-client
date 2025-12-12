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
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}