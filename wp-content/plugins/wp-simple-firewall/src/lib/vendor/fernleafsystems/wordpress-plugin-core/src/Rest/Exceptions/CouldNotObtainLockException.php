<?php declare( strict_types=1 );

namespace FernleafSystems\Wordpress\Plugin\Core\Rest\Exceptions;

class CouldNotObtainLockException extends ServerSideException {

	const DEFAULT_ERROR_SUBCODE = 501;
}