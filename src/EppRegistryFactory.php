<?php
/**
 * Tembo EPP client library
 *
 * Written in 2023-2025 by Taras Kondratyuk (https://namingo.org)
 * Based on xpanel/epp-bundle written in 2019 by Lilian Rudenco (info@xpanel.com)
 *
 * @license MIT
 */

namespace Pinga\Tembo;

use Pinga\Tembo\Registries\EeEpp;
use Pinga\Tembo\Registries\EuEpp;
use Pinga\Tembo\Registries\FiEpp;
use Pinga\Tembo\Registries\FrEpp;
use Pinga\Tembo\Registries\FredEpp;
use Pinga\Tembo\Registries\GenericEpp;
use Pinga\Tembo\Registries\GrEpp;
use Pinga\Tembo\Registries\HkEpp;
use Pinga\Tembo\Registries\HrEpp;
use Pinga\Tembo\Registries\ItEpp;
use Pinga\Tembo\Registries\LtEpp;
use Pinga\Tembo\Registries\LvEpp;
use Pinga\Tembo\Registries\MxEpp;
use Pinga\Tembo\Registries\NoEpp;
use Pinga\Tembo\Registries\PlEpp;
use Pinga\Tembo\Registries\PtEpp;
use Pinga\Tembo\Registries\SeEpp;
use Pinga\Tembo\Registries\UaEpp;
use Pinga\Tembo\Registries\VrsnEpp;

class EppRegistryFactory
{
    public static function create($registry)
    {
        switch ($registry) {
            case 'EE':
                return new EeEpp();
                break;
            case 'EU':
                return new EuEpp();
                break;
            case 'FI':
                return new FiEpp();
                break;
            case 'FR':
                return new FrEpp();
                break;
            case 'FRED':
                return new FredEpp();
                break;
            case 'GR':
                return new GrEpp();
                break;
            case 'HK':
                return new HkEpp();
                break;
            case 'HR':
                return new HrEpp();
                break;
            case 'IT':
                return new ItEpp();
                break;
            case 'LT':
                return new LtEpp();
                break;
            case 'LV':
                return new LvEpp();
                break;
            case 'MX':
                return new MxEpp();
                break;
            case 'NO':
                return new NoEpp();
                break;
            case 'PL':
                return new PlEpp();
                break;
            case 'PT':
                return new PtEpp();
                break;
            case 'SE':
                return new SeEpp();
                break;
            case 'UA':
                return new UaEpp();
                break;
            case 'VRSN':
                return new VrsnEpp();
                break;
            default:
                return new GenericEpp();
        }
    }
}