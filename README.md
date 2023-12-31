# DTO

A simple PHP library for holding and transafering data. Each piece of data can be defined as either optinal or required.

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
To then use this DTO, create a new instance, passing in the defined properties and their values into the contructor as an array.

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

echo $createUserDto->name; // Bob
echo $createUserDto->password; // some_pass
```

If you don't pass in one of the properties when contructing your DTO, when you try and access it, the DTO will return `null`.
```php
$createUserDto = new CreateUserDto(['name' => 'Bob']);

echo $createUserDto->name; // Bob
echo $createUserDto->password; // null
```

## Required Properties
By default, all defined properties are optional. Optionally, you may add the `Optional` attribute to your optional properties for visibility and clarity.

If you would like to mark a property as required, use the `Required` attribute.
```php
namespace App\Your\Namespace;

use Memuya\Dto\Dto;
use Memuya\Dto\Types\Required;
use Memuya\Dto\Types\Optional;
use Memuya\Dto\Exceptions\RequiredPropertyNotFoundException;

class CreateUserDto extends Dto
{
    #[Optional]
    protected string $name;

    #[Required]
    protected string $password;
}
```
Now if you don't pass in `password`, an exception will be thrown.
```php
try {
    $createUserDto = new CreateUserDto(['name' => 'Bob']);
} catch (RequiredPropertyNotFoundException $ex) {
    echo $ex->getMessage(); // Output -> 'password' is a required property on \App\Your\Namespace\CreateUserDto.
    // or
    echo $ex->getFriendlyMessage(); // Output -> 'password' is required.
}
```