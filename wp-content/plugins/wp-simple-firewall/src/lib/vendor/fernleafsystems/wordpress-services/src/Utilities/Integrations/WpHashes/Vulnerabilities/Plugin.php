<?php

namespace FernleafSystems\Wordpress\Services\Utilities\Integrations\WpHashes\Vulnerabilities;

use FernleafSystems\Wordpress\Services\Core\VOs\Assets\WpPluginVo;

class Plugin extends BasePluginTheme {

	const ASSET_TYPE = 'plugin';

	/**
	 * @param WpPluginVo $plugin
	 * @return array[]|null
	 */
	public function getFromVO( WpPluginVo $plugin ) {
		return $this->getVulnerabilities( $plugin->slug, $plugin->Version );
	}
}