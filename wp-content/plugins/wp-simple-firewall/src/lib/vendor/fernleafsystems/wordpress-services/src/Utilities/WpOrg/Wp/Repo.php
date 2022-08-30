<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Wp;

use FernleafSystems\Wordpress\Services;

class Repo extends Services\Utilities\WpOrg\Base\RepoBase {

	const URL_VCS_ROOT = 'https://core.svn.wordpress.org';
	const URL_VCS_ROOT_IL8N = 'https://i18n.svn.wordpress.org';

	/**
	 * @param string $version
	 * @param bool   $useLocale
	 * @return string
	 */
	public static function GetUrlForVersion( $version, $useLocale = true ) {
		return sprintf(
			'%s/tags/%s',
			$useLocale ? static::URL_VCS_ROOT_IL8N : static::URL_VCS_ROOT,
			$useLocale ? $version.'/dist' : $version
		);
	}

	/**
	 * @return string
	 */
	public static function GetUrlForVersions() {
		return static::GetUrlForVersion( '' );
	}

	/**
	 * @param string $fileFragment
	 * @param string $version
	 * @param bool   $useSiteLocale
	 * @return string|null
	 */
	public function downloadFromVcs( $fileFragment, $version = null, $useSiteLocale = true ) {
		$file = parent::downloadFromVcs( $fileFragment, $version, $useSiteLocale );
		if ( $useSiteLocale && empty( $file ) ) {
			$file = parent::downloadFromVcs( $fileFragment, $version, false );
		}
		return $file;
	}

	/**
	 * @param string $fileFragment
	 * @param string $version - leave empty to use the current version
	 * @param bool   $useSiteLocale
	 * @return bool
	 */
	public function existsInVcs( $fileFragment, $version = null, $useSiteLocale = true ) {
		$file = parent::existsInVcs( $fileFragment, $version, $useSiteLocale );
		if ( $useSiteLocale && empty( $file ) ) {
			$file = parent::existsInVcs( $fileFragment, $version, false );
		}
		return $file;
	}

	/**
	 * @param string $fileFragment
	 * @param string $version
	 * @param bool   $useSiteLocale
	 * @return string
	 */
	public function getVcsUrlForFileAndVersion( $fileFragment, $version, $useSiteLocale = true ) {
		if ( empty( $version ) ) {
			$version = Services\Services::WpGeneral()->getVersion();
		}
		return sprintf( '%s/%s', static::GetUrlForVersion( $version, $useSiteLocale ), ltrim( $fileFragment, '/' ) );
	}
}