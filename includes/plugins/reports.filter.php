<?php
	/**
	 *	Фильтр отчетов
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModReport
	 */
	
	/**
	 * 	Фильтр для отчетов
	 */
	class CReportFilter extends CFlex {
		protected $d1 = ""; // начальная дата
		
		public function GetUrlAttr( ) {
			$r = "";
			$tmp = array( );
			$arrConfig = $this->GetConfig( );
			if ( $this->d1 !== "" ) {
				$tmp[ ] = "d1=".$this->FilterAttr( "d1", $arrConfig, FLEX_FILTER_FORM );
			}
			if ( !empty( $tmp ) ) {
				$r = join( "&", $tmp );
			}
			return $r;
		} // function GetUrl
		
		public function GetWhere( ) {
			$r = "";
			$objReport = new CReport( );
			if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $this->d1 ) ) {
				$objReport->Create( array( $objReport->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE ) => $this->d1 ), FLEX_FILTER_FORM );
				$r = "`".$objReport->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE )."`=".$objReport->GetAttributeValue( "cr_date", FLEX_FILTER_DATABASE );
			}
			
			return $r;
		} // function GetWhere
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "ReportFilter";
			//
			return $arrConfig;
		} // function GetConfig
		
	} // class CReportFilter
	
	/**
	 * 	Фильтр для отчетов
	 */
	class CQueriesFilter extends CFlex {
		protected $d1 = ""; // начальная дата
		
		public function GetUrlAttr( ) {
			$r = "";
			$tmp = array( );
			$arrConfig = $this->GetConfig( );
			if ( $this->d1 !== "" ) {
				$tmp[ ] = "d1=".$this->FilterAttr( "d1", $arrConfig, FLEX_FILTER_FORM );
			}
			if ( !empty( $tmp ) ) {
				$r = join( "&", $tmp );
			}
			return $r;
		} // function GetUrl
		
		public function GetWhere( ) {
			$r = "";
			$objReport = new CQueries( );
			if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $this->d1 ) ) {
				$objReport->Create( array( $objReport->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE ) => $this->d1 ), FLEX_FILTER_FORM );
				$r = "`".$objReport->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE )."`=".$objReport->GetAttributeValue( "cr_date", FLEX_FILTER_DATABASE );
			}
			
			return $r;
		} // function GetWhere
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "QueriesFilter";
			//
			return $arrConfig;
		} // function GetConfig
		
	} // class CReportFilter
	
	/**
	 * 	Фильтр для отчетов
	 */
	class CQueriesFilter2 extends CFlex {
		protected $d1 = ""; // начальная дата
		protected $h1 = ""; // начальный час
		protected $h2 = ""; // конечный час
		
		/**
		 *	Получение XML экземпляра
		 *	@param $domDoc DOMDocument экземпляр данного класса
		 *	@return CResult
		 */
		public function GetXML( &$domDoc ) {
			$objRet = parent::GetXML( $domDoc );
			$tmp = $objRet->GetResult( "doc" );
			//
			for( $i = 1; $i < 3; ++$i ) {
				$szName = "sel".$i;
				$szAttr = "h".$i;
				$tmp1 = $domDoc->createElement( $szName );
				for( $j = 0; $j < 25; ++$j ) {
					$tmp2 = $domDoc->createElement( "h" );
					$szHour = ( $j < 10 ? "0" : "" ).$j;
					$tmp2->setAttribute( "title", $szHour.":00" );
					$tmp2->setAttribute( "value", $j );
					if ( $j == intval( $this->$szAttr ) ) {
						$tmp2->setAttribute( "selected", true );
					}
					$tmp1->appendChild( $tmp2 );
				}
				$tmp->appendChild( $tmp1 );
			}
			//
			$objRet->AddResult( $tmp, "doc" );
			return $objRet;
		} // function GetXML
		
		public function GetUrlAttr( ) {
			$r = "";
			$tmp = array( );
			$arrConfig = $this->GetConfig( );
			if ( $this->d1 !== "" ) {
				$tmp[ ] = "d1=".$this->FilterAttr( "d1", $arrConfig, FLEX_FILTER_FORM );
			}
			if ( $this->h1 !== "" ) {
				$tmp[ ] = "h1=".$this->FilterAttr( "h1", $arrConfig, FLEX_FILTER_FORM );
			}
			if ( $this->h2 !== "" ) {
				$tmp[ ] = "h2=".$this->FilterAttr( "h2", $arrConfig, FLEX_FILTER_FORM );
			}
			if ( !empty( $tmp ) ) {
				$r = join( "&", $tmp );
			}
			return $r;
		} // function GetUrl
		
		public function GetWhere( $szClass = "CQDomain" ) {
			$r = "";
			$tmp1 = array( );
			$tmp = new $szClass( );
			if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $this->d1 ) ) {
				$tmp->Create( array( $tmp->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE ) => $this->d1 ), FLEX_FILTER_FORM );
				$tmp1[ ] = "`".$tmp->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE )."`=".$tmp->GetAttributeValue( "cr_date", FLEX_FILTER_DATABASE );
			}
			$tmp2 = array( );
			if ( $this->h1 !== "" && intval( $this->h1 ) ) {
				$tmp1[ ] = "`".$tmp->GetAttributeIndex( "hour", NULL, FLEX_FILTER_DATABASE )."`>=".$this->h1;
				//$tmp2[ 0 ] = "`".$tmp->GetAttributeIndex( "hour", NULL, FLEX_FILTER_DATABASE )."`>=".$this->h1;
			}
			if ( $this->h2 !== "" && intval( $this->h2 ) ) {
				$tmp1[ ] = "`".$tmp->GetAttributeIndex( "hour", NULL, FLEX_FILTER_DATABASE )."`<".$this->h2;
				//$tmp2[ 1 ] = "`".$tmp->GetAttributeIndex( "hour", NULL, FLEX_FILTER_DATABASE )."`<".$this->h2;
			}
			/*
			if ( count( $tmp2 ) == 2 ) {
				$tmp1[ ] = "(".join( " OR ", $tmp2 ).")";
			} else {
				$tmp2 = current( $tmp2 );
				$tmp1[ ] = $tmp2;
			}
			//*/
			if ( !empty( $tmp1 ) ) {
				$r = join( " AND ", $tmp1 );
			}
			//
			return $r;
		} // function GetWhere
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "QueriesFilter2";
			//
			return $arrConfig;
		} // function GetConfig
		
	} // class CReportFilter
	
?>