<?php

namespace FernleafSystems\Wordpress\Services\Utilities\Integrations\WpHashes\Hashes;

use FernleafSystems\Wordpress\Services;

class Plugin extends PluginThemeBase {

	const TYPE = 'plugin';

	/**
	 * @param Services\Core\VOs\Assets\WpPluginVo $VO
	 * @return array|null
	 */
	public function getPluginHashes( Services\Core\VOs\Assets\WpPluginVo $VO ) {
		return $this->getHashes( $VO->slug, $VO->Version );
	}

	/**
	 * @param Services\Core\VOs\WpPluginVo $VO
	 * @return array|null
	 * @deprecated 2.15
	 */
	public function getHashesFromVO( Services\Core\VOs\WpPluginVo $VO ) {
		return $this->getHashes( $VO->slug, $VO->Version );
	}
}