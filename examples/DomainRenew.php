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

    $domainRenew = $epp->domainRenew([
        'domainname' => 'test.example',
        'regperiod'  => 1,
    ]);

    if (isset($domainRenew['error'])) {
        echo 'DomainRenew Error: ' . $domainRenew['error'] . PHP_EOL;
        return;
    }

    echo "DomainRenew result: {$domainRenew['code']}: {$domainRenew['msg']}" . PHP_EOL;

    $fields = [
        'Domain Name'          => 'name',
        'New Expiration Date'  => 'exDate',
    ];

    foreach ($fields as $label => $key) {
        if (isset($domainRenew[$key]) && $domainRenew[$key] !== '') {
            echo "{$label}: {$domainRenew[$key]}" . PHP_EOL;
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