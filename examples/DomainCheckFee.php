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

    $domainCheckFee = $epp->domainCheckFee([
        'domainname' => 'test.example',
        'currency' => 'EUR',
        'command' => 'create',
        'years' => 1,
    ]);

    if (isset($domainCheckFee['error'])) {
        echo 'domainCheckFee Error: ' . $domainCheckFee['error'] . PHP_EOL;
        return;
    }

    echo "domainCheckFee result: {$domainCheckFee['code']}: {$domainCheckFee['msg']}" . PHP_EOL;

    foreach (($domainCheckFee['domains'] ?? []) as $i => $domain) {
        $name  = $domain['name'] ?? 'unknown';
        $avail = filter_var($domain['avail'] ?? false, FILTER_VALIDATE_BOOL);
        $reason = $domain['reason'] ?? 'no reason given';

        if ($avail) {
            echo 'Domain ' . ($i + 1) . ": {$name} is available. {$reason}" . PHP_EOL;
            continue;
        }

        echo 'Domain ' . ($i + 1) . ": {$name} is not available because: {$reason}" . PHP_EOL;
    }

    $fields = [
        'Domain Name'      => 'domain',
        'Domain Fee'    => 'feeAmount',
        'Fee Currency'    => 'currency',
        'Domain Class'     => 'feeClass',
    ];

    foreach ($fields as $label => $key) {
        if (isset($domainCheckFee[$key]) && $domainCheckFee[$key] !== '') {
            echo "{$label}: {$domainCheckFee[$key]}" . PHP_EOL;
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