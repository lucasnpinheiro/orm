<?php
	
	namespace RocketGears;
	
	/**
	 * ORM base class
	 */
	class ORM
	{
		/**
		 * Static instance tracker
		 */
		public static $instance = NULL;
		
		/**
		 * Exception strings
		 */
		public static $exception_strings = array(
			'100' => 'Invalid database connection string or credentials.',
			'200' => 'Parent class not set.',

			'201' => 'Database connection not initialized.',
			'202' => 'Data object not initialized.',
			'203' => 'String field name expected.',
			'204' => 'Unknown field name: "[FIELD_NAME]".',
			'205' => 'String or integer object ID expected.',
			
			'300' => 'Object table name not set.',
			
			'400' => 'Relationship not defined.',
			
			'900' => 'An unknown error has occurred.',
		);
		
		/**
		 * Object data
		 */
		protected $_data = array();

		/**
		 * Array for defining one to many relationships
		 */
		protected $_to_many = array();

		/**
		 * Array for defining one to one relationships
		 */
		protected $_to_one = array();
		
		/**
		 * Constructor
		 */
		public function __construct(){ }



		/**
		 * Static initialization
		 * @param string $pdo_connection_string
		 * @param string $connection_username
		 * @param string $connection_password
		 * @return bool
		 * @throws ORMException on connection error
		 */
		public static function init($pdo_connection_string, $connection_username, $connection_password)
		{
			// Ensure only string parameters were passed
			if(!is_string($pdo_connection_string) || !is_string($connection_username) || !is_string($connection_password))
			{
				throw new ORMException(ORM::$exception_strings[100], 100);
			}
			
			try
			{
				// Initialize PDO object with connection parameters
				self::$instance = new \PDO($pdo_connection_string, $connection_username, $connection_password);
			}catch(\PDOException $e){
				// Wrap PDO exception to generic ORM exception
				throw new ORMException(ORM::$exception_strings[100], 100);
			}

			return;
		}
		
		
		
		/**
		 * Load an object by ID
		 * @param int|string $object_id
		 * @param array $data
		 * @return ORM
		 * @throws ORMException
		 */
		public static function load($object_id, $data = NULL)
		{
			// Check that the called class was inherited
			$parent = get_called_class();
			if($parent == 'RocketGears\ORM')
			{
				// ORM::load() called
				throw new ORMException(ORM::$exception_strings[200], 200);
			}

			// Initialize instance of parent class
			$parent = new $parent();

			// Proxy call to parent
			return $parent->_load($object_id, $data);
		}



		private function _load($object_id, $data = NULL)
		{
			// Check that the object ID is a string
			if(is_string($object_id) === FALSE && is_numeric($object_id) === FALSE)
			{
				throw new ORMException(ORM::$exception_strings[205], 205);
			}

			// Check initilization
			$this->_check_init();
			
			// Check if the data should be loaded from the method argument
			if($data !== NULL && is_array($data) === TRUE)
			{
				$this->_data = $data;
				return $this;
			}
			
			// Get primary key
			$prepare = self::$instance->prepare("SELECT * FROM `{$this->_get_table_name()}` WHERE `{$this->_get_primary_key()}` = :object_id LIMIT 1");
			$prepare->execute(array('object_id' => $object_id));
			$data = $prepare->fetch(\PDO::FETCH_ASSOC);
			if($data !== FALSE)
			{
				$this->_data = $data;
			}
			
			return $this;
		}



		/**
		 * Set one to many relationship
		 * @param string $table
		 * @param string $map_table
		 * @return ORM
		 */
		public function _has_many($table, $map_table = NULL)
		{
			if($map_table === NULL)
			{
				// Set relationship
				$this->_to_many[$table] = $table;

				// Done
				return $this;
			}else{
				// Set relationship
				$this->_to_many_map[$table] = $map_table;

				// Done
				return $this;
			}

			throw new Exception("Relationship table does not exist.");
		}



		/**
		 * @brief Set one to one relationship.
		 * 
		 * @param string $table - name of the table/model
		 * @param string $map_table - table with id to id relationship mapping
		 * @return object - ORM
		 */
		public function _has_one($table, $map_table = NULL)
		{
			if($map_table === NULL)
			{
				// Set relationship
				$this->_to_one[$table] = $table;

				// Done
				return $this;
			}else{
				// Set relationship
				$this->_to_one_map[$table] = $map_table;

				// Done
				return $this;
			}
		}



		/**
		 * @brief Magic method override.
		 * 
		 * @param string $name - method to call
		 * @param array $arguments - parameters for method
		 * @return mixed - instance of a ORM_Base derived class | array of ORM_Wrapper | unknown
		 * @throws Exception - if unable to identify/locate the method $name
		 */
		public function __call($name, $arguments)
		{
			// One to many
			if(isset($this->_to_many[$name]))
			{
				// Return wrapper
				$return = new ORMWrapper;

				// Get model records
				$prepare = self::$instance->prepare("SELECT * FROM `{$this->_to_many[$name]}` WHERE `{$this->_get_table_name()}_id` = :object_id");
				if($prepare->execute(array('object_id' => $this->_data["{$this->_get_table_name()}_id"])))
				{
					$model_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $this->_to_many[$name])));
					while($data = $prepare->fetch(\PDO::FETCH_ASSOC))
					{
						eval('$object = '.$model_name.'::load(' . $data[$name."_id"] . ', $data);');
						$return->push($object);
					}
				}
				return $return;
			}
				
			// One to one
			if(isset($this->_to_one[$name]))
			{
				$model_name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $this->_to_one[$name])));;
				eval('$return = '.$model_name.'::load(' . $this->_data[$name."_id"] . ');');
				return $return;
			}
							
			throw new ORMException(ORM::$exception_strings[400], 400);
		}

		
		/**
		 * Get a data field
		 * @param string $field_name
		 * @throws ORMException
		 */
		public function get($field_name)
		{
			// Check that the object is loaded
			if($this->_data === NULL)
			{
				// No object is loaded
				throw new ORMException(ORM::$exception_strings[202], 202);
			}

			// Check that the field name is a string
			else if(is_string($field_name) === FALSE && is_numeric($field_name) === FALSE)
			{
				// String field name expected
				throw new ORMException(ORM::$exception_strings[203], 203);
			}

			// Check that the requested field exists
			else if(isset($this->_data[$field_name]) === FALSE)
			{
				// Field is not set
				throw new ORMException(str_replace('[FIELD_NAME]', $field_name, ORM::$exception_strings[204]), 204);
			}

			// Field is good
			else if(isset($this->_data[$field_name]) === TRUE)
			{
				return $this->_data[$field_name];
			}

			// Unknown error
			throw new ORMException(ORM::$exception_strings[900], 900);
		}
		
		
		
		/**
		 * Ensure the ORM object is initialized
		 * @return bool
		 * @throws ORMException on database connection not initialized
		 */
		private function _check_init()
		{
			if(self::$instance === NULL)
			{
				throw new ORMException(ORM::$exception_strings[201], 201);
			}
			return TRUE;
		}
		
		
		
		/**
		 * Get the table name from the class definition
		 * @return string
		 * @throws ORMException on invalid table name (string check only, does not verify DB)
		 */
		private function _get_table_name()
		{
			// Make sure the defined table name is set and is a string
			if(isset($this->orm_table) === FALSE || empty($this->orm_table) === TRUE || is_string($this->orm_table) === FALSE)
			{
				throw new ORMException(ORM::$exception_strings[300], 300);
			}
			
			// Return table name
			return $this->orm_table;
		}



		private function _get_primary_key()
		{
			return strtolower($this->_get_table_name()) . "_id";
		}

	}
