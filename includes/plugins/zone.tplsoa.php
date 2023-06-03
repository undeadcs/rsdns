<?php
	/**
	 *	Шаблон SOA
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	/**
	 * 	Шаблон SOA записи
	 */
	class CTplSoa extends CFlex {
		protected $id = 0;
		protected $ttl = 0;
		protected $origin = ""; // будет чистая строка, чтоб не разрушать файлы зон, после сноса сервака
		protected $refresh = 0;
		protected $retry = 0;
		protected $expire = 0;
		protected $minimum_ttl = 0;
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_tplsoa";
			$arrConfig[ FLEX_CONFIG_PREFIX ] = "tplsoa_";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = "TplSoa";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "id"		][ FLEX_CONFIG_DIGITS	] = 10;
			return $arrConfig;
		} // function GetConfig
		
	} // class CTplSoa
	
?>