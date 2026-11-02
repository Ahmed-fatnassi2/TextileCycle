<?php

namespace App\Exceptions;

use RuntimeException;

// L'assistant IA ne peut pas répondre (clé absente, API OpenAI en erreur ou injoignable).
class AssistantUnavailableException extends RuntimeException
{
}
