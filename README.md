# Laravel Helpers

Helpers for Laravel 8 project

| Version | Supported |
| - | - |
| 8.*  | :white_check_mark: |
| 9.*  | :question: |

## How to Use

You may include the helper inside `Controller.php`. For more information, you can refer [PHP Traits](https://www.php.net/manual/en/language.oop5.traits.php).

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Zdirnecamlcs96\Helpers\Traits\Helpers; // Import here

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, Helpers; // Use here

}

```

## After install

Please remove `ValidatesRequests` Trait in `LoginController`, it will cause ambiguous error as this package overwrote the function inside the trait.

## Contributing

Thank you for considering contributing. Since this is for private usage, please contact me for the contribution guide.

## Laravel License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
