<?php
	/**
	 *	Модуль клиентской части
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModClient
	 */

	// клиентские разделы CS - Client Section
	define( "CS_REGDOMAIN",			1	); // регистрация доменов
	define( "CS_ZONE",			2	); // редактор файлов зон
	define( "CS_PERSONAL",			3	); // персональные данные
	define( "CS_HELP",			4	); // помощь
	define( "CS_EXIT",			5	); // выход

	/**
	 * 	Модуль клиентской части системы
	 */
	class CHModClient extends CHandler {
		public	$iDelayReg	= 2592000,	// 60 * 60 * 24 * 30 ; 30 - суток, 1 домен в 30 суток
			$iDelayAdd	= 864000,	// 60 * 60 * 24 * 10 ; 10 - суток
			$iAddLimit	= 100;		// количество добавлений зон за 10 суток
		
		private function Menu( ) {
			global $objCMS;
			$objMenu = new CMenu( );
			$szRootHttp = $objCMS->GetPath( 'root_relative' );
			$arrMenu = array(
				CS_REGDOMAIN => array( 'title' => 'Регистрация доменов', 'url' => $szRootHttp.'/' ),
				CS_ZONE => array( 'title' => 'Редактор файлов зон', 'url' => $szRootHttp.'/zone/' ),
				CS_PERSONAL => array( 'title' => 'Персональные данные', 'url' => $szRootHttp.'/account/' ),
				CS_HELP => array( 'title' => 'Помощь', 'url' => $szRootHttp.'/help/' ),
				CS_EXIT => array( 'title' => 'выход', 'url' => $szRootHttp.'/exit/' ),
			);
			if ( isset( $arrMenu[ $objCMS->wgi ] ) ) {
				$arrMenu[ $objCMS->wgi ][ 'flags' ] = $objCMS->wgi_state;
			}
			$objMenu->Create( array( 'items' => $arrMenu ) );
			return $objMenu;
		} // function Menu
		
		/**
		 *	Проверка на срабатывание (перехват)
		 *	@param $szQuery string строка тестирования
		 *	@return bool
		 */
		public function Test( $szQuery ) {
			global $objCMS;
			$iRank = $objCMS->GetUserRank( );
			if ( $iRank === SUR_CLIENT ) {
				return true;
			}
			return false;
		} // function Test
		
		/**
		 *	Обработка
		 *	@param $szQuery string строка, на которой произошел перехват
		 *	@return bool
		 */
		public function Process( $szQuery ) {
			global $objCMS, $objCurrent, $iCurrentSysRank, $mxdCurrentData, $szCurrentMode, $arrErrors, $mxdLinks;
			$objCMS->SetWGI( CS_REGDOMAIN );
			$objCMS->SetWGIState( MF_THIS );
			$objCurrent = 'Client';
			$szCurrentMode = 'RegDomain';
			$arrErrors = array( );
			$iCurrentSysRank = $objCMS->GetUserRank( );
			$mxdCurrentData = array( );
			$arrErrors = array( );
			$modUser = new CHModUser( );
			$szLogin = $objCMS->GetUserLogin( );
			$tmp = $modUser->GetUser( $szLogin );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$szType = $tmp[ 'type' ];
				unset( $tmp[ 'type' ] );
				$mxdCurrentData[ 'current_user' ] = current( $tmp );
			}
			$modZone = new CHModZone( );
			
			if ( preg_match( '/^\/zone\//', $szQuery ) ) {
				$szCurrentMode = 'ZoneList';
				$objCMS->SetWGI( CS_ZONE );
				$mxdCurrentData[ 'zone_list' ] = array( );
				$iGVId = $mxdCurrentData[ 'current_user' ]->graph_vertex_id;
				$objFileZone = new CFileZone( );
				$szGVIdIndex = $objFileZone->GetAttributeIndex( 'graph_vertex_id', NULL, FLEX_FILTER_DATABASE );
				$tmp = $objCMS->GetLinkObjects( $iGVId, true, 'User/Zone' );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$hZone = new CFlexHandler( );
					$hZone->Create( array( 'database' => $objCMS->database ) );
					$arrIds = array( );
					foreach( $tmp as $i => $v ) {
						$arrIds[ $v->id ] = $v->id;
					}
					$tmp = $hZone->GetObject( array(
						FHOV_WHERE => "`".$szGVIdIndex."` IN (".join( ",", $arrIds ).")",
						FHOV_TABLE => "ud_zone", FHOV_INDEXATTR => "id", FHOV_OBJECT => "CFileZone"
					) );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( );
						foreach( $tmp as $i => $v ) {
							if ( $v->type !== FZT_DIRECT ) {
								unset( $tmp[ $i ] );
							}
						}
						$mxdCurrentData[ "zone_list" ] = $tmp;
						$mxdCurrentData[ "zone_by_name" ] = array( );
						foreach( $tmp as $i => $v ) {
							$mxdCurrentData[ "zone_by_name" ][ $v->name ] = $i;
						}
						
						if ( preg_match( '/^\/zone\/[^\/]*\//', $szQuery ) ) {
							preg_match( '/^\/zone\/([^\/]*)\//', $szQuery, $tmp );
							//$szName = $tmp[ 1 ];
							$iIndex = intval( $tmp[ 1 ] );
							if ( isset( $mxdCurrentData[ "zone_list" ][ $iIndex ] ) ) {
								$szCurrentMode = "ZoneEdit";
								$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
								//$iIndex = $mxdCurrentData[ "zone_by_name" ][ $szName ];
								$mxdCurrentData[ "current_zone" ] = clone $mxdCurrentData[ "zone_list" ][ $iIndex ];
								if ( $mxdCurrentData[ "current_user" ]->state === US_ACTIVE ) {
									$modZone->CheckLockState( $mxdCurrentData, $arrErrors );
								} else {
									$modZone->GetZoneRRs( $mxdCurrentData[ "current_zone" ] );
									$mxdCurrentData[ "zone_locked" ] = true;
								}
								$bLocked = isset( $mxdCurrentData[ "zone_locked" ] );
								
								$hServer = new CHServer( );
								$hServer->Create( array( "database" => $objCMS->database ) );
								$tmp = $hServer->GetObject( array( FHOV_TABLE => "ud_server", FHOV_OBJECT => "CServer", FHOV_INDEXATTR => "id" ) );
								if ( $tmp->HasResult( ) ) {
									$mxdCurrentData[ "servers" ] = $tmp->GetResult( );
								}
								
								if ( preg_match( '/^\/zone\/[^\/]*\/add_rr\/\d*\//', $szQuery ) ) {
									if ( !$bLocked ) {
										$this->ModeZoneAddRR( $szQuery, $mxdCurrentData, $arrErrors );
										if ( isset( $mxdCurrentData[ "current_mode" ] ) ) {
											$szCurrentMode = $mxdCurrentData[ "current_mode" ];
										}
									}
								} elseif ( preg_match( '/^\/zone\/[^\/]*\/old\//', $szQuery ) ) {
									$this->ModeZoneOld( $szQuery, $mxdCurrentData, $arrErrors );
									if ( isset( $mxdCurrentData[ "current_mode" ] ) ) {
										$szCurrentMode = $mxdCurrentData[ "current_mode" ];
									}
								} elseif ( preg_match( '/^\/zone\/[^\/]*\/upload\//', $szQuery ) ) {
									if ( !$bLocked ) {
										$this->ModeZoneUpload( $szQuery, $mxdCurrentData, $arrErrors );
										if ( isset( $mxdCurrentData[ "current_mode" ] ) ) {
											$szCurrentMode = $mxdCurrentData[ "current_mode" ];
										}
									}
								} elseif ( preg_match( '/^\/zone\/[^\/]*\/export\//', $szQuery ) ) {
									$this->ModeZoneExport( $szQuery, $mxdCurrentData, $arrErrors );
									if ( isset( $mxdCurrentData[ "current_mode" ] ) ) {
										$szCurrentMode = $mxdCurrentData[ "current_mode" ];
									}
								} elseif ( preg_match( '/^\/zone\/[^\/]*\/save\//', $szQuery ) ) {
									if ( !$bLocked ) {
										$modZone->SaveZone( $mxdCurrentData );
									}
								} elseif ( preg_match( '/^\/zone\/[^\/]*\/exit\//', $szQuery ) ) {
									if ( !$bLocked ) {
										$modZone->UnlockZone( $mxdCurrentData[ "current_zone" ] );
										Redirect( $objCMS->GetPath( "root_relative" )."/zone/" );
									}
								} else {
									if ( count( $_POST ) ) {
										if ( $bLocked || $mxdCurrentData[ "current_user" ]->state !== US_ACTIVE ) {
											Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->name."/" );
										}
										$bRedir = true;
										$bWasError = false;
										$arrData = $_POST;
										$szRRsIndex = $mxdCurrentData[ "current_zone" ]->GetAttributeIndex( "rrs", NULL, FLEX_FILTER_FORM );
										$tmp = $mxdCurrentData[ "current_zone" ]->rrs;
										$arrCurRRs = array( );
										foreach( $tmp as $i => $v ) {
											$arrCurRRs[ $i ] = clone $v;
										}
										$arrNewRRs = $arrData[ $szRRsIndex ];
										$tmp = $modZone->CheckRRs( $arrCurRRs, $arrNewRRs, FLEX_FILTER_FORM, $mxdCurrentData );
										if ( $tmp->HasError( ) ) {
											$mxdCurrentData[ "current_zone" ]->ClearRRs( );
											$mxdCurrentData[ "current_zone" ]->Create( array( $szRRsIndex => $arrCurRRs ), FLEX_FILTER_FORM );
										} else {
											$bWasChanged = ( bool ) $tmp->HasResult( );
											$arrUpdRRs = $tmp->GetResult( );
											$arrInput = array( );
											$hRRs = new CHResourceRecord( );
											$hRRs->Create( array( "database" => $objCMS->database ) );
											
											$szDefaultTTLIndex = $mxdCurrentData[ "current_zone" ]->GetAttributeIndex( "default_ttl", NULL, FLEX_FILTER_FORM );
											if ( isset( $arrData[ $szDefaultTTLIndex ] ) ) {
												$arrInput[ $szDefaultTTLIndex ] = $arrData[ $szDefaultTTLIndex ];
												if ( $arrData[ $szDefaultTTLIndex ] != $mxdCurrentData[ "current_zone" ]->GetAttributeValue( "default_ttl" ) ) {
													$bWasChanged = true;
												}
											}
											
											$szCommentIndex = $mxdCurrentData[ "current_zone" ]->GetAttributeIndex( "comment", NULL, FLEX_FILTER_FORM );
											if ( isset( $arrData[ $szCommentIndex ] ) ) {
												$arrInput[ $szCommentIndex ] = $arrData[ $szCommentIndex ];
											}
											
											//
											$arrToDel = array( );
											if ( isset( $arrData[ "del" ] ) && is_array( $arrData[ "del" ] ) ) {
												foreach( $arrCurRRs as $i => $v ) {
													if ( $v->type !== "SOA" ) {
														if ( in_array( $v->id, $arrData[ "del" ] ) ) {
															$arrToDel[ $i ] = $v;
															unset( $arrCurRRs[ $i ] );
															if ( isset( $arrUpdRRs[ $i ] ) ) {
																unset( $arrUpdRRs[ $i ] );
															}
														}
													}
												}
												if ( !empty( $arrToDel ) ) {
													$bWasChanged = true;
													$tmp = $hRRs->DelObject( $arrToDel, array( FHOV_TABLE => "ud_tmp_rr" ) );
												}
											}
											$mxdCurrentData[ "current_zone" ]->Create( $arrInput, FLEX_FILTER_FORM );
											$hZone = new CFlexHandler( );
											$hZone->Create( array( "database" => $objCMS->database ) );
											$tmp = $hZone->UpdObject( array( $mxdCurrentData[ "current_zone" ] ), array( FHOV_IGNOREATTR => array( "rrs" ), FHOV_TABLE => "ud_tmp_zone", FHOV_INDEXATTR => "id" ) );
											$arrIgnoreAttr = array( );
											foreach( $arrCurRRs as $i => $v ) {
												$arrIgnoreAttr = array_merge( $arrIgnoreAttr, $v->GetAttrIgnoreList( ) );
											}
											$arrOptions = array( FHOV_TABLE => "ud_tmp_rr", FHOV_INDEXATTR => "id" );
											if ( !empty( $arrIgnoreAttr ) ) {
												$arrOptions[ FHOV_IGNOREATTR ] = $arrIgnoreAttr;
											}
											
											$modZone->SetRROrder( $arrCurRRs );
											$tmp = $hRRs->UpdObject( $arrCurRRs, $arrOptions );
											if ( $tmp->HasError( ) ) {
												$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
											} else {
												if ( isset( $arrData[ "save" ] ) ) {
													Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->name."/save/" );
												} else {
													Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->name."/" );
												}
											}
										}
									}
								}
							}
						}
					}
				}
				//
			} elseif ( preg_match( '/^\/account\//', $szQuery ) ) {
				if ( $mxdCurrentData[ "current_user" ]->state === US_ACTIVE ) {
					if ( count( $_POST ) ) {
						$arrData = $_POST;
						$objClient = new CClient( );
						$fltArray = new CArrayFilter( );
						$arrIndex = array(
							"id" => $objClient->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
							"graph_vertex_id" => $objClient->GetAttributeIndex( "graph_vertex_id", NULl, FLEX_FILTER_FORM ),
							"login" => $objClient->GetAttributeIndex( "login", NULl, FLEX_FILTER_FORM ),
							"reg_date" => $objClient->GetAttributeIndex( "reg_date", NULl, FLEX_FILTER_FORM ),
							"last_edit" => $objClient->GetAttributeIndex( "last_edit", NULl, FLEX_FILTER_FORM ),
							"last_login" => $objClient->GetAttributeIndex( "last_login", NULl, FLEX_FILTER_FORM ),
							"state" => $objClient->GetAttributeIndex( "state", NULl, FLEX_FILTER_FORM ),
							"ip_block" => $objClient->GetAttributeIndex( "ip_block", NULl, FLEX_FILTER_FORM ),
							"zones" => $objClient->GetAttributeIndex( "zones", NULl, FLEX_FILTER_FORM ),
						);
						$fltArray->SetArray( $arrIndex );
						$arrData = $fltArray->Apply( $arrData );
						//
						$arrIgnoreAttr = array( "graph_vertex_id", "login", "zones", "ip_block", "reg_date", "state", "reg_date", "last_login" );
						$szPasswordIndex = $objClient->GetAttributeIndex( "password", NULl, FLEX_FILTER_FORM );
						if ( !isset( $arrData[ $szPasswordIndex ] ) || $arrData[ $szPasswordIndex ] == "" ) {
							$arrIgnoreAttr[ ] = "password";
							$arrData[ $szPasswordIndex ] = $mxdCurrentData[ "current_user" ]->password;
						}
						$arrData[ "client_last_edit" ] = date( "Y-m-d H:i:s" );
						$arrData[ "client_login" ] = $mxdCurrentData[ "current_user" ]->login;
						$arrData[ "client_ip_block" ] = $mxdCurrentData[ "current_user" ]->ip_block;
						//
						$tmp = $mxdCurrentData[ "current_user" ]->Create( $arrData, FLEX_FILTER_FORM );
						if ( $tmp->HasError( ) ) {
							$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
						} else {
							if ( $mxdCurrentData[ "current_user" ]->state !== US_ACTIVE ) {
								Redirect( $objCMS->GetPath( "root_relative" )."/account/" );
								exit;
							}
							$hClient = new CFlexHandler( );
							$hClient->Create( array( "database" => $objCMS->database ) );
							$arrOptions = array( FHOV_TABLE => "ud_client", FHOV_INDEXATTR => "id" );
							if ( !empty( $arrIgnoreAttr ) ) {
								$arrOptions[ FHOV_IGNOREATTR ] = $arrIgnoreAttr;
							}
							$hClient->UpdObject( array( $mxdCurrentData[ "current_user" ] ), $arrOptions );
							Redirect( $objCMS->GetPath( "root_relative" )."/account/" );
						}
					}
				} else {
					$mxdCurrentData[ "user_blocked" ] = true;
				}
				
				$szCurrentMode = "Account";
				$objCMS->SetWGI( CS_PERSONAL );
				//
			} elseif ( preg_match( '/^\/help\//', $szQuery ) ) {
				$szCurrentMode = "Help";
				$objCMS->SetWGI( CS_HELP );
				//
			} else {
				if ( $mxdCurrentData[ 'current_user' ]->state === US_ACTIVE ) {
					if ( preg_match( '/^\/add_zone\/[^\/]+\//', $szQuery ) ) {
						$tmp = NULL;
						preg_match( '/^\/add_zone\/([^\/]*)\//', $szQuery, $tmp );
						$szName = $tmp[ 1 ];
						//
						$iGVId = $mxdCurrentData[ 'current_user' ]->graph_vertex_id;
						$objFileZone = new CFileZone( );
						$szGVIdIndex = $objFileZone->GetAttributeIndex( 'graph_vertex_id', NULL, FLEX_FILTER_DATABASE );
						$tmp = $objCMS->GetLinkObjects( $iGVId, true, 'User/Zone' );
						if ( $tmp->HasResult( ) ) {
							$tmp = $tmp->GetResult( );
							$hZone = new CFlexHandler( );
							$hZone->Create( array( 'database' => $objCMS->database ) );
							$arrIds = array( );
							foreach( $tmp as $i => $v ) {
								$arrIds[ $v->id ] = $v->id;
							}
							$tmp = $hZone->GetObject( array(
								FHOV_WHERE => '`'.$szGVIdIndex.'` IN ('.join( ',', $arrIds ).')',
								FHOV_TABLE => 'ud_zone', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CFileZone'
							) );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->GetResult( );
								foreach( $tmp as $i => $v ) {
									if ( $v->name === $szName ) {
										$mxdCurrentData[ 'filezone' ] = $v;
										break;
									}
								}
							}
						}
						$szCurrentMode = 'ZoneReg';
						$objCMS->SetWGIState( MF_CURRENT | MF_THIS );
						//
					} elseif ( preg_match( '/^\/add_zone\//', $szQuery ) ) {
						$szCurrentMode = 'ZoneAdd';
						$objCMS->SetWGIState( MF_CURRENT | MF_THIS );
						$objFileZone = new CFileZone( );
						$mxdCurrentData[ 'current_domain' ] = $objFileZone;
						$szNameIndex = $objFileZone->GetAttributeIndex( 'name', NULL, FLEX_FILTER_FORM );
						$modServer = new CHModLink( );
						$iN = $modServer->ServerCount( );
						if ( $iN ) {
							$modZone = new CHModZone( );
							$iN = $modZone->ExistsTplSoa( );
							if ( $iN ) {
								$bAllowAdd = $this->AllowedAdd( $mxdCurrentData );
								if ( $bAllowAdd ) {
									if ( count( $_POST ) ) {
										$arrData = $_POST;
										if ( $mxdCurrentData[ 'current_user' ]->state !== US_ACTIVE ) {
											Redirect( $objCMS->GetPath( 'root_relative' ) );
										}
										$mxdCurrentData[ 'client_add' ] = true;
										$tmp = $modZone->AddFileZone2( $arrData, $mxdCurrentData, FLEX_FILTER_FORM );
										if ( $tmp->HasError( ) ) {
											$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
										} else {
											$tmp = $tmp->GetResult( 'zone_name' );
											Redirect( $objCMS->GetPath( 'root_relative' ).'/add_zone/'.$tmp.'/' );
										}
									}
								} else {
									$mxdCurrentData[ 'not_allowed_add' ] = true;
								}
							}
						}
					} elseif ( preg_match( "/^\/[^\/]+\//", $szQuery ) ) {
						$tmp = NULL;
						preg_match( "/^\/([^\/]*)\//", $szQuery, $tmp );
						$szName = $tmp[ 1 ];
						//
						$iGVId = $mxdCurrentData[ 'current_user' ]->graph_vertex_id;
						$objFileZone = new CFileZone( );
						$szGVIdIndex = $objFileZone->GetAttributeIndex( 'graph_vertex_id', NULL, FLEX_FILTER_DATABASE );
						$tmp = $objCMS->GetLinkObjects( $iGVId, true, 'User/Zone' );
						if ( $tmp->HasResult( ) ) {
							$tmp = $tmp->GetResult( );
							$hZone = new CFlexHandler( );
							$hZone->Create( array( 'database' => $objCMS->database ) );
							$arrIds = array( );
							foreach( $tmp as $i => $v ) {
								$arrIds[ $v->id ] = $v->id;
							}
							$tmp = $hZone->GetObject( array(
								FHOV_WHERE => '`'.$szGVIdIndex.'` IN ('.join( ',', $arrIds ).')',
								FHOV_TABLE => 'ud_zone', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CFileZone'
							) );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->GetResult( );
								foreach( $tmp as $i => $v ) {
									if ( $v->name === $szName ) {
										$mxdCurrentData[ 'filezone' ] = $v;
										break;
									}
								}
							}
						}
						$szCurrentMode = 'DomainReg';
						$objCMS->SetWGIState( MF_CURRENT | MF_THIS );
						//
					} else {
						$objFileZone = new CFileZone( );
						$mxdCurrentData[ 'current_domain' ] = $objFileZone;
						$szNameIndex = $objFileZone->GetAttributeIndex( 'name', NULL, FLEX_FILTER_FORM );
						$modServer = new CHModLink( );
						$iN = $modServer->ServerCount( );
						if ( $iN ) {
							$modZone = new CHModZone( );
							$iN = $modZone->ExistsTplSoa( );
							if ( $iN ) {
								$bAllowRegister = $this->AllowedRegisterDomain( $mxdCurrentData );
								if ( $bAllowRegister ) {
									if ( isset( $_GET[ $szNameIndex ] ) ) {
										// ввели доменное имя
										$szZoneName = @strval( $_GET[ $szNameIndex ] );
										$tmp = $objFileZone->Create( array( $szNameIndex => $szZoneName ) );
										$mxdCurrentData[ 'current_domain' ] = $objFileZone;
										if ( $tmp->HasError( ) ) {
											$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
										} else {
											if ( $modZone->FreeDomain( $objFileZone->GetAttributeValue( 'name' ) ) ) {
												$mxdCurrentData[ 'reg' ] = true;
												$mxdCurrentData[ 'zone_name' ] = $szZoneName;
											} else {
												// домен занят
												$arrErrors[ ] = new CError( 1, 'Доменное имя занято' );
											}
										}
									}
									if ( count( $_POST ) ) {
										$arrData = $_POST;
										if ( $mxdCurrentData[ 'current_user' ]->state !== US_ACTIVE ) {
											Redirect( $objCMS->GetPath( 'root_relative' ) );
										}
										$mxdCurrentData[ 'client_add' ] = true;
										$tmp = $modZone->AddFileZone( $arrData, $mxdCurrentData, FLEX_FILTER_FORM );
										if ( $tmp->HasError( ) ) {
											$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
										} else {
											$tmp = $tmp->GetResult( 'zone_name' );
											Redirect( $objCMS->GetPath( 'root_relative' ).'/'.$tmp.'/' );
										}
									}
								} else {
									$mxdCurrentData[ 'not_allowed_register' ] = true;
								}
							}
						}
					}
				} else {
					$mxdCurrentData[ 'user_blocked' ] = true;
				}
			}
			
			$mxdCurrentData[ 'menu' ] = $this->Menu( );
			
			$szFolder = $objCMS->GetPath( 'root_application' );
			if ( $szFolder !== false && file_exists( $szFolder.'/index_client.php' ) ) {
				include_once( $szFolder.'/index_client.php' );
			}
			
			return true;
		} // function Process
		
		/**
		 * 	Режим добавления ресурсной записи
		 */
		private function ModeZoneAddRR( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			$modZone = new CHModZone( );
			$mxdCurrentData[ "current_mode" ] = "ZoneAddRR";
			$tmp = NULL;
			preg_match( '/^\/zone\/[^\/]*\/add_rr\/(\d*)\//', $szQuery, $tmp );
			$szRRTypeName = "rr_type";
		     	$tmpAddRrPos = intval( $tmp[ 1 ] );
		     	$mxdCurrentData[ "rr_pos" ] = $tmpAddRrPos;
			if ( isset( $_GET[ $szRRTypeName ] ) ) {
		     		$szInpType = strval( $_GET[ $szRRTypeName ] );
		     		$mxdCurrentData[ "rr_mode" ] = "input";
		     		$arrRRTypes = array(
					RRT_SOA => "CRR_SOA", RRT_NS => "CRR_NS", RRT_A => "CRR_A", RRT_AAAA => "CRR_AAAA",
					RRT_CNAME => "CRR_CNAME", RRT_MX => "CRR_MX", RRT_PTR => "CRR_PTR", RRT_SRV => "CRR_SRV",
					RRT_TXT => "CRR_TXT",
					RRT_TTL => "CRR__TTL", RRT_ORIGIN => "CRR__ORIGIN", RRT_INCLUDE => "CRR__INCLUDE"
				);
				$objTmp = NULL;
				if ( isset( $arrRRTypes[ $szInpType ] ) ) {
					$szClass = $arrRRTypes[ $szInpType ];
					$objTmp = new $szClass( );
				} else {
					$objTmp = new CResourceRecord( );
				}
				if ( $objTmp !== NULL ) {
					$szOrderIndex = $objTmp->GetAttributeIndex( "order" );
					$objTmp->Create( array( $szOrderIndex => $tmpAddRrPos ) );
					$mxdCurrentData[ "current_rr" ] = $objTmp;
				}
		     	} elseif ( count( $_POST ) ) {
		     		if ( $mxdCurrentData[ "current_user" ]->state !== US_ACTIVE ) {
		     			Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->name."/" );
		     		}
		     		$arrData = $_POST;
		     		$tmpRR = new CResourceRecord( );
		     		$arrFilter = array(
		     			"id" => $tmpRR->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
		     			"zone_file_id" => $tmpRR->GetAttributeIndex( "zone_file_id", NULL, FLEX_FILTER_FORM ),
		     			"order" => $tmpRR->GetAttributeIndex( "order", NULL, FLEX_FILTER_FORM ) // в этом контексте порядок запрещено вводить
		     		);
		     		$fltArray = new CArrayFilter( );
		     		$arrData = $fltArray->Apply( $arrData );
		     		$arrData[ $arrFilter[ "zone_file_id" ] ] = $mxdCurrentData[ "current_zone" ]->GetAttributeValue( "id" );
		     		$arrData[ $arrFilter[ "order" ] ] = $tmpAddRrPos;
		     		unset( $tmpRR );
		     		$bRedir = true;
		     		$hRRs = new CHResourceRecord( );
		     		$hRRs->Create( array( "database" => $objCMS->database ) );
		     		$objRR = $hRRs->GenerateRR( $arrData );
		     		if ( $objRR->HasError( ) ) {
		     			if ( $objRR->HasResult( ) ) {
		     				$mxdCurrentData[ "current_rr" ] = $objRR->GetResult( "rr" );
		     			}
		     			$bRedir = false;
		     			$arrErrors = array_merge( $arrErrors, $objRR->GetError( ) );
		     		} else {
		     			$objRR = $objRR->GetResult( "rr" );
		     			$arrOptions = array( FHOV_TABLE => "ud_tmp_rr" );
		     			$tmpIgnoreAttr = $objRR->GetAttrIgnoreList( );
		     			if ( !empty( $tmpIgnoreAttr ) ) {
		     				$arrOptions[ FHOV_IGNOREATTR ] = $tmpIgnoreAttr;
		     			}
			     		$tmp = $hRRs->AddObject( array( $objRR ), $arrOptions );
			     		if ( $tmp->HasError( ) ) {
			     			$mxdCurrentData[ "current_rr" ] = $objRR;
			     			$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
			     		} else {
			     			$iId = $tmp->GetResult( "insert_id" );
			     			$arrCurRrs = $mxdCurrentData[ "current_zone" ]->rrs;
				     		$tmp = array( $objRR->GetAttributeIndex( "id" ) => $iId );
			     			$objRR->Create( $tmp );
				     		$arrCurRrs[ ] = $objRR;
				     		$modZone->SetRROrder( $arrCurRrs );
				     		$arrOptions = array( FHOV_ONLYATTR => array( "id", "order", "data" ), FHOV_TABLE => "ud_tmp_rr", FHOV_INDEXATTR => "id" );
				     		$hRRs->UpdObject( $arrCurRrs, $arrOptions );
				     		Redirect( $objCMS->GetPath( "root_relative" )."/zone/".urlencode( $mxdCurrentData[ "current_zone" ]->GetAttributeValue( "name" ) )."/" );
			     		}
		     		}
		     	} else {
		     		$mxdCurrentData[ "rr_mode" ] = "select";
		     	}
		} // function MdoeZoneAddRR
		
		/**
		 * 	Режим просмотра версий файла
		 */
		private function ModeZoneOld( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			$modZone = new CHModZone( );
			$mxdCurrentData[ "current_mode" ] = "ZoneOldList";
			$mxdCurrentData[ "old_zone_list" ] = array( );
			$tmp = $modZone->GetOldZones( $mxdCurrentData[ "current_zone" ] );
			if ( $tmp->HasResult( ) ) {
				$mxdCurrentData[ "old_zone_list" ] = $tmp->GetResult( );
			}
				
			if ( preg_match( '/^\/zone\/[^\/]*\/old\/\d*\//', $szQuery ) ) {
				$tmp = NULL;
				preg_match( '/^\/zone\/[^\/]*\/old\/(\d*)\//', $szQuery, $tmp );
				$iId = intval( $tmp[ 1 ] );
				if ( isset( $mxdCurrentData[ "old_zone_list" ][ $iId ] ) ) {
					$mxdCurrentData[ "current_mode" ] = "ZoneOldView";
					$objCurrentOldZone = clone $mxdCurrentData[ "old_zone_list" ][ $iId ];
					$modZone->GetZoneRRs( $objCurrentOldZone, "ud_old_rr" );
					$mxdCurrentData[ "current_old_zone" ] = $objCurrentOldZone;
						
					if ( preg_match( '/^\/zone\/[^\/]*\/old\/\d*\/load\//', $szQuery ) ) {
						if ( $mxdCurrentData[ "current_user" ]->state === US_ACTIVE && !isset( $mxdCurrentData[ "zone_locked" ] ) ) {
							$modZone->RestoreFileZone( $mxdCurrentData[ "current_zone" ], $mxdCurrentData[ "current_old_zone" ] );
						}
						Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->name."/" );
					}
				}
			} else {
				if ( count( $_POST ) && isset( $_POST[ "del" ] ) ) {
					if ( !isset( $mxdCurrentData[ "zone_locked" ] ) ) {
						$arrData = $_POST[ "del" ];
						$tmp = array( );
						foreach( $arrData as $i => $v ) {
							if ( is_int( $i ) && isset( $mxdCurrentData[ "old_zone_list" ][ $i ] ) ) {
								$tmp[ $i ] = $mxdCurrentData[ "old_zone_list" ][ $i ];
							}
						}
						if ( !empty( $tmp ) ) {
							$modZone->DelFileZone( $tmp, true, false );
						}
					}
					Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->name."/old/" );
				}
			}
		} // function ModeZoneOld
		
		/**
		 * 	Режим загрузки файла зон
		 */
		private function ModeZoneUpload( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			$mxdCurrentData[ "current_mode" ] = "ZoneUpload";
			$objCurrentZone = $mxdCurrentData[ "current_zone" ];
			$modZone = new CHModZone( );
			
			if ( count( $_POST ) && isset( $_POST[ "load" ] ) ) {
				if ( is_uploaded_file( $_FILES[ "file_zone" ][ "tmp_name" ] ) ) {
					$szText = "";
					$hFile = fopen( $_FILES[ "file_zone" ][ "tmp_name" ], "rb" );
					if ( $hFile ) {
						$szText = fread( $hFile, $_FILES[ "file_zone" ][ "size" ] );
						fclose( $hFile );
					}
					if ( !empty( $szText ) ) {
						$objParser = new CZoneParser( );
						$tmp = $objParser->GetFileZone( $szText );
						if ( $tmp->HasError( ) ) {
							$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
						} else {
							$objNewZone = new CFileZone( );
							$arrRRs = $tmp->GetResult( "rrs" );
							$objDefaultTTL = $tmp->GetResult( "default_ttl" );
							$arrIndex = array(
								"rrs" => $objNewZone->GetAttributeIndex( "rrs" ),
								"default_ttl" => $objNewZone->GetAttributeIndex( "default_ttl" ),
							);
							$arrIni = array( $arrIndex[ "rrs" ] => $arrRRs );
							if ( $objDefaultTTL !== NULL ) {
								$arrIni[ $arrIndex[ "default_ttl" ] ] = $objDefaultTTL->name;
							}
							$objNewZone->Create( $arrIni );
							$objCurrentZone = $mxdCurrentData[ "current_zone" ];
							if ( $modZone->CompareZones( $mxdCurrentData[ "current_zone" ], $objNewZone ) ) {
								Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$objCurrentZone->name."/" );
							} else {
								$tmp = $modZone->ReplaceZone( $objCurrentZone, $objNewZone, false, "ud_tmp_zone", "ud_tmp_rr" );
								if ( $tmp->HasError( ) ) {
									$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
								} else {
									Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$objCurrentZone->name."/" );
								}
							}
						}
					}
				}
			}
		} // function ModeZoneUpload
		
		/**
		 * 	Экспорт файла зоны
		 */
		private function ModeZoneExport( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			$mxdCurrentData[ "current_mode" ] = "ZoneExport";
			//
			if ( count( $_POST ) && isset( $_POST[ "type" ] ) ) {
				/**
				 * 1. Генерим текст файла
				 * 2. Выдаем его браузеру
				 */
				$iType = intval( $_POST[ "type" ] ) % 3;
				$iMode = FZM_BIND;
				if ( $iType == 1 ) {
					$iMode = FZM_CSV;
				}
				$szName = $mxdCurrentData[ "current_zone" ]->name;
				if ( $iType == 0 ) {
					$szName .= ".bind";
				} elseif ( $iType == 1 ) {
					$szName .= ".csv";
				} else {
					$szName .= ".nsd";
				}
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename="'.$szName.'"' );
				$szText = $mxdCurrentData[ "current_zone" ]->GetText( $iMode );
				echo $szText;
				exit;
			}
		} // function ModeZoneExport
		
		/**
		 * 	Проверяет разрешено ли регистрировать человеку домен
		 */
		private function AllowedRegisterDomain( &$mxdCurrentData ) {
			global $objCMS;
			$iGVId = $mxdCurrentData[ 'current_user' ]->graph_vertex_id;
			$objFileZone = new CFileZone( );
			$szGVIdIndex = $objFileZone->GetAttributeIndex( 'graph_vertex_id', NULL, FLEX_FILTER_DATABASE );
			$tmp = $objCMS->GetLinkObjects( $iGVId, true, 'User/Zone' );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$hCommon = new CFlexHandler( );
				$hCommon->Create( array( 'database' => $objCMS->database ) );
				$arrIds = array( );
				foreach( $tmp as $i => $v ) {
					$arrIds[ $v->id ] = $v->id;
				}
				$iFlags = FZF_CLIENTCREATE | FZF_REGISTERED; // ищем те, что регал сам клиент
				$tmp = $hCommon->GetObject( array(
					FHOV_WHERE => '`zone_type`='.FZT_DIRECT.' AND `zone_flags`='.$iFlags.' AND `'.$szGVIdIndex.'` IN ('.join( ',', $arrIds ).')',
					FHOV_TABLE => 'ud_zone', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CFileZone',
					FHOV_ORDER => 'zone_reg_date DESC', FHOV_LIMIT => '1'
				) );
				if ( $tmp->HasResult( ) ) {
					$tmp = current( $tmp->GetResult( ) );
					$iCurDate = time( );
					$iDate = strtotime( $tmp->reg_date );
					$iDiff = $iCurDate - $iDate;
					return $iDiff > $this->iDelayReg;
				}
			}
			return false;
		} // function AllowedRegisterDomain
		
		/**
		 * 	Проверяет разрешено ли добавлять зоны
		 */
		private function AllowedAdd( &$mxdCurrentData ) {
			global $objCMS;
			$iGVId = $mxdCurrentData[ 'current_user' ]->graph_vertex_id;
			$objFileZone = new CFileZone( );
			$szGVIdIndex = $objFileZone->GetAttributeIndex( 'graph_vertex_id', NULL, FLEX_FILTER_DATABASE );
			$tmp = $objCMS->GetLinkObjects( $iGVId, true, 'User/Zone' );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$hCommon = new CFlexHandler( );
				$hCommon->Create( array( 'database' => $objCMS->database ) );
				$arrIds = array( );
				foreach( $tmp as $i => $v ) {
					$arrIds[ $v->id ] = $v->id;
				}
				$iFlags = FZF_CLIENTCREATE; // ищем те, что добавлял сам клиент
				$tmp = $hCommon->GetObject( array(
					FHOV_WHERE => '`zone_type`='.FZT_DIRECT.' AND `zone_flags`='.$iFlags.' AND `'.$szGVIdIndex.'` IN ('.join( ',', $arrIds ).')',
					FHOV_TABLE => 'ud_zone', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CFileZone',
					FHOV_ORDER => 'zone_reg_date DESC'
				) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$iCurDate = time( );
					foreach( $tmp as $i => $v ) {
						$iDate = strtotime( $v->reg_date );
						$iDiff = $iCurDate - $iDate;
						if ( $iDiff > $this->iDelayAdd ) {
							unset( $tmp[ $i ] );
						}
					}
					$iCount = count( $tmp );
					return $iCount < $this->iAddLimit;
				}
			}
			return false;
		} // function AllowedAdd
		
	} // class CHModClient
?>