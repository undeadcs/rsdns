<?php
	/**
	 *	Запросы к серверу
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModReport
	 */

	/**
	 * 	Запросы за сутки
	 */
	class CQueries extends CFlex {
		protected $id = 0;
		protected $cr_date = "";
		protected $maximum = 0.0;
		protected $total = 0.0;
		protected $queries = array( );
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true, "cr_date" => true, "maximum" => true, "total" => true, "queries" => true
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Получение XML экземпляра
		 *	@param $domDoc DOMDocument экземпляр данного класса
		 *	@return CResult
		 */
		public function GetXML( &$domDoc ) {
			global $objCMS;
			$objRet = parent::GetXML( $domDoc );
			$tmp = $objRet->GetResult( "doc" );
			
			$tmp1 = array( );
			for( $i = 0; $i < 24; ++$i ) {
				$szHour = "".( $i < 10 ? "0".$i : $i );
				for( $j = 0; $j < 60; $j += 5 ) {
					$szMinute = "".( $j < 10 ? "0".$j : $j );
					$tmp2 = array( );
					$tmp2[ "time" ] = $szHour.":".$szMinute;
					if ( $i == 0 ) {
						$tmp2[ "index" ] = intval( $j );
					} elseif ( $j < 10 ) {
						$tmp2[ "index" ] = intval( $i.$szMinute );
					} else {
						$tmp2[ "index" ] = intval( $szHour.$szMinute );
					}
					$tmp1[ ] = $tmp2;
				}
			}
			$tmp2 = array( );
			foreach( $tmp1 as $i => $v ) {
				$fValue = 0.0;
				if ( isset( $this->queries[ $v[ "index" ] ] ) ) {
					$fValue = floatval( $this->queries[ $v[ "index" ] ] );
				}
				$tmp2[ $v[ "index" ] ] = array(
					$fValue, $v[ "time" ]
				);
			}
			foreach( $tmp2 as $i => $v ) {
				$tmp1 = $domDoc->createElement( "TimeQuery" );
				$tmp1->setAttribute( "timestamp", $i );
				$tmp1->setAttribute( "queries", $v[ 0 ] );
				$tmp1->setAttribute( "time", $v[ 1 ] );
				$tmp->appendChild( $tmp1 );
			}
			
			//$domText = new DOMComment( '<!--[if IE]><script language="javascript" type="text/javascript" src="'.$objCMS->GetPath( "root_relative" ).'/excanvas.pack.js"></script><![endif]-->' );
			//$tmp->appendChild( $domText );
			$objRet->AddResult( $tmp, "doc" );
			return $objRet;
		} // function GetXML
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE	] = "ud_queries";
			$arrConfig[ FLEX_CONFIG_PREFIX	] = "queries_";
			$arrConfig[ FLEX_CONFIG_SELECT	] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE	] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE	] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME	] = "Queries";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "cr_date"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_DATE;
			$arrConfig[ "queries"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_ARRAY;
			return $arrConfig;
		} // function GetConfig
		
		/**
		 *	Инициализация атрибута объекта
		 *	@param $szName string имя атрибута
		 *	@param $arrInput mixed некое значение
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return CResult
		 */
		protected function InitAttr( $szName, &$arrInput, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::InitAttr( $szName, $arrInput, $arrConfig, $iMode );
			if ( $szName == "queries" && $iMode == FLEX_FILTER_DATABASE ) {
				$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
				if ( isset( $arrInput[ $szIndex ] ) ) {
					$this->queries = unserialize( $arrInput[ $szIndex ] );
				}
			}
			return $objRet;
		} // function InitAttr
		
		/**
		 *	Получение значения атрибута объекта
		 *	@param $szName string имя атрибута
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return mixed
		 */
		protected function FilterAttr( $szName, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			$tmp = parent::FilterAttr( $szName, $arrConfig,  $iMode );
			if ( $szName == "queries" && $iMode == FLEX_FILTER_DATABASE ) {
				$tmp = "'".@mysql_real_escape_string( serialize( $this->queries ) )."'";
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 * 	Очищает набор запросов
		 */
		public function ClearQueries( ) {
			$this->queries = array( );
		} // function ClearQueries
		
	} // class CQueries
	
	// QIT - Query Item Type
	define( "QIT_DOMAIN",	0	);
	define( "QIT_IP",	1	);
	
	/**
	 * 	Элемент запроса
	 */
	class CQueryItem extends CFlex {
		protected $id = 0;
		protected $type = QIT_DOMAIN;
		protected $value = "";
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true, "type" => true, "value" => true
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
			$arrConfig[ FLEX_CONFIG_TABLE	] = "ud_qitem";
			$arrConfig[ FLEX_CONFIG_PREFIX	] = "qitem_";
			$arrConfig[ FLEX_CONFIG_SELECT	] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE	] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE	] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME	] = "QueryItem";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			return $arrConfig;
		} // function GetConfig
		
	} // class CQueryItem
	
	/**
	 * 	Счетчик запросов
	 */
	class CQueryItemCount extends CFlex {
		protected $id = 0;
		protected $qdomain_id = 0; // элемент подсчета
		protected $qitem_id = 0; // элемент подсчета
		protected $count = 0.0; // счетчик запросов
		protected $label = ""; // метка для распознавания
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true, "qdomain_id" => true, "qitem_id" => true, "count" => true, "label" => true
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
			$arrConfig[ FLEX_CONFIG_TABLE	] = "ud_qcount";
			$arrConfig[ FLEX_CONFIG_PREFIX	] = "qcount_";
			$arrConfig[ FLEX_CONFIG_SELECT	] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE	] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE	] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME	] = "QueryCount";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "qdomain_id"	][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			$arrConfig[ "qitem_id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			return $arrConfig;
		} // function GetConfig
		
	} // class CQueryItemCount
	
	/**
	 * 	Запросы к домену
	 */
	class CQDomain extends CFlex {
		protected $id = 0;
		protected $cr_date = "";
		protected $hour = 0;
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true, "cr_date" => true, "hour" => true
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
			$arrConfig[ FLEX_CONFIG_TABLE	] = "ud_qdomains";
			$arrConfig[ FLEX_CONFIG_PREFIX	] = "qdomains_";
			$arrConfig[ FLEX_CONFIG_SELECT	] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE	] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE	] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME	] = "QDomain";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "hour"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED;
			$arrConfig[ "cr_date"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_DATE;
			return $arrConfig;
		} // function GetConfig
		
		/**
		 *	Получение данных для CREATE
		 *	@return CResult
		 */
		public function GetSQLCreate( ) {
			$objRet = parent::GetSQLCreate( );
			$tmp = new CResult( );
			$arrAttr = $objRet->GetResult( "attr" );
			$arrValues = $objRet->GetResult( "values" );
			unset( $arrAttr[ "queries" ], $arrValues[ "queries" ] );
			$tmp->AddResult( $arrAttr, "attr" );
			$tmp->AddResult( $arrValues, "values" );
			
			$arrConfig = $this->GetConfig( );
			$szTable = isset( $arrConfig[ FLEX_CONFIG_TABLE ] ) ? $arrConfig[ FLEX_CONFIG_TABLE ] : "";
			if ( is_string( $szTable ) && $szTable !== "" && !empty( $arrValues ) ) {
				$tmp->AddResult( $szTable, "table" );
				$szTable = "`".@mysql_real_escape_string( $szTable )."`";
				$tmp->AddResult( "CREATE TABLE IF NOT EXISTS ".$szTable." (".join( ",", $arrValues ).")", "query" );
			}
			return $tmp;
		} // function GetSQLCreate
		
		/**
		 *	Инициализация атрибута объекта
		 *	@param $szName string имя атрибута
		 *	@param $arrInput mixed некое значение
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return CResult
		 */
		protected function InitAttr( $szName, &$arrInput, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::InitAttr( $szName, $arrInput, $arrConfig, $iMode );
			if ( $szName == "queries" && $iMode == FLEX_FILTER_DATABASE ) {
				$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
				if ( isset( $arrInput[ $szIndex ] ) ) {
					$this->queries = unserialize( $arrInput[ $szIndex ] );
				}
			}
			return $objRet;
		} // function InitAttr
		
		/**
		 *	Получение значения атрибута объекта
		 *	@param $szName string имя атрибута
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return mixed
		 */
		protected function FilterAttr( $szName, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			$tmp = parent::FilterAttr( $szName, $arrConfig,  $iMode );
			if ( $szName == "queries" && $iMode == FLEX_FILTER_DATABASE ) {
				$tmp = "'".@mysql_real_escape_string( serialize( $this->queries ) )."'";
			}
			return $tmp;
		} // function FilterAttr
		
	} // class CQDomain
	
	class CCountQueryByDomain extends CFlex {
		protected $id = 0;
		protected $domain = "";
		protected $count = 0.0;
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true, "domain" => true, "count" => true
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
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "DomainCount";
			return $arrConfig;
		} // function GetConfig
		
		/**
		 *	Получение значения атрибута объекта
		 *	@param $szName string имя атрибута
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return mixed
		 */
		protected function FilterAttr( $szName, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			if ( $szName === "domain" && $iMode === FLEX_FILTER_XML ) {
				if ( strlen( $this->$szName ) > 49 ) {
					//ShowVarD( strlen( "123456789012345678901234567890123456789012345678901234567890" ) );
					//$this->$szName = wordwrap( $this->$szName, 49, "\r\n", true );
				}
			}
			$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			return $tmp;
		} // function FilterAttr
		
	} // class CCountQueryByDomain
	
?>