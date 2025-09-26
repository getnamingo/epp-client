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

use Pinga\Tembo\EppRegistryFactory;

function connectEpp (string $registry) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=mydatabase', 'username', 'password');
        $stmt = $pdo->prepare("SELECT local_cert, local_pk, passphrase FROM epp_credentials WHERE id = :id");
        $stmt->execute(array('id' => 1));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $epp = EppRegistryFactory::create($registry);
        $info = array(
            'host' => 'epp.example.com',
            'port' => 700,
            'timeout' => 30,
            'tls' => '1.3',
            'bind' => false,
            'bindip' => '1.2.3.4:0',
            'verify_peer' => false,
            'verify_peer_name' => false,
            'cafile' => '',
            'local_cert' => $result['local_cert'],
            'local_pk' => $result['local_pk'],
            'passphrase' => $result['passphrase'],
            'allow_self_signed' => true
        );
        $epp->connect($info);
        $login = $epp->login(array(
            'clID' => 'testregistrar1',
            'pw' => 'testpassword1',
            //'newpw' => 'testpassword2',
            'prefix' => 'tembo'
        ));
        if (array_key_exists('error', $login)) {
            echo 'Login Error: ' . $login['error'] . PHP_EOL;
            exit();
        } else {
            echo 'Login Result: ' . $login['code'] . ': ' . $login['msg'][0] . PHP_EOL;
        }
        return $epp;
    } catch(\Pinga\Tembo\Exception\EppException $e) {
        echo "Error : ".$e->getMessage() . PHP_EOL;
    } catch(Throwable $e) {
        echo "Error : ".$e->getMessage() . PHP_EOL;
    }
}