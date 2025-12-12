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

    $domainUpdateNS = $epp->domainUpdateNS([
        'domainname' => 'test.example',

        /**
         * Name server update mode:
         *
         * Option A (most TLDs):
         *   Use host objects only (<domain:hostObj>)
         */
        'ns1' => 'ns1.example.com',
        'ns2' => 'ns2.example.com',

        /**
         * Option B (TLDs requiring <domain:hostAttr> with glue):
         *   Uncomment and use this block instead of ns1/ns2 above.
         *
         * 'nss' => [
         *     [
         *         'hostName' => 'ns1.example.com',
         *         'ipv4'     => '192.168.1.1',
         *         'ipv6'     => '2001:db8::1',
         *     ],
         *     [
         *         'hostName' => 'ns2.example.com',
         *         'ipv4'     => '192.168.1.2',
         *     ],
         *     [
         *         'hostName' => 'ns3.example.com',
         *     ],
         * ],
         */
    ]);

    if (isset($domainUpdateNS['error'])) {
        echo 'DomainUpdateNS Error: ' . $domainUpdateNS['error'] . PHP_EOL;
        return;
    }

    echo "DomainUpdateNS result: {$domainUpdateNS['code']}: {$domainUpdateNS['msg']}" . PHP_EOL;

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}