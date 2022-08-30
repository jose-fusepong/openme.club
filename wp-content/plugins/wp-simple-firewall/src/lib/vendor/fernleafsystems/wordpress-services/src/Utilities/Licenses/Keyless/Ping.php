<?php declare( strict_types=1 );

namespace FernleafSystems\Wordpress\Services\Utilities\Licenses\Keyless;

class Ping extends Base {

	const API_ACTION = 'ping';

	public function ping() :bool {
		$raw = $this->sendReq();
		return is_array( $raw ) && ( $raw[ 'ping' ] ?? '' ) === 'pong';
	}
}