<?php
	/**
	 *	Ресурсные записи файлов зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	// классы ресурсных записей RRC - Resource Record Class
	define( "RRC_NONE",		""	); // пусто
	define( "RRC_IN",		"IN"	); // IN - Internet

	// типы ресурсных записей RRT - Resource Record Type
	define( "RRT_NONE",		""		); // пусто - ни при каких обстоятельствах такая запись существовать не может!
	define( "RRT_SOA",		"SOA"		); // SOA
	define( "RRT_NS",		"NS"		); // NS
	define( "RRT_A",		"A"		); // A
	define( "RRT_CNAME",		"CNAME"		); // CNAME
	define( "RRT_MX",		"MX"		); // MX
	define( "RRT_PTR",		"PTR"		); // PTR
	define( "RRT_SRV",		"SRV"		); // SRV
	define( "RRT_AAAA",		"AAAA"		); // AAAA
	define( "RRT_TXT",		"TXT"		); // TXT
	define( "RRT_TTL",		"_TTL"		); // $TTL
	define( "RRT_ORIGIN",		"_ORIGIN"	); // $ORIGIN
	define( "RRT_INCLUDE",		"_INCLUDE"	); // $INCLUDE
	
	/**
	 *	Ресурсная запись файла зон (общий вид)
	 */
	class CResourceRecord extends CFlex {
		protected $id = 0;
		protected $zone_file_id = 0;
		protected $order = 0; // порядок в файле зон
		protected $name = ''; // имя ( первый параметр )
		protected $ttl = ''; // время жизни в секундах
		protected $class = ''; // класс ( второй параметр )
		protected $type = ''; // тип ( третий параметр )
		protected $data = ''; // данные
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'id' => true,
				'zone_file_id' => true,
				'order' => true,
				'name' => true,
				'ttl' => true,
				'class' => true,
				'type' => true,
				'data' => true
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			return array( );
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			return $objRet;
		} // function HasError
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = 'ud_rr';
			$arrConfig[ FLEX_CONFIG_PREFIX ] = 'rr_';
			$arrConfig[ FLEX_CONFIG_SELECT ] = 'id';
			$arrConfig[ FLEX_CONFIG_UPDATE ] = 'id';
			$arrConfig[ FLEX_CONFIG_DELETE ] = 'id';
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = 'ResRec';
			// настройки атрибутов
			$arrConfig[ 'id'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ 'zone_file_id'	][ FLEX_CONFIG_TYPE	] = //FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL;
			$arrConfig[ 'order'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL;
			$arrConfig[ 'data'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_TEXT;
			$arrConfig[ 'name'		][ FLEX_CONFIG_TITLE	] = 'Имя';
			$arrConfig[ 'ttl'		][ FLEX_CONFIG_TITLE	] = 'TTL';
			$arrConfig[ 'class'		][ FLEX_CONFIG_TITLE	] = 'Класс';
			$arrConfig[ 'type'		][ FLEX_CONFIG_TITLE	] = 'Тип';
			$arrConfig[ 'data'		][ FLEX_CONFIG_TITLE	] = 'Данные';
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
			if ( $szName == "class" ) {
				if ( empty( $this->class ) ) {
					$this->class = RRC_IN;
				}
			}
			return $objRet;
		} // function InitAttr
		
	} // class CResourceRecord
	
	// для каждой типовой записи будем паковать и распаковывать поле данных
	
	class CRR_SOA extends CResourceRecord {
		protected $origin = ''; // хост
		protected $person = ''; // владелец ( email с заменой @ на . )
		protected $serial = ''; // номер версии
		protected $refresh = 0; // обновление ( с )
		protected $retry = 0; // повтор ( с )
		protected $expire = 0; // устаревание ( с )
		protected $minimum_ttl = 0; // минимальное TLL ( с )
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'serial' => true, 'origin' => true, 'person' => true, 'refresh' => true,
				'retry' => true, 'expire' => true, 'minimum_ttl' => true
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			$tmp = array(
				'origin' => 'origin', 'person' => 'person', 'serial' => 'serial',
				'refresh' => 'refresh', 'retry' => 'retry', 'expire' => 'expire',
				'minimum_ttl' => 'minimum_ttl'
			);
			return $tmp;
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			$arrConfig = $this->GetConfig( );
			$arrMust = array( "name", "origin", "person", "serial", "refresh", "retry", "expire", "minimum_ttl" );
			foreach( $arrMust as $v ) {
				$szTitle = ( isset( $arrConfig[ $v ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $v ][ FLEX_CONFIG_TITLE ] ) : $v );
				if ( $this->$v === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $v );
				} elseif ( $v == "serial" || $v == "refresh" || $v == "retry" || $v == "expire" || $v == "minimum_ttl" ) {
					if ( !is_numeric( $this->$v ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $v );
					}
				}
			}
			return $objRet;
		} // function HasError
		
		/**
		 *	Наполнение параметров объекта
		 *	используя маппинг можно получить разные имена атрибутов сущности в разных хранилищах
		 *	@param $arrInput array входные данные для объекта
		 *	@param $iInputMode int режим, из которого были получены данные
		 *	@return CResult
		 */
		public function Create( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::Create( $arrInput, $iMode );
			// наполняем спец поля
			$arrConfig = $this->GetConfig( );
			if ( !empty( $this->data ) ) {
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$szValue = trim( $this->data );
					$arrValue = explode( " ", $szValue );
					$this->origin = $arrValue[ 0 ];
					$this->person = $arrValue[ 1 ];
					$this->serial = $arrValue[ 2 ];
					$this->refresh = intval( $arrValue[ 3 ] );
					$this->retry = intval( $arrValue[ 4 ] );
					$this->expire = intval( $arrValue[ 5 ] );
					$this->minimum_ttl = intval( $arrValue[ 6 ] );
				}
			}
			return $objRet;
		} // function Create
		
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
			if ( $szName == "type" ) {
				$this->type = RRT_SOA;
			}
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			$arrMust = array( "name", "origin", "person", "serial", "refresh", "retry", "expire", "minimum_ttl" );
			if ( in_array( $szName, $arrMust ) ) {
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ), $szName );
				} elseif ( $arrInput[ $szIndex ] === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
				} elseif ( $szName == "serial" || $szName == "refresh" || $szName == "retry" || $szName == "expire" || $szName == "minimum_ttl" ) {
					if ( !is_numeric( $this->$szName ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
					}
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
			$arrConfigRow = isset( $arrConfig[ $szName ] ) ? $arrConfig[ $szName ] : array( FLEX_CONFIG_LENGHT => 255 );
			$tmp = NULL;
			if ( $szName == "data" ) {
				// наполним атрибут нужными данными
				$tmp = array( );
				$tmp[ ] = $this->origin;
				$tmp[ ] = $this->person;
				$tmp[ ] = $this->serial;
				$tmp[ ] = $this->refresh;
				$tmp[ ] = $this->retry;
				$tmp[ ] = $this->expire;
				$tmp[ ] = $this->minimum_ttl;
				$tmp = join( " ", $tmp );
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = "'".@mysql_real_escape_string( $tmp )."'";
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( "cp1251", "UTF-8//TRANSLIT", $tmp );
				}
			} else {
				$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ "origin"		][ FLEX_CONFIG_TITLE	] = "Origin";
			$arrConfig[ "person"		][ FLEX_CONFIG_TITLE	] = "Person";
			$arrConfig[ "serial"		][ FLEX_CONFIG_TITLE	] = "Serial";
			$arrConfig[ "refresh"		][ FLEX_CONFIG_TITLE	] = "Fefresh";
			$arrConfig[ "retry"		][ FLEX_CONFIG_TITLE	] = "Retry";
			$arrConfig[ "expire"		][ FLEX_CONFIG_TITLE	] = "Expire";
			$arrConfig[ "minimum_ttl"	][ FLEX_CONFIG_TITLE	] = "MinimumTTL";
			return $arrConfig;
		} // function GetConfig
		
		/**
		 * 	Обновление номера
		 */
		public function UpdateSerial( ) {
			++$this->serial;
			
			$tmp = array( );
			$tmp[ ] = $this->origin;
			$tmp[ ] = $this->person;
			$tmp[ ] = $this->serial;
			$tmp[ ] = $this->refresh;
			$tmp[ ] = $this->retry;
			$tmp[ ] = $this->expire;
			$tmp[ ] = $this->minimum_ttl;
			$this->data = join( " ", $tmp );
		} // function UpdateSerial
		
	} // class CRR_SOA
	
	class CRR_NS extends CResourceRecord {
		protected $server = ''; // имя сервера
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'server' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			$tmp = array( 'server' => 'server' );
			return $tmp;
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			$arrConfig = $this->GetConfig( );
			$arrMust = array( "name", "server" );
			foreach( $arrMust as $v ) {
				$szTitle = ( isset( $arrConfig[ $v ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $v ][ FLEX_CONFIG_TITLE ] ) : $v );
				if ( $this->$v === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $v );
				}
			}
			return $objRet;
		} // function HasError
		
		/**
		 *	Наполнение параметров объекта
		 *	используя маппинг можно получить разные имена атрибутов сущности в разных хранилищах
		 *	@param $arrInput array входные данные для объекта
		 *	@param $iInputMode int режим, из которого были получены данные
		 *	@return CResult
		 */
		public function Create( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::Create( $arrInput, $iMode );
			// наполняем спец поля
			$arrConfig = $this->GetConfig( );
			if ( !empty( $this->data ) ) {
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$this->server = trim( $this->data );
				}
			}
			return $objRet;
		} // function Create
		
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
			if ( $szName == "type" ) {
				$this->type = RRT_NS;
			}
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			$arrMust = array( "name", "server" );
			if ( in_array( $szName, $arrMust ) ) {
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ), $szName );
				} elseif ( $this->$szName === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
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
			$arrConfigRow = isset( $arrConfig[ $szName ] ) ? $arrConfig[ $szName ] : array( FLEX_CONFIG_LENGHT => 255 );
			$tmp = "";
			if ( $szName == "data" ) {
				$tmp = $this->server;
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = "'".@mysql_real_escape_string( $tmp )."'";
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( "cp1251", "UTF-8//TRANSLIT", $tmp );
				}
			} else {
				$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ "server" ][ FLEX_CONFIG_TITLE ] = "Server";
			return $arrConfig;
		} // function GetConfig
		
	} // class CRR_NS
	
	class CRR_A extends CResourceRecord {
		protected $address = ''; // IPv4 адрес
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'address' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			$tmp = array( "address" => "address" );
			return $tmp;
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			$arrConfig = $this->GetConfig( );
			$arrMust = array( "name", "address" );
			foreach( $arrMust as $v ) {
				$szTitle = ( isset( $arrConfig[ $v ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $v ][ FLEX_CONFIG_TITLE ] ) : $v );
				if ( $this->$v === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $v );
				} elseif ( $v == "address" ) {
					if ( !CValidator::IpAddress( $this->$v ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $v );
					}
				}
			}
			return $objRet;
		} // function HasError
		
		/**
		 *	Наполнение параметров объекта
		 *	используя маппинг можно получить разные имена атрибутов сущности в разных хранилищах
		 *	@param $arrInput array входные данные для объекта
		 *	@param $iInputMode int режим, из которого были получены данные
		 *	@return CResult
		 */
		public function Create( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::Create( $arrInput, $iMode );
			// наполняем спец поля
			$arrConfig = $this->GetConfig( );
			if ( !empty( $this->data ) ) {
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$this->address = trim( $this->data );
				}
			}
			return $objRet;
		} // function Create
		
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
			if ( $szName == "type" ) {
				$this->type = RRT_A;
			}
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			$arrMust = array( "name", "address" );
			if ( in_array( $szName, $arrMust ) ) {
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ), $szName );
				} elseif ( $this->$szName === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
				} elseif ( $szName == "address" ) {
					if ( !CValidator::IpAddress( $this->$szName ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
					}
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
			$arrConfigRow = isset( $arrConfig[ $szName ] ) ? $arrConfig[ $szName ] : array( FLEX_CONFIG_LENGHT => 255 );
			if ( $szName == "data" ) {
				$tmp = $this->address;
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = "'".@mysql_real_escape_string( $tmp )."'";
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( "cp1251", "UTF-8//TRANSLIT", $tmp );
				}
			} else {
				$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ 'address' ][ FLEX_CONFIG_TITLE ] = 'Address';
			return $arrConfig;
		} // function GetConfig
		
	} // class CRR_A
	
	class CRR_CNAME extends CResourceRecord {
		protected $host = ''; // имя хоста
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'host' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Наполнение параметров объекта
		 *	используя маппинг можно получить разные имена атрибутов сущности в разных хранилищах
		 *	@param $arrInput array входные данные для объекта
		 *	@param $iInputMode int режим, из которого были получены данные
		 *	@return CResult
		 */
		public function Create( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::Create( $arrInput, $iMode );
			// наполняем спец поля
			$arrConfig = $this->GetConfig( );
			if ( !empty( $this->data ) ) {
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$this->host = $this->data;
				}
			}
			return $objRet;
		} // function Create
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			$tmp = array( 'host' => 'host' );
			return $tmp;
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			$arrConfig = $this->GetConfig( );
			$arrMust = array( "name", "host" );
			foreach( $arrMust as $v ) {
				$szTitle = $this->GetAttributeTitle( $v, $arrConfig );
				if ( $this->$v === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $v );
				}
			}
			return $objRet;
		} // function HasError
		
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
			if ( $szName == "type" ) {
				$this->type = RRT_CNAME;
			}
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			$arrMust = array( "name", "host" );
			if ( in_array( $szName, $arrMust ) ) {
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ), $szName );
				} elseif ( $this->$szName === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
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
			$arrConfigRow = isset( $arrConfig[ $szName ] ) ? $arrConfig[ $szName ] : array( FLEX_CONFIG_LENGHT => 255 );
			$tmp = NULL;
			if ( $szName == "data" ) {
				$tmp = $this->host;
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = "'".@mysql_real_escape_string( $tmp )."'";
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( "cp1251", "UTF-8//TRANSLIT", $tmp );
				}
			} else {
				$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ "host" ][ FLEX_CONFIG_TITLE ] = "Host";
			return $arrConfig;
		} // function GetConfig
		
	} // class CRR_CNAME
	
	class CRR_MX extends CResourceRecord {
		protected $preference = ''; // приоритет
		protected $host = ''; // хост
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'preference' => true,
				'host' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Наполнение параметров объекта
		 *	используя маппинг можно получить разные имена атрибутов сущности в разных хранилищах
		 *	@param $arrInput array входные данные для объекта
		 *	@param $iInputMode int режим, из которого были получены данные
		 *	@return CResult
		 */
		public function Create( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::Create( $arrInput, $iMode );
			// наполняем спец поля
			$arrConfig = $this->GetConfig( );
			if ( !empty( $this->data ) ) {
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = explode( " ", $this->data );
					$this->preference = $tmp[ 0 ];
					$this->host = $tmp[ 1 ];
				}
			}
			return $objRet;
		} // function Create
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			$tmp = array( "preference" => "preference", "host" => "host" );
			return $tmp;
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			$arrConfig = $this->GetConfig( );
			$arrMust = array( "name", "preference", "host" );
			foreach( $arrMust as $v ) {
				$szTitle = $this->GetAttributeTitle( $v, $arrConfig );
				if ( $this->$v === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $v );
				} elseif ( $v == "preference" ) {
					if ( !is_numeric( $this->$v ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $v );
					}
				} elseif ( $v == "host" ) {
					if ( !CValidator::DomainName( $this->$v, true, true ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $v );
					}
				}
			}
			return $objRet;
		} // function HasError
		
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
			if ( $szName == "type" ) {
				$this->type = RRT_MX;
			}
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			$arrMust = array( "name", "preference", "host" );
			if ( in_array( $szName, $arrMust ) ) {
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ), $szName );
				} elseif ( $this->$szName === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
				} elseif ( $szName == "preference" ) {
					if ( !is_numeric( $this->$szName ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
					}
				} elseif ( $szName == "host" ) {
					if ( !CValidator::DomainName( $this->$szName, true, true ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
					}
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
			$arrConfigRow = isset( $arrConfig[ $szName ] ) ? $arrConfig[ $szName ] : array( FLEX_CONFIG_LENGHT => 255 );
			$tmp = NULL;
			if ( $szName == "data" ) {
				$tmp = $this->data;
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = $this->preference." ".$this->host;
					$tmp = "'".@mysql_real_escape_string( $tmp )."'";
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( "cp1251", "UTF-8//TRANSLIT", $tmp );
				}
			} else {
				$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ "preference"	][ FLEX_CONFIG_TITLE ] = "Preference";
			$arrConfig[ "host"		][ FLEX_CONFIG_TITLE ] = "Host";
			return $arrConfig;
		} // function GetConfig
		
	} // class CRR_MX
	
	class CRR_PTR extends CResourceRecord {
		protected $name_ptr = ""; // имя
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'name_ptr' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Наполнение параметров объекта
		 *	используя маппинг можно получить разные имена атрибутов сущности в разных хранилищах
		 *	@param $arrInput array входные данные для объекта
		 *	@param $iInputMode int режим, из которого были получены данные
		 *	@return CResult
		 */
		public function Create( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::Create( $arrInput, $iMode );
			// наполняем спец поля
			$arrConfig = $this->GetConfig( );
			if ( !empty( $this->data ) ) {
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$this->name_ptr = trim( $this->data );
				}
			}
			return $objRet;
		} // function Create
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			$tmp = array( "name_ptr" => "name_ptr" );
			return $tmp;
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			$arrConfig = $this->GetConfig( );
			$arrMust = array( "name", "name_ptr" );
			foreach( $arrMust as $v ) {
				$szTitle = $this->GetAttributeTitle( $v, $arrConfig );
				if ( $this->$v === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $v );
				}/* elseif ( $v == 'name_ptr' ) {
					if ( !preg_match( '/\.$/', $this->$v ) ) {
						$objRet->AddError( new CError( 1, "Поле '$szTitle' содержит не допустимое значение" ), $v );
					}
				}*/
			}
			return $objRet;
		} // function HasError
		
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
			if ( $szName == "type" ) {
				$this->type = RRT_PTR;
			}
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			$arrMust = array( "name", "name_ptr", "priority", "weight", "port", "target" );
			if ( in_array( $szName, $arrMust ) ) {
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ), $szName );
				} elseif ( $this->$szName === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
				}/* elseif ( $szName == 'name_ptr' ) {
					if ( !preg_match( '/\.$/', $this->$v ) ) {
						$objRet->AddError( new CError( 1, "Поле '$szTitle' содержит не допустимое значение" ), $v );
					}
				}*/
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
			$arrConfigRow = isset( $arrConfig[ $szName ] ) ? $arrConfig[ $szName ] : array( FLEX_CONFIG_LENGHT => 255 );
			if ( $szName == "data" ) {
				$tmp = $this->name_ptr;
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = "'".@mysql_real_escape_string( $tmp )."'";
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( "cp1251", "UTF-8//TRANSLIT", $tmp );
				}
			} else {
				$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ "name_ptr" ][ FLEX_CONFIG_TITLE ] = "Host name";
			return $arrConfig;
		} // function GetConfig
		
	} // class CRR_PTR
	
	class CRR_SRV extends CResourceRecord {
		protected $service = ''; // сервис
		protected $proto = ''; // прототип
		protected $priority = 0; // приоритет
		protected $weight = 0; // вес
		protected $port = 0; // порт
		protected $target = ''; // цель
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'service' => true, 'proto' => true, 'priority' => true,
				'weight' => true, 'port' => true, 'target' => true
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Наполнение параметров объекта
		 *	используя маппинг можно получить разные имена атрибутов сущности в разных хранилищах
		 *	@param $arrInput array входные данные для объекта
		 *	@param $iInputMode int режим, из которого были получены данные
		 *	@return CResult
		 */
		public function Create( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::Create( $arrInput, $iMode );
			// наполняем спец поля
			$arrConfig = $this->GetConfig( );
			if ( $iMode == FLEX_FILTER_DATABASE ) {
				if ( !empty( $this->name ) ) {
					$tmp = $this->name;
					$szRegExp = '/_([0-9a-zA-Z]*)\._([0-9a-zA-Z]*)\.([0-9a-zA-Z]*)/';
					$tmp1 = NULL;
					if ( preg_match( $szRegExp, $tmp, $tmp1 ) ) {
						$this->service = $tmp1[ 1 ];
						$this->proto = $tmp1[ 2 ];
						$this->name = $tmp1[ 3 ];
					}
				}
				if ( !empty( $this->data ) ) {
					$tmp = trim( $this->data );
					$tmp = explode( " ", $tmp );
					$this->priority = $tmp[ 0 ];
					$this->weight = $tmp[ 1 ];
					$this->port = $tmp[ 2 ];
					$this->target = $tmp[ 3 ];
				}
			}
			return $objRet;
		} // function Create
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			$tmp = array(
				'service' => 'service', 'proto' => 'proto', 'priority' => 'priority',
				'weight' => 'weight', 'port' => 'port', 'target' => 'target'
			);
			return $tmp;
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			$arrConfig = $this->GetConfig( );
			$arrMust = array( 'service', 'proto', 'priority', 'weight', 'port', 'target' );
			foreach( $arrMust as $v ) {
				$szTitle = $this->GetAttributeTitle( $v, $arrConfig );
				if ( $this->$v === '' ) {
					$objRet->AddError( new CError( 1, "Поле '$szTitle' пусто" ), $v );
				} elseif ( $v == 'priority' || $v == 'weight' || $v == 'port' ) {
					if ( !is_numeric( $this->$v ) ) {
						$objRet->AddError( new CError( 1, "Поле '$szTitle' содержит недопустимое значение" ), $v );
					}
				}
			}
			return $objRet;
		} // function HasError
		
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
			if ( $szName == "type" ) {
				$this->type = RRT_SRV;
			}
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			$arrMust = array( "service", "proto", "priority", "weight", "port", "target" );
			if ( in_array( $szName, $arrMust ) ) {
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ), $szName );
				} elseif ( $this->$szName === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
				} elseif ( $szName == "priority" || $szName == "weight" || $szName == "port" ) {
					if ( !is_numeric( $this->$szName ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит недопустимое значение" ), $szName );
					}
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
			$arrConfigRow = isset( $arrConfig[ $szName ] ) ? $arrConfig[ $szName ] : array( FLEX_CONFIG_LENGHT => 255 );
			if ( $szName == "name" ) {
				$tmp = $this->name;
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = "_".$this->service."._".$this->proto.".".$this->name;
					$tmp = "'".@mysql_real_escape_string( $tmp )."'";
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( "cp1251", "UTF-8//TRANSLIT", $tmp );
				}
			} elseif ( $szName == "data" ) {
				$tmp = $this->data;
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = $this->priority." ".$this->weight." ".$this->port." ".$this->target;
					$tmp = "'".@mysql_real_escape_string( $tmp )."'";
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( "cp1251", "UTF-8//TRANSLIT", $tmp );
				}
			} else {
				$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ "service"		][ FLEX_CONFIG_TITLE ] = "Service";
			$arrConfig[ "proto"		][ FLEX_CONFIG_TITLE ] = "Proto";
			$arrConfig[ "priority"		][ FLEX_CONFIG_TITLE ] = "Priority";
			$arrConfig[ "weight"		][ FLEX_CONFIG_TITLE ] = "Weight";
			$arrConfig[ "port"		][ FLEX_CONFIG_TITLE ] = "Port";
			$arrConfig[ "target"		][ FLEX_CONFIG_TITLE ] = "Target";
			return $arrConfig;
		} // function GetConfig
		
	} // class CRR_SRV
	
	class CRR_AAAA extends CResourceRecord {
		protected $address = ''; // IPv6 адрес
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'address' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Наполнение параметров объекта
		 *	используя маппинг можно получить разные имена атрибутов сущности в разных хранилищах
		 *	@param $arrInput array входные данные для объекта
		 *	@param $iInputMode int режим, из которого были получены данные
		 *	@return CResult
		 */
		public function Create( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::Create( $arrInput, $iMode );
			// наполняем спец поля
			$arrConfig = $this->GetConfig( );
			if ( !empty( $this->data ) ) {
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$this->address = $this->data;
				}
			}
			return $objRet;
		} // function Create
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			$tmp = array( "address" => "address" );
			return $tmp;
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			$arrConfig = $this->GetConfig( );
			$arrMust = array( "name", "address" );
			foreach( $arrMust as $v ) {
				$szTitle = $this->GetAttributeTitle( $v, $arrConfig );
				if ( $this->$v === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $v );
				} elseif ( $v == "address" ) {
					if ( !CValidator::IpAddress( $this->$v, false ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $v );
					}
				}
			}
			return $objRet;
		} // function HasError
		
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
			if ( $szName == "type" ) {
				$this->type = RRT_AAAA;
			}
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			$arrMust = array( "name", "address" );
			if ( in_array( $szName, $arrMust ) ) {
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ), $szName );
				} elseif ( $this->$szName === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
				} elseif ( $szName == "address" ) {
					if ( !CValidator::IpAddress( $this->$szName, false ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
					}
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
			$arrConfigRow = isset( $arrConfig[ $szName ] ) ? $arrConfig[ $szName ] : array( FLEX_CONFIG_LENGHT => 255 );
			if ( $szName == "data" ) {
				$tmp = $this->address;
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$tmp = '\''.@mysql_real_escape_string( $tmp ).'\'';
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( "cp1251", "UTF-8//TRANSLIT", $tmp );
				}
			} else {
				$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ 'address' ][ FLEX_CONFIG_TITLE ] = 'Address';
			return $arrConfig;
		} // function GetConfig
		
	} // class CRR_AAAA
	
	class CRR_TXT extends CResourceRecord {
		protected $text = ''; // текст
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'text' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Наполнение параметров объекта
		 *	используя маппинг можно получить разные имена атрибутов сущности в разных хранилищах
		 *	@param $arrInput array входные данные для объекта
		 *	@param $iInputMode int режим, из которого были получены данные
		 *	@return CResult
		 */
		public function Create( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::Create( $arrInput, $iMode );
			// наполняем спец поля
			$arrConfig = $this->GetConfig( );
			if ( !empty( $this->data ) ) {
				if ( $iMode == FLEX_FILTER_DATABASE ) {
					$this->text = $this->data;
				}
			}
			return $objRet;
		} // function Create
		
		/**
		 * 	Возвращает массив игнорируемых атрибутов
		 * 	@return array
		 */
		public function GetAttrIgnoreList( ) {
			$tmp = array( 'text' => 'text' );
			return $tmp;
		} // function GetIgnoreAttrList
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			$arrConfig = $this->GetConfig( );
			$arrMust = array( 'name', 'text' );
			foreach( $arrMust as $v ) {
				$szTitle = $this->GetAttributeTitle( $v, $arrConfig );
				if ( $this->$v === "" ) {
					$objRet->AddError( new CError( 1, "Поле '$szTitle' пусто" ), $v );
				}
			}
			return $objRet;
		} // function HasError
		
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
			if ( $szName == 'type' ) {
				$this->type = RRT_TXT;
			}
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			$arrMust = array( 'name', 'text' );
			if ( in_array( $szName, $arrMust ) ) {
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '$szTitle'" ), $szName );
				} elseif ( $this->$szName === '' ) {
					$objRet->AddError( new CError( 1, "Поле '$szTitle' пусто" ), $szName );
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
			$arrConfigRow = isset( $arrConfig[ $szName ] ) ? $arrConfig[ $szName ] : array( FLEX_CONFIG_LENGHT => 255 );
			if ( $szName == 'data' ) {
				$tmp = $this->text;
				if ( $iMode & FLEX_FILTER_DATABASE ) {
					$tmp = "'".@mysql_real_escape_string( $tmp )."'";
				} elseif ( $iMode == FLEX_FILTER_XML ) {
					$tmp = iconv( 'cp1251', 'UTF-8//TRANSLIT', $tmp );
				}
			} else {
				$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			}
			return $tmp;
		} // function FilterAttr
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ 'text' ][ FLEX_CONFIG_TITLE ] = 'Text';
			return $arrConfig;
		} // function GetConfig
		
	} // class CRR_TXT
	
	/**
	 * 	Директива TTL
	 */
	class CRR__TTL extends CResourceRecord {
		
		/**
		 * 	Проверяет наличие ошибок в записи
		 */
		public function Check( ) {
			$objRet = new CResult( );
			if ( !is_numeric( $this->name ) ) {
				$objRet->AddError( new CError( 1, 'Поле \'$TTL\' содержит не допустимое значение' ) );
			}
			return $objRet;
		} // function HasError
		
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

			$this->ttl = '';
			$this->type = '_TTL';
			$this->class = '';
			$this->data = '';
			
			return $objRet;
		} // function InitAttr
		
	} // class CRR__TTL
	
	/**
	 * 	Директива ORIGIN
	 */
	class CRR__ORIGIN extends CResourceRecord {
		
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
			
			$this->ttl = '';
			$this->type = '_ORIGIN';
			$this->class = '';
			$this->data = '';
			
			return $objRet;
		} // function InitAttr
		
	} // class CRR__ORIGIN
	
	/**
	 * 	Директива INCLUDE
	 */
	class CRR__INCLUDE extends CResourceRecord {
		
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
			
			$this->ttl = '';
			$this->type = '_INCLUDE';
			$this->class = '';
			$this->data = '';
			
			return $objRet;
		} // function InitAttr
		
	} // class CRR__INCLUDE
	
	/**
	 * 	Обработчик ресурсных записей
	 */
	class CHResourceRecord extends CFlexHandler {
		
		/**
		 * 	Генерирует ресурсную запись нужного типа
		 * 	@param $arrInput array набор данных записи
		 * 	@param $iMode int режим работы
		 * 	@return CResult
		 */
		public function GenerateRR( $arrInput, $iMode = FLEX_FILTER_PHP ) {
			$objRet = new CResult( );
			$objRR = new CResourceRecord( );
			$szTypeIndex = $objRR->GetAttributeIndex( 'type', NULL, $iMode );
			$arrRRTypes = array(
				RRT_SOA => 'CRR_SOA', RRT_NS => 'CRR_NS', RRT_A => 'CRR_A', RRT_AAAA => 'CRR_AAAA',
				RRT_CNAME => 'CRR_CNAME', RRT_MX => 'CRR_MX', RRT_PTR => 'CRR_PTR', RRT_SRV => 'CRR_SRV',
				RRT_TXT => 'CRR_TXT',
				RRT_TTL => 'CRR__TTL', RRT_ORIGIN => 'CRR__ORIGIN', RRT_INCLUDE => 'CRR__INCLUDE'
			);
			if ( isset( $arrRRTypes[ $arrInput[ $szTypeIndex ] ] ) ) {
				$szClass = $arrRRTypes[ $arrInput[ $szTypeIndex ] ];
				$objRR = new $szClass( );
			}
			$tmp = $objRR->Create( $arrInput, $iMode );
			if ( $iMode != FLEX_FILTER_DATABASE && $tmp->HasError( ) ) {
				$objRet->AddError( $tmp );
			}
			if ( $objRR->type === 'PTR' ) {
				if ( !preg_match( '/\.$/', $objRR->name_ptr ) ) {
					$objRR->Create( array(
						'rr_name_ptr' => $objRR->name_ptr.'.'
					) );
				}
			}
			$objRet->AddResult( $objRR, 'rr' );
			return $objRet;
		} // function $arrInput
		
	} // class CHResourceRecord
	
?>