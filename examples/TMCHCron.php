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

$settings = [
        'smd' => [
            'username' => '',
            'password' => '',
            'url' => 'https://test.ry.marksdb.org/smdrl/smdrl-latest.csv',
            'save_to' => 'smdrl-latest.csv'
        ],
        'surl' => [
            'username' => '',
            'password' => '',
            'url' => 'https://test.ry.marksdb.org/dnl/surl-latest.csv',
            'save_to' => 'surl-latest.csv'
        ],
        'dnl' => [
            'username' => '',
            'password' => '',
            'url' => 'https://test.ry.marksdb.org/dnl/dnl-latest.csv',
            'save_to' => 'dnl-latest.csv'
        ],
        'clr' => [
            'username' => '',
            'password' => '',
            'url' => 'http://crl.icann.org/tmch.crl',
            'save_to' => 'tmch.crl'
        ]
    ];

    foreach ($settings as $key => $value) {
        $username = $value['username'];
        $password = $value['password'];
        $fp = fopen($value['save_to'], 'w');
        
        if (!$fp) {
            throw new Exception('Could not open: ' . $value['save_to']);
        }
        
        $ch = curl_init($value['url']);
        
        if ($username && $password) {
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        }
        
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        fclose($fp);
        
        $date = date('Y-m-j H:i:s');
        
        if ($statusCode === 200) {
            echo "{$date} | {$key} | Success!\n";
        } else {
            echo "{$date} | {$key} | Failed with code: {$statusCode}\n";
        }
    }
