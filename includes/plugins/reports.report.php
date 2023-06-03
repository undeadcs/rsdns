<?php
	/**
	 *	Отчет
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModReport
	 */

	/**
	 * 	Отчет
	 */
	class CReport extends CFlex {
		protected $id = 0;
		protected $cr_date = ""; // дата создания отчета
		protected $cl_count = 0; // количество клиентов
		protected $cl_active = 0; // активных клиентов
		protected $cl_inactive = 0; // неактивных клиентов
		protected $cl_blocked = 0; // заблокированных клиентов
		protected $zones = 0; // количество зон
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true,
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
			$arrConfig[ FLEX_CONFIG_TABLE	] = "ud_report";
			$arrConfig[ FLEX_CONFIG_PREFIX	] = "report_";
			$arrConfig[ FLEX_CONFIG_SELECT	] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE	] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE	] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME	] = "Report";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "cr_date"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_DATE;
			$arrConfig[ "cl_count"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			$arrConfig[ "cl_active"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			$arrConfig[ "cl_inactive"	][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			$arrConfig[ "cl_blocked"	][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			$arrConfig[ "zones"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			return $arrConfig;
		} // function GetConfig
		
	} // class CReport
	
?>