<?php
	/**
	 *	Фильтр пользователей
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModUser
	 */

	/**
	 * 	Фильтр для клиентов
	 */
	class CClientFilter extends CFlex {
		protected $d1 = ""; // начальная дата
		protected $d2 = ""; // конечная дата
		protected $st = 0; // состояние
		protected $kw = ""; // ключевые слова
		
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
			if ( $this->st ) {
				$tmp[ ] = "st=".$this->FilterAttr( "st", $arrConfig, FLEX_FILTER_FORM );
			}
			if ( $this->kw !== "" ) {
				$tmp[ ] = "kw=".urlencode( $this->FilterAttr( "kw", $arrConfig, FLEX_FILTER_FORM ) );
			}
			if ( !empty( $tmp ) ) {
				$r = join( "&", $tmp );
			}
			return $r;
		} // function GetUrl
		
		public function GetWhere( ) {
			$r = "";
			$objClient = new CClient( );
			$arrIndex = array(
				"reg_date" => $objClient->GetAttributeIndex( "reg_date", NULL, FLEX_FILTER_FORM ),
				"state" => $objClient->GetAttributeIndex( "state", NULL, FLEX_FILTER_FORM ),
			);
			$arrWhere = array( );
			$tmp1 = array( );
			if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $this->d1 ) ) {
				$objClient->Create( array( $arrIndex[ "reg_date" ] => $this->d1 ), FLEX_FILTER_FORM );
				$tmp1[ "date1" ] = "`".$objClient->GetAttributeIndex( "reg_date", NULL, FLEX_FILTER_DATABASE )."` >= ".$objClient->GetAttributeValue( "reg_date", FLEX_FILTER_DATABASE );
			}
			
			if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $this->d2 ) ) {
				$objClient->Create( array( $arrIndex[ "reg_date" ] => $this->d2 ), FLEX_FILTER_FORM );
				$tmp1[ "date2" ] = "`".$objClient->GetAttributeIndex( "reg_date", NULL, FLEX_FILTER_DATABASE )."` <= ".$objClient->GetAttributeValue( "reg_date", FLEX_FILTER_DATABASE );
			}
			if ( isset( $tmp1[ "date1" ], $tmp1[ "date2" ] ) ) {
				$arrWhere[ ] = "(".join( " AND ", $tmp1 ).")";
			} elseif ( isset( $tmp1[ "date1" ] ) ) {
				$arrWhere[ ] = $tmp1[ "date1" ];
			} elseif ( isset( $tmp1[ "date2" ] ) ) {
				$arrWhere[ ] = $tmp1[ "date2" ];
			}
			if ( $this->st ) {
				$objClient->Create( array( $arrIndex[ "state" ] => $this->st ), FLEX_FILTER_FORM );
				if ( $objClient->state ) {
					$arrWhere[ ] = "`".$objClient->GetAttributeIndex( "state", NULL, FLEX_FILTER_DATABASE )."`=".$objClient->GetAttributeValue( "state", FLEX_FILTER_DATABASE );
				}
			}
			if ( $this->kw !== "" ) {
				$tmp = urldecode( $this->kw );
				$tmp = explode( " ", $tmp );
				$arrIndex = $objClient->GetAttributeIndexList( FLEX_FILTER_FORM );
				$fltArray = new CArrayFilter( );
				$arrAttrKw = array(
					"id", "graph_vertex_id", "reg_date", "last_edit", "last_login", "state", "zones"
					/*"login", "password", "add_info", "ip_block", "email", "full_name",
					"full_name_en", "inn", "kpp", "country", "phone", "fax", "addr", "addr_p"*/
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
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "ClientFilter";
			//
			$arrConfig[ "kw" ][ FLEX_CONFIG_LENGHT ] = 40;
			return $arrConfig;
		} // function GetConfig
		
	} // class CClientFilter
	
?>