<?php

declare(strict_types=1);

namespace Memuya\Dto;

use ReflectionClass;
use ReflectionProperty;
use Memuya\Dto\Types\Required;
use Memuya\Dto\Exceptions\RequiredPropertyNotFoundException;

abstract class Dto
{
    /**
     * Setup.
     *
     * Note: Set as final so self::fromArray() is safe.
     *
     * @param array<string, mixed> $args
     */
    final public function __construct(...$args)
    {
        // Allows for arguments or an array.
        // Taken from https://github.com/spatie/data-transfer-object/blob/main/src/DataTransferObject.php#L22
        if (is_array($args[0] ?? null)) {
            $args = $args[0];
        }
        
        $this->setProperties($args);
    }

    /**
     * Create a new instance.
     *
     * @param array<string, mixed> $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * Set the given data for each property defined on the DTO.
     *
     * @param array<int|string, mixed> $data
     * @return void
     * @throws RequiredPropertyNotFoundException
     */
    private function setProperties(array $data): void
    {
        $properties = (new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();

            if (!isset($data[$propertyName]) && $this->isRequired($property)) {
                throw new RequiredPropertyNotFoundException(
                    message: sprintf("'%s' is a required property on %s", $propertyName, static::class),
                    propertyName: $propertyName
                );
            }

            if (isset($data[$propertyName])) {
                $this->setProperty($property, $data[$propertyName]);
            }
        }
    }

    /**
     * Set the value against the given property.
     *
     * @param ReflectionProperty $property
     * @param mixed $value
     */
    private function setProperty(ReflectionProperty $property, mixed $value): void
    {
        $property->setValue($this, $value ?? $property->getDefaultValue() ?? null);
    }

    /**
     * Return the DTO data as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        $properties = (new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $data[$property->getName()] = isset($this->{$property->getName()}) ? $property->getValue($this) : null;
        }

        return $data;
    }

    /**
     * Check if the given property was marked as required.
     *
     * @param ReflectionProperty $property
     * @return bool
     */
    private function isRequired(ReflectionProperty $property): bool
    {
        return count($property->getAttributes(Required::class)) > 0;
    }

    /**
     * Return the value for the given property.
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property): mixed
    {
        return isset($this->{$property}) ? $this->{$property} : null;
    }
}
