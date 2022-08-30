<?php

namespace FernleafSystems\Wordpress\Plugin\Core\Databases\Base;

use Carbon\Carbon;
use FernleafSystems\Wordpress\Plugin\Core\Databases\Exceptions\ColumnDoesNotExistException;
use FernleafSystems\Wordpress\Services\Services;

abstract class BaseQuery {

	/**
	 * @var Handler
	 */
	protected $dbh;

	/**
	 * @var array
	 */
	protected $rawWheres;

	protected $includeSoftDeleted;

	/**
	 * @var int
	 */
	protected $limit = 0;

	/**
	 * @var int
	 */
	protected $page = 1;

	/**
	 * @var array
	 */
	protected $orderBys;

	/**
	 * @var string
	 */
	protected $groupBy;

	/**
	 * @var mixed
	 */
	protected $lastQueryResult;

	public function __construct() {
		$this->customInit();
	}

	/**
	 * override to add custom init actions
	 */
	protected function customInit() {
	}

	/**
	 * @param string $columnLeft
	 * @param string $columnRight
	 * @param string $operator
	 */
	public function addWhereCompareColumns( string $columnLeft, string $columnRight, string $operator = '=' ) {
		$schema = $this->getDbH()->getTableSchema();
		if ( !$schema->hasColumn( $columnLeft ) ) {
			throw new ColumnDoesNotExistException( sprintf( 'Column "%s" does not exist in this table', $columnLeft ) );
		}
		if ( !$schema->hasColumn( $columnRight ) ) {
			throw new ColumnDoesNotExistException( sprintf( 'Column "%s" does not exist in this table', $columnRight ) );
		}

		return $this->addRawWhere( [ '`'.$columnLeft.'`', $operator, '`'.$columnRight.'`' ] );
	}

	/**
	 * @param string       $column
	 * @param string|array $value
	 * @param string       $operator
	 * @return $this
	 */
	public function addWhere( string $column, $value, string $operator = '=' ) {
		if ( !$this->isValidComparisonOperator( $operator ) ) {
			return $this; // Exception?
		}
		$schema = $this->getDbH()->getTableSchema();
		if ( !$schema->hasColumn( $column ) ) {
			throw new ColumnDoesNotExistException( sprintf( 'Column "%s" does not exist in this table', $column ) );
		}

		if ( is_array( $value ) ) {
			$value = array_map( 'esc_sql', $value );
			$value = "('".implode( "','", $value )."')";
		}
		else {
			if ( strtoupper( $operator ) === 'LIKE' ) {
				$value = sprintf( '%%%s%%', $value );
			}
			if ( !is_int( $value ) ) {
				$value = sprintf( "'%s'", esc_sql( $value ) );
			}
		}

		return $this->addRawWhere( [ '`'.$column.'`', $operator, $value ] );
	}

	/**
	 * @param array $where
	 * @return $this
	 */
	public function addRawWhere( array $where ) {
		$rawWheres = $this->getRawWheres();
		$rawWheres[] = $where;
		return $this->setRawWheres( $rawWheres );
	}

	/**
	 * @param string $column
	 * @param mixed  $mValue
	 * @return $this
	 */
	public function addWhereEquals( string $column, $mValue ) {
		return $this->addWhere( $column, $mValue, '=' );
	}

	/**
	 * @param string $column
	 * @param array  $values
	 * @return $this
	 */
	public function addWhereIn( string $column, $values ) {
		if ( !empty( $values ) && is_array( $values ) ) {
			$this->addWhere( $column, $values, 'IN' );
		}
		return $this;
	}

	/**
	 * @param string $column
	 * @param array  $values
	 * @return $this
	 */
	public function addWhereNotIn( string $column, array $values ) {
		if ( !empty( $values ) ) {
			$this->addWhere( $column, $values, 'NOT IN' );
		}
		return $this;
	}

	/**
	 * @param string $column
	 * @param string $like
	 * @param string $left
	 * @param string $right
	 * @return $this
	 */
	public function addWhereLike( string $column, $like, $left = '%', $right = '%' ) {
		return $this->addWhere( $column, $left.$like.$right, 'LIKE' );
	}

	/**
	 * @param int    $nNewerThanTimeStamp
	 * @param string $column
	 * @return $this
	 */
	public function addWhereNewerThan( $nNewerThanTimeStamp, $column = 'created_at' ) {
		return $this->addWhere( $column, $nNewerThanTimeStamp, '>' );
	}

	/**
	 * @param int    $nOlderThanTimeStamp
	 * @param string $column
	 * @return $this
	 */
	public function addWhereOlderThan( $nOlderThanTimeStamp, $column = 'created_at' ) {
		return $this->addWhere( $column, $nOlderThanTimeStamp, '<' );
	}

