<?php
	/**
	 *	Парсер текста файлов зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	/**
	 * 	Парсер файлов зон
	 */
	class CZoneParser extends CFlex {
		private $hRRs = NULL;
		
		private function WrongSymbols( &$szText ) {
			$szRegExp = '/[^\x00-\xff]/sU';//'/[^0-9a-zA-Z\s\-\\.\/,;\'"~=()\$@*+_]/sU';
			return preg_match( $szRegExp, $szText );
		} // function WrongSymbols
		
		private function FilterEmpty( &$arrRRs ) {
			foreach( $arrRRs as $i => $v ) {
				$v = trim( $v );
				if ( $v === "" ) {
					// пустые убираем
					unset( $arrRRs[ $i ] );
				} elseif ( preg_match( '/;/', $v ) ) {
					// комментарии тоже
					$tmp = preg_replace( "/;.*/", '', $v );
					if ( $tmp !== "" ) {
						$arrRRs[ $i ] = $tmp;
					}
				}
			}
		} // function FilterEmpty
		
		private function ImplodeBlocks( &$arrRRs ) {
			$tmp = array( );
			$tmp1 = "";
			$bInBraceBlock = false;
			foreach( $arrRRs as $i => $v ) {
				if ( preg_match( "/\(/", $v ) ) {
					$bInBraceBlock = true;
				}
				if ( $bInBraceBlock ) {
					$tmp1[ ] = $v;
					if ( preg_match( "/\)/", $v ) ) {
						if ( !empty( $tmp1 ) ) {
							$tmp[ ] = join( " ", $tmp1 );
						}
						$bInBraceBlock = false;
						$tmp1 = array( );
					}
				} else {
					$tmp[ ] = $v;
				}
			}
			$arrRRs = $tmp;
		} // function ImplodeBlocks
		
		private function FilterAttrs( &$arrAttr ) {
			// склевивает атрибуты, которые были строками ""
			$bInBlock = false;
			$tmp = array( );
			$tmp1 = "";
			foreach( $arrAttr as $i => $v ) {
				$v = trim( $v );
				if ( preg_match( '/"/', $v ) ) {
					if ( $bInBlock ) {
						$bInBlock = false;
						$tmp[ ] = $tmp1.$v;
						$tmp1 = "";
					} else {
						$bInBlock = true;
						$tmp1 .= $v." ";
					}
				} else {
					if ( $bInBlock ) {
						$tmp1 .= $v." ";
					} elseif ( $v != "" && $v != "(" && $v != ")" ) {
						$tmp[ ] = $v;
					}
				}
			}
			if ( !empty( $tmp1 ) ) {
				$tmp[ ] = $tmp1;
			}
			$arrAttr = $tmp;
		} // function FilterAttrs
		
		private function BuildAttrs( &$arrRRs ) {
			$tmp = array( );
			foreach( $arrRRs as $i => $v ) {
				$tmp1 = preg_split( '/\s/', $v );
				$this->FilterAttrs( $tmp1 );
				$tmp[ ] = $tmp1;
			}
			$arrRRs = $tmp;
		} // function BuildAttrs
		
		private function BuildRR( $arrRow ) {
			$objRet = new CResult( );
			$tmpRRRow = array(
				"rr_name" => "",
				"rr_ttl" => "",
				"rr_class" => "",
				"rr_type" => "",
				"rr_data" => ""
			);
			$arrClass = array( "IN" );
			$arrType = array( "SOA", "NS", "A", "MX", "CNAME", "PTR", "AAAA", "SRV", "TXT" );
			$arrDirs = array( "\$ORIGIN", "\$TTL", "\$INCLUDE" );
			if ( in_array( $arrRow[ 0 ], $arrDirs ) ) {
				$tmpRRRow[ "rr_type" ] = str_replace( "\$", "_", $arrRow[ 0 ] );
				$tmpRRRow[ "rr_name" ] = $arrRow[ 1 ];
			} else {
				foreach( $arrRow as $i => $v ) {
					if ( in_array( $v, $arrClass ) ) {
						/**
						 * 1. попался класс на пути
						 * 2. класс уже попадался, значит то было имя, а это класс
						 * 3. остальное идет в данные
						 */
						if ( $tmpRRRow[ "rr_class" ] == "" ) {
							$tmpRRRow[ "rr_class" ] = $v;
						} elseif ( $tmpRRRow[ "rr_name" ] == "" ) {
							$tmpRRRow[ "rr_name" ] = $tmpRRRow[ "rr_class" ];
							$tmpRRRow[ "rr_class" ] = $v;
						} else {
							$tmpRRRow[ "rr_data" ] .= $v." ";
						}
					} elseif ( in_array( $v, $arrType ) ) {
						/**
						 * 1. Попался тип на пути
						 * 2. Тип уже был значит все идет в данные
						 */
						if ( $tmpRRRow[ "rr_type" ] == "" ) {
							$tmpRRRow[ "rr_type" ] = $v;
						} elseif ( $tmpRRRow[ "rr_name" ] == "" ) {
							$tmpRRRow[ "rr_name" ] = $tmpRRRow[ "rr_type" ];
							$tmpRRRow[ "rr_type" ] = $v;
						} else {
							$tmpRRRow[ "rr_data" ] .= $v." ";
						}
					} else {
						/**
						 *
						 */
						if ( $tmpRRRow[ "rr_class" ] == "" ) {
							if ( is_numeric( $v ) ) {
								if ( $tmpRRRow[ "rr_ttl" ] == "" ) {
									$tmpRRRow[ "rr_ttl" ] = $v;
								} elseif ( $tmpRRRow[ "rr_type" ] == "" ) {
									$objRet->AddError( new CError( 1, "Ошибка в записи" ) );
									return $objRet;
								} else {
									$tmpRRRow[ "rr_data" ] .= $v." ";
								}
							} else {
								if ( $tmpRRRow[ "rr_name" ] == "" ) {
									if ( $tmpRRRow[ "rr_type" ] == "" ) {
										$tmpRRRow[ "rr_name" ] = $v;
									} else {
										$tmpRRRow[ "rr_data" ] .= $v." ";
									}
								} elseif ( $tmpRRRow[ "rr_type" ] == "" ) {
									$objRet->AddError( new CError( 1, "Ошибка в записи" ) );
									return $objRet;
								} else {
									$tmpRRRow[ "rr_data" ] .= $v." ";
								}
							}
						} elseif ( $tmpRRRow[ "rr_type" ] == "" ) {
							$objRet->AddError( new CError( 1, "Ошибка в записи" ) );
							return $objRet;
						} else {
							$tmpRRRow[ "rr_data" ] .= $v." ";
						}
					}
				}
			}
			$tmpRRRow[ "rr_data" ] = trim( $tmpRRRow[ "rr_data" ] );
			if ( $tmpRRRow[ "rr_type" ] === "PTR" ) {
				// в обратных зонах циферка может означать имя, а не TTL (которое пропускается), т.к. там пишется часть IP
				if ( $tmpRRRow[ "rr_name" ] === "" && $tmpRRRow[ "rr_ttl" ] !== "" ) {
					$tmp = intval( $tmpRRRow[ "rr_ttl" ] );
					if ( $tmp < 256 ) {
						$tmpRRRow[ "rr_name" ] = $tmpRRRow[ "rr_ttl" ];
						$tmpRRRow[ "rr_ttl" ] = "";
					}
				}
			}
			$tmp = $this->hRRs->GenerateRR( $tmpRRRow, FLEX_FILTER_DATABASE );
			if ( $tmp->HasResult( ) ) {
				$tmp1 = $tmp->GetResult( );
				$tmp1 = current( $tmp1 );
				$objRet->AddResult( $tmp1, "rr" );
			}
			return $objRet;
		} // function BuildRR
		
		private function BuildRRs( $arrRRs ) {
			$tmp = array( );
			foreach( $arrRRs as $v ) {
				$tmp1 = $this->BuildRR( $v );
				if ( $tmp1->HasResult( ) ) {
					$tmp[ ] = $tmp1->GetResult( "rr" );
				}
				/*if ( $tmp1->HasError( ) ) {
					ShowVar( $v );
				}*/
			}
			return $tmp;
		} // function BuildRRs
		
		private function Parse( $szText ) {
			/**
			 * 1. Разбиваем текст по строкам
			 * 2. Сносим комментарии и пустые строки
			 * 3. Склеиваем многострочные записи
			 * 4. Строим атрибуты каждой записи
			 * 5. Строим записи
			 */
			$this->hRRs = new CHResourceRecord( );
			$arrRRs = preg_split( "/\r\n|\n|\r/sU", $szText );
			$this->FilterEmpty( $arrRRs );
			$this->ImplodeBlocks( $arrRRs );
			$this->BuildAttrs( $arrRRs );
			return $this->BuildRRs( $arrRRs );
		} // function Parse
		
		/**
		 * 	Фильтрация вредных символов
		 */
		private function FilterSymbols( $szText ) {
			$szText = preg_replace(
				array( '/<\?/sU', '/\?>/sU' ),
				array( '&lt;?', '?&gt;' ),
				$szText
			);
			return $szText;
		} // function FIlterSymbols
		
		/**
		 * 	Получение файла зон из текста
		 * 	@param $szText string исходный текст файла зон
		 * 	@return CResult
		 */
		public function GetFileZone( $szText ) {
			$objRet = new CResult( );
			$szText = $this->FilterSymbols( $szText );
			if ( $this->WrongSymbols( $szText ) ) {
				$objRet->AddError( new CError( 1, "Не допустимые символы в тексте" ) );
			} else {
				$tmp = $this->Parse( $szText );
				foreach( $tmp as $i => $v ) {
					if ( $v->type === "" ) {
						$objRet->AddError( new CError( 1, "Неизвестный тип ресурсной записи" ) );
						unset( $tmp[ $i ] );
					} else {
						$tmp1 = $v->Check( );
						if ( $tmp1->HasError( ) ) {
							$objRet->AddError( $tmp1 );
						}
					}
				}
				$objTTL = NULL;
				if ( empty( $tmp ) ) {
					$objRet->AddError( new CError( 1, "Пустой файл" ) );
					return $objRet;
				}
				$bWasSoa = false;
				$bWasTTL = false;
				foreach( $tmp as $i => $v ) {
					if ( $v->type === "SOA" ) {
						if ( $bWasSoa ) {
							unset( $tmp[ $i ] );
						} else {
							$bWasSoa = true;
						}
					} elseif( $v->type === "_TTL" && !$bWasTTL ) {
						$bWasTTL = true;
						$objTTL = $v;
						unset( $tmp[ $i ] );
					}
				}
				if ( !$bWasSoa ) {
					$objRet->AddError( new CError( 1, "Не допустимый набор записей в файле" ) );
					return $objRet;
				}
				$objRet->AddResult( $tmp, "rrs" );
				if ( isset( $objTTL ) ) {
					$objRet->AddResult( $objTTL, "default_ttl" );
				}
			}
			return $objRet;
		} // function GetFileZone
		
	} // class CZoneParser
?>