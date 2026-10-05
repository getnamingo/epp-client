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

try
{
    $epp = connect();

    $contactCheck = $epp->contactCheck([
        'contact' => ['tembo007', 'tembo009'],
    ]);

    if (isset($contactCheck['error'])) {
        echo 'ContactCheck Error: ' . $contactCheck['error'] . PHP_EOL;
        return;
    }

    echo "ContactCheck result: {$contactCheck['code']}: {$contactCheck['msg']}" . PHP_EOL;

    foreach (($contactCheck['contacts'] ?? []) as $i => $contact) {
        $id     = $contact['id'] ?? 'unknown';
        $avail  = filter_var($contact['avail'] ?? false, FILTER_VALIDATE_BOOL);
        $reason = $contact['reason'] ?? null;

        if ($avail) {
            echo 'Contact ' . ($i + 1) . ": ID {$id} is available" . PHP_EOL;
            continue;
        }

        echo 'Contact ' . ($i + 1) . ": ID {$id} is not available";
        echo $reason ? " because: {$reason}" : '';
        echo PHP_EOL;
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