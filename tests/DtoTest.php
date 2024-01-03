<?php

declare(strict_types=1);

use Memuya\Dto\Dto;
use Memuya\Dto\Types\Optional;
use PHPUnit\Framework\TestCase;
use Memuya\Dto\Exceptions\RequiredPropertyNotFoundException;

final class DtoTest extends TestCase
{
    public function testRequiredPropertyMustBeSet(): void
    {
        $this->expectException(RequiredPropertyNotFoundException::class);

        new class ([]) extends Dto {
            protected string $name;
        };
    }

    public function testSettingAllRequiredPropertiesDoesNotThrowException(): void
    {
        $name = 'test_name';
        $age = 100;

        $dto = new class (['name' => $name, 'age' => $age]) extends Dto {
            protected string $name;

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
            protected string $name;

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
            protected string $name;

            #[Optional]
            protected int $age;
        };

        $this->assertNull($dto->age);
        $this->assertSame($name, $dto->name);
    }

    public function testCanCreateNewInstance()
    {
        $name = 'test_name';
        $data = ['name' => $name];
        $newInstance = \Memuya\Test\TestData\TestDto::fromArray($data);

        $this->assertInstanceOf(Dto::class, $newInstance);
        $this->assertSame($name, $newInstance->name);
    }
}
