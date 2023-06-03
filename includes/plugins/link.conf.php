<?php
	/**
	 *	Конфиг бинда
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModLink
	 */

	/**
	 * 	Элемент конфига бинда
	 */
	class CBindConfStatement extends CFlex {
		protected $name = "";
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"name" => true,
				"label" => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		public function GetText( ) {
			return "\r\n".$this->name.";\r\n";
		} // function GetText
		
	} // class CBindConfStatement
	
	/**
	 * 	include
	 */
	class CBCSInclude extends CBindConfStatement {
		protected $file = "";
		
		public function GetText( ) {
			return "\r\ninclude \"".$this->file."\";\r\n";
		} // function GetText
		
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
			if ( $szName == "name" ) {
				$this->name = "include";
			}
			return $objRet;
		} // function InitAttr
		
	} // class CBCSInclude
	
	/**
	 * 	zone
	 */
	class CBCSZone extends CBindConfStatement {
		protected $zone_name = "";
		protected $type = "";
		protected $file = "";
		protected $masters = "";
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"zone_name" => true,
				"type" => true,
				"file" => true,
				"masters" => true
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		public function GetText( ) {
			if ( $this->masters === "" ) {
				return "\r\nzone \"".$this->zone_name."\" { type ".$this->type."; file \"".$this->file."\"; };\r\n";
			} else {
				return "\r\nzone \"".$this->zone_name."\" { type ".$this->type."; masters {".$this->masters.";}; };\r\n";
			}
		} // function GetText
		
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
			if ( $szName == "name" ) {
				$this->name = "zone";
			}
			return $objRet;
		} // function InitAttr
		
	} // class CBCSZone
	
	/**
	 * 	Обработчик конфига бинда
	 * 	<p>имя таблицы - это имя файла конфига</p> 
	 */
	class CHBindConfig extends CFlexHandler {
		
		/**
		 *	Проверяет таблицу
		 *	@param $arrOptions array набор настроек
		 *	@return void
		 */
		public function CheckTable( $arrOptions ) {
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			
			if ( !empty( $szTable ) ) {
				if ( !file_exists( $szTable ) ) {
					$hFile = @fopen( $szTable, "wb" );
					if ( $hFile ) {
						fclose( $hFile );
					}
				}
			}
		} // function CheckTable
		
		/**
		 *	Получение объектов
		 *	@param $arrOptions array массив настроек
		 *	@return CResult
		 */
		public function GetObject( $arrOptions = array( ) ) {
			$mxdRet = new CResult( );
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			
			if ( !empty( $szTable ) ) {
				if ( file_exists( $szTable ) ) {
					$szText = @file_get_contents( $szTable );
					if ( $szText !== "" ) {
						$szIndex = $arrOptions[ FHOV_WHERE ];
						if ( $szIndex === "zone" ) {
							$mxdRet = $this->GetConfZones( $szText );
						} elseif ( $szIndex === "include" ) {
							$mxdRet = $this->GetConfIncludes( $szText );
						}
					}
				}
			}
			
			return $mxdRet;
		} // function GetObject
		
		/**
		 *	Добавление объектов
		 *	@param $arrInput array набор новых объектов
		 *	@param $arrOptions array массив настроек
		 *	@return CResult
		 */
		public function AddObject( $arrInput, $arrOptions = array( ) ) {
			$objRet = new CResult( );
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			
			if ( !empty( $szTable ) ) {
				$arrBase = array( );
				$tmp = $this->GetObject( array( FHOV_WHERE => $arrOptions[ FHOV_WHERE ], FHOV_TABLE => $arrOptions[ FHOV_TABLE ] ) );
				if ( $tmp->HasResult( ) ) {
					$arrBase = $tmp->GetResult( );
				}
				foreach( $arrInput as $i => $v ) {
					$arrBase[ $i ] = $v;
				}
				if ( $arrOptions[ FHOV_WHERE ] == "zone" ) {
					$this->SetConfZone( $arrOptions[ FHOV_TABLE ], $arrBase );
				}
			}
			
			return $objRet;
		} // function AddObject
		
		/**
		 *	Удаление объекта
		 *	@param $arrInput array массив экземпляров класса
		 *	@param $arrOptions array массив настроек
		 *	@return CResult
		 */
		public function DelObject( $arrInput, $arrOptions = array( ) ) {
			$objRet = new CResult( );
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			
			if ( !empty( $szTable ) ) {
				$arrBase = array( );
				$tmp = $this->GetObject( array( FHOV_WHERE => $arrOptions[ FHOV_WHERE ], FHOV_TABLE => $arrOptions[ FHOV_TABLE ] ) );
				if ( $tmp->HasResult( ) ) {
					$arrBase = $tmp->GetResult( );
				}
				foreach( $arrInput as $i => $v ) {
					if ( isset( $arrBase[ $i ] ) ) {
						unset( $arrBase[ $i ] );
					}
				}
				if ( $arrOptions[ FHOV_WHERE ] == "zone" ) {
					$this->SetConfZone( $arrOptions[ FHOV_TABLE ], $arrBase );
				}
			}
						
			return $objRet;
		} // function DelObject
		
		/**
		 *	Обновление объектов
		 *	@param $arrInput array массив экземпляров класса
		 *	@param $arrOptions array массив настроек
		 *	@return CResult
		 */
		public function UpdObject( $arrInput, $arrOptions = array( ) ) {
			$objRet = new CResult( );
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			
			if ( !empty( $szTable ) ) {
				$arrBase = array( );
				$tmp = $this->GetObject( array( FHOV_WHERE => $arrOptions[ FHOV_WHERE ], FHOV_TABLE => $arrOptions[ FHOV_TABLE ] ) );
				if ( $tmp->HasResult( ) ) {
					$arrBase = $tmp->GetResult( );
				}
				foreach( $arrInput as $i => $v ) {
					if ( isset( $arrBase[ $i ] ) ) {
						$arrBase[ $i ] = $v;
					}
				}
				if ( $arrOptions[ FHOV_WHERE ] == "zone" ) {
					$this->SetConfZone( $arrOptions[ FHOV_TABLE ], $arrBase );
				}
			}
			
			return $objRet;
		} // function UpdObject
		
		private function SetConfZone( $szFile, $arrObject ) {
			$r = "";
			ob_start( );
			foreach( $arrObject as $i => $v ) {
				echo $v->GetText( );
			}
			$r = ob_get_clean( );
			file_put_contents( $szFile, $r );
		} // function AddConfZone
		
		private function GetConfZones( $szText ) {
			$objRet = new CResult( );
			$arrRegExp = array(
				"zone" => '/zone[^"]*"([^"]*)"[^\{]*\{([^\}]*)\}/sU',
				"zone_type" => '/type\s*([a-zA-Z]*);\s*/sU',
				"zone_file" => '/file[^"]*"([^"]*)";/',
				"zone_masters" => '/masters[^\{]*\{([0-9a-zA-Z.]*);/',
			);
			$tmp = NULL;
			preg_match_all( $arrRegExp[ "zone" ], $szText, $tmp );
			foreach( $tmp[ 1 ] as $i => $v ) {
				$tmp1 = array(
					"zone_name" => $v,
					"type" => "",
					"file" => "",
					"masters" => "",
					"label" => $v
				);
				$tmp2 = $tmp[ 2 ][ $i ];
				$tmp3 = NULL;
				if ( preg_match( $arrRegExp[ "zone_type" ], $tmp2, $tmp3 ) ) {
					$tmp1[ "type" ] = $tmp3[ 1 ];
				}
				$tmp3 = NULL;
				if ( preg_match( $arrRegExp[ "zone_file" ], $tmp2, $tmp3 ) ) {
					$tmp1[ "file" ] = $tmp3[ 1 ];
				}
				$tmp3 = NULL;
				if ( preg_match( $arrRegExp[ "zone_masters" ], $tmp2, $tmp3 ) ) {
					$tmp1[ "masters" ] = $tmp3[ 1 ];
				}
				$objZone = new CBCSZone( );
				$objZone->Create( $tmp1 );
				$objRet->AddResult( $objZone, $objZone->zone_name );
			}
			return $objRet;
		} // function GetConfZones
		
		private function GetConfIncludes( $szText ) {
			$objRet = new CResult( );
			$arrRegExp = array(
				"include" => '/include[^"]*"([^"]*)"/sU',
			);
			$tmp = NULL;
			preg_match_all( $arrRegExp[ "include" ], $szText, $tmp );
			foreach( $tmp[ 1 ] as $i => $v ) {
				$tmp1 = array( "file" => $v );
				$objInclude = new CBCSInclude( );
				$objInclude->Create( $tmp1 );
				$objRet->AddResult( $objInclude, $objInclude->file );
			}
			return $objRet;
		} // function GetConfIncludes
		
	} // class CHBindConfig
	
?>