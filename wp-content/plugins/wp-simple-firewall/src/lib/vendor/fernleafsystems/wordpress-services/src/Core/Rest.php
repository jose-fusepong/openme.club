<?php

namespace FernleafSystems\Wordpress\Services\Core;

use FernleafSystems\Wordpress\Services\Services;

class Rest {

	public function callInternal( array $req ) :\WP_REST_Response {
		$internal = new \WP_REST_Request(
			$req[ 'method' ] ?? 'GET', $req[ 'route' ], $req[ 'attributes' ] ?? [] );
		if ( !empty( $req[ 'query_params' ] ) ) {
			$internal->set_query_params( $req[ 'query_params' ] );
		}
		if ( !empty( $req[ 'body_params' ] ) ) {
			$internal->set_body_params( $req[ 'query_params' ] );
		}
		return rest_do_request( $internal );
	}

	/**
	 * @return string|null
	 */
	public function getNamespace() {
		$nameSpace = null;

		$sRoute = $this->getRoute();
		if ( !empty( $sRoute ) ) {
			$aParts = array_filter( explode( '/', $sRoute ) );
			if ( !empty( $aParts ) ) {
				$nameSpace = array_shift( $aParts );
			}
		}
		return $nameSpace;
	}

	/**
	 * @return string|null
	 */
	public function getRoute() {
		$sRoute = null;

		if ( $this->isRest() ) {
			$oReq = Services::Request();
			$oWp = Services::WpGeneral();

			$sRoute = $oReq->request( 'rest_route' );
			if ( empty( $sRoute ) && $oWp->isPermalinksEnabled() ) {
				$sFullUri = $oWp->getHomeUrl( $oReq->getPath() );
				$sRoute = substr( $sFullUri, strlen( get_rest_url( get_current_blog_id() ) ) );
			}
		}
		return $sRoute;
	}

	public function isRest() :bool {
		$isRest = ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || !empty( $_REQUEST[ 'rest_route' ] );

		global $wp_rewrite;
		if ( !$isRest && function_exists( 'rest_url' ) && is_object( $wp_rewrite ) ) {
			$sRestUrlBase = get_rest_url( get_current_blog_id(), '/' );
			$sRestPath = trim( parse_url( $sRestUrlBase, PHP_URL_PATH ), '/' );
			$sRequestPath = trim( Services::Request()->getPath(), '/' );
			$isRest = !empty( $sRequestPath ) && !empty( $sRestPath )
					  && ( strpos( $sRequestPath, $sRestPath ) === 0 );
		}
		return $isRest;
	}

	/**
	 * @return string|null
	 * @deprecated
	 */
	protected function getPath() {
		return $this->getRoute();
	}
}