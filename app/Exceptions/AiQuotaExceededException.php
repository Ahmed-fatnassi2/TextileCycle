<?php

namespace App\Exceptions;

class AiQuotaExceededException extends AssistantUnavailableException
{
    public function __construct(public readonly int $remaining = 0)
    {
        parent::__construct('Le quota quotidien de cette fonctionnalité est atteint.');
    }
}
