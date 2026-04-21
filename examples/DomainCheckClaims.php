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

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}