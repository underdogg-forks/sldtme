<?php

namespace App\Events;

use App\Models\Organization;
use Illuminate\Foundation\Events\Dispatchable;

class BeforeOrganizationDeletion
{
    use Dispatchable;

    public Organization $organization;

    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }
}
