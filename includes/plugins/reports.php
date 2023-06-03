<?php
	/**
	 *	Отчеты
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModReport
	 */

	require( "reports.report.php" );
	require( "reports.queries.php" );
	require( "reports.filter.php" );
	require( "reports.aggregate.php" );
	
	/**
	 * 	Модуль отчетов
	 */
	class CHModReport extends CHandler {
		protected $hCommon = NULL;
		
		/**
		 * 	Инициализация обработчиокв
		 */
		public function InitObjectHandler( ) {
			global $objCMS;
			$this->hCommon = new CFlexHandler( );
			$this->hCommon->Create( array( "database" => $objCMS->database ) );
			$this->hCommon->CheckTable( array( FHOV_TABLE => "ud_report", FHOV_OBJECT => "CReport" ) );
			$this->hCommon->CheckTable( array( FHOV_TABLE => "ud_queries", FHOV_OBJECT => "CQueries" ) );
		} // function InitHandlers
		
		/**
		 *	Проверка на срабатывание (перехват)
		 *	@param $szQuery string строка тестирования
		 *	@return bool
		 */
		public function Test( $szQuery ) {
			return ( preg_match( '/^\/reports\//', $szQuery ) ? true : false );
		} // function Test
		
		/**
		 *	Обработка
		 *	@param $szQuery string строка, на которой произошел перехват
		 *	@return bool
		 */
		public function Process( $szQuery ) {
			global $g_arrConfig, $objCMS, $objCurrent, $iCurrentSysRank, $mxdCurrentData, $szCurrentMode, $arrErrors, $mxdLinks;
			//
			$objCMS->SetWGI( WGI_REPORTS );
			$objCMS->SetWGIState( MF_THIS );
			$objCurrent = "Report";
			$szCurrentMode = "List";
			$this->InitObjectHandler( );
			$arrErrors = array( );
			$iCurrentSysRank = $objCMS->GetUserRank( );
			$mxdCurrentData = array( );
			//
			if ( preg_match( '/^\/reports\/servers\//', $szQuery ) ) {
				$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
				$szCurrentMode = "Servers";
				$arrOptions = array( FHOV_LIMIT => "1", FHOV_TABLE => "ud_queries", FHOV_OBJECT => "CQueries" );
				$objFilter = new CQueriesFilter( );
				$objFilter->Create( $_GET, FLEX_FILTER_FORM );
				$szWhere = $objFilter->GetWhere( );
				if ( $szWhere == "" ) {
					$objFilter->Create( array( "d1" => date( "Y-m-d" ) ) );
					$szWhere = $objFilter->GetWhere( );
				}
				$mxdCurrentData[ "url_for_ip" ] = $objFilter->GetUrlAttr( );
				$arrOptions[ FHOV_WHERE ] = $szWhere;
				$mxdCurrentData[ "filter" ] = $objFilter;
				$tmp = $this->hCommon->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$mxdCurrentData[ "current_queries" ] = current( $tmp );
				}
				//
			} elseif ( preg_match( '/^\/reports\/domains\//', $szQuery ) ) {
				$szCurrentMode = "Domains";
				$arrOptions = array( FHOV_TABLE => "ud_qdomains", FHOV_OBJECT => "CQDomain", FHOV_INDEXATTR => "id" );
				$tmp = new CQDomain( );
				$objFilter = new CQueriesFilter2( );
				$objFilter->Create( $_GET, FLEX_FILTER_FORM );
				$szWhere = $objFilter->GetWhere( );
				if ( $szWhere == "" ) {
					$objFilter->Create( array(
						"d1" => date( "Y-m-d" ),
						"h1" => 0,
						"h2" => 24
					) );
					$szWhere = $objFilter->GetWhere( );
				}
				$arrOptions[ FHOV_WHERE ] = $szWhere;
				$mxdCurrentData[ "url_for_ip" ] = $objFilter->GetUrlAttr( );
				$mxdCurrentData[ "filter" ] = $objFilter;
				//
				$tmp = $this->hCommon->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					$arrHours = $tmp->GetResult( );
					$arrKeys = array_keys( $arrHours );
					$objCount = new CQueryItemCount( );
					$arrIndex = $objCount->GetAttributeIndexList( FLEX_FILTER_DATABASE );
					$arrOptions = array(
						FHOV_WHERE => "`".$arrIndex[ "label" ]."`='Time/Domain' AND `".$arrIndex[ "qdomain_id" ]."` IN(".join( ",", $arrKeys ).")",
						FHOV_ORDER => "`".$arrIndex[ "count" ]."` DESC",
						FHOV_TABLE => "ud_qcount",
						FHOV_OBJECT => "CQueryItemCount",
						FHOV_INDEXATTR => "id"
					);
					$tmp = $this->hCommon->GetObject( $arrOptions );
					if ( $tmp->HasResult( ) ) {
						$arrCounts = $tmp->GetResult( );
						$arrDomainIds = array( );
						$arrDomainToIdCount = array( );
						foreach( $arrCounts as $v ) {
							if ( !isset( $arrDomainIds[ $v->qitem_id ] ) ) {
								$arrDomainIds[ $v->qitem_id ] = 0.0;
							}
							$arrDomainIds[ $v->qitem_id ] += $v->count;
							if ( !isset( $arrDomainToIdCount[ $v->qitem_id ] ) ) {
								$arrDomainToIdCount[ $v->qitem_id ] = array( );
							}
							$arrDomainToIdCount[ $v->qitem_id ][ ] = $v->id;
						}
						arsort( $arrDomainIds );
						$tmp = new CQueryItem( );
						$arrIndex = $tmp->GetAttributeIndexList( FLEX_FILTER_DATABASE );
						$arrKeys = array_keys( $arrDomainIds );
						$tmp = $this->hCommon->GetObject( array(
							FHOV_WHERE => "`".$arrIndex[ "type" ]."`=".QIT_DOMAIN." AND `".$arrIndex[ "id" ]."` IN(".join( ",", $arrKeys ).")",
							FHOV_TABLE => "ud_qitem", FHOV_OBJECT => "CQueryItem", FHOV_INDEXATTR => "id"
						) );
						// домены к которым делались запросы
						$arrDomains = $tmp->GetResult( );
							
						if ( preg_match( '/^\/reports\/domains\/[0-9]*\//', $szQuery ) ) {
							$tmp = NULL;
							preg_match( '/^\/reports\/domains\/([0-9]*)\//', $szQuery, $tmp );
							$szSelectedDomain = urldecode( $tmp[ 1 ] );
							$szSelectedDomainId = 0;
							$iDomain = 0;
							foreach( $arrDomains as $v ) {
								if ( $v->id == $szSelectedDomain ) {
									$iDomain = $v->id;
									$szSelectedDomain = $v->value;
									$szSelectedDomainId = $iDomain;
									break;
								}
							}
							$arrCurCounts = $arrDomainToIdCount[ $iDomain ];
							$objCount = new CQueryItemCount( );
							$arrIndex = $objCount->GetAttributeIndexList( FLEX_FILTER_DATABASE );
							// выберем счетчики для IP
							$arrOptions = array(
								FHOV_WHERE => "`".$arrIndex[ "label" ]."`='Domain/Ip' AND `".$arrIndex[ "qdomain_id" ]."` IN(".join( ",", $arrCurCounts ).")",
								FHOV_ORDER => "`".$arrIndex[ "count" ]."` DESC",
								FHOV_TABLE => "ud_qcount",
								FHOV_OBJECT => "CQueryItemCount",
								FHOV_INDEXATTR => "id"
							);
							$tmp = $this->hCommon->GetObject( $arrOptions );
							$arrCounts = $tmp->GetResult( );
							$arrIps = array( );
							foreach( $arrCounts as $i => $v ) {
								if ( !isset( $arrIps[ $v->qitem_id ] ) ) {
									$arrIps[ $v->qitem_id ] = 0.0; 
								}
								$arrIps[ $v->qitem_id ] += $v->count;
							}
							arsort( $arrIps );
							$arrKeys = array_keys( $arrIps );
							$tmp = new CQueryItem( );
							$arrIndex = $tmp->GetAttributeIndexList( FLEX_FILTER_DATABASE );
							$tmp = $this->hCommon->GetObject( array(
								FHOV_WHERE => "`".$arrIndex[ "type" ]."`=".QIT_IP."  AND `".$arrIndex[ "id" ]."` IN(".join( ",", $arrKeys ).")",
								FHOV_TABLE => "ud_qitem", FHOV_OBJECT => "CQueryItem", FHOV_INDEXATTR => "id"
							) );
							$arrDomains = $tmp->GetResult( );
							$arrIps = array_slice( $arrIps, 0, 50, true );
							$tmp = array( );
							foreach( $arrIps as $i => $v ) {
								$fCount = $v;
								$szDomain = $arrDomains[ $i ]->value;
								$tmp1 = new CCountQueryByDomain( );
								$tmp1->Create( array(
									"domain" => $szDomain,
									"count" => $fCount
								) );
								$tmp[ ] = $tmp1;
							}
							//ShowVarD( $szSelectedDomain, $tmp );
							if ( strlen( $szSelectedDomain ) > 49 ) {
								//$szSelectedDomain = wordwrap( $szSelectedDomain, 49, "\r\n", true );
							}
							$mxdCurrentData[ "query_ip_domain" ] = $tmp;
							$mxdCurrentData[ "selected_domain" ] = $szSelectedDomain;
							$mxdCurrentData[ "selected_domain_id" ] = $szSelectedDomainId;
							$szCurrentMode = "DomainView";
							//
						} else {
							$arrDomainIds = array_slice( $arrDomainIds, 0, 50, true );
							$tmp = array( );
							//ShowVarD( $arrDomains );
							foreach( $arrDomainIds as $i => $v ) {
								$fCount = $v;
								$szDomain = $arrDomains[ $i ]->value;
								$tmp1 = new CCountQueryByDomain( );
								$tmp1->Create( array(
									"id" => $arrDomains[ $i ]->id,
									"domain" => $szDomain,
									"count" => $fCount
								) );
								$tmp[ ] = $tmp1;
							}
							$mxdCurrentData[ "query_domain" ] = $tmp;
						}
					}
				}
				//
			} else {
				$arrOptions = array( FHOV_LIMIT => "1", FHOV_TABLE => "ud_report", FHOV_OBJECT => "CReport" );
				$objFilter = new CReportFilter( );
				$objFilter->Create( $_GET, FLEX_FILTER_FORM );
				$szWhere = $objFilter->GetWhere( );
				if ( $szWhere == "" ) {
					$objFilter->Create( array( "d1" => date( "Y-m-d" ) ) );
					$szWhere = $objFilter->GetWhere( );
				}
				$arrOptions[ FHOV_WHERE ] = $szWhere;
				$mxdCurrentData[ "filter" ] = $objFilter;
				$tmp = $this->hCommon->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$mxdCurrentData[ "current_report" ] = current( $tmp );
					unset( $tmp );
				}
			}
			//
			$szFolder = $objCMS->GetPath( "root_application" );
			if ( $szFolder !== false && file_exists( $szFolder."/index.php" ) ) {
				include_once( $szFolder."/index.php" );
			}
			
			return true;
		} // function Process
		
	} // class CHModReport
?>