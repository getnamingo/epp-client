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

    $params = [
        'domainname' => 'test.example',
        'period'     => 1,

        // ------------------------------------------------------------------
        // [A] CONTACTS
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
        // ENCODED SIGNED MARK
        // ------------------------------------------------------------------
        'encodedSignedMark' => 'INSERT_HERE'
    ];

    $domainCreateSunrise = $epp->domainCreateSunrise($params);

    if (array_key_exists('error', $domainCreateSunrise))
    {
        echo 'DomainCreateSunrise Error: ' . $domainCreateSunrise['error'] . PHP_EOL;
    }
    else
    {
        echo 'DomainCreateSunrise Result: ' . $domainCreateSunrise['code'] . ': ' . $domainCreateSunrise['msg'] . PHP_EOL;
        echo 'New Domain: ' . $domainCreateSunrise['name'] . PHP_EOL;
        echo 'Created On: ' . $domainCreateSunrise['crDate'] . PHP_EOL;
        echo 'Expires On: ' . $domainCreateSunrise['exDate'] . PHP_EOL;
    }

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}