<?php
/**
 * Namingo EPP Client
 *
 * (c) 2023–2026 Namingo Team (https://namingo.org)
 * Based on https://github.com/xpanel/epp-bundle by Lilian Rudenco
 *
 * MIT License
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Pinga\Tembo\EppRegistryFactory;

function connectEpp(?string $registry = null) {
    // Registry profile to use (see README for available registry codes, e.g. 'generic', 'SE', 'UA')
    $registry ??= 'generic';

    $epp = EppRegistryFactory::create($registry);
    $epp->setLogPath(__DIR__ . '/../log');

    $info = [
        // EPP endpoint
        // For EPP-over-HTTPS use:
        //   'host' => 'https://registry.example.com/epp',
        //   'port' => 443,
        'host'    => 'epp.example.com',
        'port'    => 700,

        // Connection settings
        'timeout' => 30,
        'tls'     => '1.2', // Change to '1.3' if required by the registry

        // Optional local bind (usually not required)
        'bind'    => false,
        'bindip'  => '1.2.3.4:0',

        // TLS verification
        // NOTE: Disabled here for test systems / self-signed certificates.
        //       Enable in production and provide a CA bundle.
        'verify_peer'      => false,
        'verify_peer_name' => false,
        'cafile'           => '',

        // Client certificate authentication
        'local_cert' => realpath(__DIR__ . '/../cert.pem'),
        'local_pk'   => realpath(__DIR__ . '/../key.pem'),
        'passphrase' => '',
        'allow_self_signed' => true,

        // Default login objects for the generic registry profile
        'loginObjects' => [
            'urn:ietf:params:xml:ns:domain-1.0',
            'urn:ietf:params:xml:ns:contact-1.0',
            'urn:ietf:params:xml:ns:host-1.0',
        ],

        // Per-registry login extensions for the generic registry profile
        'loginExtensions' => [
            'urn:ietf:params:xml:ns:secDNS-1.1',
            'urn:ietf:params:xml:ns:rgp-1.0',
            // e.g. 'urn:ietf:params:xml:ns:fee-0.7',
        ],
    ];

    if (!empty($info['loginObjects'])) {
        $epp->setLoginObjects($info['loginObjects']);
    }

    if (!empty($info['loginExtensions'])) {
        $epp->setLoginExtensions($info['loginExtensions']);
    }

    $epp->connect($info);

    $login = $epp->login([
        'clID' => 'testregistrar1',
        'pw' => 'testpassword1',
        // 'newpw' => 'testpassword2',
        'prefix' => 'namingo',
    ]);

    if (isset($login['error'])) {
        throw new RuntimeException('Login Error: ' . $login['error']);
    }

    $msg = $login['msg'] ?? '';
    $msgText = is_array($msg) ? implode(' ', $msg) : (string) $msg;

    echo "Login Result: {$login['code']}: {$msgText}" . PHP_EOL;

    return $epp;
}

function connect(?string $registry = null) {
    return connectEpp($registry);
}