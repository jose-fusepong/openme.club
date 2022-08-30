<?php declare( strict_types=1 );

namespace FernleafSystems\Wordpress\Plugin\Core\Databases\Common;

use FernleafSystems\Wordpress\Services\Services;

class BuildColumnFromDef {

	const MACROTYPE_PRIMARYID = 'primary_id';
	const MACROTYPE_TIMESTAMP = 'timestamp';
	const MACROTYPE_UNSIGNEDINT = 'unsigned_int';
	const MACROTYPE_BIGINT = 'bigint';
	const MACROTYPE_FOREIGN_KEY_ID = 'foreign_key_id';
	const MACROTYPE_BINARYHASH = 'binary_hash';
	const MACROTYPE_HASH = 'hash';
	const MACROTYPE_SHA1 = 'sha1';
	const MACROTYPE_MD5 = 'md5';
	const MACROTYPE_IP = 'ip';
	const MACROTYPE_META = 'meta';
	const MACROTYPE_TEXT = 'text';
	const MACROTYPE_URL = 'url';
	const MACROTYPE_BOOL = 'bool';
	const MACROTYPE_CHAR = 'char';
	const MACROTYPE_VARCHAR = 'varchar';

	private $def;

	public function __construct( array $def ) {
		$this->setDef( $def );
	}

	public function setDef( array $def ) {
		$this->def = $def;
		return $this;
	}

	public function build() :string {
		$def = $this->buildStructure();
		return sprintf( '%s%s %s %s %s',
			$def[ 'type' ],
			isset( $def[ 'length' ] ) ? sprintf( '(%s)', $def[ 'length' ] ) : '',
			implode( ' ', $def[ 'attr' ] ?? [] ),
			isset( $def[ 'default' ] ) ? sprintf( "DEFAULT %s", $def[ 'default' ] ) : '',
			isset( $def[ 'comment' ] ) ? sprintf( "COMMENT '%s'", str_replace( "'", '', $def[ 'comment' ] ) ) : ''
		);
	}

	public function buildStructure() :array {
		$structure = Services::DataManipulation()->mergeArraysRecursive(
			$this->getMacroTypeDef( $this->def[ 'macro_type' ] ?? '' ),
			$this->def
		);

		if ( $this->def[ 'no_length' ] ?? false ) {
			unset( $structure[ 'length' ] );
		}

		return $structure;
	}

	protected function getMacroTypeDef( string $macroType ) :array {
		switch ( $macroType ) {

			case self::MACROTYPE_BOOL:
				$def = array_merge( $this->getMacroTypeDef( self::MACROTYPE_UNSIGNEDINT ), [
					'type'    => 'tinyint',
					'length'  => 1,
					'comment' => 'Boolean',
				] );
				break;

			case self::MACROTYPE_BIGINT:
				$def = array_merge( $this->getMacroTypeDef( self::MACROTYPE_UNSIGNEDINT ), [
					'type'    => 'bigint',
					'comment' => 'BIG Int',
				] );
				break;

			case self::MACROTYPE_CHAR:
				$def = [
					'type'    => 'char',
					'length'  => 1,
					'attr'    => [
						'NOT NULL',
					],
					'default' => "''",
					'comment' => 'Fixed-Length String',
				];
				break;

			case self::MACROTYPE_HASH:
				$def = array_merge(
					$this->getMacroTypeDef( self::MACROTYPE_VARCHAR ),
					[
						'length'  => 40,
						'comment' => 'SHA1 Hash',
					]
				);
				break;

			case self::MACROTYPE_BINARYHASH:
				$def = [
					'type'   => 'binary',
					'length' => 16,
					'attr'   => [
						'NOT NULL',
					],
				];
				break;

			case self::MACROTYPE_SHA1:
				$def = array_merge(
					$this->getMacroTypeDef( self::MACROTYPE_BINARYHASH ),
					[
						'length'  => 20,
						'comment' => 'SHA1 Hash',
					]
				);
				break;

			case self::MACROTYPE_MD5:
				$def = array_merge(
					$this->getMacroTypeDef( self::MACROTYPE_BINARYHASH ),
					[
						'length'  => 16,
						'comment' => 'MD5 Hash',
					]
				);
				break;

			case self::MACROTYPE_FOREIGN_KEY_ID:
				$def = array_merge(
					$this->getMacroTypeDef( self::MACROTYPE_UNSIGNEDINT ),
					[
						'comment'     => 'Foreign Key For Primary ID',
						'foreign_key' => [
							'ref_col'        => 'id',
							'wp_prefix'      => true,
							'cascade_update' => true,
							'cascade_delete' => true
						],
					]
				);
				unset( $def[ 'default' ] );
				break;

			case self::MACROTYPE_IP:
				$def = [
					'type'    => 'varbinary',
					'length'  => 16,
					'attr'    => [
						'NOT NULL'
					],
					'comment' => 'IP Address',
				];
				break;

			case self::MACROTYPE_META:
				$def = array_merge(
					$this->getMacroTypeDef( self::MACROTYPE_TEXT ),
					[
						'comment' => 'Meta Data',
					]
				);
				break;

			case self::MACROTYPE_TEXT:
				$def = [
					'type' => 'text',
				];
				break;

			case self::MACROTYPE_URL:
				$def = array_merge(
					$this->getMacroTypeDef( self::MACROTYPE_VARCHAR ),
					[
						'comment' => 'Site URL',
					]
				);
				break;

			case self::MACROTYPE_VARCHAR:
				$def = [
					'type'    => 'varchar',
					'length'  => 120,
					'attr'    => [
						'NOT NULL',
					],
					'default' => "''",
				];
				break;

			case self::MACROTYPE_TIMESTAMP:
				$def = array_merge( $this->getMacroTypeDef( self::MACROTYPE_UNSIGNEDINT ), [
					'length'  => 15,
					'comment' => 'Epoch Timestamp',
				] );
				break;

			case self::MACROTYPE_UNSIGNEDINT:
				$def = [
					'type'    => 'int',
					'length'  => 11,
					'attr'    => [
						'UNSIGNED',
						'NOT NULL',
					],
					'default' => 0,
				];
				break;

			case self::MACROTYPE_PRIMARYID:
				$def = array_merge(
					$this->getMacroTypeDef( self::MACROTYPE_UNSIGNEDINT ),
					[
						'comment' => 'Primary ID',
					]
				);
				$def[ 'attr' ][] = 'AUTO_INCREMENT';
				unset( $def[ 'default' ] );
				break;

			default:
				$def = [];
				break;
		}

		return $def;
	}
}