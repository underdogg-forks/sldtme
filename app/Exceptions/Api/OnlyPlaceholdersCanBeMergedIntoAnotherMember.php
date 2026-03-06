<?php

namespace App\Exceptions\Api;

class OnlyPlaceholdersCanBeMergedIntoAnotherMember extends ApiException
{
    public const string KEY = 'only_placeholders_can_be_merged_into_another_member';
}
