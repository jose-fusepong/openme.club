<?php declare( strict_types=1 );

namespace FernleafSystems\Wordpress\Plugin\Core\Databases\Common;

use FernleafSystems\Utilities\Data\Adapter\DynPropertiesClass;
use FernleafSystems\Wordpress\Services\Services;

/**
 * @property string  $slug
 * @property string  $table        - full and complete table name including any prefixes
 * @property string  $table_prefix
 * @property string  $primary_key
 * @property array[] $cols_ids
 * @property array[] $cols_custom
 * @property array[] $cols_timestamps
 * @property array[] $foreign_keys
 * @property string  $col_older_than
 * @property bool    $has_updated_at
 * @property bool    $has_created_at
 * @property bool    $has_deleted_at
 * @property int     $autoexpire
 * @property int     $ready_check_interval
 * @property bool    $has_ip_col
 * @property bool    $is_ip_binary
 */
class TableSchema extends DynPropertiesClass {

	const PRIMARY_KEY = 'id';

	public function __get( string $key ) {
		$val = parent::__get( $key );
		switch ( $key ) {
			case 'has_ip_col':
				$val = in_array( 'ip', $this->getColumnNames() );
				break;
			case 'is_ip_binary':
				$val = $this->has_ip_col && ( stripos( $this->getColumnType( 'ip' ), 'varbinary' ) !== false );
				break;
			case 'table':
				$val = $this->buildTableName();
				break;
			case 'col_older_than':
				if ( empty( $val ) || !$this->hasColumn( $val ) ) {
					$val = 'created_at';
				}
				break;
			case 'has_updated_at':
				$val = is_null( $val ) ? true : $val;
				break;
			case 'foreign_keys':
				if ( !is_array( $val ) ) {
					$val = [];
				}
				break;
			case 'ready_check_interval':
				if ( !is_int( $val ) ) {
					$val = 30;
				}
				break;
			default:
				break;
		}
		return $val;
	}

	protected function buildTableName() :string {
		return sprintf( '%s%s%s',
			Services::WpDb()->getPrefix(),
			empty( $this->table_prefix ) ? '' : $this->table_prefix.'_',
			$this->slug
		);
	}

	public function buildCreate() :string {
		return ( new CreateTable( $this ) )->buildCreateSQL();
	}

	/**
	 * @return string[]
	 */
	public function getColumnNames() :array {
		return array_keys( $this->getColumnsDefs() );
	}

	public function getColumnType( string $col ) :string {
		$defs = $this->getColumnsDefs();
		return isset( $defs[ $col ] ) ? $defs[ $col ][ 'type' ] : '';
	}

	/**
	 * @return string[]
	 */
	public function enumerateColumns() :array {
		return array_map(
			function ( array $colDef ) {
				// convert from array column def to string.
				return ( new BuildColumnFromDef( $colDef ) )->build();
			},
			array_merge(
				$this->getColumn_ID(),
				$this->cols_custom ?? [],
				$this->getColumns_Timestamps()
			)
		);
	}

	/**
	 * @return array[]
	 */
	public function getColumnsDefs() :array {
		return array_map(
			function ( array $colDef ) {
				return ( new BuildColumnFromDef( $colDef ) )->buildStructure();
			},
			array_merge(
				$this->getColumn_ID(),
				$this->cols_custom ?? [],
				$this->getColumns_Timestamps()
			)
		);
	}

	/**
	 * @return string[]
	 */
	protected function getColumn_ID() :array {
		return [
			$this->getPrimaryKeyColumnName() => [ 'macro_type' => BuildColumnFromDef::MACROTYPE_PRIMARYID ],
		];
	}

	/**
	 * @return array[]
	 */
	protected function getColumns_Timestamps() :array {

		$standardTsCols = [];
		if ( $this->has_updated_at && !array_key_exists( 'updated_at', $this->cols_timestamps ) ) {
			$standardTsCols[ 'updated_at' ] = [
				'comment' => 'Last Updated'
			];
		}
		if ( $this->has_created_at && !array_key_exists( 'created_at', $this->cols_timestamps ) ) {
			$standardTsCols[ 'created_at' ] = [
				'comment' => 'Created'
			];
		}
		if ( $this->has_deleted_at && !array_key_exists( 'deleted_at', $this->cols_timestamps ) ) {
			$standardTsCols[ 'deleted_at' ] = [
				'comment' => 'Soft Deleted'
			];
		}

		return array_map(
			function ( array $colDef ) {
				$colDef[ 'macro_type' ] = BuildColumnFromDef::MACROTYPE_TIMESTAMP;
				return $colDef;
			},
			array_merge(
				$this->cols_timestamps ?? [],
				$standardTsCols
			)
		);
	}

	public function getPrimaryKeyColumnName() :string {
		return $this->primary_key ?? static::PRIMARY_KEY;
	}

	public function hasColumn( string $col ) :bool {
		return in_array( strtolower( $col ), $this->getColumnNames() );
	}
}