<?php
	/**
	 *	Модуль серверов
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModLink
	 */

	require( "link.conf.php" );
	require( "link.server.php" );
	
	/**
	 *	Перехватчик для модуля Link
	 */
	class CHModLink extends CHandler {
		private $hCommon = NULL;
		private $hServer = NULL;
		
		/**
		 * 	Инициализация обработчиков
		 * 	@return void
		 */
		public function InitHandlers( ) {
			global $objCMS;
			$this->hServer = new CHServer( );
			$this->hServer->Create( array( 'database' => $objCMS->database ) );
			$this->hServer->CheckTable( array( FHOV_TABLE => 'ud_server', FHOV_OBJECT => 'CServer' ) );
			$this->hCommon = new CFlexHandler( );
			$this->hCommon->Create( array( 'database' => $objCMS->database ) );
		} // function InitHandlers
		
		/**
		*	Проверка на срабатывание (перехват)
		*	@param $szQuery string строка тестирования
		*	@return bool
		*/
		public function Test( $szQuery ) {
			return ( preg_match( '/^\/link\//', $szQuery ) ? true : false );
		} // function Test
		
		/**
		*	Обработка
		*	@param $szQuery string строка, на которой произошел перехват
		*	@return bool
		*/
		public function Process( $szQuery ) {
			global $objCMS, $objCurrent, $mxdCurrentData, $szCurrentMode, $arrErrors;
			$this->InitHandlers( );
			$objCMS->SetWGI( WGI_LINK );
			$objCMS->SetWGIState( MF_THIS );
			$objCurrent = "Link";
			$szCurrentMode = "List";
			$arrErrors = array( );
			$mxdCurrentData = array(
				"server_list" => array( ),
				"current_server" => NULL
			);
			
			if ( preg_match( '/^\/link\/\+\//', $szQuery ) ) {
				$mxdCurrentData[ "current_server" ] = new CServer( );
				$arrIndex = array(
					FLEX_FILTER_PHP => $mxdCurrentData[ "current_server" ]->GetAttributeIndexList( ),
					FLEX_FILTER_DATABASE => $mxdCurrentData[ "current_server" ]->GetAttributeIndexList( FLEX_FILTER_DATABASE )
				);
				if ( count( $_POST ) ) {
					$arrData = $_POST;
					$tmp = $mxdCurrentData[ "current_server" ]->Create( $arrData, FLEX_FILTER_FORM );
					if ( $tmp->HasError( ) ) {
						$arrErrors = $tmp->GetError( );
					} else {
						// проверим на существование в системе
						$szNameValue = $mxdCurrentData[ "current_server" ]->GetAttributeValue( "name", FLEX_FILTER_DATABASE );
						$tmp1 = array( FHOV_WHERE => "`".$arrIndex[ FLEX_FILTER_DATABASE ][ "name" ]."=".$szNameValue, FHOV_TABLE => "ud_server", FHOV_OBJECT => "CServer" );
						$tmp = $this->hServer->GetObject( $tmp1 );
						if ( $tmp->HasResult( ) ) {
							$arrErrors[ ] = new CError( 1, "Сервер уже существует" );
						} else {
							$tmp1 = $objCMS->AddToWorld( WGI_LINK, "ModLink/Server" );
							if ( $tmp1->HasResult( ) ) {
								$iServerVId = $tmp1->GetResult( "graph_vertex_id" );
								$mxdCurrentData[ "current_server" ]->Create( array( $arrIndex[ FLEX_FILTER_PHP ][ "graph_vertex_id" ] => $iServerVId ) );
								$tmp = $this->hServer->AddObject( array( $mxdCurrentData[ "current_server" ] ), array( FHOV_TABLE => "ud_server" ) );
								if ( $tmp->HasError( ) ) {
									$objCMS->DelFromWorld( array( $iServerVId ) );
									$arrErrors = $tmp->GetError( );
								} else {
									$modLogger = new CHModLogger( );
									$modLogger->AddLog(
										$objCMS->GetUserLogin( ),
										"ModLink",
										"ModLink::AddServer",
										"added server to system, name: ".$mxdCurrentData[ "current_server" ]->name
									);
									Redirect( $objCMS->GetPath( "root_relative" )."/link/" );
								}
							}
						}
					}
				}
				
				$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
				$szCurrentMode = "Edit";
				//
			} elseif ( preg_match( '/^\/link\/\d{1,10}\//', $szQuery ) ) {
				$tmp = NULL;
				preg_match( '/^\/link\/(\d{1,10})\//', $szQuery, $tmp );
				$objServer = new CServer( );
				$szIdIndex = $objServer->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM );
				$objServer->Create( array( $szIdIndex => intval( $tmp[ 1 ] ) ), FLEX_FILTER_FORM );
				$szIdValue = $objServer->GetAttributeValue( "id", FLEX_FILTER_FORM );
				$arrOptions = array( FHOV_WHERE => "`".$szIdIndex."`=".$szIdValue, FHOV_TABLE => "ud_server", FHOV_OBJECT => "CServer", FHOV_INDEXATTR => "id" );
				$tmp = $this->hServer->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					$iIds = array( );
					$tmp = $tmp->GetResult( );
					$arrNames = array( );
					foreach( $tmp as $v ) {
						$iIds[ $v->graph_vertex_id ] = $v->graph_vertex_id;
						$arrNames[ ] = $v->name;
					}
					$tmp = $this->hServer->DelObject( $tmp, array( FHOV_TABLE => "ud_server", FHOV_INDEXATTR => "id" ) );
					if ( $tmp->HasError( ) ) {
						$arrErrors = $tmp->GetError( );
					} else {
						$modLogger = new CHModLogger( );
						$modLogger->AddLog(
							$objCMS->GetUserLogin( ),
							"ModLink",
							"ModLink::DelServer",
							"server deleted, name: ".join( ", ", $arrNames )
						);
						$objCMS->DelFromWorld( $iIds );
						Redirect( $objCMS->GetPath( "root_relative" )."/link/" );
					}
				}
				//
			} elseif ( preg_match( '/^\/link\/([^\/]*)\//', $szQuery ) ) {
				// вводим имя сервака и попадаем к нему в настройки
				$tmp = NULL;
				preg_match( '/^\/link\/([^\/]*)\//', $szQuery, $tmp );
				$tmp = $tmp[ 1 ];
				$objServer = new CServer( );
				$tmp = $objServer->Create( array( "server_name" => $tmp ), FLEX_FILTER_FORM );
				if ( $tmp->HasError( ) ) {
					$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
				} else {
					// должно быть валидное имя
					$szNameIndex = $objServer->GetAttributeIndex( "name", NULL, FLEX_FILTER_DATABASE );
					$szNameValue = $objServer->GetAttributeValue( "name", FLEX_FILTER_DATABASE );
					$arrOptions = array( FHOV_WHERE => "`".$szNameIndex."`=".$szNameValue, FHOV_TABLE => "ud_server", FHOV_OBJECT => "CServer" );
					$tmp = $this->hServer->GetObject( $arrOptions );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( );
						$mxdCurrentData[ "current_server" ] = current( $tmp );

						if ( count( $_POST ) ) {
							$arrData = $_POST;
							$arrFilter = array(
								"id" => $objServer->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
								"graph_vertex_id" => $objServer->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_FORM ),
								"name" => $objServer->GetAttributeIndex( "name", NULL, FLEX_FILTER_FORM )
							);
							$fltAttr = new CArrayFilter( $arrFilter );
							$arrData = $fltAttr->Apply( $arrData );
							//
							$arrData[ $arrFilter[ "id" ] ] = $mxdCurrentData[ "current_server" ]->id;
							$arrData[ $arrFilter[ "graph_vertex_id" ] ] = $mxdCurrentData[ "current_server" ]->graph_vertex_id;
							$arrData[ $arrFilter[ "name" ] ] = $mxdCurrentData[ "current_server" ]->name;
							//
							$tmp = $objServer->Create( $arrData, FLEX_FILTER_FORM );
							if ( $tmp->HasError( ) ) {
								$mxdCurrentData[ "current_server" ] = $objServer;
								$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
							} else {
								$arrOptions = array(
									FHOV_ONLYATTR => array( "id", "name", "ip", "type", "config_file", "zone_folder", "root_prefix" ),
									FHOV_TABLE => "ud_server",
									FHOV_INDEXATTR => "id"
								);
								$tmp = $this->hServer->UpdObject( array( $objServer ), $arrOptions );
								if ( $tmp->HasError( ) ) {
									$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
								} else {
									Redirect( $objCMS->GetPath( "root_relative" )."/link/" );
								}
							}
						}
						
						$szCurrentMode = "Edit";
						$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
					}
				}
			} else {
				$tmp = $this->hServer->GetObject( array( FHOV_TABLE => "ud_server", FHOV_INDEXATTR => "name", FHOV_OBJECT => "CServer" ) );
				if ( $tmp->HasResult( ) ) {
					$mxdCurrentData[ "server_list" ] = $tmp->GetResult( );
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
		 * 	Подсчитывает количество серверов
		 */
		public function ServerCount( ) {
			global $objCMS;
			$this->hServer = new CHServer( );
			$this->hServer->Create( array( "database" => $objCMS->database ) );
			$this->hServer->CheckTable( array( FHOV_TABLE => "ud_server", FHOV_OBJECT => "CServer" ) );
			$tmp = $this->hServer->CountObject( array( FHOV_TABLE => "ud_server" ) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( "count" );
				return $tmp;
			}
			return 0;
		} // function ServerCount
		
		/**
		 * 	Получение всех серверов в системе
		 */
		public function GetServers( ) {
			if ( $this->hServer === NULL ) {
				$this->InitHandlers( );
			}
			return $this->hServer->GetObject( array( FHOV_TABLE => "ud_server", FHOV_INDEXATTR => "id", FHOV_OBJECT => "CServer" ) );
		} // function GetServers
		
		/**
		 * 	Получение 2-х серверов master slave
		 */
		public function GetMasterSlave( ) {
			if ( $this->hCommon === NULL ) {
				$this->InitHandlers( );
			}
			$arrRet = array( ST_MASTER => NULL, ST_SLAVE => NULL );
			$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => 'ud_server', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CServer' ) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				foreach( $tmp as $i => $v ) {
					if ( $v->type === ST_MASTER ) {
						$arrRet[ ST_MASTER ] = clone $v;
					} elseif ( $v->type === ST_SLAVE ) {
						$arrRet[ ST_SLAVE ] = clone $v;
					}
					if ( $arrRet[ ST_MASTER ] && $arrRet[ ST_SLAVE ] ) {
						break;
					}
				}
			}
			return $arrRet;
		} // public function GetMasterSlave
		
	} // class CHModLink
?>