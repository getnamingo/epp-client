<?php
/**
 * Tembo EPP client test file
 *
 * Written in 2023-2025 by Taras Kondratyuk (https://namingo.org)
 * Based on xpanel/epp-bundle written in 2019 by Lilian Rudenco (info@xpanel.com)
 *
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Connection.php';

try
{
    $epp = connectEpp('generic');

    $params = array(
        'domainname' => 'test.example'
    );
    $domainReport = $epp->domainReport($params);
    
    if (array_key_exists('error', $domainReport))
    {
        echo 'DomainReport Error: ' . $domainReport['error'] . PHP_EOL;
    }
    else
    {
        echo "DomainReport result: " . $domainReport['code'] . ": " . $domainReport['msg'] . PHP_EOL;
    }
    
    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}