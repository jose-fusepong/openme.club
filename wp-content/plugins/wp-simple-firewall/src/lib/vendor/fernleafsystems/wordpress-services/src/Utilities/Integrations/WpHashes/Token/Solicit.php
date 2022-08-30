<?php

namespace FernleafSystems\Wordpress\Services\Utilities\Integrations\WpHashes\Token;

class Solicit extends Base {

	/**
	 * @param string $url
	 * @param string $installID
	 * @return array|null
	 */
	public function retrieve( $url, $installID ) {
		/** @var RequestVO $oReq */
		$oReq = $this->getRequestVO();
		$oReq->action = 'solicit';
		$oReq->install_id = $installID;
		$oReq->url = strpos( $url, '?' ) ? explode( '?', $url, 2 )[ 0 ] : $url;
		return $this->query();
	}

	protected function getApiUrl() :string {
		/** @var RequestVO $req */
		$req = $this->getRequestVO();
		return sprintf( '%s/%s/%s', parent::getApiUrl(), $req->action, $req->install_id );
	}

	protected function getQueryData() :array {
		/** @var RequestVO $req */
		$req = $this->getRequestVO();
		$data = parent::getQueryData();
		$data[ 'url' ] = $req->url;
		return $data;
	}
}