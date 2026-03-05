<?php

namespace App\Models\Concerns;

use OwenIt\Auditing\Auditable;

trait CustomAuditable
{
    use Auditable;

    /**
     * @var array<string>|null
     */
    protected ?array $auditEvents = null;

    public function disableAuditing(): void
    {
        $this->auditEvents = [];
    }
}
