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

    $domainUpdateAuthinfo = $epp->domainUpdateAuthinfo([
        'domainname' => 'test.example',
        'authInfo'   => 'P@ssword123!',
    ]);

    if (isset($domainUpdateAuthinfo['error'])) {
        echo 'DomainUpdateAuthinfo Error: ' . $domainUpdateAuthinfo['error'] . PHP_EOL;
        return;
    }

    echo "DomainUpdateAuthinfo result: {$domainUpdateAuthinfo['code']}: {$domainUpdateAuthinfo['msg']}" . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;

} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
    exit(1);
}