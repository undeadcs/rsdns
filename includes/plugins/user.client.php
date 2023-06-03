<?php
	/**
	 *	Учетка: Клиент
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModUser
	 */

	// Состояние пользователя US - User State
	define( 'US_NOTACTIVE',		1	); // не активный
	define( 'US_ACTIVE',		2	); // активный
	define( 'US_BLOCKED',		3	); // заблокирован

	/**
	 * 	Клиент
	 */
	class CClient extends CUser {
		protected	$state		= US_NOTACTIVE,	// состояние
				$ip_block	= '',		// блок ip адресов
				$email		= '',		// адрес электронной почты
				$first_name	= '',		// имя
				$last_name	= '',		// фамилия
				$full_name	= '',		// полное имя
				$full_name_en	= '',		// полное имя на английском
				$inn		= '',		// ИНН
				$kpp		= '',		// КПП
				$country	= '',		// страна
				$phone		= '',		// телефон
				$fax		= '',		// факс
				$addr		= '',		// юридический адрес
				$postcode	= '',		// почтовый индекс
				$region		= '',		// область
				$city		= '',		// город
				$street		= '',		// улица
				$person		= '',		// получатель
				$zones		= array( );	// файлы зон
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'state' => true, 'balance' => true, 'ip_block' => true,	'email' => true,
				'full_name' => true, 'full_name_en' => true, 'inn' => true, 'kpp' => true,
				'country' => true, 'phone' => true, 'fax' => true, 'addr' => true, 'postcode' => true,
				'first_name' => true, 'last_name' => true, 'region' => true, 'city' => true,
				'street' => true, 'person' => true, 'zones' => true
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 * 	Получение блока ip как массива пар ( ip, mask )
		 */
		public function GetIpBlockArray( ) {
			if ( empty( $this->ip_block ) ) {
				return array( );
			}
			$arrRet = array( );
			$arrBlock = preg_split( '/\r\n|\n|\r/', $this->ip_block );
			foreach( $arrBlock as $i => $v ) {
				$arrIp = array( );
				$tmp = NULL;
				preg_match( '/^(?:(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.)(?:(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.)(?:(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.)(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(?:\/([0-9]|[12][0-9]|3[0-2]))$/', $v, $tmp );
				for( $j = 1; $j < 5; ++$j ) {
					$arrIp[ ] = $tmp[ $j ];
				}
				$szMask = $tmp[ 5 ];
				$arrRet[ ] = array(
					'ip' => join( '.', $arrIp ),
					'mask' => $szMask
				);
			}
			return $arrRet;
		} // function GetIpBlockArray
		
		/**
		 *	Получение конфига
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE	] = 'ud_client';
			$arrConfig[ FLEX_CONFIG_PREFIX	] = 'client_';
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = 'Client';
			// настройки атрибутов
			$arrConfig[ 'state'		][ FLEX_CONFIG_TITLE	] = 'Состояние';
			$arrConfig[ 'ip_block'		][ FLEX_CONFIG_TITLE	] = 'Блок IP адресов';
			$arrConfig[ 'ip_block'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_TEXT;
			$arrConfig[ 'add_info'		][ FLEX_CONFIG_TITLE	] = 'Дополнительная информация';
			$arrConfig[ 'zones'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_ARRAY;
			$arrConfig[ 'email'		][ FLEX_CONFIG_TITLE	] = 'E-mail';
			$arrConfig[ 'full_name'		][ FLEX_CONFIG_TITLE	] = 'Организация (по русски)';
			$arrConfig[ 'full_name_en'	][ FLEX_CONFIG_TITLE	] = 'Организация (латиницей)';
			$arrConfig[ 'inn'		][ FLEX_CONFIG_TITLE	] = 'ИНН';
			$arrConfig[ 'kpp'		][ FLEX_CONFIG_TITLE	] = 'КПП';
			$arrConfig[ 'country'		][ FLEX_CONFIG_TITLE	] = 'Страна';
			$arrConfig[ 'phone'		][ FLEX_CONFIG_TITLE	] = 'Телефон';
			$arrConfig[ 'fax'		][ FLEX_CONFIG_TITLE	] = 'Факс';
			$arrConfig[ 'addr'		][ FLEX_CONFIG_TITLE	] = 'Юридичесикй адрес';
			$arrConfig[ 'postcode'		][ FLEX_CONFIG_TITLE	] = 'Почтовый индекс';
			$arrConfig[ 'region'		][ FLEX_CONFIG_TITLE	] = 'Область';
			$arrConfig[ 'city'		][ FLEX_CONFIG_TITLE	] = 'Город, населенный пункт';
			$arrConfig[ 'street'		][ FLEX_CONFIG_TITLE	] = 'Улица, дом, офис';
			$arrConfig[ 'person'		][ FLEX_CONFIG_TITLE	] = 'Получатель';
			// ограничения на значения
			$arrConfig[ 'add_info'		][ FLEX_CONFIG_LENGHT	] = 2048;
			$arrConfig[ 'ip_block'		][ FLEX_CONFIG_LENGHT	] = 1024;
			$arrConfig[ 'email'		][ FLEX_CONFIG_LENGHT	] = 128;
			$arrConfig[ 'inn'		][ FLEX_CONFIG_LENGHT	] = 10;
			$arrConfig[ 'kpp'		][ FLEX_CONFIG_LENGHT	] = 9;
			$arrConfig[ 'country'		][ FLEX_CONFIG_LENGHT	] = 2;
			$arrConfig[ 'postcode'		][ FLEX_CONFIG_LENGHT	] = 6;
			return $arrConfig;
		} // function GetConfig
		
		/**
		 *	Получение данных для CREATE
		 *	@return CResult
		 */
		public function GetSQLCreate( ) {
			$objRet = parent::GetSQLCreate( );
			$tmp = new CResult( );
			$arrAttr = $objRet->GetResult( 'attr' );
			$arrValues = $objRet->GetResult( 'values' );
			unset( $arrAttr[ 'zones' ], $arrValues[ 'zones' ], $arrAttr[ 'fields' ], $arrValues[ 'fields' ] );
			$tmp->AddResult( $arrAttr, 'attr' );
			$tmp->AddResult( $arrValues, 'values' );
			
			$arrConfig = $this->GetConfig( );
			$szTable = isset( $arrConfig[ FLEX_CONFIG_TABLE ] ) ? $arrConfig[ FLEX_CONFIG_TABLE ] : '';
			if ( is_string( $szTable ) && $szTable !== '' && !empty( $arrValues ) ) {
				$tmp->AddResult( $szTable, 'table' );
				$szTable = '`'.@mysql_real_escape_string( $szTable ).'`';
				$tmp->AddResult( 'CREATE TABLE IF NOT EXISTS '.$szTable.' ('.join( ',', $arrValues ).')', 'query' );
			}
			return $tmp;
		} // function GetSQLCreate
		
		/**
		 *	Получение XML экземпляра
		 *	@param $domDoc DOMDocument экземпляр данного класса
		 *	@return CResult
		 */
		public function GetXML( &$domDoc ) {
			$mxdRet = parent::GetXML( $domDoc );
			$domCur = $mxdRet->GetResult( 'doc' );
			$arrAdd = array( 'zones' );
			foreach( $arrAdd as $v ) {
				if ( !empty( $this->$v ) ) {
					$tmp = $this->$v;
					foreach( $tmp as $j => $w ) {
						$tmpXml = $w->GetXML( $domDoc );
						if ( !$tmpXml->HasError( ) ) {
							$tmpXml = $tmpXml->GetResult( 'doc' );
							$domCur->appendChild( $tmpXml );
						}
					}
				}
			}
			$mxdRet->AddResult( $domCur, 'doc' );
			return $mxdRet;
		} // function GetXML
		
		/**
		 *	Фильтрует значение для выбранного атрибута, используется для ввода данных в объект
		 *	@param $szName string имя атрибута
		 *	@param $arrInput mixed некое значение
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return CResult
		 */
		protected function InitAttr( $szName, &$arrInput, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			$objRet = new CResult( );
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			if ( $szName == 'zones' ) {
				if ( isset( $arrInput[ $szIndex ] ) && is_array( $arrInput[ $szIndex ] ) ) {
					foreach( $arrInput[ $szIndex ] as $i => $v ) {
						if ( is_object( $v ) && ( get_class( $v ) == 'CFileZone' ) ) {
							$this->zones[ ] = $v;
						} else {
							$objFileZone = new CFileZone( );
							$tmp = $objFileZone->Create( $v, $iMode );
							if ( !$tmp->HasError( ) ) {
								$this->zones[ ] = $objFileZone;
							}
						}
					}
				}
			} else {
				$objRet = parent::InitAttr( $szName, $arrInput, $arrConfig, $iMode );
				$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
				$szTitle = $this->GetAttributeTitle( $szName, $arrConfig );
				$arrMust = array(
					'balance', 'email', 'full_name_en', 'full_name', 'country', 'phone', 'addr',
					'postcode', 'region', 'city', 'street', 'person', 'first_name', 'last_name'
				);
				if ( in_array( $szName, $arrMust ) ) {
					if ( !isset( $arrInput[ $szIndex ] ) ) {
						$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ), $szName );
					} elseif ( $arrInput[ $szIndex ] === '' ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
					} elseif ( $szName == 'email' ) {
						if ( !CValidator::Email( $this->$szName ) || ( strlen( $this->$szName ) < 6 ) ) {
							$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
						}
					} elseif ( $szName == 'full_name_en' ) {
						if ( !CValidator::EngOnly( $this->$szName ) || ( strlen( $this->$szName ) < 6 ) ) {
							$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
						}
					} elseif ( $szName == 'full_name' ) {
						if ( strlen( $this->$szName ) < 10 ) {
							$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
						}
					} elseif ( $szName == 'phone' ) {
						if ( !CValidator::Phone( $this->$szName ) ) {//|| ( strlen( $this->$szName ) < 15 ) ) {
							$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
						}
					} elseif ( $szName == 'fax' ) {
						if ( !CValidator::Phone( $this->$szName ) ) {//|| ( strlen( $this->$szName ) < 15 ) ) {
							$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
						}
					} elseif ( $szName == 'addr' ) {
						if ( strlen( $this->$szName ) < 15 ) {
							$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
						}
					} elseif ( $szName == 'addr_p' ) {
						if ( strlen( $this->$szName ) < 15 ) {
							$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
						}
					} elseif ( $szName == 'country' ) {
						if ( strlen( $this->$szName ) < 2 ) {
							$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
						}
					} elseif ( $szName == 'postcode' ) {
						if ( strlen( $this->$szName ) < 6 ) {
							$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
						}
					}
				}
				if ( ( $szName == 'inn' ) && ( $this->$szName !== '' ) ) {
					if ( !is_numeric( $this->$szName ) || ( strlen( $this->$szName ) < 10 ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
					}
				}
				if ( ( $szName == 'kpp' ) && ( $this->$szName !== '' ) ) {
					if ( !is_numeric( $this->$szName ) || ( strlen( $this->$szName ) < 9 ) ) {
						$objRet->AddError( new CError( 1, "Поле '".$szTitle."' содержит не допустимое значение" ), $szName );
					}
				}
				if ( ( $szName == 'ip_block' ) && ( $this->$szName !== '' ) ) {
					if ( !CValidator::IpBlock( $this->$szName ) ) {
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
			$tmp = parent::FilterAttr( $szName, $arrConfig, $iMode );
			if ( $szName == 'email' && $iMode == FLEX_FILTER_XML && ( strlen( $tmp ) > 63 ) ) {
				$tmp = wordwrap( $tmp, 63, '<br/>', true );
			}
			return $tmp;
		} // function FilterAttr
		
	} // class CClient
	
	/**
	 * 	IP блок
	 */
	class CIpBlock extends CFlex {
		protected $name = '';
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'name' => true
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
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = 'IpBlock';
			return $arrConfig;
		} // function GetConfig
		
	} // class CIpBlock
	
	
?>