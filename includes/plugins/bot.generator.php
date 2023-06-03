<?php
	/**
	 *	Генератор
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModBot
	 */

	// опции генерации строки RWO - Random Word Option
	define( "RWO_NUMBER",			bindec( "00000000000000000000000000000001" ) ); // числа
	define( "RWO_ENLOW",			bindec( "00000000000000000000000000000010" ) ); // a-z
	define( "RWO_ENUP",			bindec( "00000000000000000000000000000100" ) ); // A-Z
	define( "RWO_RULOW",			bindec( "00000000000000000000000000001000" ) ); // а-я
	define( "RWO_RUUP",			bindec( "00000000000000000000000000010000" ) ); // А-Я
	define( "RWO_PUNCT",			bindec( "00000000000000000000000000100000" ) ); // пунктуация

	/**
	 * 	Генератор бреда
	 */
	class CRandomGenerator {
		
		public function __construct( ) {
			mt_srand( );
		} // function __construct
		
		/**
		 * 	Генерит случайную учетку
		 */
		public function Client( ) {
			$objRet = new CClient( );
			$arrIndex = $objRet->GetAttributeIndexList( );
			$arrData = array(
				$arrIndex[ "login" ] => $this->RndWord( 20, RWO_NUMBER | RWO_ENLOW | RWO_ENUP ),
				$arrIndex[ "password" ] => $this->RndWord( 20, RWO_NUMBER | RWO_ENLOW | RWO_ENUP ),
				$arrIndex[ "reg_date" ] => $this->RndDate( false ),
				$arrIndex[ "last_edit" ] => $this->RndDate( ),
				$arrIndex[ "last_login" ] => $this->RndDate( ),
				$arrIndex[ "add_info" ] => $this->RndWordList( RWO_NUMBER | RWO_ENLOW | RWO_ENUP | RWO_RULOW | RWO_RUUP, 20 ),
				$arrIndex[ "ip_block" ] => $this->RndIp( mt_rand( 1, 5 ) ),
				$arrIndex[ "email" ] => $this->RndEmail( ),
				$arrIndex[ "full_name" ] => $this->RndWordList( RWO_NUMBER | RWO_RULOW | RWO_RUUP, 10 ),
				$arrIndex[ "full_name_en" ] => $this->RndWordList( RWO_NUMBER | RWO_ENLOW | RWO_ENUP, 10 ),
				$arrIndex[ "inn" ] => $this->RndWord( 10 ),
				$arrIndex[ "kpp" ] => $this->RndWord( 9 ),
				$arrIndex[ "country" ] => $this->RndWord( 2, RWO_ENLOW ),
				$arrIndex[ "phone" ] => $this->RndPhone( ),
				$arrIndex[ "fax" ] => $this->RndPhone( ),
				$arrIndex[ "addr" ] => $this->RndWordList( RWO_NUMBER | RWO_RULOW | RWO_RUUP, 10 ),
				$arrIndex[ "postcode" ] => $this->RndWord( 9, RWO_NUMBER ),
				$arrIndex[ "region" ] => $this->RndWordList( RWO_RULOW | RWO_RUUP ),
				$arrIndex[ "city" ] => $this->RndWord( mt_rand( 5, 15 ), RWO_RULOW | RWO_RUUP ),
				$arrIndex[ "street" ] => $this->RndWord( mt_rand( 5, 15 ), RWO_RULOW | RWO_RUUP ),
				$arrIndex[ "person" ] => $this->RndWord( mt_rand( 5, 15 ), RWO_RULOW | RWO_RUUP ),
				$arrIndex[ "first_name" ] => $this->RndWord( mt_rand( 5, 20 ), RWO_RULOW | RWO_RUUP ),
				$arrIndex[ "last_name" ] => $this->RndWord( mt_rand( 5, 20 ), RWO_RULOW | RWO_RUUP )
			);
			$objRet->Create( $arrData );
			return $objRet;
		} // function Client
		
		public function Server( ) {
			$objRet = new CServer( );
			$arrIndex = $objRet->GetAttributeIndexList( );
			$arrData = array(
			);
			$objRet->Create( $arrData );
			return $objRet;
		} // function Server
		
		public function RndStr( $szSymbols, $iLength ) {
			$iMax = strlen( $szSymbols ) - 1;
			$r = "";
			for( $i = 0; $i < $iLength; ++$i ) {
				$iIndex = mt_rand( 0, $iMax );
				$r .= $szSymbols[ $iIndex ];
			}
			return $r;
		}
		
		public function RndWord( $iLength = 254, $iMode = RWO_NUMBER ) {
			$szSymbols = "";
			if ( $iMode & RWO_NUMBER ) {
				$szSymbols .= "0123456789";
			}
			if ( $iMode & RWO_ENLOW ) {
				$szSymbols .= "abcdefghijklmnopqrstuvwxyz";
			}
			if ( $iMode & RWO_ENUP ) {
				$szSymbols .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			}
			if ( $iMode & RWO_RULOW ) {
				$szSymbols .= "абвгдежзийклмнопрстуфхцчшщъыьэюя";
			}
			if ( $iMode & RWO_RUUP ) {
				$szSymbols .= "АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ";
			}
			if ( $iMode & RWO_PUNCT ) {
				$szSymbols .= ",.?:;\"'(){}[]!@*/\\#%&~`^\$|-+=";
			}
			return $this->RndStr( $szSymbols, $iLength );
		}
		
		public function RndWordList( $iMode = RWO_NUMBER, $iNum = 1, $iMin = 1, $iMax = 20 ) {
			$r = "";
			$tmp = array( );
			for( $i = 0; $i < $iNum; ++$i ) {
				$tmp[ ] = $this->RndWord( mt_rand( $iMin, $iMax ), $iMode );
			}
			$r = join( " ", $tmp );
			return $r;
		}

		public function RndDate( $bTime = true ) {
			$r = "";
			$tmp = array( );
			$tmp[ ] = mt_rand( 2007, 2011 ); // год
			$tmp[ ] = mt_rand( 1, 12 ); // месяц
			$tmp[ ] = mt_rand( 1, 30 ); // день
			$r = join( "-", $tmp );
			if ( $bTime ) {
				$tmp = array( );
				$tmp[ ] = mt_rand( 0, 23 );
				$tmp[ ] = mt_rand( 0, 60 );
				$tmp[ ] = mt_rand( 0, 60 );
				$r .= " ".join( ":", $tmp );
			}
			return $r;
		} // function RndDate
		
		public function RndIp( $iNum = 1, $bIPv4 = true ) {
			$r = "";
			$tmp = array( );
			if ( $bIPv4 ) {
				for( $i = 0; $i < $iNum; ++$i ) {
					$tmp1 = array( );
					for( $j = 0; $j < 4; ++$j ) {
						$tmp1[ ] = mt_rand( 0, 255 );
					}
					$tmp[ ] = join( ".", $tmp1 );
				}
				$r = join( "\r\n", $tmp );
			} else {
				// random IPv6 isnt supported yet
			}
			return $r;
		} // function RndIp
		
		public function RndDomainName( $iPartsNum = 1, $iPartMin = 3, $iPartMax = 10 ) {
			$r = "";
			$tld = $this->RndWord( 2, RWO_ENLOW );
			$tmp = array( );
			for( $i = 0; $i < $iPartsNum; ++$i ) {
				$tmp[ ] = $this->RndWord( mt_rand( $iPartMin, $iPartMax ), RWO_NUMBER | RWO_ENLOW );
			}
			$r = join( ".", $tmp ).".".$tld;
			return $r;
		}
		
		public function RndEmail( $iLoginLen = 20, $iPartsDomain = 1, $iPartMin = 3, $iPartMax = 10 ) {
			return $this->RndLogin( $iLoginLen )."@".$this->RndDomainName( $iPartsDomain, $iPartMin, $iPartMax );
		}
		
		public function RndLogin( $iLength = 20 ) {
			return $this->RndWord( $iLength, RWO_NUMBER | RWO_ENLOW | RWO_ENUP );
		}
		
		public function RndPassword( $iLength = 20 ) {
			return $this->RndWord( $iLength, RWO_NUMBER | RWO_ENLOW | RWO_ENUP );
		}
		
		public function RndPhone( $iCityCode = 3, $iNumber = 6 ) {
			return "+7 ".$this->RndWord( $iCityCode )." ".$this->RndWord( $iNumber );
		}
		
	} // class CRandomGenerator
	
?>