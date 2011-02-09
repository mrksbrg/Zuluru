<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Application model for Cake.
 *
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.model
 */
class AppModel extends Model {
	// Make all models containable; we make a lot of use of this feature for limiting
	// which related data is loaded in any given find.
	// TODO: Add trim, ProperCase behaviours where appropriate
	var $actsAs = array('Containable');

	// Some common-but-non-standard regexes we need in multiple models
	const NAME_REGEX = '/^[ a-z0-9\-\.\',]*$/i';
	const EXTENDED_NAME_REGEX = '/^[ 0-9a-z\-\.\'",\!\?@&()]*$/i';
	const ADDRESS_REGEX = '/^[ 0-9a-z\-\.\',#&]*$/i';

	//
	// Generic afterFind function, which handles data in the many different
	// layouts it might appear in, and calls the model-specific _afterFind
	// method (if it exists) on the individual records for any adjustments
	// that are required.
	//
	// The records passed into _afterFind will *always* have the alias as an
	// index, and may have other indices as well for related models, or related
	// models may be "under" the main record, depending on the query.  Trying
	// to generically handle all of those situations is just too much!
	//
	function afterFind ($results) {
		if (method_exists ($this, '_afterFind') && !empty ($results)) {
			// The data can come in many forms
			if (array_key_exists(0, $results)) {
				foreach ($results as $key => $result) {
					$results[$key] = $this->afterFind ($result);
				}
			} else if (array_key_exists($this->alias, $results)) {
				if (empty ($results[$this->alias])) {
					// Don't do anything with empty records
				} else if (array_key_exists(0, $results[$this->alias])) {
					$results = $this->afterFind ($results[$this->alias]);
				} else {
					$results = $this->_afterFind ($results);
				}
			} else if (count($results) == 1 && array_key_exists ('count', $results)) {
				// Don't do anything with records that are just pagination counts
			} else {
				$results = $this->_afterFind (array($this->alias => $results));
				$results = $results[$this->alias];
			}
		}
		return $results;
	}

	//
	// Validation helpers
	//

	function mustmatch($check, $field1, $field2) {
		$data = current($this->data);
		return $data[$field1] === $data[$field2];
	}

	function matchpassword($check) {
		$value = array_values($check);
		$value = $value[0];
		if (Configure::read ('security.salted_hash')) {
			$compare = Security::hash($value);
		} else {
			$compare = Security::hash($value, null, '');
		}

		return ($compare == $this->data['User']['password']);
	}

	function mustnotmatch($check, $field1, $field2) {
		$data = current($this->data);
		if (!array_key_exists ($field1, $data) || !array_key_exists ($field2, $data)) {
			return true;
		}
		return $data[$field1] !== $data[$field2];
	}

	function inconfig($check, $config) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		return array_key_exists($value, Configure::read($config));
	}

	function indateconfig($check, $config) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		if (!is_array($value)) {
			$year = date ('Y', strtotime ($value));
		} else {
			if (!array_key_exists ('year', $value)) {
				return false;
			}
			$year = $value['year'];
		}

		$min = Configure::read("options.year.$config.min");
		$max = Configure::read("options.year.$config.max");
		if ($min === null || $max === null) {
			return false;
		}

		return ($min <= $year && $year <= $max);
	}

	function greaterdate($check, $field) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		$data = current($this->data);
		return ($value > $data[$field]);
	}

	// Check a combined date and time, using standard separate date and time validators
	function datetime($check) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		list ($date, $time) = explode (' ', $value, 2);
		$Validation =& Validation::getInstance();
		return ($Validation->date ($date) && $Validation->time ($time));
	}

	function inquery($check, $model, $field) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		$model_obj = ClassRegistry::init($model);
		$values = $model_obj->find('list', array('fields' => $field));

		return in_array ($value, $values);
	}

	/**
	 * Validate that a number is in specified range.
	 * if $lower and $upper are not set, will return true if
	 * $check is a legal finite on this platform.
	 * Copied from the main "range" validation function, but
	 * altered to be an inclusive range instead of exclusive.
	 *
	 * @param string $check Value to check
	 * @param integer $lower Lower limit
	 * @param integer $upper Upper limit
	 * @return boolean Success
	 * @access public
	 */
	function inclusive_range($check, $lower = null, $upper = null) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		if (!is_numeric($value)) {
			return false;
		}
		if (isset($lower) && isset($upper)) {
			return ($value >= $lower && $value <= $upper);
		}
		return is_finite($value);
	}

	/**
	 * Handle validation of a questionnaire response
	 *
	 * @param mixed $check The data to check for validity
	 * @param mixed $rule The rule to check with
	 * @return mixed true if the data is valid, false otherwise
	 *
	 */
	function response($check, $rule) {
		$value = array_shift ($check);
		$value = $value['answer'];

		$Validation =& Validation::getInstance();
		if (method_exists($Validation, $rule)) {
			return $Validation->dispatchMethod($rule, array($value));
		} elseif (!is_array($rule)) {
			return preg_match($rule, $value);
		} elseif (Configure::read('debug') > 0) {
			trigger_error(sprintf(__('Could not find validation handler %s for %s', true), $rule, 'response'), E_USER_WARNING);
		}

		return false;
	}

	function response_select($check, $opts, $required) {
		$value = array_shift ($check);
		$value = $value['answer_id'];

		// A value from the provided list of options is okay
		if (in_array ($value, $opts))
			return true;
		// If the question is not required, a blank value is okay
		if ($value === '' && !$required)
			return true;
		// Nothing else is okay
		return false;
	}
}
?>