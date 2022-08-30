<?php

namespace FernleafSystems\Wordpress\Services\Utilities\Integrations\WpHashes;

use FernleafSystems\Wordpress\Services\Utilities\HttpRequest;
use FernleafSystems\Wordpress\Services\Utilities\Integrations\RequestVO;

abstract class ApiBase {

	const API_URL = 'https://wphashes.com/api/apto-wphashes';
	const API_VERSION = 1;
	const API_ENDPOINT = '';
	const REQUEST_TYPE = 'GET';
	const RESPONSE_DATA_KEY = '';

	protected static $API_TOKEN;

	/**
	 * @var RequestVO
	 */
	private $req;

	/**
	 * @var bool
	 */
	private $useQueryCache = false;

	/**
	 * @var array
	 */
	private static $QueryCache = [];

	/**
	 * @param string $apiToken
	 */
	public function __construct( $apiToken = null ) {
		$this->setApiToken( $apiToken );
	}

	protected function getApiUrl() :string {
		return sprintf( '%s/v%s/%s', static::API_URL, static::API_VERSION, static::API_ENDPOINT );
	}

	protected function getQueryData() :array {
		return empty( static::$API_TOKEN ) ? [] : [ 'token' => static::$API_TOKEN ];
	}

	/**
	 * @return RequestVO|mixed
	 */
	protected function getRequestVO() {
		if ( !isset( $this->req ) ) {
			$this->req = $this->newReqVO();
		}
		return $this->req;
	}

	/**
	 * @return RequestVO
	 */
	protected function newReqVO() {
		return new RequestVO();
	}

	/**
	 * @return array|mixed|null
	 */
	public function query() {
		$data = $this->fireRequestDecodeResponse();
		if ( is_array( $data ) ) {
			if ( strlen( static::RESPONSE_DATA_KEY ) > 0 ) {
				$data = $data[ static::RESPONSE_DATA_KEY ] ?? null;
			}
		}
		else {
			$data = null;
		}
		return $data;
	}

	/**
	 * @return array|null - null on failure
	 */
	protected function fireRequestDecodeResponse() {
		$response = $this->fireRequest();
		return empty( $response ) ? null : json_decode( $response, true );
	}

	protected function fireRequest() :string {
		$this->preRequest();
		switch ( static::REQUEST_TYPE ) {
			case 'POST':
				$response = $this->fireRequest_POST();
				break;
			case 'GET':
			default:
				$response = $this->fireRequest_GET();
				break;
		}
		return trim( $response );
	}

	protected function preRequest() {
	}

	protected function fireRequest_GET() :string {
		$response = null;

		$url = add_query_arg( $this->getQueryData(), $this->getApiUrl() );
		$sig = md5( $url );

		if ( $this->isUseQueryCache() && isset( self::$QueryCache[ $sig ] ) ) {
			$response = self::$QueryCache[ $sig ];
		}

		if ( is_null( $response ) ) {
			$response = ( new HttpRequest() )->getContent( $url, $this->getRequestDefaults() );
			if ( $this->isUseQueryCache() ) {
				self::$QueryCache[ $sig ] = $response;
			}
		}

		return (string)$response;
	}

	protected function fireRequest_POST() :string {
		$http = new HttpRequest();
		$http->post(
			add_query_arg( $this->getQueryData(), $this->getApiUrl() ),
			array_merge( $this->getRequestDefaults(), [
				'body' => $this->getRequestVO()->getRawData()
			] )
		);
		return $http->isSuccess() ? (string)$http->lastResponse->body : '';
	}

	public function isUseQueryCache() :bool {
		return (bool)$this->useQueryCache;
	}

	/**
	 * @param string $token
	 * @return $this
	 */
	public function setApiToken( $token ) {
		if ( is_string( $token ) && preg_match( '#^[a-z0-9]{32,}$#', $token ) ) {
			static::$API_TOKEN = $token;
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setUseQueryCache( bool $useQueryCache ) {
		$this->useQueryCache = $useQueryCache;
		return $this;
	}

	protected function getRequestDefaults() :array {
		return [];
	}
}