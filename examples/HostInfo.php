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
        'hostname' => 'ns1.test.example'
    );
    $hostInfo = $epp->hostInfo($params);
    
    if (array_key_exists('error', $hostInfo))
    {
        echo 'HostInfo Error: ' . $hostInfo['error'] . PHP_EOL;
    }
    else
    {
        if ($registry == 'fred') {
        echo 'HostInfo Result: ' . $hostInfo['code'] . ': ' . $hostInfo['msg'] . PHP_EOL;
        echo 'Name: ' . $hostInfo['name'] . PHP_EOL;
        echo 'Status ';
        foreach ($hostInfo['status'] as $key => $value) {
            echo $key . ': ' . $value . ', ';
        }
        echo PHP_EOL;
        echo 'Addr ';
        foreach ($hostInfo['addr'] as $key => $value) {
            echo $key . ': ' . $value . ', ';
        }
        echo PHP_EOL;
        echo 'Current Registrar: ' . $hostInfo['clID'] . PHP_EOL;
        echo 'Original Registrar: ' . $hostInfo['crID'] . PHP_EOL;
        echo 'Created On: ' . $hostInfo['crDate'] . PHP_EOL;
        echo 'Updated By: ' . $hostInfo['upID'] . PHP_EOL;
        echo 'Updated On: ' . $hostInfo['upDate'] . PHP_EOL;
        } else {
        echo 'HostInfo Result: ' . $hostInfo['code'] . ': ' . $hostInfo['msg'] . PHP_EOL;
        echo 'Name: ' . $hostInfo['name'] . PHP_EOL;
        echo 'Status: ' . $hostInfo['status'][0] . PHP_EOL;
        echo 'Addr: ' . $hostInfo['addr'][0] . PHP_EOL;
        echo 'Current Registrar: ' . $hostInfo['clID'] . PHP_EOL;
        echo 'Original Registrar: ' . $hostInfo['crID'] . PHP_EOL;
        echo 'Created On: ' . $hostInfo['crDate'] . PHP_EOL;
        echo 'Updated By: ' . $hostInfo['upID'] . PHP_EOL;
        echo 'Updated On: ' . $hostInfo['upDate'] . PHP_EOL;
        }
    }

    $logout = $epp->logout();

    echo 'Logout Result: ' . $logout['code'] . ': ' . $logout['msg'][0] . PHP_EOL;
} catch(\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
} catch(Throwable $e) {
    echo "Error : ".$e->getMessage() . PHP_EOL;
}