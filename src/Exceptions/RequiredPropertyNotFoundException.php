<?php

declare(strict_types=1);

namespace Memuya\Dto\Exceptions;

use Exception;
use Throwable;

class RequiredPropertyNotFoundException extends Exception
{
    /**
     * The required property that is missing.
     *
     * @var string
     */
    private string $propertyName;

    /**
     * Set up.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param string $propertyName
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, string $propertyName = "")
    {
        parent::__construct($message, $code, $previous);

        $this->propertyName = $propertyName;
    }

    /**
     * Return a user friendly error string.
     *
     * @return string
     */
    public function getFriendlyMessage(): string
    {
        return sprintf("'%s' is required.", $this->propertyName);
    }
}
