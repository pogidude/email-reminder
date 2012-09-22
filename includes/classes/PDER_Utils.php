<?php

class PDER_Utils{
	/**
	 * @param string $filename path to file
	 * @param mixed $data data to pass on to the view file
	 * @param string $parent parent directory that $filename can be relative to.
	 * 		pass FALSE if you are passing an absolute path to $filename.
	 * @return string contents of the file
	 */
	public static function get_view( $filename, $data = null, $parent = PDER_VIEWS ){
		if( empty( $filename ) ) return '';
		
		$file = '';
		
		if( empty( $parent ) ){
			//we will assume that $filename is absolute path
			$file = $filename;
		} else {
			//$filename is relative to $parent
			$file = trailingslashit( $parent ) . $filename;
		}
		
		//check if file exists
		if( !file_exists( $file ) ) return '';
		
		ob_start();
		include $file;
		$contents = ob_get_contents();
		ob_end_clean();
		
		return $contents;
	}
}