	/**
	 * @param string $column
	 * @param mixed  $value
	 * @return $this
	 */
	public function addWhereSearch( $column, $value ) {
		return $this->addWhere( $column, $value, 'LIKE' );
	}

	public function buildExtras() :string {
		$extras = array_filter(
			[
				$this->getGroupBy(),
				$this->buildOrderBy(),
				$this->buildLimitPhrase(),
				$this->buildOffsetPhrase(),
			]
		);
		return implode( "\n", $extras );
	}

	public function buildLimitPhrase() :string {
		return $this->hasLimit() ? sprintf( 'LIMIT %s', $this->getLimit() ) : '';
	}

	protected function buildOffsetPhrase() :string {
		return $this->hasLimit() ? sprintf( 'OFFSET %s', $this->getOffset() ) : '';
	}

	/**
	 * @return $this
	 */
	public function clearWheres() {
		return $this->setRawWheres( [] );
	}

	/**
	 * @return int
	 */
	protected function getOffset() {
		return (int)$this->getLimit()*( $this->getPage() - 1 );
	}

	public function buildWhere() :string {
		$wheres = array_merge(
			$this->getRawWheres(),
			$this->getDynamicWheres()
		);

		$wheres = array_map( function ( array $where ) {
			return $this->rawWhereToString( $where );
		}, $wheres );

		return empty( $wheres ) ? '' : 'WHERE '.implode( ' AND ', $wheres );
	}

	public function buildQuery() :string {
		return sprintf( $this->getBaseQuery(),
			$this->getDbH()->getTable(),
			$this->buildWhere(),
			$this->buildExtras()
		);
	}

	/**
	 * @param int    $ts
	 * @param string $comparisonOp
	 * @return $this
	 */
	public function filterByCreatedAt( $ts, $comparisonOp ) {
		if ( !preg_match( '#[^=<>]#', $comparisonOp ) && is_numeric( $ts ) ) {
			$this->addWhere( 'created_at', (int)$ts, $comparisonOp );
		}
		return $this;
	}

	/**
	 * @param int $startTS
	 * @param int $endTS
	 * @return $this
	 */
	public function filterByBoundary( $startTS, $endTS ) {
		return $this->filterByCreatedAt( $endTS, '<=' )
					->filterByCreatedAt( $startTS, '>=' );
	}

	/**
	 * @param int $ts
	 * @return $this
	 */
	public function filterByBoundary_Day( int $ts ) {
		$carbon = ( new Carbon() )->setTimestamp( $ts );
		return $this->filterByBoundary( $carbon->startOfDay()->timestamp, $carbon->endOfDay()->timestamp );
	}

	/**
	 * @param int $ts
	 * @return $this
	 */
	public function filterByBoundary_Hour( int $ts ) {
		$carbon = ( new Carbon() )->setTimestamp( $ts );
		return $this->filterByBoundary( $carbon->startOfHour()->timestamp, $carbon->endOfHour()->timestamp );
	}

	/**
	 * @param int $ts
	 * @return $this
	 */
	public function filterByBoundary_Month( int $ts ) {
		$carbon = ( new Carbon() )->setTimestamp( $ts );
		return $this->filterByBoundary( $carbon->startOfMonth()->timestamp, $carbon->endOfMonth()->timestamp );
	}

	/**
	 * @param int $ts
	 * @return $this
	 */
	public function filterByBoundary_Week( int $ts ) {
		$carbon = ( new Carbon() )->setTimestamp( $ts );
		return $this->filterByBoundary( $carbon->startOfWeek()->timestamp, $carbon->endOfWeek()->timestamp );
	}

	/**
	 * @param int $ts
	 * @return $this
	 */
	public function filterByBoundary_Year( int $ts ) {
		$carbon = ( new Carbon() )->setTimestamp( $ts );
		return $this->filterByBoundary( $carbon->startOfYear()->timestamp, $carbon->endOfYear()->timestamp );
	}

	/**
	 * @param array $ids
	 * @return $this
	 */
	public function filterByIDs( array $ids ) {
		return $this->addWhereIn( 'id', array_map( 'intval', $ids ) );
	}

	protected function getBaseQuery() :string {
		return "";
	}

	/**
	 * @return Handler
	 */
	public function getDbH() {
		return $this->dbh;
	}

	/**
	 * @param Handler|mixed $dbh
	 * @return $this
	 */
	public function setDbH( $dbh ) {
		$this->dbh = $dbh;
		return $this;
	}

	protected function preQuery() :bool {
		return true;
	}

	public function query() :bool {
		return $this->preQuery() && $this->execQuerySql();
	}

	protected function execQuerySql() :bool {
		$this->lastQueryResult = Services::WpDb()->doSql( $this->buildQuery() );
		return $this->lastQueryResult !== false && $this->lastQueryResult > 0;
	}

