<?php

	namespace RocketGears;
	
	/**
	 * ORM wrapper class
	 */
	class ORMWrapper implements \Iterator, \Countable
	{
		/**
		 * @var $position
		 * @brief integer - current position in the data array
		 */
		private $position = 0;
		
		/**
		 * @var $array
		 * @brief array of objects to iterate through 
		 */
		private $array = array();

		
		public function __construct()
		{
			$this->position = 0;
		}
		
		/**
		 * @brief Resets the position to the beginning of the data.
		 */
		function rewind()
		{
			$this->position = 0;
		}

		/**
		 * @brief Returns the current item in the data.
		 * 
		 * @return mixed - object from data array, null if none found
		 */
		function current()
		{
			if($this->valid())
			{
				return $this->array[$this->position];
			}
			return NULL;
		}
		
		
		/**
		 * @brief Returns the current position
		 *  
		 * @return integer - current position in the data
		 */
		function key()
		{
			return $this->position;
		}
		
		
		/**
		 * @brief Moves the position forward
		 */
		function next()
		{
			++$this->position;
		}
		
		
		/**
		 * @brief Checks if current item is a valid object
		 * 
		 * @return bool - true if valid object, false otherwise
		 */
		function valid()
		{
			return isset($this->array[$this->position]);
		}
		
		
		/**
		 * @brief Counts the number of data items
		 * 
		 * @return integer - number of data items
		 */
		function count()
		{
			return count($this->array);
		}
		
		
		/**
		 * @brief Return first data item
		 * 
		 * @return mixed -  first object in data, or NULL if invalid
		 */
		function first()
		{
			$this->rewind();
			if($this->valid())
			{
				return $this->current();
			}
			return NULL;
		}
		
		
		/**
		 * @brief Push an item onto the data array
		 * 
		 * @param object $object - item to add to the data array
		 */
		function push($object)
		{
			$this->array[] = $object;
		}
	}
