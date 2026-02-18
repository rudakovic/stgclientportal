<?php
namespace NUX\StripeSubToWooSub;

class CSV_Importer {

	public static function import_csv( $file ) {
        error_log('importing csv' . $file);
		$csv_file = file( $file );
		$data     = [];
		foreach ( $csv_file as $line ) {
			$data[] = str_getcsv( $line );
		}
		return $data;
	}

	public static function two_row_headers( $data ) {
		$headers = array();
		$latest = '';
		foreach ( $data[0] as $key => $value ) {
			
			if ( $value !== '' ) {
				$latest = $value;
			}
			$headers[$key] = $data[1][$key] !== '' ? $latest . ' -- ' . $data[1][$key] : $latest;
		}
		return $headers;
	}

	public static function one_row_headers( $data ) {
		$headers = array();
		$latest = '';
		foreach ( $data[0] as $key => $value ) {
			
			if ( $value !== '' ) {
				$latest = $value;
			}
			$headers[$key] = $latest;
		}
		return $headers;
	}

	public static function make_multi_array( $headers, $data ) {
		$data_array = array();
		foreach( $data as $i => $line ) {
			foreach( $line as $key => $value ) {
				$data_array[$i][$headers[$key]] = $value;
			}
		}
		return $data_array;
	}

	public static function make_keyed_array( $headers, $data, $key_column = 0 ) {
		$data_array = array();
		foreach( $data as $i => $line ) {
			$selected_key = $line[$key_column];
			foreach( $line as $key => $value ) {
				$data_array[$selected_key][$headers[$key]] = $value;
			}
		}
		return $data_array;
	}

    public static function get_csv_data( $file ) {
        $data = self::import_csv( $file );
        $headers = self::one_row_headers( $data );
        $data = self::make_multi_array( $headers, $data );
        //remove the header row now
		unset($data[0]);
        return $data;
    }

	public static function get_keyed_csv_data( $file, $key_column = 0 ) {
		$data = self::import_csv( $file );
		$headers = self::one_row_headers( $data );
		$data = self::make_keyed_array( $headers, $data, $key_column );
		//remove the header row now
		unset($data[0]);

		return $data;
	}
}