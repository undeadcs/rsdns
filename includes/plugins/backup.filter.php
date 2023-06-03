<?php
	/**
	 *	Фильтр бэкапов
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModBackup
	 */

	class CBackupFilter extends CFlex {
		protected $d1 = ""; // дата
		protected $d2 = ""; // дата
		protected $m = ""; // модуль
		
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
			if ( !empty( $tmp ) ) {
				$r = join( "&", $tmp );
			}
			return $r;
		} // function GetUrl
		
		public function GetWhere( ) {
			$r = "";
			$tmp = new CBackup( );
			$arrIndex = $tmp->GetAttributeIndexList( FLEX_FILTER_FORM );
			$arrWhere = array( );
			$tmp1 = array( );
			if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $this->d1 ) ) {
				$tmp2 = preg_replace( '/(\d{4})-(\d{2})-(\d{2})/', '$1$2${3}000000', $this->d1 );
				$tmp1[ "date1" ] = "number(@cr_date)>=".$tmp2;
			}
			
			if ( preg_match( '/\d{4}-\d{2}-\d{2}/', $this->d2 ) ) {
				$tmp2 = preg_replace( '/(\d{4})-(\d{2})-(\d{2})/', '$1$2${3}000000', $this->d2 );
				$tmp1[ "date2" ] = "number(@cr_date)<=".$tmp2;
			}
			if ( isset( $tmp1[ "date1" ], $tmp1[ "date2" ] ) ) {
				$arrWhere[ ] = "(".join( " and ", $tmp1 ).")";
			} elseif ( isset( $tmp1[ "date1" ] ) ) {
				$arrWhere[ ] = $tmp1[ "date1" ];
			} elseif ( isset( $tmp1[ "date2" ] ) ) {
				$arrWhere[ ] = $tmp1[ "date2" ];
			}
			if ( $this->m !== "" ) {
				if ( $this->m === "db" ) {
					$arrWhere[ ] = "((@components='2') or (@components='3') or (@components='6') or (@components='7'))";
				} elseif ( $this->m === "source" ) {
					$arrWhere[ ] = "((@components='1') or (@components='3') or (@components='5') or (@components='7'))";
				} elseif ( $this->m === "zone" ) {
					$arrWhere[ ] = "((@components='4') or (@components='5') or (@components='6') or (@components='7'))";
				}
			}
			if ( !empty( $arrWhere ) ) {
				$r = ":".join( " and ", $arrWhere );
			}
			return $r;
		} // function GetWhere
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "BackupFilter";
			//
			return $arrConfig;
		} // function GetConfig
		
	} // class CBackupFilter
	
?>