<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg;

use FernleafSystems\Wordpress\Services\Services;

/**
 * @deprecated
 */
class Core {

	const URL_SVN_ROOT = 'https://core.svn.wordpress.org';

	/**
	 * @var string[]
	 */
	private $aWpVersions;

	/**
	 * @return string[]
	 */
	public function getAllVersions() {
		if ( empty( $this->aWpVersions ) ) {
			$this->aWpVersions = $this->downloadVersions();
		}
		return $this->aWpVersions;
	}

	/**
	 * @return string
	 */
	public function getLatestVersion() {
		$versions = $this->getAllVersions();
		return end( $versions );
	}

	/**
	 * @param string $sVersionBranch - leave empty to use the current WP Version
	 * @return string
	 * @throws \Exception
	 */
	public function getLatestVersionForBranch( $sVersionBranch = null ) {
		if ( empty( $sVersionBranch ) ) {
			$sVersionBranch = Services::WpGeneral()->getVersion();
		}
		$aParts = explode( '.', $sVersionBranch );
		if ( count( $aParts ) < 2 ) {
			throw new \Exception( sprintf( 'Invalid version "%s" provided.', $sVersionBranch ) );
		}

		$sThisBranch = $aParts[ 0 ].'.'.$aParts[ 1 ];

		$aPossible = array_filter(
			$this->getAllVersions(),
			function ( $sVersion ) use ( $sThisBranch ) {
				return strpos( $sVersion, $sThisBranch ) === 0;
			}
		);
		return end( $aPossible );
	}

	protected function downloadVersions() :array {
		$versions = [];
		$svnVersionsContent = Services::HttpRequest()->getContent(
			sprintf( '%s/%s/', static::URL_SVN_ROOT, 'tags' )
		);

		if ( !empty( $svnVersionsContent ) ) {
			$svnDom = new \DOMDocument();
			$svnDom->loadHTML( $svnVersionsContent );

			foreach ( $svnDom->getElementsByTagName( 'a' ) as $element ) {
				/** @var \DOMElement $element */
				$href = $element->getAttribute( 'href' );
				if ( $href != '../' && !filter_var( $href, FILTER_VALIDATE_URL ) ) {
					$versions[] = trim( $href, '/' );
				}
			}
		}

		usort( $versions, 'version_compare' );
		return $versions;
	}

	/**
	 * @param string[] $aWpVersions
	 * @return $this
	 */
	public function setWpVersions( $aWpVersions ) {
		$this->aWpVersions = $aWpVersions;
		return $this;
	}
}