	/**
	 * @return array[]|int|string[]|Record[]|mixed|null
	 */
	public function queryWithResult() {
		return $this->query() ? $this->getLastQueryResult() : null;
	}

	/**
	 * @return array[]|int|string[]|Record[]|mixed
	 */
	public function getLastQueryResult() {
		return $this->lastQueryResult;
	}

	public function getLimit() :int {
		return (int)max( $this->limit, 0 );
	}

	public function getRawWheres() :array {
		return is_array( $this->rawWheres ) ? $this->rawWheres : [];
	}

	public function getDynamicWheres() :array {
		$wheres = [];
		if ( !$this->isIncludeSoftDeletedRows() && $this->getDbH()->getTableSchema()->hasColumn( 'deleted_at' ) ) {
			$wheres[] = [ 'deleted_at', '=', 0 ];
		}
		return $wheres;
	}

	public function getGroupBy() :string {
		return empty( $this->groupBy ) ? '' : sprintf( 'GROUP BY `%s`', $this->groupBy );
	}

	protected function buildOrderBy() :string {
		$order = '';

		$schema = $this->getDbH()->getTableSchema();

		if ( !is_array( $this->orderBys ) ) {
			// Defaults to created_at if aOrderBys is untouched. Set to empty array for no order
			if ( $schema->hasColumn( 'created_at' ) ) {
				$this->orderBys = [ 'created_at' => 'DESC' ];
			}
			else {
				$this->orderBys = [ 'id' => 'DESC' ];
			}
		}
		if ( !empty( $this->orderBys ) ) {
			$orders = [];
			foreach ( $this->orderBys as $col => $order ) {
				$orders[] = sprintf( '`%s` %s', esc_sql( $col ), esc_sql( $order ) );
			}
			$order = sprintf( 'ORDER BY %s', implode( ', ', $orders ) );
		}
		return $order;
	}

	public function getPage() :int {
		return (int)max( $this->page, 1 );
	}

	public function hasLimit() :bool {
		return $this->getLimit() > 0;
	}

	public function isIncludeSoftDeletedRows() :bool {
		return $this->includeSoftDeleted ?? false;
	}

	protected function rawWhereToString( array $rawWhere ) :string {
		return vsprintf( '%s %s %s', $rawWhere );
	}

	/**
	 * @return $this
	 */
	public function reset() {
		return $this->setLimit( 0 )
					->setRawWheres( [] )
					->setPage( 1 )
					->setOrderBy( '' );
	}

	/**
	 * @param bool $includeSoftDeleted
	 * @return $this
	 */
	public function setIncludeSoftDeleted( bool $includeSoftDeleted ) {
		$this->includeSoftDeleted = $includeSoftDeleted;
		return $this;
	}

	/**
	 * @param int $limit
	 * @return $this
	 */
	public function setLimit( int $limit ) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @param string $groupByColumn
	 * @return $this
	 */
	public function setGroupBy( $groupByColumn ) {
		if ( empty( $groupByColumn ) ) {
			$this->groupBy = '';
		}
		elseif ( $this->getDbH()->getTableSchema()->hasColumn( $groupByColumn ) ) {
			$this->groupBy = $groupByColumn;
		}
		return $this;
	}

	/**
	 * @param string $orderByColumn
	 * @param string $order
	 * @param bool   $replace
	 * @return $this
	 */
	public function setOrderBy( string $orderByColumn, $order = 'DESC', $replace = false ) {
		if ( !is_array( $this->orderBys ) || $replace ) {
			$this->orderBys = [];
		}
		if ( $this->getDbH()->getTableSchema()->hasColumn( $orderByColumn ) ) {
			$this->orderBys[ $orderByColumn ] = $order;
		}
		return $this;
	}

	/**
	 * @param int $page
	 * @return $this
	 */
	public function setPage( int $page ) {
		$this->page = $page;
		return $this;
	}

	/**
	 * @param array[] $wheres
	 * @return $this
	 */
	public function setRawWheres( array $wheres ) {
		$this->rawWheres = $wheres;
		return $this;
	}

	/**
	 * @param Record $VO
	 * @return $this
	 */
	public function setWheresFromVo( $VO ) {
		foreach ( $VO->getRawData() as $col => $mVal ) {
			$this->addWhereEquals( $col, $mVal );
		}
		return $this;
	}

	/**
	 * Very basic
	 */
	protected function isValidComparisonOperator( string $op ) :bool {
		return in_array(
			strtoupper( $op ),
			[ '=', '<', '>', '!=', '<>', '<=', '>=', '<=>', 'IN', 'NOT IN', 'LIKE', 'NOT LIKE' ]
		);
	}
}