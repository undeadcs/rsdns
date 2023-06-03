<?php
	/**
	 *	Фильтр логов
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModLogger
	 */

	class CLogFilter extends CFlex {
		protected $d1 = ""; // дата
		protected $d2 = ""; // дата
		protected $m = ""; // модуль
		protected $u = ""; // пользователь
		protected $ip = ""; // ip адрес
		
		public function GetUrlAttr( ) {
			$r = "";
			$tmp = array( );
			$arrConfig = $this->GetConfig( );
			if ( $this->d1 !== "" ) {
				$tmp[ ] = "d1=".$this->FilterAttr( "d1", $arrConfig, FLEX_FILTER_FORM );
			}
			if ( $this->d2 !== "" ) {
				$tmp[ ] = "d2=".$this->FilterAttr( "d2", $arrConfig, FLEX_FILTER_FORM );
			}
			if ( $this->m ) {
				$tmp[ ] = "m=".$this->FilterAttr( "m", $arrConfig, FLEX_FILTER_FORM );
			}
			if ( $this->u !== "" ) {
				$tmp[ ] = "u=".urlencode( $this->FilterAttr( "u", $arrConfig, FLEX_FILTER_FORM ) );
			}
			if ( $this->ip !== "" ) {
				$tmp[ ] = "ip=".urlencode( $this->FilterAttr( "ip", $arrConfig, FLEX_FILTER_FORM ) );
			}
			if ( !empty( $tmp ) ) {
				$r = join( "&", $tmp );
			}
			return $r;
		} // function GetUrl
		
		public function GetWhere( ) {
			$r = "";
			$objLog = new CLog( );
			$arrIndex = $objLog->GetAttributeIndexList( FLEX_FILTER_FORM );
			$arrWhere = array( );
			$tmp1 = array( );
			if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $this->d1 ) ) {
				$objLog->Create( array( $arrIndex[ "cr_date" ] => $this->d1 ), FLEX_FILTER_FORM );
				$tmp1[ "date1" ] = "`".$objLog->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE )."` >= ".$objLog->GetAttributeValue( "cr_date", FLEX_FILTER_DATABASE );
			}
			
			if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $this->d2 ) ) {
				$objLog->Create( array( $arrIndex[ "cr_date" ] => $this->d2 ), FLEX_FILTER_FORM );
				$tmp1[ "date2" ] = "`".$objLog->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE )."` <= ".$objLog->GetAttributeValue( "cr_date", FLEX_FILTER_DATABASE );
			}
			if ( isset( $tmp1[ "date1" ], $tmp1[ "date2" ] ) ) {
				$arrWhere[ ] = "(".join( " AND ", $tmp1 ).")";
			} elseif ( isset( $tmp1[ "date1" ] ) ) {
				$arrWhere[ ] = $tmp1[ "date1" ];
			} elseif ( isset( $tmp1[ "date2" ] ) ) {
				$arrWhere[ ] = $tmp1[ "date2" ];
			}
			if ( $this->m !== "" ) {
				$objLog->Create( array( $arrIndex[ "module" ] => $this->m ), FLEX_FILTER_FORM );
				$arrWhere[ ] = "`".$objLog->GetAttributeIndex( "module", NULL, FLEX_FILTER_DATABASE )."`=".$objLog->GetAttributeValue( "module", FLEX_FILTER_DATABASE );
			}
			if ( $this->u !== "" ) {
				$tmp = urldecode( $this->u );
				$arrWhere[ ] = "`".$objLog->GetAttributeIndex( "user", NULL, FLEX_FILTER_DATABASE )."` LIKE '%".@mysql_real_escape_string( $tmp )."%'";
			}
			if ( $this->ip !== "" ) {
				$tmp = urldecode( $this->ip );
				$arrWhere[ ] = "`".$objLog->GetAttributeIndex( "ip_address", NULL, FLEX_FILTER_DATABASE )."` LIKE '%".@mysql_real_escape_string( $tmp )."%'";
			}
			if ( !empty( $arrWhere ) ) {
				$r = join( " AND ", $arrWhere );
			}
			return $r;
		} // function GetWhere
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "LogFilter";
			//
			return $arrConfig;
		} // function GetConfig
		
	} // class CLogFilter
	
?>