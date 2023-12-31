<?php

declare(strict_types=1);

namespace Memuya\Dto;

use ReflectionClass;
use ReflectionProperty;
use Memuya\Dto\Types\Optional;
use Memuya\Dto\Types\Required;
use Memuya\Dto\Exceptions\RequiredPropertyNotFoundException;

abstract class Dto
{
    /**
     * Stores all the data for each defined property on the child classes.
     *
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * Setup.
     *
     * Note: Set as final so self::fromArray() is safe.
     *
     * @param array<string, mixed> $data
     */
    final public function __construct(array $data)
    {
        $this->setProperties($data);
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
     * @param array<string, mixed> $data
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

            unset($this->{$propertyName});
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
        $propertyName = $property->getName();

        /**
         * Set the property directly to enforce any typehints it may have.
         * A TypeError exception will be thrown if the given type is invalid.
         *
         * @throws \TypeError
         */
        $this->data[$propertyName] = $this->{$propertyName} = $value ?? $property->getDefaultValue() ?? null;
    }

    /**
     * Return the underlaying data.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
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
        return $this->data[$property] ?? null;
        // return $this->data[$property] ?? throw new Exception(sprintf("'%s' property never set.", $property));
    }
}
