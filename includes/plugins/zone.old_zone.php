<?php
	/**
	 *	Версии файлов зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	/**
	 * 	Старая версия файла зон
	 */
	class COldFileZone extends CFileZone {
		protected $ip_edited = "";
		protected $version = "";
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_old_zone";
			$arrConfig[ FLEX_CONFIG_PREFIX ] = "old_zone_";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = "OldZone";
			return $arrConfig;
		} // function GetConfig
		
	} // class COldFileZone
	
?>