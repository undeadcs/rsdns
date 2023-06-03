<?php
	/**
	 *	Модуль логов
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModLogger
	 */

	require( "logger.log.php" );
	require( "logger.filter.php" );

	/**
	 *	Перехватчик для модуля Logger
	 */
	class CHModLogger extends CHandler {
		private $hLog = NULL;
		
		/**
		 * 	Инициализация обработчиков
		 */
		public function InitHandlers( ) {
			global $objCMS;
			$this->hLog = new CHLog( );
			$this->hLog->Create( array( "database" => $objCMS->database ) );
			$this->hLog->CheckTable( array( FHOV_TABLE => "ud_log", FHOV_OBJECT => "CLog" ) );
		} // function InitHandlers
		
		/**
		 *	Проверка на срабатывание (перехват)
		 *	@param $szQuery string строка тестирования
		 *	@return bool
		 */
		public function Test( $szQuery ) {
			return ( preg_match( '/^\/logger\//', $szQuery ) ? true : false );
		} // function Test
		
		/**
		 *	Обработка
		 *	@param $szQuery string строка, на которой произошел перехват
		 *	@return bool
		 */
		public function Process( $szQuery ) {
			global $objCMS, $objCurrent, $mxdCurrentData, $szCurrentMode, $arrErrors;
			// выставляем текущий модуль
			$this->InitHandlers( );
			$objCMS->SetWGI( WGI_LOGGER );
			$objCMS->SetWGIState( MF_THIS );
			$objCurrent = "Logger";
			$szCurrentMode = "List";
			
			$objLog = new CLog( );
			
			if ( preg_match( '/^\/logger\/\d*\//', $szQuery ) ) {
				$tmp = NULL;
				preg_match( '/^\/logger\/(\d*)\//', $szQuery, $tmp );
				$iId = intval( $tmp[ 1 ] );
				
				$szIdIndex = $objLog->GetAttributeIndex( "id", NULL, FLEX_FILTER_DATABASE );
				$tmp = $this->hLog->GetObject( array(
					FHOV_WHERE => "`".$szIdIndex."`=".$iId,
					FHOV_TABLE => "ud_log",
					FHOV_INDEXATTR => "id",
					FHOV_OBJECT => "CLog"
				) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$mxdCurrentData[ "current_log" ] = current( $tmp );
					$szCurrentMode = "View";
					$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
				}
			} elseif ( count( $_POST ) && isset( $_POST[ "del" ] ) ) {
				$arrData = $_POST[ "del" ];
				$arrIds = array( );
				foreach( $arrData as $i => $v ) {
					if ( is_int( $i ) ) {
						$arrIds[ $i ] = $i;
					}
				}
				$szIdIndex = $objLog->GetAttributeIndex( "id", NULL, FLEX_FILTER_DATABASE );
				if ( !empty( $arrIds ) ) {
					$tmp = $this->hLog->GetObject( array(
						FHOV_WHERE => "`".$szIdIndex."` IN(".join( ",", $arrIds ).")",
						FHOV_TABLE => "ud_log", FHOV_INDEXATTR => "id", FHOV_OBJECT => "CLog"
					) );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( );
						/*$arrIds = array( );
						foreach( $tmp as $v ) {
							$arrIds[ $v->graph_vertex_id ] = $v->graph_vertex_id;
						}*/
						$this->hLog->DelObject( $tmp, array( FHOV_TABLE => "ud_log" ) );
						//$objCMS->DelFromWorld( $arrIds );
					}
				}
				Redirect( $objCMS->GetPath( "root_relative" )."/logger/" );
			}
			
			if ( $mxdCurrentData === NULL ) {
				$mxdCurrentData[ "log_list" ] = array( );
				$szCrDateIndex = $objLog->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE );
				$arrOptions = array(
					FHOV_ORDER => "`".$szCrDateIndex."` DESC",
					FHOV_TABLE => "ud_log", FHOV_INDEXATTR => "id", FHOV_OBJECT => "CLog"
				);
				//
				$objFilter = new CLogFilter( );
				$objFilter->Create( $_GET, FLEX_FILTER_FORM );
				$szWhere = $objFilter->GetWhere( );
				if ( $szWhere !== "" ) {
					$arrOptions[ FHOV_WHERE ] = $szWhere;
				}
				$mxdCurrentData[ "filter" ] = $objFilter;
				$szUrl = $objFilter->GetUrlAttr( );
				if ( $szUrl === "" ) {
					$szUrl = $objCMS->GetPath( "root_relative" )."/logger/?";
				} else {
					$szUrl = $objCMS->GetPath( "root_relative" )."/logger/?".$szUrl."&";
				}
				//
				$iCount = $this->hLog->CountObject( $arrOptions );
				$iCount = $iCount->GetResult( "count" );
				$objPager = new CPager( );
				$arrData = array(
					"url" => $szUrl,
					"page" => @$_GET[ "page" ],
					"page_size" => 15,
					"total" => $iCount
				);
				$objPager->Create( $arrData, FLEX_FILTER_FORM );
				$szLimit = $objPager->GetSQLLimit( );
				if ( $szLimit !== "" ) {
					$arrOptions[ FHOV_LIMIT ] = $szLimit;
				}
				//
				$tmp = $this->hLog->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					$mxdCurrentData[ "log_list" ] = $tmp->GetResult( );
					$mxdCurrentData[ "pager" ] = $objPager;
				}
			}
			
			// передаем управление приложению
			$szFolder = $objCMS->GetPath( "root_application" );
			if ( $szFolder !== false && file_exists( $szFolder."/index.php" ) ) {
				include_once( $szFolder."/index.php" );
			}
			return true;
		} // function Process
		
		/**
		 * 	Добавление лога
		 */
		public function AddLog( $szUser, $szModule, $szAction, $szComment ) {
			global $objCMS;
			if ( $this->hLog === NULL ) {
				$this->InitHandlers( );
			}
			$objRet = new CResult( );
			$objLog = new CLog( );
			$arrIndex = array(
				"user" => $objLog->GetAttributeIndex( "user" ),
				"module" => $objLog->GetAttributeIndex( "module" ),
				"action" => $objLog->GetAttributeIndex( "action" ),
				"cr_date" => $objLog->GetAttributeIndex( "cr_date" ),
				"ip_address" => $objLog->GetAttributeIndex( "ip_address" ),
				"comment" => $objLog->GetAttributeIndex( "comment" )
			);
			$arrInput = array(
				$arrIndex[ "user" ] => $szUser,
				$arrIndex[ "module" ] => $szModule,
				$arrIndex[ "action" ] => $szAction,
				$arrIndex[ "cr_date" ] => date( "Y-m-d H:i:s" ),
				$arrIndex[ "ip_address" ] => @$_SERVER[ "REMOTE_ADDR" ],
				$arrIndex[ "comment" ] => $szComment,
			);
			$objLog->Create( $arrInput );
			/*$tmp = $objCMS->AddToWorld( WGI_LOGGER, "ModLogger/Log" );
			if ( $tmp->HasResult( ) ) {
				$iGVId = $tmp->GetResult( "graph_vertex_id" );
				$szGVIdIndex = $objLog->GetAttributeIndex( "graph_vertex_id" );
				$objLog->Create( array( $szGVIdIndex => $iGVId ) );*/
				$tmp = $this->hLog->AddObject( array( $objLog ), array( FHOV_TABLE => "ud_log" ) );
				if ( $tmp->HasError( ) ) {
					$objRet->AddError( $tmp );
				}
			//}
			return $objRet;
		} // function AddLog
		
		/**
		 * 	Удаление логов
		 */
		public function DelLog( $arrIds ) {
			global $objCMS;
			if ( $this->hLog === NULL ) {
				$this->InitHandlers( );
			}
		} // function DelLog
		
	} // class CHModLogger
?>