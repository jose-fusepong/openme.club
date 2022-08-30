<?php

namespace FernleafSystems\Wordpress\Services\Utilities\Integrations\WpHashes\Util;

class Diff extends Base {

	const API_ENDPOINT = 'util/diff';
	const REQUEST_TYPE = 'POST';
	const RESPONSE_DATA_KEY = 'diff';

	/**
	 * @param string $left
	 * @param string $right
	 * @return array|null
	 */
	public function getDiff( $left, $right ) {
		$req = $this->getRequestVO();
		$req->left = $left;
		$req->right = $right;
		return $this->query();
	}
}