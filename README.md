# RocketGears ORM

An extremely minimal ORM for PHP that utilizes PDO for data source abstraction.

## Requirements

* [Composer](http://getcomposer.org)

## Installing Dependencies

To fetch dependencies into your local project, just run the install command of composer.phar.

```bash
$ php composer.phar install
```

OR

```bash
$ composer install
```

## Running Unit Tests

Run phpunit to check that everything is working.

```bash
$ php vendor/bin/phpunit
```

## Sample Usage
```php
<?php
	
	/**
	 * Minimal class definition
	 */
	class User extends RocketGears\ORM
	{
		protected $orm_table = 'user';
	}
	
	// Initialize ORM connection
	ORM::init('mysql:host=localhost;dbname=orm_database_name', 'orm_username', 'orm_password');
	
	// Load a user by ID
	$user = User::load(15);
	
	// Print the user's name
	echo $user->get('name');
	
?>
```

## Exceptions

All exceptions thrown will be of type RocketGears\ORMException.

Exception Code   | Description
---------------- | :-------------
100              | Invalid database connection string or credentials.
200              | Parent class not set.
201              | Database connection not initialized.
202              | Data object not initialized.
203              | String field name expected.
204              | Unknown field name: "[FIELD_NAME]"
205              | String or integer object ID expected.
900              | An unknown error has occurred.

