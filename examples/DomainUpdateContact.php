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

    $domainUpdateContact = $epp->domainUpdateContact([
        'domainname'     => 'test.example',
        'contacttype'   => 'admin',
        'old_contactid' => 'ABC123',
        'new_contactid' => 'ABC456',
    ]);

    if (isset($domainUpdateContact['error'])) {
        echo 'DomainUpdateContact Error: ' . $domainUpdateContact['error'] . PHP_EOL;
        return;
    }

    echo "DomainUpdateContact result: {$domainUpdateContact['code']}: {$domainUpdateContact['msg']}" . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}