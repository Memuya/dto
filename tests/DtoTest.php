<?php

declare(strict_types=1);

use Memuya\Dto\Dto;
use Memuya\Dto\Types\Optional;
use Memuya\Dto\Modifiers\Label;
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
        $data = ['name' => $name, 'testDto' => ['key' => new \Memuya\Test\TestData\TestDto(name: 'adasd')]];

        $dto = new class ($data) extends Dto {
            protected string $name;

            #[Optional]
            protected int $age;

            protected array $testDto;
        };

        $this->assertNull($dto->age);
        $this->assertSame($name, $dto->name);
    }

    public function testRecursivelyTransformsDtosIntoArray()
    {
        $name = 'test_name';
        $data = ['testDto' => ['key' => new \Memuya\Test\TestData\TestDto(name: $name)]];

        $dto = new class ($data) extends Dto {
            protected array $testDto;
        };

        $array = $dto->toArray();

        // Should be arrays all the way down.
        $this->assertIsArray($array);
        $this->assertIsArray($array['testDto']);
        $this->assertArrayHasKey('key', $array['testDto']);
        $this->assertIsArray($array['testDto']['key']);
        $this->assertArrayHasKey('name', $array['testDto']['key']);
        $this->assertSame($name, $array['testDto']['key']['name']);
    }

    public function testCanCreateNewInstance()
    {
        $name = 'test_name';
        $data = ['name' => $name];
        $newInstance = \Memuya\Test\TestData\TestDto::fromArray($data);

        $this->assertInstanceOf(Dto::class, $newInstance);
        $this->assertSame($name, $newInstance->name);
    }

    public function testPropertyLabelIsUsedInExceptionWhenRequiredPropertyIsAbsent()
    {
        try {
            // This will always throw RequiredPropertyNotFoundException since we're not passing in a name.
            new class ([]) extends Dto {
                #[Label('Some label')]
                protected string $name;
            };
        } catch (RequiredPropertyNotFoundException $ex) {
            $this->assertStringContainsString('Some label', $ex->getFriendlyMessage());
        }
    }
}
