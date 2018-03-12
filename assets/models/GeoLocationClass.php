<?php
/**
 * Created by PhpStorm.
 * User: Chepur
 * Date: 16.02.2018
 * Time: 15:17
 */

namespace models;

/**
 * Class GeoLocation
 */
class GeoLocation extends Base {
	const DB_NAME = 'geolocation';
	const DB_FILTER_NAME = 'geo_filters';
	const className = 'geolocation';
	const filter_fields = array( 'id', 'geo_id', 'parent' );
	protected static
		$instance = NULL;
	public $words;
	protected $_filters = array();

	public function __construct() {
		parent::__construct();

		if ( mysqli_num_rows( mysqli_query( $this->link, "SHOW TABLES LIKE '" . static::DB_FILTER_NAME . "'" ) ) != 1 ) {
			mysqli_query( $this->link, "CREATE TABLE " . static::DB_FILTER_NAME . "
(
  id       INT     AUTO_INCREMENT PRIMARY KEY,
  `geo_id` INT(11) NOT NULL,
  `parent` INT(11) DEFAULT NULL,
  CONSTRAINT `FK_geo_parent` FOREIGN KEY (`parent`) REFERENCES `geolocation` (`id`),
  CONSTRAINT `geo_filters_geolocation_id_fk` FOREIGN KEY (`geo_id`) REFERENCES `geolocation` (`id`)
)" );
		}
		$filt_res = mysqli_query( $this->link, "SELECT " . join( ',', self::filter_fields ) . " FROM " . self::DB_FILTER_NAME );
		for ( $row_no = $filt_res->num_rows - 1; $row_no >= 0; $row_no -- ) {
			$filt_res->data_seek( $row_no );
			$row = $filt_res->fetch_assoc();
			if ( is_null( $row['parent'] ) ) {
				if ( ! is_array( $this->_filters[ $row['geo_id'] ] ) ) {
					$this->_filters[ $row['geo_id'] ] = array();
				}
			} else {
				$this->_filters[ $row['parent'] ][] = $row['geo_id'];
			}
		}

		if ( $reg_excl = $_POST['include_geo'] ) {
			$_SESSION['include_geo'] = $reg_excl;
			array_walk(
				$_SESSION['include_geo'],
				function ( &$item, $key ) {
					$item = $this->words[ $key ];
				}
			);
		}
	}

	public
	function getFilters() {
		return $this->_filters;
	}

	public
	function insert(
		$word
	) {
		$geo_id = parent::insert( $word );
		if ( ! $geo_id ) {
			return FALSE;
		}
		if ( in_array( $geo_id, $this->_filters ) || in_array( $geo_id, array_keys( $this->_filters ) ) ) {
			return FALSE;
		}
		$result = mysqli_query( $this->link, "INSERT INTO " . self::DB_FILTER_NAME . " (geo_id) VALUE ('" . $geo_id . "')" );
		if ( $result ) {
			return mysqli_insert_id( $this->link );
		}

		return FALSE;
	}


}