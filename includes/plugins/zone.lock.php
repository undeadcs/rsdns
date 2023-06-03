<?php
	/**
	 *	Модуль файлов зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	class CZoneLock extends CFlex {
		protected $id = 0;
		protected $cr_date = "";
		protected $user_v_id = 0;
		protected $zone_v_id = 0;
		protected $ip = "";
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true,
				"cr_date" => true,
				"user_v_id" => true,
				"zone_v_id" => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_lock";
			$arrConfig[ FLEX_CONFIG_PREFIX ] = "lock_";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = "ZoneLock";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "id"		][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ "cr_date"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_DATE | FLEX_TYPE_TIME;
			$arrConfig[ "user_v_id"		][ FLEX_CONFIG_TYPE	] = //FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			$arrConfig[ "zone_v_id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			return $arrConfig;
		} // function GetConfig
		
	} // class CZoneLock
	
?>