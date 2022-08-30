<?php declare( strict_types=1 );

namespace FernleafSystems\Wordpress\Services\Utilities\Integrations\WpHashes\CrowdSourcedHashes\Submit;

use FernleafSystems\Wordpress\Services;

abstract class BaseSubmit extends Services\Utilities\Integrations\WpHashes\CrowdSourcedHashes\Base {

	const API_ENDPOINT = 'cshashes/submit';

	protected $hashes;

	public function setHashes( array $hashes ) {
		$this->hashes = $hashes;
		return $this;
	}

	public function preRequest() {
		/** @var RequestVO $req */
		$req = $this->getRequestVO();
		$req->hash = sha1( json_encode( $this->hashes ) );
	}

	protected function getApiUrl() :string {
		/** @var RequestVO $req */
		$req = $this->getRequestVO();
		return sprintf( '%s/%s', parent::getApiUrl(), $req->hash );
	}
}