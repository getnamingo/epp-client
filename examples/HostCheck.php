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

    $hostCheck = $epp->hostCheck([
        'hostname' => 'ns1.test.example',
    ]);

    if (isset($hostCheck['error'])) {
        echo 'HostCheck Error: ' . $hostCheck['error'] . PHP_EOL;
        return;
    }

    echo "HostCheck result: {$hostCheck['code']}: {$hostCheck['msg']}" . PHP_EOL;

    foreach (($hostCheck['hosts'] ?? []) as $i => $host) {
        $label = $host['name'] ?? $host['id'] ?? 'unknown';
        $avail = filter_var($host['avail'] ?? false, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $avail = $avail ?? ((int)($host['avail'] ?? 0) === 1); // fallback

        if ($avail) {
            echo 'Host ' . ($i + 1) . ": {$label} is available" . PHP_EOL;
        } else {
            $reason = $host['reason'] ?? 'no reason given';
            echo 'Host ' . ($i + 1) . ": {$label} is not available because: {$reason}" . PHP_EOL;
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