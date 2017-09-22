# RBAC - Role Based Access Control for Laravel

Another role based access control package for Laravel 5.

### Requirements

- Barista works with PHP 5.6 or above.

### Installation

```bash
$ composer require gguney/rbac
```

### Usage
Add package's service provider to your config/app.php

```php
...
        GGuney\RBAC\RBACServiceProvicer::class,
...
		    'aliases' => [
...
        'RBAC' => RBAC\Facades\RBAC::class,
    ],
...
```


### Author

Gökhan Güney - <gokhanguneygg@gmail.com><br />

### License

RBAC is licensed under the MIT License - see the `LICENSE` file for details
