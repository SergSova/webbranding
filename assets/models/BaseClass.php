<?php
/**
 * Created by PhpStorm.
 * User: Chepur
 * Date: 16.02.2018
 * Time: 15:15
 */

namespace models;

class Base extends DbConnect {
	const DB_NAME = '';
	const className = '';
	const fields = array( 'id', 'word' );
	const filter_fields = '';
	const DB_FILTER_NAME = '';
	protected static
		$instance;
	protected $words;

	/**
	 * Base constructor.
	 */
	protected function __construct() {
		parent::__construct();
		$this->words = array();

		if ( mysqli_num_rows( mysqli_query( $this->link, "SHOW TABLES LIKE '" . static::DB_NAME . "'" ) ) != 1 ) {
			mysqli_query( $this->link, "CREATE TABLE " . static::DB_NAME . "(
	        	id INT AUTO_INCREMENT PRIMARY KEY,
	        	word VARCHAR(150) NULL
	        	)" );
		}

		$res = mysqli_query( $this->link, "SELECT " . join( ',', self::fields ) . " FROM " . static::DB_NAME );
		for ( $row_no = $res->num_rows - 1; $row_no >= 0; $row_no -- ) {
			$res->data_seek( $row_no );
			$row                                    = $res->fetch_assoc();
			$this->words[ $row[ self::fields[0] ] ] = mb_strtolower( $row[ self::fields[1] ] );
		}
		$_SESSION[ static::className ] = serialize( $this );
	}


	/**
	 * @return static
	 */
	public static function getInstance() {
		if ( NULL === static::$instance ) {
			static::$instance = new static();
		} elseif ( $_SESSION[ static::className ] ) {
			static::$instance = unserialize( $_SESSION[ static::className ] );
		}

		if ( $reg_excl = $_POST[ static::DB_NAME ] ) {
			if ( $reg_excl == '#' ) {
				unset( $_SESSION[ static::DB_NAME ] );
			} else {
				$_SESSION[ static::DB_NAME ] = $reg_excl;
				/*заменить значения из чекбоксов на слова*/
				array_walk(
					$_SESSION[ static::DB_NAME ],
					function ( &$item, $key ) {
						$item = static::$instance->words[ $key ];
					}
				);
			}
		}

		return static::$instance;
	}


	public function insert( $word ) {
		if ( ! in_array( $word, $this->words ) ) {
			$result = mysqli_query( $this->link, "INSERT INTO " . static::DB_NAME . " (word) VALUE ('" . $word . "')" );
			if ( $result ) {
				return mysqli_insert_id( $this->link );
			}
		}

		return FALSE;
	}

	public function delete( $word ) {
		if ( in_array( $word, $this->words ) ) {
			$result = mysqli_query( $this->link, "DELETE FROM " . static::DB_NAME . " WHERE word=" . $word );

			return $result;
		}

		return FALSE;
	}
}