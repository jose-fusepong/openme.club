<?php

namespace FernleafSystems\Wordpress\Services\Utilities\Net;

use FernleafSystems\Wordpress\Services\Services;

class FindSourceFromIp extends BaseIP {

	/**
	 * @param string $ip
	 * @return string|null
	 */
	public function run( string $ip ) {
		$theSource = null;
		foreach ( $this->getSources() as $source ) {
			try {
				if ( Services::IP()->checkIp( $ip, $this->getIpsFromSource( $source ) ) ) {
					$theSource = $source;
					break;
				}
			}
			catch ( \Exception $e ) {
			}
		}
		return $theSource;
	}
}