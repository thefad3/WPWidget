<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! function_exists( 'array_search_taxonomy_fields' ) ){

	function array_search_taxonomy_fields( $field_groups, $taxonomy ) {

		$meta_keys = array();
		$search_keys = array();

		if( isset( $field_groups['job'] ) || isset( $field_groups[ 'company' ] ) || isset( $field_groups[ 'resume' ] ) ) {
			$search_keys = array_keys( $field_groups );
			// Loop through each field group (job, company, resume)
			foreach ( $search_keys as $group ) {
				// Loop through all fields in group (job_phone, etc)
				foreach ( $field_groups[ $group ] as $field => $conf ) {
					if ( isset( $conf[ 'taxonomy' ] ) && $conf[ 'taxonomy' ] === $taxonomy ) return $field;
				}
			}
		} else {
			// Single array was passed
			// Loop through all fields in group (job_phone, etc)
			foreach ( $field_groups as $field => $conf ) {
				if ( isset( $conf[ 'taxonomy' ] ) && $conf[ 'taxonomy' ] === $taxonomy ) return $field;
			}
		}

		return false;
	}

}

if ( ! function_exists( 'array_column' ) ) {
	/**
	 * Returns the values from a single column of the input array, identified by
	 * the $columnKey.
	 *
	 * Optionally, you may provide an $indexKey to index the values in the returned
	 * array by the values from the $indexKey column in the input array.
	 *
	 * @param array $input     A multi-dimensional array (record set) from which to pull
	 *                         a column of values.
	 * @param mixed $columnKey The column of values to return. This value may be the
	 *                         integer key of the column you wish to retrieve, or it
	 *                         may be the string key name for an associative array.
	 * @param mixed $indexKey  (Optional.) The column to use as the index/keys for
	 *                         the returned array. This value may be the integer key
	 *                         of the column, or it may be the string key name.
	 *
	 * @return array
	 */
	function array_column( $input = NULL, $columnKey = NULL, $indexKey = NULL ) {

		// Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc   = func_num_args();
		$params = func_get_args();

		if ( $argc < 2 ) {
			trigger_error( "array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING );

			return NULL;
		}

		if ( ! is_array( $params[ 0 ] ) ) {
			trigger_error(
				'array_column() expects parameter 1 to be array, ' . gettype( $params[ 0 ] ) . ' given',
				E_USER_WARNING
			);

			return NULL;
		}

		if ( ! is_int( $params[ 1 ] )
		     && ! is_float( $params[ 1 ] )
		     && ! is_string( $params[ 1 ] )
		     && $params[ 1 ] !== NULL
		     && ! ( is_object( $params[ 1 ] ) && method_exists( $params[ 1 ], '__toString' ) )
		) {
			trigger_error( 'array_column(): The column key should be either a string or an integer', E_USER_WARNING );

			return FALSE;
		}

		if ( isset( $params[ 2 ] )
		     && ! is_int( $params[ 2 ] )
		     && ! is_float( $params[ 2 ] )
		     && ! is_string( $params[ 2 ] )
		     && ! ( is_object( $params[ 2 ] ) && method_exists( $params[ 2 ], '__toString' ) )
		) {
			trigger_error( 'array_column(): The index key should be either a string or an integer', E_USER_WARNING );

			return FALSE;
		}

		$paramsInput     = $params[ 0 ];
		$paramsColumnKey = ( $params[ 1 ] !== NULL ) ? (string) $params[ 1 ] : NULL;

		$paramsIndexKey = NULL;
		if ( isset( $params[ 2 ] ) ) {
			if ( is_float( $params[ 2 ] ) || is_int( $params[ 2 ] ) ) {
				$paramsIndexKey = (int) $params[ 2 ];
			} else {
				$paramsIndexKey = (string) $params[ 2 ];
			}
		}

		$resultArray = array();

		foreach ( $paramsInput as $row ) {
			$key    = $value = NULL;
			$keySet = $valueSet = FALSE;

			if ( $paramsIndexKey !== NULL && array_key_exists( $paramsIndexKey, $row ) ) {
				$keySet = TRUE;
				$key    = (string) $row[ $paramsIndexKey ];
			}

			if ( $paramsColumnKey === NULL ) {
				$valueSet = TRUE;
				$value    = $row;
			} elseif ( is_array( $row ) && array_key_exists( $paramsColumnKey, $row ) ) {
				$valueSet = TRUE;
				$value    = $row[ $paramsColumnKey ];
			}

			if ( $valueSet ) {
				if ( $keySet ) {
					$resultArray[ $key ] = $value;
				} else {
					$resultArray[] = $value;
				}
			}

		}

		return $resultArray;
	}

}