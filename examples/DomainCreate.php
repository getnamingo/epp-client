<?php
/**
 * Tembo EPP client test file
 *
 * Written in 2023-2025 by Taras Kondratyuk (https://namingo.org)
 * Based on xpanel/epp-bundle written in 2019 by Lilian Rudenco (info@xpanel.com)
 *
 * @license MIT
 */

/**
 * To run this demo using a second test account:
 *
 * 1) Replace the include:
 *      require_once __DIR__ . '/Connection.php';
 *    with:
 *      require_once __DIR__ . '/Connection2.php';
 *
 * 2) Replace:
 *      $epp = connectEpp('generic');
 *    with:
 *      $epp = connectEpp2('generic');
 *
 * This allows testing with two separate credentials
 * without changing the code logic.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Connection.php';

try
{
    $epp = connectEpp('generic');

    /**
     * DOMAIN CREATE PARAMS
     *
     * Adjust ONLY the blocks marked:
     *   - [A] Nameservers as <domain:hostObj>
     *   - [B] Nameservers as <domain:hostAttr>
     *   - [C] Contacts (remove if registry does not use them)
     */

    $params = [
        'domainname' => 'test.example',
        'period'     => 1,

        // ------------------------------------------------------------------
        // [A] NAMESERVERS AS <domain:hostObj>
        // ------------------------------------------------------------------
        //
        // Use this if the registry expects hostObj (just hostnames, often when
        // glue/host objects are handled separately).
        //
        // - KEEP this block if you want hostObj
        // - COMMENT this block out if you want hostAttr (see [B] below)
        //
        'nss' => [
            'ns1.google.com',
            'ns2.google.com',
        ],

        // ------------------------------------------------------------------
        // [B] NAMESERVERS AS <domain:hostAttr>
        // ------------------------------------------------------------------
        //
        // Use this if the registry expects hostAttr (host + optional IPs)
        // directly inside the domain:create.
        //
        // - COMMENT OUT the block in [A] above
        // - UNCOMMENT the block below
        //
        /*
        'nss' => [
            [
                'hostName' => 'ns.test.it',
                'ipv4'     => '192.168.100.10',
            ],
            [
                'hostName' => 'ns2.test.it',
                'ipv4'     => '192.168.100.20',
            ],
            [
                'hostName' => 'ns3.foo.com', // no glue IP
            ],
        ],
        */

        // ------------------------------------------------------------------
        // [C] CONTACTS
        // ------------------------------------------------------------------
        //
        // If the registry DOES support registrant/admin/tech/billing contacts,
        // keep or adjust this block.
        //
        // If the registry does NOT use contacts, DELETE or COMMENT OUT
        // the whole section below.
        //
        'registrant' => 'tembo007',
        'contacts' => [
            'admin'   => 'tembo007',
            'tech'    => 'tembo007',
            'billing' => 'tembo007',
        ],

        // ------------------------------------------------------------------
        // AUTH-INFO PASSWORD
        // ------------------------------------------------------------------
        'authInfoPw' => 'Domainpw123@',
    ];

    $domainCreate = $epp->domainCreate($params);

    if (array_key_exists('error', $domainCreate))
    {
        echo 'DomainCreate Error: ' . $domainCreate['error'] . PHP_EOL;
    }
    else
    {
        echo 'DomainCreate Result: ' . $domainCreate['code'] . ': ' . $domainCreate['msg'] . PHP_EOL;
        echo 'New Domain: ' . $domainCreate['name'] . PHP_EOL;
        echo 'Created On: ' . $domainCreate['crDate'] . PHP_EOL;
        echo 'Expires On: ' . $domainCreate['exDate'] . PHP_EOL;
    }

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}