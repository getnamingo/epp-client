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

    $contactUpdateStatus = $epp->contactUpdateStatus([
        'contactid' => 'C-TEST123',               // Contact object identifier
        'command'   => 'add',                     // add | rem
        'status'    => 'clientTransferProhibited' // common: clientTransferProhibited | clientUpdateProhibited | clientDeleteProhibited
    ]);

    if (isset($contactUpdateStatus['error'])) {
        echo 'ContactUpdateStatus Error: ' . $contactUpdateStatus['error'] . PHP_EOL;
        return;
    }

    echo "ContactUpdateStatus result: {$contactUpdateStatus['code']}: {$contactUpdateStatus['msg']}" . PHP_EOL;

    $logout = $epp->logout();
    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
    exit(1);
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
    exit(1);
}