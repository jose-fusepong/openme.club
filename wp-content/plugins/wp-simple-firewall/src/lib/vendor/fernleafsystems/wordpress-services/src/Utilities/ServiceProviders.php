<?php

namespace FernleafSystems\Wordpress\Services\Utilities;

use FernleafSystems\Wordpress\Services\Services;
use FernleafSystems\Wordpress\Services\Utilities\Integrations\WpHashes\Services\{
	IPs,
	ProviderIPs
};
use FernleafSystems\Wordpress\Services\Utilities\Net\IpID;
use FernleafSystems\Wordpress\Services\Utilities\Options\Transient;

class ServiceProviders {

	// Must reflect the keys in the service providers api
	const PROVIDER_CLOUDFLARE = 'cloudflare';
	const PROVIDER_DUCKDUCKGO = 'duckduckgo';
	const PROVIDER_GTMETRIX = 'gtmetrix';
	const PROVIDER_ICONTROLWP = 'icontrolwp';
	const PROVIDER_MANAGEWP = 'managewp';
	const PROVIDER_NODEPING = 'nodeping';
	const PROVIDER_PAYPALIPN = 'paypal_ipn';
	const PROVIDER_PINGDOM = 'pingdom';
	const PROVIDER_STATUSCAKE = 'statuscake';
	const PROVIDER_STRIPE = 'stripe';
	const PROVIDER_UPTIMEROBOT = 'uptimerobot';

	private $providers;

	/**
	 * @return array[][]
	 */
	public function getProviders() :array {
		if ( !isset( $this->providers ) ) {
			$IPs = Transient::Get( 'apto_provider_ips' );
			if ( empty( $IPs ) || !is_array( $IPs ) ) {
				$IPs = ( new ProviderIPs() )->getIPs();
				if ( empty( $IPs ) ) { // fallback
					$raw = Services::Data()->readFileWithInclude( Services::DataDir( 'service_providers.json' ) );
					if ( !empty( $raw ) ) {
						$IPs = json_decode( $raw, true );
					}
				}
				Transient::Set( 'apto_provider_ips', $IPs, DAY_IN_SECONDS );
			}
			$this->providers = is_array( $IPs ) ? $IPs : [];
		}
		return $this->providers;
	}

	/**
	 * @return array[][]
	 * @deprecated 2.26
	 */
	public static function GetProviderIPs() :array {
		$IPs = Transient::Get( 'apto_provider_ips' );
		if ( empty( $IPs ) || !is_array( $IPs ) ) {
			$IPs = ( new ProviderIPs() )->getIPs();
			if ( empty( $IPs ) ) { // fallback
				$raw = Services::Data()->readFileWithInclude( Services::DataDir( 'service_providers.json' ) );
				if ( !empty( $raw ) ) {
					$IPs = json_decode( $raw, true );
				}
			}
			Transient::Set( 'apto_provider_ips', $IPs, DAY_IN_SECONDS );
		}
		return is_array( $IPs ) ? $IPs : [];
	}

	public function getProviderInfo( string $providerSlug ) :array {
		$info = [];
		foreach ( $this->getProviders() as $category ) {
			foreach ( $category as $slug => $provider ) {
				if ( $providerSlug === $slug ) {
					$info = $provider;
					break;
				}
			}
		}
		return $info;
	}

	public function getProviderName( string $providerSlug ) :string {
		$info = $this->getProviderInfo( $providerSlug );
		return empty( $info ) ? 'Unknown' : $info[ 'name' ];
	}

	public function getProvidersOfType( string $type ) :array {
		$providers = [];
		foreach ( $this->getProviders() as $category ) {
			foreach ( $category as $slug => $provider ) {
				if ( isset( $provider[ 'type' ] ) && in_array( $type, $provider[ 'type' ] ) ) {
					$providers[] = $slug;
				}
			}
		}
		return $providers;
	}

	public function getSearchProviders() :array {
		return $this->getProvidersOfType( 'search' );
	}

	public function getUptimeProviders() :array {
		return $this->getProvidersOfType( 'uptime' );
	}

	public function getWpSiteManagementProviders() :array {
		return $this->getProvidersOfType( 'wp_site_management' );
	}

	/**
	 * @return string[]
	 */
	public function getAllCrawlerUseragents() :array {
		$agents = [];
		foreach ( $this->getProviders()[ 'crawlers' ] ?? [] as $crawler ) {
			$agents = array_merge( $agents, $crawler[ 'agents' ] ?? [] );
		}
		return $agents;
	}

	/**
	 * @return string[][][]|null
	 * @deprecated 2.26
	 */
	protected function getAllServiceIPs() {
		$aIps = Transient::Get( 'serviceips_all' );
		if ( empty( $aIps ) ) {
			$aIps = ( new IPs() )->getIPs();
			$aIps = Transient::Set( 'serviceips_all', $aIps, WEEK_IN_SECONDS );
		}
		return $aIps;
	}

	/**
	 * @param string $slug
	 * @param bool   $flatList
	 * @return string[][]|string[]
	 * @deprecated 2.26
	 */
	public function getIpsForSlug( $slug, $flatList = false ) :array {
		$all = $this->getAllServiceIPs();
		$IPs = empty( $all[ $slug ] ) ? [ 4 => [], 6 => [] ] : $all[ $slug ];
		return $flatList ? array_merge( $IPs[ 4 ], $IPs[ 6 ] ) : $IPs;
	}

	/**
	 * @param string $ip
	 * @deprecated 2.26
	 */
	public function isIp_Cloudflare( $ip ) :bool {
		return IpID::IsIpInServiceCollection( $ip, self::PROVIDER_CLOUDFLARE );
	}

	/**
	 * @param string     $ip
	 * @param string[][] $collection
	 * @return bool
	 * @deprecated 2.26
	 */
	public function isIpInCollection( $ip, array $collection ) :bool {
		try {
			$version = Services::IP()->getIpVersion( $ip );
			$exists = $version !== false && Services::IP()->checkIp( $ip, $collection[ $version ] );
		}
		catch ( \Exception $e ) {
			$exists = false;
		}
		return $exists;
	}
}