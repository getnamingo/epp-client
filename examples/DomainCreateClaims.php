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
 * Replace the include:
 *    require_once __DIR__ . '/Connection.php';
 *  with:
 *    require_once __DIR__ . '/Connection2.php';
 *
 * This allows testing with two separate credentials
 * without changing the code logic.
 */

require_once __DIR__ . '/Connection.php';

try
{
    $epp = connect();

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

        // ------------------------------------------------------------------
        // CLAIMS NOTICE DETAILS
        // ------------------------------------------------------------------
        'noticeID' => 'ABCDE1234FGHIJK5678',
        'notAfter' => '2023-02-24T09:30:00.0Z',
        'acceptedDate' => '2023-02-21T09:30:00.0Z'
    ];

    $domainCreateClaims = $epp->domainCreateClaims($params);

    if (array_key_exists('error', $domainCreateClaims))
    {
        echo 'DomainCreateClaims Error: ' . $domainCreateClaims['error'] . PHP_EOL;
    }
    else
    {
        echo 'DomainCreateClaims Result: ' . $domainCreateClaims['code'] . ': ' . $domainCreateClaims['msg'] . PHP_EOL;
        echo 'New Domain: ' . $domainCreateClaims['name'] . PHP_EOL;
        echo 'Created On: ' . $domainCreateClaims['crDate'] . PHP_EOL;
        echo 'Expires On: ' . $domainCreateClaims['exDate'] . PHP_EOL;
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