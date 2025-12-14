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

    $domainCheck = $epp->domainCheck([
        'domains' => ['test.example', 'tembo.example'],
    ]);

    if (isset($domainCheck['error'])) {
        echo 'DomainCheck Error: ' . $domainCheck['error'] . PHP_EOL;
        return;
    }

    echo "DomainCheck result: {$domainCheck['code']}: {$domainCheck['msg']}" . PHP_EOL;

    foreach (($domainCheck['domains'] ?? []) as $i => $domain) {
        $name  = $domain['name'] ?? 'unknown';
        $avail = filter_var($domain['avail'] ?? false, FILTER_VALIDATE_BOOL);

        if ($avail) {
            echo 'Domain ' . ($i + 1) . ": {$name} is available" . PHP_EOL;
            continue;
        }

        $reason = $domain['reason'] ?? 'no reason given';
        echo 'Domain ' . ($i + 1) . ": {$name} is not available because: {$reason}" . PHP_EOL;
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