<?php declare( strict_types=1 );

namespace FernleafSystems\Wordpress\Services\Utilities\Constants;

class Regex {

	const ASSET_SLUG = '([A-Za-z0-9]+[_\-])*[A-Za-z0-9]+';
	const ASSET_VERSION = '([0-9]+\.)*[0-9]+';
	const HASH_MD5 = '[A-Fa-f0-9]{32}';
	const HASH_SHA1 = '[A-Fa-f0-9]{40}';
}