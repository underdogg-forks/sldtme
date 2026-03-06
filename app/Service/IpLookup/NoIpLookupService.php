<?php

namespace App\Service\IpLookup;

class NoIpLookupService implements IpLookupServiceContract
{
    public function lookup(string $ip): ?IpLookupResponseDto
    {
        return null;
    }
}
