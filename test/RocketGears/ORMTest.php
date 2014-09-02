<?php

	use RocketGears\ORM;

	class NoTableNameSet extends RocketGears\ORM
	{
	}

	class NonStringTableNameSet extends RocketGears\ORM
	{
		protected $orm_table = array();
	}

	class EmptyTableNameSet extends RocketGears\ORM
	{
		protected $orm_table = '';
	}

	class User extends RocketGears\ORM
	{
		protected $orm_table = 'user';
	}


	class ORMTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * Fixture connection
		 */
		protected $DB = NULL;
		


		/**
		 * Fixture set up
		 */
		protected function setUp()
		{
			// Check if the connection has been made
			if($this->DB === NULL)
			{
				$this->DB = new \PDO('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');
			}
			
			// Create the test table
			$this->DB->exec("
				CREATE TABLE IF NOT EXISTS `user` (
					`user_id` int(10) NOT NULL AUTO_INCREMENT,
					`user_email_address` varchar(150) DEFAULT NULL,
					`user_password` char(40) DEFAULT NULL,
					`user_timezone` varchar(150) DEFAULT NULL,
					`user_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`user_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			");
			
			// Insert user 1
			$this->DB->exec("
				INSERT INTO
					user
				SET
					user_email_address = 'test@test.com',
					user_password = SHA1('password'),
					user_timezone = 'America/Los_Angeles'
			");
		}



		/**
		 * Fixture tear down
		 */
		protected function tearDown()
		{
			// Drop the user table
			$this->DB->exec("DROP TABLE `user`; ");
		}		
		
		
		
		/**
		 * Invalid connection DB name
		 * @expectedException RocketGears\ORMException
		 * @expectedExceptionCode 100
		 */
		public function testInvalidConnectionCredentialsDB()
		{
			ORM::init('mysql:host=localhost;dbname=doesnotexistdb', 'orm_username', 'orm_password');
		}



		/**
		 * Invalid connection username type
		 * @expectedException RocketGears\ORMException
		 * @expectedExceptionCode 100
		 */
		public function testInvalidConnectionCredentialsUsername()
		{
			ORM::init('mysql:host=localhost;dbname=doesnotexistdb', array(), 'orm_password');
		}



		/**
		 * Valid connection
		 */
		public function testValidConnectionCredentials()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');
			$this->assertEquals(get_class(ORM::$instance), 'PDO');
		}



		/**
		 * Table name not set
		 * @expectedException RocketGears\ORMException
		 * @expectedExceptionCode 300
		 */
		public function testTableNameNotSet()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');

			NoTableNameSet::load(1);
		}



		/**
		 * Non string table name
		 * @expectedException RocketGears\ORMException
		 * @expectedExceptionCode 300
		 */
		public function testTableNameNotString()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');

			NonStringTableNameSet::load(1);
		}



		/**
		 * Non string table name
		 * @expectedException RocketGears\ORMException
		 * @expectedExceptionCode 300
		 */
		public function testTableNameEmptyString()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');

			EmptyTableNameSet::load(1);
		}



		/**
		 * Direct call to ORM::load()
		 * @expectedException RocketGears\ORMException
		 * @expectedExceptionCode 200
		 */
		public function testLoadParentClassNotDefined()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');
			
			ORM::load(1);
		}


		/**
		 * Basic test for ORM::load()
		 */
		public function testLoadBasic()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');
			
			$user = User::load(1);
			
			$this->assertEquals($user->get('user_id'), '1');
		}



		/**
		 * Non string object ID passed to ORM::load()
		 * @expectedException RocketGears\ORMException
		 * @expectedExceptionCode 205
		 */
		public function testLoadInvalidObjectIDType()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');
			
			$user = User::load(array());
		}



		/**
		 * Basic test for ORM::get()
		 */
		public function testGetBasic()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');
			
			$user = User::load(1);
			
			$this->assertEquals($user->get('user_id'), '1');
		}



		/**
		 * Non string value for ORM::get()
		 * @expectedException RocketGears\ORMException
		 * @expectedExceptionCode 203
		 */
		public function testGetInvalidFieldType()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');
			
			$user = User::load(1);

			$user->get(array());
		}



		/**
		 * Non existant field for ORM::get()
		 * @expectedException RocketGears\ORMException
		 * @expectedExceptionCode 204
		 */
		public function testGetInvalidFieldName()
		{
			ORM::init('mysql:host=localhost;dbname=rocket_orm', 'orm_username', 'orm_password');
			
			$user = User::load(1);
			
			$user->get('doesnotexistfieldname');
		}


	}