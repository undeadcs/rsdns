<?php
	/**
	 *	Модуль файлов зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	class CFileZoneTmp extends CFileZone {
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_tmp_zone";
			$arrConfig[ FLEX_CONFIG_PREFIX ] = "zone_";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "Zone";
			return $arrConfig;
		} // function GetConfig
		
	} // class CFileZoneTmp
?>