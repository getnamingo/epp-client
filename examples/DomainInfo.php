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

    $domainInfo = $epp->domainInfo([
        'domainname' => 'test.example',
        'authInfoPw' => 'Domainpw123@',
    ]);

    if (isset($domainInfo['error'])) {
        echo 'DomainInfo Error: ' . $domainInfo['error'] . PHP_EOL;
        return;
    }

    echo "DomainInfo Result: {$domainInfo['code']}: {$domainInfo['msg']}" . PHP_EOL;

    $fields = [
        'Name'              => 'name',
        'ROID'              => 'roid',
        'Registrant'        => 'registrant',
        'Current Registrar' => 'clID',
        'Original Registrar'=> 'crID',
        'Created On'        => 'crDate',
        'Updated By'        => 'upID',
        'Updated On'        => 'upDate',
        'Expires On'        => 'exDate',
        'Transferred On'    => 'trDate',
        'Password'          => 'authInfo',
    ];

    foreach ($fields as $label => $key) {
        if (isset($domainInfo[$key]) && $domainInfo[$key] !== '') {
            echo "{$label}: {$domainInfo[$key]}" . PHP_EOL;
        }
    }

    /**
     * Normalize status:
     * - list: ['ok', 'clientTransferProhibited']
     * - map: ['ok' => '...', 'clientTransferProhibited' => '...']
     * - string: 'ok'
     */
    if (isset($domainInfo['status']) && $domainInfo['status'] !== '') {
        echo 'Status: ';
        $status = $domainInfo['status'];

        if (is_array($status)) {
            $parts = [];
            foreach ($status as $k => $v) {
                $parts[] = is_int($k) ? (string)$v : "{$k}: {$v}";
            }
            echo implode(', ', $parts) . PHP_EOL;
        } else {
            echo $status . PHP_EOL;
        }
    }

    /**
     * Contacts: tolerate shapes like:
     * - [['type' => 'admin', 'id' => 'X'], ...]
     * - ['admin' => 'X', 'tech' => 'Y'] (less common)
     */
    if (!empty($domainInfo['contact']) && is_array($domainInfo['contact'])) {
        $wanted = ['admin', 'billing', 'tech'];

        foreach ($wanted as $type) {
            $id = null;

            // keyed format: ['admin' => 'X']
            if (isset($domainInfo['contact'][$type]) && is_scalar($domainInfo['contact'][$type])) {
                $id = (string) $domainInfo['contact'][$type];
            } else {
                // list format
                foreach ($domainInfo['contact'] as $c) {
                    if (!is_array($c)) {
                        continue;
                    }
                    if (($c['type'] ?? null) === $type && isset($c['id']) && $c['id'] !== '') {
                        $id = (string) $c['id'];
                        break;
                    }
                }
            }

            if ($id !== null) {
                echo ucfirst($type) . ": {$id}" . PHP_EOL;
            }
        }
    }

    /**
     * Name servers
     */
    if (!empty($domainInfo['ns']) && is_array($domainInfo['ns'])) {
        $ns = array_values(array_filter($domainInfo['ns'], 'is_scalar'));
        sort($ns, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($ns as $server) {
            echo "Name Server: {$server}" . PHP_EOL;
        }
    }

    /**
     * Hosts
     */
    if (!empty($domainInfo['host']) && is_array($domainInfo['host'])) {
        $hosts = array_values(array_filter($domainInfo['host'], 'is_scalar'));
        sort($hosts, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($hosts as $host) {
            echo "Host: {$host}" . PHP_EOL;
        }
    }

    /**
     * DNSSEC (secDNS): dsData / keyData (optional)
     */
    if (!empty($domainInfo['dsData']) && is_array($domainInfo['dsData'])) {
        foreach ($domainInfo['dsData'] as $i => $ds) {
            if (!is_array($ds)) continue;

            $keyTag     = $ds['keyTag'] ?? null;
            $alg        = $ds['alg'] ?? null;
            $digestType = $ds['digestType'] ?? null;
            $digest     = $ds['digest'] ?? null;

            if ($keyTag !== null && $alg !== null && $digestType !== null && $digest !== null) {
                echo "DS #".($i+1).": keyTag={$keyTag}, alg={$alg}, digestType={$digestType}, digest={$digest}" . PHP_EOL;
            }
        }
    }

    if (!empty($domainInfo['keyData']) && is_array($domainInfo['keyData'])) {
        foreach ($domainInfo['keyData'] as $i => $kd) {
            if (!is_array($kd)) continue;

            $flags    = $kd['flags'] ?? null;
            $protocol = $kd['protocol'] ?? null;
            $alg      = $kd['alg'] ?? null;
            $pubKey   = $kd['pubKey'] ?? null;

            if ($flags !== null && $protocol !== null && $alg !== null && $pubKey !== null) {
                echo "DNSKEY #".($i+1).": flags={$flags}, protocol={$protocol}, alg={$alg}, pubKey={$pubKey}" . PHP_EOL;
            }
        }
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