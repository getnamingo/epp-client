<?php

/*
 * Namingo EPP Client
 * SPDX-License-Identifier: MIT
 */

require_once __DIR__ . '/Connection.php';

try {
    // The selected registry profile in Connection.php chooses the backend.
    $epp = connect();
    $balance = $epp->balanceInfo();

    print_r($balance);

    $epp->logout();
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
