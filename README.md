# DTO

A simple PHP library for holding and transafering data. Each piece of data is required by default unless specified otherwise.

# Usage Example
Let's use an example where we want to have a DTO for creating a new user in your application. All you need to do is create a class that extends `Memuya\Dto\Dto` and set the properies on that class as protected.
```php
namespace App\Your\Namespace;

use Memuya\Dto\Dto;

class CreateUserDto extends Dto
{
    protected string $name;

    protected string $password;
}
```
To then use this DTO, create a new instance, passing in the defined properties and their values into the contructor as an array or as arguments.

```php
$createUserDto = new CreateUserDto([
    'name' => 'Bob',
    'password' => 'some_pass',
]);
// or
$createUserDto = CreateUserDto::fromArray([
    'name' => 'Bob',
    'password' => 'some_pass',
]);
// or
$createUserDto = new CreateUserDto(
    name: 'Bob',
    password: 'some_pass'
);

echo $createUserDto->name; // Bob
echo $createUserDto->password; // some_pass
```

If you don't pass in one of the properties when contructing your DTO, it will throw an exception. In this example, we've forgotten to pass in the `password`.
```php
try {
    $createUserDto = new CreateUserDto(['name' => 'Bob']);
} catch (\Memuya\Dto\Exceptions\RequiredPropertyNotFoundException $ex) {
    echo $ex->getMessage(); // Output -> 'password' is a required property on \App\Your\Namespace\CreateUserDto.
    // or
    echo $ex->getFriendlyMessage(); // Output -> 'password' is required.
}
```

## Optional Properties
By default, all defined properties are required. If you want to mark a property as optional, you may add the `Optional` attribute to it.

Accessing an optional property that has not been set will return `null`.
```php
namespace App\Your\Namespace;

use Memuya\Dto\Dto;
use Memuya\Dto\Types\Optional;
use Memuya\Dto\Exceptions\RequiredPropertyNotFoundException;

class CreateUserDto extends Dto
{
    protected string $name;

    #[Optional]
    protected string $password;
}

echo $createUserDto->name; // Bob
echo $createUserDto->password; // null
```

You can also mark required properties with the `Required` attribute if you would like visibility in your DTO. This is optional as all properties are required by default.

```php
namespace App\Your\Namespace;

use Memuya\Dto\Dto;
use Memuya\Dto\Types\Optional;
use Memuya\Dto\Types\Required;

class CreateUserDto extends Dto
{
    #[Required]
    protected string $name;

    #[Optional]
    protected string $password;
}
```