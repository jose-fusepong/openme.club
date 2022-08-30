<?php declare( strict_types=1 );

namespace FernleafSystems\Wordpress\Services\Utilities\Net;

use FernleafSystems\Wordpress\Services\Services;

class VerifyHostToIP {

	public function run( string $ip, string $hostnameRegex ) :bool {
		$srvIP = Services::IP();

		$isVerifiedCrawlerBot = false;

		// 1. Can we resolve the IP to a hostname
		$hostname = @gethostbyaddr( $ip ); // returns the ip on failure
		if ( !empty( $hostname ) && ( $hostname !== $ip ) ) {

			// 2. Does the hostname match the pattern we're expecting?
			$isHostnameMatch = preg_match( $hostnameRegex, $hostname );
			if ( $isHostnameMatch ) {

				$forwardIP = gethostbyname( $hostname );

				// i.e. the IP could be resolved from the host.
				if ( $forwardIP !== $hostname && $srvIP->isValidIp( $forwardIP ) ) {

					// 3. Did the forward DNS lookup bring us back to the original IP? Win!
					if ( $srvIP->checkIp( $forwardIP, $ip ) ) {
						$isVerifiedCrawlerBot = true;
					}
					elseif ( $srvIP->getIpVersion( $forwardIP ) !== $srvIP->getIpVersion( $ip ) ) {
						// Perhaps the IP we started with was maybe IPv6, but the forward lookup was IPv4 (or vice-versa)
						// Now we need to test whether the rDNS for the new forward IP brings us back to the same hostname.
						$alternativeHostname = @gethostbyaddr( $forwardIP ); // returns the ip on failure
						$isVerifiedCrawlerBot = $alternativeHostname === $hostname;
					}
				}
			}
		}

		return $isVerifiedCrawlerBot;
	}
}