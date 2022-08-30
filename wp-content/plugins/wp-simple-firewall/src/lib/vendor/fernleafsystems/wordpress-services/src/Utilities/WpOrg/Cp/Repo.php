<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Cp;

use FernleafSystems\Wordpress\Services;

class Repo extends Services\Utilities\WpOrg\Base\RepoBase {

	const URL_VCS_ROOT = 'https://raw.githubusercontent.com/ClassicPress/ClassicPress-release';
	const URL_VCS_ROOT_IL8N = self::URL_VCS_ROOT;
	const URL_VCS_VERSIONS = 'https://api.github.com/repos/ClassicPress/ClassicPress-release/releases';
	const URL_VCS_VERSION = 'https://github.com/ClassicPress/ClassicPress-release/releases/tag';

	/**
	 * @param string $sVersion
	 * @return string
	 */
	public static function GetUrlForVersion( $sVersion ) {
		return sprintf( '%s/%s', static::URL_VCS_VERSION, $sVersion );
	}

	/**
	 * @param string $sVersion
	 * @return string
	 */
	public static function GetUrlForFiles( $sVersion ) {
		return sprintf( '%s/%s', static::URL_VCS_ROOT, $sVersion );
	}

	/**
	 * @return string
	 */
	public static function GetUrlForVersions() {
		return static::URL_VCS_VERSIONS;
	}

	/**
	 * @param string $fileFragment
	 * @param string $version
	 * @param bool   $useSiteLocale
	 * @return string|null
	 */
	public function downloadFromVcs( $fileFragment, $version = null, $useSiteLocale = true ) {
		$sFile = parent::downloadFromVcs( $fileFragment, $version, $useSiteLocale );
		if ( $useSiteLocale && empty( $sFile ) ) {
			$sFile = parent::downloadFromVcs( $fileFragment, $version, false );
		}
		return $sFile;
	}

	/**
	 * @param string $fileFragment
	 * @param string $version - leave empty to use the current version
	 * @param bool   $useSiteLocale
	 * @return bool
	 */
	public function existsInVcs( $fileFragment, $version = null, $useSiteLocale = true ) {
		$sFile = parent::existsInVcs( $fileFragment, $version, $useSiteLocale );
		if ( $useSiteLocale && empty( $sFile ) ) {
			$sFile = parent::existsInVcs( $fileFragment, $version, false );
		}
		return $sFile;
	}

	/**
	 * @param string $fileFragment
	 * @param string $version
	 * @param bool   $useSiteLocale - not yet used for ClassicPress
	 * @return string
	 */
	public function getVcsUrlForFileAndVersion( $fileFragment, $version, $useSiteLocale = true ) {
		if ( empty( $version ) ) {
			$version = Services\Services::WpGeneral()->getVersion();
		}
		return sprintf( '%s/%s', static::GetUrlForFiles( $version ), ltrim( $fileFragment, '/' ) );
	}
}