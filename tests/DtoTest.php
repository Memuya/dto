<?php

declare(strict_types=1);

use Memuya\Dto\Dto;
use Memuya\Dto\Types\Optional;
use Memuya\Dto\Types\Required;
use PHPUnit\Framework\TestCase;
use Memuya\Dto\Exceptions\RequiredPropertyNotFoundException;

final class DtoTest extends TestCase
{
    public function testRequiredPropertyMustBeSet(): void
    {
        $this->expectException(RequiredPropertyNotFoundException::class);

        new class ([]) extends Dto {
            #[Required]
            protected string $name;
        };
    }

    public function testSettingAllRequiredPropertiesDoesNotThrowException(): void
    {
        $name = 'test_name';
        $age = 100;

        $dto = new class (['name' => $name, 'age' => $age]) extends Dto {
            #[Required]
            protected string $name;

            #[Required]
            protected int $age;
        };

        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
    }

    public function testCanPassNamedArgumentsToConstructor(): void
    {
        $name = 'test_name';
        $age = 100;

        $dto = new class (name: $name, age: $age) extends Dto {
            #[Required]
            protected string $name;

            #[Required]
            protected int $age;
        };

        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
    }

    public function testOptionalPropertiesAreNotRequired()
    {
        $name = 'test_name';
        $data = ['name' => $name];

        $dto = new class ($data) extends Dto {
            #[Required]
            protected string $name;

            #[Optional]
            protected int $age;
        };

        $this->assertNull($dto->age);
        $this->assertArrayHasKey('name', $dto->toArray());
        $this->assertArrayHasKey('age', $dto->toArray());
        $this->assertCount(2, $dto->toArray());
    }

    public function testPropertiesNotSetAsRequiredOrOptionalAreTreatedAsOptional()
    {
        $dto = new class ([]) extends Dto {
            protected string $name;
            protected int $age;
        };

        $this->assertNull($dto->name);
        $this->assertNull($dto->age);
        $this->assertArrayHasKey('name', $dto->toArray());
        $this->assertArrayHasKey('age', $dto->toArray());
        $this->assertCount(2, $dto->toArray());
    }

    public function testCanCreateNewInstance()
    {
        $dto = new class ([]) extends Dto {};
        $newInstance = $dto::fromArray([]);

        $this->assertInstanceOf(Dto::class, $newInstance);
    }
}
