<?php

namespace App\Extensions\Auditing\Resolvers;

use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class CustomIpAddressResolver implements Resolver
{
    public static function resolve(Auditable $auditable): string
    {
        $ip = $auditable->preloadedResolverData['ip_address'] ?? Request::ip();

        if ($ip !== null) {
            $ip = self::anonymizeIpAddress($ip);
        }

        return $ip;
    }

    private static function anonymizeIpAddress(string $ipAddress): string
    {
        /* @source https://stackoverflow.com/a/48777412 */
        return preg_replace(
            ['/\.\d*$/', '/[\da-f]*:[\da-f]*$/'],
            ['.0', '0:0'],
            $ipAddress
        );
    }
}
