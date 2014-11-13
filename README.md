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
400              | Relationship not defined.
900              | An unknown error has occurred.

## Relationships

### One To One

User table definition:

| user             
| ---------------- 
| user_id
| role_id       
| name                    

Role table definition:

| role
| ----------------
| role_id
| name

```php
<?php
	
	/**
	 * User class definition
	 */
	class User extends RocketGears\ORM
	{
		protected $orm_table = 'user';
		
		public function __construct()
		{
			parent::__construct();
			$this->_has_one('role');
		}
	}

	/**
	 * Role class definition
	 */
	class Role extends RocketGears\ORM
	{
		protected $orm_table = 'role';
	}

	// Initialize ORM connection
	ORM::init('mysql:host=localhost;dbname=orm_database_name', 'orm_username', 'orm_password');
	
	// Load a user by ID
	$user = User::load(15);
	
	// Now get the user's role
	$role = $user->role();
	
	// Print the user role name
	echo "<p>{$role->get('name')}</p>";
	
?>
```

### One To Many

User table definition:

| user             
| ---------------- 
| user_id          
| name             

Address table definition:

| address
| ----------------
| address_id
| user_id
| street_1

```php
<?php
	
	/**
	 * User class definition
	 */
	class User extends RocketGears\ORM
	{
		protected $orm_table = 'user';
		
		public function __construct()
		{
			parent::__construct();
			$this->_has_many('address');
		}
	}

	/**
	 * Address class definition
	 */
	class Address extends RocketGears\ORM
	{
		protected $orm_table = 'address';
	}

	// Initialize ORM connection
	ORM::init('mysql:host=localhost;dbname=orm_database_name', 'orm_username', 'orm_password');
	
	// Load a user by ID
	$user = User::load(15);
	
	// Now get the user's addresses
	$addresses = $user->address();
	
	// Count the number of addresses returned
	echo "<p>User has {$addresses->count()} addresses.</p>";
	
	// Loop through addresses
	echo "<ol>";
	foreach($addresses as $address)
	{
		// Print the user address
		echo "<li>{$address->get('street_1')}</li>";
	}
	echo "</ol>";
	
?>
```