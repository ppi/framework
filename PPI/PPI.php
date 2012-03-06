<?php
class PPI {
	
	static function getDataSource() {
		return PPI_Helper::getDataSource();
	}
	
	static function getDataSourceConnection($key) {
		return PPI_Helper::getDataSourceConnection($key);
	}
	
}