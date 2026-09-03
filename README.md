# Authorization

![Chevere](chevere.svg)

[![Build](https://img.shields.io/github/actions/workflow/status/chevere/authorization/test.yml?branch=1.0&style=flat-square)](https://github.com/chevere/authorization/actions)
![Code size](https://img.shields.io/github/languages/code-size/chevere/authorization?style=flat-square)
[![Apache-2.0](https://img.shields.io/github/license/chevere/authorization?style=flat-square)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-blueviolet?style=flat-square)](https://phpstan.org/)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchevere%2Fauthorization%2F1.0)](https://dashboard.stryker-mutator.io/reports/github.com/chevere/authorization/1.0)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=chevere_authorization&metric=alert_status)](https://sonarcloud.io/dashboard?id=chevere_authorization)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_authorization&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chevere_authorization)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_authorization&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chevere_authorization)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_authorization&metric=security_rating)](https://sonarcloud.io/dashboard?id=chevere_authorization)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=chevere_authorization&metric=coverage)](https://sonarcloud.io/dashboard?id=chevere_authorization)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=chevere_authorization&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chevere_authorization)
[![CodeFactor](https://www.codefactor.io/repository/github/chevere/authorization/badge)](https://www.codefactor.io/repository/github/chevere/authorization)

## Summary

Authorization is a custom RBAC implementation that allows to define permissions, which are granted by roles which user belong to. It works by assigning unique bit masks for roles, which define a permissions [RolesMask](#rolesmask).

## Installing

Authorization is available through [Packagist](https://packagist.org/packages/chevere/authorization) and the repository source is at [chevere/authorization](https://github.com/chevere/authorization).

```sh
composer require chevere/authorization
```

## Quick Start

```php
use Chevere\Authorization\Role;
use Chevere\Authorization\Roles;
use Chevere\Authorization\RolesMask;

$user = new Role(
    4, // bit
    'user', // name
    PostPermission::View // permission granted
);
$editor = new Role(
    2,
    'editor',
    $user, // inherits user role permissions
    PostPermission::Edit,
    PostPermission::Create
);
$admin = new Role(
    1,
    'admin',
    ...PostPermission::values(),
    ...UserPermission::values()
);
$roles = new Roles($admin, $editor, $user);
$rolesMask = new RolesMask($roles);
// Assertion
$rolesMask($bitmask, ...$permission);
// Validation
$bool = $rolesMask->contains($bitmask, ...$permission);
```

## Permissions

Permissions are defined as string backed enum implementing `PermissionInterface` with the `PermissionTrait`. It contains one or more cases, each one representing a permission.

```php
use Chevere\Authorization\Interfaces\PermissionInterface;
use Chevere\Authorization\Traits\PermissionTrait;

enum PostPermission: string implements PermissionInterface
{
    use PermissionTrait;

    case Create = 'post.create';
    case Delete = 'post.delete';
    case Edit = 'post.edit';
    case View = 'post.view';
}
```

## Role

A Role is defined by a **bit** (power of two), its **name** and **permissions/roles** attached to it. If the role inherits from another role(s), it will have all the permissions of the inherited role(s).

```php
use Chevere\Authorization\Role;

$user = new Role(
    4,
    'user',
    PostPermission::View
);
$editor = new Role(
    2,
    'editor',
    $user, // inherits user role
    PostPermission::Edit,
    PostPermission::Create
);
```

### Role bit

Use method `bit()` to retrieve the role bit.

```php
$user->bit(); // 4
$editor->bit(); // 2
```

### Role name

Use method `name()` to retrieve the role name.

```php
$user->name(); // 'user'
$editor->name(); // 'editor'
```

### Role mask

Use method `mask()` to retrieve the role mask. Will reflect the role bit + the inherited role(s) bit.

```php
$user->mask(); // 4
$editor->mask(); // 6 (2 | 4)
```

### Role inherits

Use method `inherits()` to retrieve the inherited role(s).

```php
$user->inherits(); // []
$editor->inherits(); // [$user]
```

### Role permissions

Use method `permissions()` to retrieve the role permissions.

```php
$user->permissions(); // [PostPermission::View]
$editor->permissions(); // [PostPermission::View, PostPermission::Edit, PostPermission::Create]
```

### Role grants

Use method `grants()` to retrieve the role grants granted by the role, excluding inherited permissions.

```php
$user->grants(); // [PostPermission::View]
$editor->grants(); // [PostPermission::Edit, PostPermission::Create]
```

## Assigning Role(s)

To assign Role(s) to a participant, compute the bit mask (sum of bits) of the roles they belong to.

```php
$marketing = new Role(16, 'marketing', ...);
$staff = new Role(8, 'staff', ...);
$staffUser = $user->setBitmask($staff->bit());
$comboUser = $user->setBitmask(
    $staff->bit() | $marketing->bit()
);
```

## Roles

Use Roles to collect Role objects, it will detect bit and name collisions. It will also compute the sum of all role bits to create a bit mask.

```php
use Chevere\Authorization\Roles;

$roles = new Roles($user, $admin, $editor);
```

### Roles mask

Use method `mask()` to retrieve the roles mask, reflecting the sum of all role bits.

```php
$roles->mask(); // 7 (1 | 2 | 4)
```

### Roles find

Use method `find()` to retrieve a role by name.

```php
$roles->find('admin'); // $admin
```

### Roles has

Use method `has()` to check if a role exists by bit(s).

```php
$roles->has(2); // true
$roles->has(1, 2); // true
```

### Roles get

Use method `get()` to retrieve a role by bit.

```php
$roles->get(2); // $admin
```

### Roles forMask

Use method `forMask()` to retrieve all roles that are part of a bit mask.

```php
$roles->forMask(1 | 2); // Roles containing $user(1) and $admin(2)
$roles->forMask(3); // Same as above, 3 = 1 | 2
```

### Roles permissions

Use method `permissions()` to retrieve all permissions from all roles.

```php
$roles->permissions();
```

## RolesMask

RolesMask creates a mask from roles, which is used to check if a participant has the required permissions to perform an action. It maps each detected permission to a bit mask combination of the roles that grant that permission.

By doing this, checking on a participant's permissions is a simple bitwise operation.

```php
use Chevere\Authorization\RolesMask;

$rolesMask = new RolesMask($roles);
```

### Assert permission

```php
$rolesMask($userMask, ...$permission);
$rolesMask(1, PostPermission::CREATE);
$rolesMask(2, PostPermission::DELETE);
```

### Contains permission

```php
$bool = $rolesMask->contains($userMask, ...$permission);
```

## Limitations

This system uses a bitmask to store roles, which limits it to **63 combinable roles** (2⁶³ − 1). This is because V1 is implemented using an `INT` column, whose maximum value on 64-bit systems is `PHP_INT_MAX` (2<sup>63</sup> − 1).

## Documentation

Documentation is available at [chevere.org](https://chevere.org/packages/authorization).

## License

Copyright [Rodolfo Berrios A.](https://rodolfoberrios.com/)

Chevere is licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
