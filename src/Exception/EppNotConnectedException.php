<?php
/**
 * Namingo EPP Client
 *
 * (c) 2023–2026 Namingo Team (https://namingo.org)
 * Based on https://github.com/xpanel/epp-bundle by Lilian Rudenco
 *
 * MIT License
 */

namespace Pinga\Tembo\Exception;

class EppNotConnectedException extends EppException
{
    protected $message = 'Not connected to EPP server. Call connect() first.';
}