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

    $pollAck = $epp->pollAck([
        'msgID' => 12345,
    ]);

    if (isset($pollAck['error'])) {
        echo 'Error: ' . $pollAck['error'] . PHP_EOL;
    } else {
        echo 'Poll Ack Result: ' . $pollAck['code'] . ': ' . $pollAck['msg'] . PHP_EOL;
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