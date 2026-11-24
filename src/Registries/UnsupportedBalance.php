<?php

/*
 * Namingo EPP Client
 * SPDX-License-Identifier: MIT
 */

namespace Pinga\Tembo\Registries;

/**
 * Registry profiles without a verified balance info protocol.
 */
trait UnsupportedBalance
{
    public function balanceInfo($params = array())
    {
        return [
            'code' => 2102,
            'msg' => 'Balance info is not supported by this registry profile',
        ];
    }
}
