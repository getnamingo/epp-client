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

    $contactCheck = $epp->contactCheck([
        'contact' => ['tembo007', 'tembo009'],
    ]);

    if (isset($contactCheck['error'])) {
        echo 'ContactCheck Error: ' . $contactCheck['error'] . PHP_EOL;
        return;
    }

    echo "ContactCheck result: {$contactCheck['code']}: {$contactCheck['msg']}" . PHP_EOL;

    foreach (($contactCheck['contacts'] ?? []) as $i => $contact) {
        $id     = $contact['id'] ?? 'unknown';
        $avail  = filter_var($contact['avail'] ?? false, FILTER_VALIDATE_BOOL);
        $reason = $contact['reason'] ?? null;

        if ($avail) {
            echo 'Contact ' . ($i + 1) . ": ID {$id} is available" . PHP_EOL;
            continue;
        }

        echo 'Contact ' . ($i + 1) . ": ID {$id} is not available";
        echo $reason ? " because: {$reason}" : '';
        echo PHP_EOL;
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