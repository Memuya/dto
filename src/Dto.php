<?php

declare(strict_types=1);

namespace Memuya\Dto;

use ReflectionClass;
use ReflectionProperty;
use Memuya\Dto\Types\Optional;
use Memuya\Dto\Modifiers\Label;
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

            if ($this->isRequiredPropertyMissing($property, $data)) {
                throw new RequiredPropertyNotFoundException(
                    message: sprintf("'%s' is a required property on %s", $propertyName, static::class),
                    propertyName: $this->getPropertyLabel($property)
                );
            }

            if (array_key_exists($propertyName, $data)) {
                $this->setProperty($property, $data[$propertyName]);
            }
        }
    }

    /**
     * Check if the given property exists in the data array as a key.
     *
     * @param ReflectionProperty $property
     * @param array<int|string, mixed> $data
     * @return bool
     */
    private function isRequiredPropertyMissing(ReflectionProperty $property, array $data): bool
    {
        return !array_key_exists($property->getName(), $data) && !$this->isOptional($property);
    }

    /**
     * Return the configured label for a property.
     *
     * @param ReflectionProperty $property
     * @return string
     */
    private function getPropertyLabel(ReflectionProperty $property): string
    {
        if (count($property->getAttributes(Label::class)) === 0) {
            return $property->getName();
        }

        $labelInstance = $property->getAttributes(Label::class)[0]->newInstance();

        return $labelInstance->getLabel();
    }

    /**
     * Set the value against the given property.
     *
     * @param ReflectionProperty $property
     * @param mixed $value
     * @return void
     * @throws \TypeError
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

        return $this->transform($data);
    }

    /**
     * Recursively transform any DTOs within an array into an array.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function transform(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof Dto) {
                $data[$key] = $value->toArray();

                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->transform($value);

                continue;
            }
        }

        return $data;
    }

    /**
     * Check if the given property was marked as optional.
     *
     * @param ReflectionProperty $property
     * @return bool
     */
    private function isOptional(ReflectionProperty $property): bool
    {
        return count($property->getAttributes(Optional::class)) > 0;
    }

    /**
     * Return the value for the given property.
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property): mixed
    {
        return $this->{$property} ?? null;
    }
}
