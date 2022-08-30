<?php

namespace FernleafSystems\Wordpress\Services\Utilities;

use FernleafSystems\Wordpress\Services\Utilities\File\ConvertLineEndings;

class DataManipulation {

	/**
	 * @param string $path
	 * @return string
	 */
	public function convertLineEndingsDosToLinux( $path ) :string {
		return ( new ConvertLineEndings() )->fileDosToLinux( $path );
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public function convertLineEndingsLinuxToDos( $path ) :string {
		return ( new ConvertLineEndings() )->fileLinuxToDos( $path );
	}

	/**
	 * @param array $toConvert
	 * @return string
	 */
	public function convertArrayToJavascriptDataString( $toConvert ) {
		$asJS = '';
		foreach ( $toConvert as $key => $value ) {
			$asJS .= sprintf( "'%s':'%s',", $key, $value );
		}
		return trim( $asJS, ',' );
	}

	/**
	 * @param array $array
	 * @return \stdClass
	 */
	public function convertArrayToStdClass( $array ) :\stdClass {
		$object = new \stdClass();
		if ( !empty( $array ) && is_array( $array ) ) {
			foreach ( $array as $key => $mValue ) {
				$object->{$key} = $mValue;
			}
		}
		return $object;
	}

	/**
	 * @param \stdClass $stdClass
	 * @return array
	 */
	public function convertStdClassToArray( $stdClass ) {
		return json_decode( json_encode( $stdClass ), true );
	}

	/**
	 * @param array    $array
	 * @param callable $callable
	 * @return array
	 */
	public function arrayMapRecursive( $array, $callable ) {
		$aMapped = [];
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$aMapped[ $key ] = $this->arrayMapRecursive( $value, $callable );
			}
			else {
				$aMapped[ $key ] = call_user_func( $callable, $value );
			}
		}
		return $aMapped;
	}

	/**
	 * @param mixed $args,...
	 * @return array
	 */
	public function mergeArraysRecursive( $args ) {
		$aArgs = array_values( array_filter( func_get_args(), 'is_array' ) );
		switch ( count( $aArgs ) ) {

			case 0:
				$aResult = [];
				break;

			case 1:
				$aResult = array_shift( $aArgs );
				break;

			case 2:
				list( $aResult, $aArray2 ) = $aArgs;
				foreach ( $aArray2 as $key => $Value ) {
					if ( !isset( $aResult[ $key ] ) ) {
						$aResult[ $key ] = $Value;
					}
//					elseif ( is_int( $key ) ) { behaviour is not as expected.
//						$aResult[] = $Value;
//					}
					elseif ( !is_array( $aResult[ $key ] ) || !is_array( $Value ) ) {
						$aResult[ $key ] = $Value;
					}
					else {
						$aResult[ $key ] = $this->mergeArraysRecursive( $aResult[ $key ], $Value );
					}
				}
				break;

			default:
				$aResult = array_shift( $aArgs );
				foreach ( $aArgs as $aArg ) {
					$aResult = $this->mergeArraysRecursive( $aResult, $aArg );
				}
				break;
		}

		return $aResult;
	}

	/**
	 * note: employs strict search comparison
	 * @param array $aArray
	 * @param mixed $mValue
	 * @param bool  $bFirstOnly - set true to only remove the first element found of this value
	 * @return array
	 */
	public function removeFromArrayByValue( $aArray, $mValue, $bFirstOnly = false ) {
		$aKeys = [];

		if ( $bFirstOnly ) {
			$mKey = array_search( $mValue, $aArray, true );
			if ( $mKey !== false ) {
				$aKeys[] = $mKey;
			}
		}
		else {
			$aKeys = array_keys( $aArray, $mValue, true );
		}

		foreach ( $aKeys as $mKey ) {
			unset( $aArray[ $mKey ] );
		}

		return $aArray;
	}

	/**
	 * @param array $aSubjectArray
	 * @param mixed $mValue
	 * @param int   $nDesiredPosition
	 * @return array
	 */
	public function setArrayValueToPosition( $aSubjectArray, $mValue, $nDesiredPosition ) {

		if ( $nDesiredPosition < 0 ) {
			return $aSubjectArray;
		}

		$nMaxPossiblePosition = count( $aSubjectArray ) - 1;
		if ( $nDesiredPosition > $nMaxPossiblePosition ) {
			$nDesiredPosition = $nMaxPossiblePosition;
		}

		$nPosition = array_search( $mValue, $aSubjectArray );
		if ( $nPosition !== false && $nPosition != $nDesiredPosition ) {

			// remove existing and reset index
			unset( $aSubjectArray[ $nPosition ] );
			$aSubjectArray = array_values( $aSubjectArray );

			// insert and update
			// http://stackoverflow.com/questions/3797239/insert-new-item-in-array-on-any-position-in-php
			array_splice( $aSubjectArray, $nDesiredPosition, 0, $mValue );
		}

		return $aSubjectArray;
	}

	/**
	 * @param array $aA
	 * @return array
	 */
	public function shuffleArray( $aA ) {
		$aKeys = array_keys( $aA );
		shuffle( $aKeys );
		return array_merge( array_flip( $aKeys ), $aA );
	}
}