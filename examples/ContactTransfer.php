<?php
/**
 * Tembo EPP client test file (Contact Transfer)
 *
 * Written in 2023-2025 by Taras Kondratyuk (https://namingo.org)
 * Based on xpanel/epp-bundle written in 2019 by Lilian Rudenco (info@xpanel.com)
 *
 * @license MIT
 */

require_once __DIR__ . '/Connection.php';

try {
    $epp = connect();

    $contactTransfer = $epp->contactTransfer([
        'contactid'  => 'C-TEST123',        // <-- change to an existing contact ID
        'authInfoPw' => 'Contactpw123@',    // <-- contact authInfo pw (if required by registry)
        'op'         => 'request',          // Transfer operation: request | query | cancel | reject | approve
    ]);

    if (isset($contactTransfer['error'])) {
        echo 'ContactTransfer Error: ' . $contactTransfer['error'] . PHP_EOL;
        return;
    }

    if (isset($contactTransfer['code'], $contactTransfer['msg'])) {
        echo "ContactTransfer Result: {$contactTransfer['code']}: {$contactTransfer['msg']}" . PHP_EOL;
    }

    $fields = [
        'Contact ID'              => 'id',
        'Transfer Status'         => 'trStatus',
        'Gaining Registrar'       => 'reID',
        'Requested On'            => 'reDate',
        'Losing Registrar'        => 'acID',
        'Transfer Confirmed On'   => 'acDate',
    ];

    foreach ($fields as $label => $key) {
        if (isset($contactTransfer[$key]) && $contactTransfer[$key] !== '') {
            echo "{$label}: {$contactTransfer[$key]}" . PHP_EOL;
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