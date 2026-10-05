<?php

/*
 * Namingo EPP Client
 *
 * Copyright (c) 2023-2026 Taras Kondratyuk
 * Copyright (c) 2025-2026 Namingo contributors
 * Copyright (c) 2026 Terbora Ltd.
 *
 * Based in part on phprri by Bigwern/phprri and DENIC
 *
 * Licensed under the MIT License.
 * See the LICENSE file distributed with this software for the full license text.
 *
 * SPDX-License-Identifier: MIT
 */

namespace Pinga\Tembo;

use Pinga\Tembo\Epp;
use Pinga\Tembo\Exception\EppException;
use Pinga\Tembo\Exception\EppNotConnectedException;

class RRIClient extends Epp
{
    /**
     * connect
     */
    public function connect($params = array())
    {
        $host = (string)$params['host'];
        $port = (int)$params['port'];
        $timeout = (int)$params['timeout'];

        $this->resource = stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

        if (!$this->resource) {
            throw new EppException("Cannot connect to server '{$host}': {$errmsg}");
        }

        return $this->resource;
    }

    /**
     * RRI_SendAndRead
     */
    public function RRI_SendAndRead($conn, $order)
    {
        $len = strlen($order);
        $nlen = pack("N", $len); // Convert Bytes of len to Network-Byte-Order
        $bytes_send = fwrite($conn, $nlen . $order, $len + 4);  // send length of order and order
        if ($bytes_send === false) {
            return false;
        }
        $nlen = fread($conn, 4); // read 4-Byte length of answer
        $bytes = unpack("N", $nlen); // convert bytes to local order
        $rest = $bytes[1];
        $answer = "";
        while ($rest) {
            $a = fread($conn, $rest); // read answer
            $answer .= $a;
            $gelesen = strlen($a);
            $rest -= $gelesen;
            if (feof($conn)) {
                break;
            }
        }
        return $answer;
    }
}
