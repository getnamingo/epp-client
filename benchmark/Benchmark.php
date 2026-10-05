<?php

/*
 * Namingo EPP Client
 *
 * Copyright (c) 2023-2026 Taras Kondratyuk
 * Copyright (c) 2025-2026 Namingo contributors
 * Copyright (c) 2026 Terbora Ltd.
 *
 * Based in part on xpanel/epp-bundle
 * Copyright (c) 2017 Lilian Rudenco
 * https://github.com/xpanel/epp-bundle
 *
 * Licensed under the MIT License.
 * See the LICENSE file distributed with this software for the full license text.
 *
 * SPDX-License-Identifier: MIT
 */

require_once __DIR__ . '/Connection.php';
require_once __DIR__ . '/Helpers.php';

try {
    $startTime = microtime(true);

    $epp = connect();

    for ($i = 0; $i < 200; $i++) {
        $domains = [];
        for ($j = 0; $j < 5; $j++) {
            $domains[] = randomDomain();
        }
        performDomainCheck($epp, $domains);
    }

    for ($i = 0; $i < 10000; $i++) {
        $domain = randomDomain();
        performDomainCreate($epp, $domain);
    }

    for ($i = 0; $i < 10000; $i++) {
        $domain = randomDomain();
        performDomainInfo($epp, $domain);
    }

    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    echo 'Total Execution Time: ' . $executionTime . ' seconds' . PHP_EOL;

    $epp->logout();
} catch (\Pinga\Tembo\Exception\EppException $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
} catch (Throwable $e) {
    echo "Error : " . $e->getMessage() . PHP_EOL;
}