<?php declare( strict_types=1 );

namespace FernleafSystems\Wordpress\Services\Utilities;

class Arrays {

	public static function SetAllValuesTo( array $arrayToSet, $value ) :array {
		return array_fill_keys( array_keys( $arrayToSet ), $value );
	}
}