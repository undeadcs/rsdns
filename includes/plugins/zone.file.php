<?php
	/**
	 *	Файл зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	// тип файла FZT - File Zone Type
	define( "FZT_DIRECT",		0	); // прямая зона
	define( "FZT_REVERSE",		1	); // обратная зона
	// режим получения текста
	define( "FZM_BIND",		0	); // формат файла бинда
	define( "FZM_CSV",		1	); // формат csv
	// флаги файла FZF - File Zone Flag
	define( 'FZF_CLIENTCREATE',	bindec( '00000000000000000000000000000001' ) ); // 0-й бит: 0 - создал суперадмин/админ/оператор, 1 - создал клиент
	define( 'FZF_REGISTERED',	bindec( '00000000000000000000000000000010' ) ); // 1-й бит: 0 - создан на прямую, 1 - создан через регистрацию домена
	
	/**
	 *	Файл зон
	 */
	class CFileZone extends CFlex {
		protected	$id			= 0,		// id записи
				$graph_vertex_id	= 0,		// id вершины в графе
				$type			= FZT_DIRECT,	// тип
				$name			= '',		// имя
				$default_ttl		= '',		// $TTL - для файла зон
				$comment		= '',		// комментарий
				$reg_date		= '',		// дата создания
				$last_edit		= '',		// дата последнего редактирования
				$flags			= 0,		// флаги
				$rrs			= array( );	// набор ресурсных записей
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'id' => true, 'name' => true, 'graph_vertex_id' => true, 'type' => true, 'state' => true,
				'default_ttl' => true, 'rrs' => true, 'reg_date' => true, 'last_edit' => true, 'flags' => true
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 * 	Очистка списка ресурсных записей
		 */
		public function ClearRRs( ) {
			$this->rrs = array( );
		} // function ClearrRrs
		
		/**
		 * 	Получение ресурсной записи по ее id
		 * 	@param $iId int id записи
		 * 	@return mixed
		 */
		public function GetRRById( $iId ) {
			return ( isset( $this->rrs[ $iId ] ) ? $this->rrs[ $iId ] : NULL );
		} // function GetRRById
		
		/**
		 * 	Возвращает количество ресурсных записей
		 */
		public function CountRRs( ) {
			return count( $this->rrs );
		} // function CountRRs
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE	] = 'ud_zone';
			$arrConfig[ FLEX_CONFIG_PREFIX	] = 'zone_';
			$arrConfig[ FLEX_CONFIG_SELECT	] = 'id';
			$arrConfig[ FLEX_CONFIG_UPDATE	] = 'id';
			$arrConfig[ FLEX_CONFIG_DELETE	] = 'id';
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = 'Zone';
			// настройки атрибутов
			$arrConfig[ 'id'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ 'id'		][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ 'graph_vertex_id'	][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_DEFAULT;
			$arrConfig[ 'graph_vertex_id'	][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ 'graph_vertex_id'	][ FLEX_CONFIG_DEFAULT	] = 0;
			$arrConfig[ 'name'		][ FLEX_CONFIG_LENGHT	] = 255;
			$arrConfig[ 'name'		][ FLEX_CONFIG_TITLE	] = 'Доменное имя';
			$arrConfig[ 'comment'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_TEXT;
			$arrConfig[ 'reg_date'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_DATE;
			$arrConfig[ 'last_edit'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_DATE | FLEX_TYPE_TIME;
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
			unset( $arrAttr[ 'rrs' ], $arrValues[ 'rrs' ] );
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
			$objRet = parent::GetXML( $domDoc );
			if ( !empty( $this->rrs ) ) {
				$doc = $objRet->GetResult( 'doc' );
				foreach( $this->rrs as $i => $v ) {
					$tmp = $v->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( 'doc' );
						$doc->appendChild( $tmp );
					}
				}
				$objRet->AddResult( $doc, 'doc' );
			}
			return $objRet;
		} // function GetXML
		
		/**
		 *	Инициализация атрибута объекта
		 *	@param $szName string имя атрибута
		 *	@param $arrInput mixed некое значение
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return CResult
		 */
		protected function InitAttr( $szName, &$arrInput, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			$objRet = new CResult( );
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			if ( $szName == 'name' ) {
				$objRet = parent::InitAttr( $szName, $arrInput, $arrConfig, $iMode );
				// проверяем доменное имя
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( isset( $arrInput[ $szIndex ] ) && is_string( $arrInput[ $szIndex ] ) ) {
					$szValue = @strval( $arrInput[ $szIndex ] );
					if ( $this->type === FZT_DIRECT ) {
						if ( !CValidator::DomainName( $szValue, true ) ) {
							$objRet->AddError( new CError( 1, 'Неверное значение поля \''.$szTitle.'\'' ) );
						}
					}
				} else {
					$objRet->AddError( new CError( 1, 'Отсутствует поле \''.$szTitle.'\'' ) );
				}
			} elseif ( $szName == 'rrs' ) {
				if ( isset( $arrInput[ $szIndex ] ) && is_array( $arrInput[ $szIndex ] ) ) {
					$arrRRTypes = array(
						RRT_SOA => 'CRR_SOA', RRT_NS => 'CRR_NS', RRT_A => 'CRR_A', RRT_AAAA => 'CRR_AAAA',
						RRT_CNAME => 'CRR_CNAME', RRT_MX => 'CRR_MX', RRT_PTR => 'CRR_PTR', RRT_SRV => 'CRR_SRV',
						RRT_TXT => 'CRR_TXT',
						RRT_TTL => 'CRR__TTL', RRT_ORIGIN => 'CRR__ORIGIN', RRT_INCLUDE => 'CRR__INCLUDE'
					);
					foreach( $arrInput[ $szIndex ] as $i => $v ) {
						$tmp = $v->GetArray( );
						$tmp = $tmp->GetResult( );
						$tmp1 = array( );
						foreach( $tmp as $j => $w ) {
							$szIndex = $v->GetAttributeIndex( $j, NULL, $iMode );
							$tmp1[ $szIndex ] = $w;
						}
						$szClass = '';
						if ( isset( $arrRRTypes[ $v->type ] ) ) {
							$szClass = $arrRRTypes[ $v->type ];
						} else {
							$szClass = 'CResourceRecord';
						}
						if ( !empty( $szClass ) ) {
							$tmpObject = new $szClass( );
							$tmpObject->Create( $tmp1, $iMode );
							if ( $tmpObject->id ) {
								$this->rrs[ $tmpObject->id ] = $tmpObject;
							} else {
								$this->rrs[ ] = $tmpObject;
							}
						}
					}
				}
			} else {
				$objRet = parent::InitAttr( $szName, $arrInput, $arrConfig, $iMode );
			}
			return $objRet;
		} // function InitAttr
		
		/**
		 * 	Получение текста файла зон
		 */
		public function GetText( $iMode = FZM_BIND ) {
			$r = '';
			
			if ( $iMode === FZM_BIND ) {
				ob_start( );
				if ( !empty( $this->default_ttl ) ) {
					echo '$TTL '.$this->default_ttl."\r\n";
				}
				$arrDirs = array( '_ORIGIN', '_TTL', '_INCLUDE' );
				foreach( $this->rrs as $i => $v ) {
					$tmp = $v->GetArray( );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( );
						$tmp1 = array( );
						if ( $v->type === "SRV" ) {
							// особая сборка
							$tmp1[ ] = "_".$tmp[ "service" ]."._".$tmp[ "proto" ].".".$tmp[ "name" ];
							$tmp1[ ] = $tmp[ "ttl" ];
							$tmp1[ ] = $tmp[ "class" ];
							$tmp1[ ] = $tmp[ "type" ];
							$tmp1[ ] = $tmp[ "priority" ];
							$tmp1[ ] = $tmp[ "weight" ];
							$tmp1[ ] = $tmp[ "port" ];
							$tmp1[ ] = $tmp[ "target" ];
						} elseif ( in_array( $v->type, $arrDirs ) ) {
							$tmp1[ ] = str_replace( "_", "\$", $tmp[ "type" ] )." ".$tmp[ "name" ];
						} else {
							$tmp1[ ] = $tmp[ "name" ];
							$tmp1[ ] = $tmp[ "ttl" ];
							$tmp1[ ] = $tmp[ "class" ];
							$tmp1[ ] = $tmp[ "type" ];
							if ( $v->type === "SOA" ) {
								// особый формат данных
								$tmp2 = array( );
								$tmp1[ ] = $tmp[ "origin" ];
								$tmp1[ ] = $tmp[ "person" ];
								$tmp2[ ] = "(\r\n";
								$tmp2[ ] = $tmp[ "serial" ]." ;Serial\r\n";
								$tmp2[ ] = $tmp[ "refresh" ]." ;Refresh\r\n";
								$tmp2[ ] = $tmp[ "retry" ]." ;Retry\r\n";
								$tmp2[ ] = $tmp[ "expire" ]." ;Expire\r\n";
								$tmp2[ ] = $tmp[ "minimum_ttl" ]." ;Minimum TTL\r\n";
								$tmp2 = join( "\t\t\t\t", $tmp2 ).")";
								$tmp1[ ] = $tmp2;
							} else {
								$tmp1[ ] = $tmp[ "data" ];
							}
						}
						echo join( "\t", $tmp1 )."\r\n";
					}
				}
				$r = ob_get_clean( );
				if ( $r === false ) {
					$r = "";
				}
			}
			if ( $iMode === FZM_CSV ) {
				ob_start( );
				$hFile = fopen( 'php://output', 'w' );
				if ( $hFile ) {
					// name ttl class type data
					$arrRow = array( '', '', '', '', '' );
					if ( !empty( $this->default_ttl ) ) {
						$arrRow[ 0 ] = '$TTL';
						$arrRow[ 1 ] = $this->default_ttl;
						fputcsv( $hFile, $arrRow, ',' );//';' );
					}
					$arrDirs = array( '_ORIGIN', '_TTL', '_INCLUDE' );
					foreach( $this->rrs as $i => $v ) {
						$arrRow = array( '', '', '', '', '' );
						$tmp = $v->GetArray( );
						if ( $tmp->HasResult( ) ) {
							$tmp = $tmp->GetResult( );
							$tmp1 = array( );
							if ( $v->type === 'SRV' ) {
								// особая сборка
								$arrRow[ 0 ] = '_'.$tmp[ 'service' ].'._'.$tmp[ 'proto' ].'.'.$tmp[ 'name' ];
								$arrRow[ 1 ] = $tmp[ 'ttl' ];
								$arrRow[ 2 ] = $tmp[ 'class' ];
								$arrRow[ 3 ] = $tmp[ 'type' ];
								$tmp1[ ] = $tmp[ 'priority' ];
								$tmp1[ ] = $tmp[ 'weight' ];
								$tmp1[ ] = $tmp[ 'port' ];
								$tmp1[ ] = $tmp[ 'target' ];
								$arrRow[ 4 ] = join( ' ', $tmp1 );
							} elseif ( in_array( $v->type, $arrDirs ) ) {
								$arrRow[ 0 ] = str_replace( '_', '$', $tmp[ 'type' ] );
								$arrRow[ 1 ] = $tmp[ 'name' ];
							} else {
								$arrRow[ 0 ] = $tmp[ 'name' ];
								$arrRow[ 1 ] = $tmp[ 'ttl' ];
								$arrRow[ 2 ] = $tmp[ 'class' ];
								$arrRow[ 3 ] = $tmp[ 'type' ];
								if ( $v->type === 'SOA' ) {
									// особый формат данных
									$tmp1[ ] = $tmp[ 'origin' ];
									$tmp1[ ] = $tmp[ 'person' ];
									$tmp1[ ] = $tmp[ 'serial' ];
									$tmp1[ ] = $tmp[ 'refresh' ];
									$tmp1[ ] = $tmp[ 'retry' ];
									$tmp1[ ] = $tmp[ 'expire' ];
									$tmp1[ ] = $tmp[ 'minimum_ttl' ];
									$arrRow[ 4 ] = join( ' ', $tmp1 );
								} else {
									$arrRow[ 4 ] = $tmp[ 'data' ];
								}
							}
							$arrRow[ 4 ] = str_replace( '"', '&quot;', $arrRow[ 4 ] );
							fputcsv( $hFile, $arrRow, ',' );//';' );
						}
					}
					fclose( $hFile );
				}
				$r = ob_get_clean( );
			}
			return $r;
		} // function GetText
		
		/**
		 * 	Получение ресурсных записей
		 * 	@param $bClone bool клонировать
		 * 	@param $bIgnoreIndex bool игнорировать индексы
		 * 	@return array
		 */
		public function GetRRs( $bClone = true, $bIgnoreIndex = false ) {
			$tmp = array( );
			foreach( $this->rrs as $i => $v ) {
				if ( $bClone ) {
					if ( $bIgnoreIndex ) {
						$tmp[ ] = clone $this->rrs[ $i ];
					} else {
						$tmp[ $i ] = clone $this->rrs[ $i ];
					}
				} else {
					if ( $bIgnoreIndex ) {
						$tmp[ ] = $this->rrs[ $i ];
					} else {
						$tmp[ $i ] = $this->rrs[ $i ];
					}
				}
			}
			return $tmp;
		} // function GetRRs
		
	} // class CFileZone
	
	/**
	 * 	Элемент списка файлов зон
	 */
	class CFileZoneListItem extends CFlex {
		protected	$id			= 0,
				$name			= '',
				$type			= 0,
				$comment		= '',
				$soa			= '',
				$client_full_name	= '',
				$client_login		= '';
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = 'FileZoneListItem';
			return $arrConfig;
		} // function GetConfig
		
	} // class CFileZoneList
	
	
?>