<?php

namespace FernleafSystems\Wordpress\Services\Utilities\File;

use FernleafSystems\Wordpress\Services\Services;

class WriteDataToFileEncrypted {

	/**
	 * @param string $path
	 * @param string $data
	 * @param string $publicKey
	 * @param string $privateKeyForVerify - verify writing successful if private key supplied
	 * @return bool
	 * @throws \Exception
	 */
	public function run( $path, $data, $publicKey, $privateKeyForVerify = null ) {
		$srvEncrypt = Services::Encrypt();

		$encrypted = $srvEncrypt->sealData( $data, $publicKey );
		if ( !$encrypted->success ) {
			throw new \Exception( 'Could not seal data with message: '.$encrypted->message );
		}

		$bSuccess = Services::WpFs()->putFileContent( $path, json_encode( $encrypted->getRawData() ) );
		if ( $bSuccess && !empty( $privateKeyForVerify ) ) {
			$bSuccess = ( new ReadDataFromFileEncrypted() )->run( $path, $privateKeyForVerify ) === $data;
		}
		return $bSuccess;
	}
}