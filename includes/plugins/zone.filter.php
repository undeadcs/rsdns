<?php
	/**
	 *	Модуль файлов зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	/**
	 * 	Фильтр для зон
	 */
	class CZoneFilter extends CFlex {
		protected $s = ""; // начальная дата
		
		public function GetUrlAttr( ) {
			$r = "";
			$tmp = array( );
			$arrConfig = $this->GetConfig( );
			if ( $this->s !== "" ) {
				$tmp[ ] = "s=".urlencode( $this->FilterAttr( "s", $arrConfig, FLEX_FILTER_FORM ) );
			}
			if ( !empty( $tmp ) ) {
				$r = join( "&", $tmp );
			}
			return $r;
		} // function GetUrl
		
		public function GetWhere( ) {
			$r = "";
			$arrWhere = array( );
			$tmp1 = array( );
			if ( $this->s !== "" ) {
				$objFileZone = new CFileZone( );
				$tmp = urldecode( $this->s );
				$tmp = explode( " ", $tmp );
				$arrIndex = $objFileZone->GetAttributeIndexList( FLEX_FILTER_FORM );
				$fltArray = new CArrayFilter( );
				$arrAttrKw = array(
					"id", "graph_vertex_id", "type", "state", "default_ttl", "comment", "reg_date", "last_edit", "rrs"
				);
				$fltArray->SetArray( $arrAttrKw );
				$arrIndex = $fltArray->Apply( $arrIndex );
				$arrInput = array( );
				foreach( $arrIndex as $i => $v ) {
					$tmp1 = array( );
					foreach( $tmp as $j => $w ) {
						$tmp1[ ] = "`".$v."` LIKE '%".@mysql_real_escape_string( $w )."%'";
					}
					$arrInput[ ] = join( " OR ", $tmp1 );
				}
				$arrWhere[ ] = "(".join( " OR ", $arrInput ).")";
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
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "ZoneFilter";
			//
			$arrConfig[ "s" ][ FLEX_CONFIG_LENGHT ] = 40;
			return $arrConfig;
		} // function GetConfig
		
	} // class CZoneFilter
	
?>