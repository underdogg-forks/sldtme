<?php

namespace App\Exceptions\Api;

class OrganizationHasNoSubscriptionButMultipleMembersException extends ApiException
{
    public const string KEY = 'organization_has_no_subscription_but_multiple_members';
}
