<?php
	/**
	 *	Модуль пользователей
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModUser
	 */

	require( 'user.user.php' );
	require( 'user.admin.php' );
	require( 'user.client.php' );
	require( 'user.filter.php' );
	require( 'user.fields.php' );
	require( 'user.network.php' );
	
	/**
	 *	Перехватчик для модуля User
	 */
	class CHModUser extends CHandler {
		private $hClient = NULL;
		private $hAdmin = NULL;
		private $hCommon = NULL;
		
		/**
		 * 	Инициализация обработчиков
		 */
		public function InitObjectHandler( ) {
			global $objCMS;
			$arrIni = array( 'database' => $objCMS->database );
			$this->hCommon = new CFlexHandler( );
			$this->hCommon->Create( $arrIni );
			// клиенты
			$this->hClient = $this->hCommon;
			$this->hClient->CheckTable( array( FHOV_TABLE => 'ud_client', FHOV_OBJECT => 'CClient' ) );
			// админы
			$this->hAdmin = $this->hCommon;
			$this->hAdmin->CheckTable( array( FHOV_TABLE => 'ud_admin', FHOV_OBJECT => 'CAdmin' ) );
			// дополнительные поля
			$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_fld', FHOV_OBJECT => 'CClientField' ) );
			$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_cfv', FHOV_OBJECT => 'CCFValue' ) );
			// ip блоки адресов ( сети ) клиентов
			$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_network', FHOV_OBJECT => 'CNetwork' ) );
		} // funciton InitObjectHandler
		
		/**
		 *	Проверка на срабатывание (перехват)
		 *	@param $szQuery string строка тестирования
		 *	@return bool
		 */
		public function Test( $szQuery ) {
			global $objCMS;
			if ( preg_match( '/^\/user\//', $szQuery ) ) {
				return true;
			}
			$iCurrentSysRank = $objCMS->GetUserRank( );
			if ( preg_match( '/^\/admins\//', $szQuery ) && ( $iCurrentSysRank == SUR_ADMIN || $iCurrentSysRank == SUR_SUPERADMIN ) ) {
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
			// выставляем текущий модуль
			$objCMS->SetWGI( WGI_USER );
			$objCMS->SetWGIState( MF_THIS );
			$objCurrent = "User";
			$szCurrentMode = "List";
			$this->InitObjectHandler( );
			$arrErrors = array( );
			$iCurrentSysRank = $objCMS->GetUserRank( );
			
			if ( preg_match( '/^\/user\/\+\//', $szQuery ) ) {
				$mxdCurrentData[ 'current_user' ] = new CClient( );
				$mxdCurrentData[ 'field_list' ] = array( );
				$tmp = $this->GetClientFields( $mxdCurrentData[ "current_user" ]->id );
				if ( $tmp->HasResult( ) ) {
					$mxdCurrentData[ "field_list" ] = $tmp->GetResult( "fields" );
				}
				
				if ( count( $_POST ) ) {
					$arrData = $_POST;
					$this->AddClient( $arrData, $mxdCurrentData, $arrErrors );
				}
				$szCurrentMode = "Edit";
				$objCMS->SetWGIState( MF_CURRENT | MF_THIS );
				//
			} elseif ( preg_match( '/^\/user\/fields\//', $szQuery ) ) {
				$szCurrentMode = "Fields";
				$mxdCurrentData[ "current_field" ] = new CClientField( );
				$mxdCurrentData[ "field_list" ] = array( );
				//
				$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => "ud_fld", FHOV_INDEXATTR => "name", FHOV_OBJECT => "CClientField" ) );
				if ( $tmp->HasResult( ) ) {
					$mxdCurrentData[ "field_list" ] = $tmp->GetResult( );
				}
				
				if ( count( $_POST ) ) {
					if ( isset( $_POST[ "act" ], $_POST[ "fld" ] ) && is_array( $_POST[ "act" ] ) ) {
						$szAct = key( $_POST[ "act" ] );
						$arrData = $_POST[ "fld" ];
						if ( is_array( $arrData ) ) {
							switch( $szAct ) {
								case "add": {
									$tmp = $mxdCurrentData[ "current_field" ]->Create( $arrData, FLEX_FILTER_FORM );
									if ( $tmp->HasError( ) ) {
										$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
									} else {
										$arrIndex = $mxdCurrentData[ "current_field" ]->GetAttributeIndexList( FLEX_FILTER_DATABASE );
										$tmp = $this->hCommon->GetObject( array(
											FHOV_WHERE => "`".$arrIndex[ "name" ]."`=".$mxdCurrentData[ "current_field" ]->GetAttributeValue( "name", FLEX_FILTER_DATABASE ),
											FHOV_TABLE => "ud_fld", FHOV_OBJECT => "CClientField"
										) );
										if ( $tmp->HasResult( ) ) {
											$arrErrors[ ] = new CError( 1, "Поле с таким именем уже существует" );
										} else {
											$tmp = $this->hCommon->AddObject( array( $mxdCurrentData[ "current_field" ] ), array( FHOV_TABLE => "ud_fld" ) );
											if ( $tmp->HasResult( ) ) {
												$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
											} else {
												Redirect( $objCMS->GetPath( "root_relative" )."/user/fields/" );
											}
										}
									}
								} break;
								case "upd": {
									$arrIndex = $mxdCurrentData[ "current_field" ]->GetAttributeIndexList( FLEX_FILTER_FORM );
									$arrFilter = array(
										"id" => $arrIndex[ "id" ],
										"type" => $arrIndex[ "type" ],
										"name" => $arrIndex[ "name" ],
										"options" => $arrIndex[ "options" ]
									);
									if ( isset( $arrData[ $arrIndex[ "name" ] ], $mxdCurrentData[ "field_list" ][ $arrData[ $arrIndex[ "name" ] ] ] ) ) {
										$mxdCurrentData[ "current_field" ] = clone $mxdCurrentData[ "field_list" ][ $arrData[ $arrIndex[ "name" ] ] ];
										$fltArray = new CArrayFilter( );
										$arrData = $fltArray->Apply( $arrData );
										$tmp = $mxdCurrentData[ "current_field" ]->Create( $arrData, FLEX_FILTER_FORM );
										if ( $tmp->HasError( ) ) {
											$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
										} else {
											$tmp = $this->hCommon->UpdObject( array( $mxdCurrentData[ "current_field" ] ), array( FHOV_TABLE => "ud_fld", FHOV_INDEXATTR => "id" ) );
											if ( $tmp->HasResult( ) ) {
												$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
											} else {
												Redirect( $objCMS->GetPath( "root_relative" )."/user/fields/" );
											}
										}
									}
								} break;
								case "del": {
									/**
									 * 1. Удаляем все значения данного поля
									 * 2. Удаляем поле
									 */
									$arrIndex = $mxdCurrentData[ "current_field" ]->GetAttributeIndexList( FLEX_FILTER_FORM );
									$arrFilter = array(
										"id" => $arrIndex[ "id" ],
										"type" => $arrIndex[ "type" ],
										"name" => $arrIndex[ "name" ],
										"options" => $arrIndex[ "options" ]
									);
									if ( isset( $arrData[ $arrIndex[ "name" ] ], $mxdCurrentData[ "field_list" ][ $arrData[ $arrIndex[ "name" ] ] ] ) ) {
										$mxdCurrentData[ "current_field" ] = clone $mxdCurrentData[ "field_list" ][ $arrData[ $arrIndex[ "name" ] ] ];
										$objFieldValue = new CCFValue( );
										$szFldIdIndex = $objFieldValue->GetAttributeIndex( "fld_id", NULL, FLEX_FILTER_DATABASE );
										$tmp = $this->hCommon->GetObject( array(
											FHOV_WHERE => "`".$szFldIdIndex."`=".$mxdCurrentData[ "current_field" ]->id,
											FHOV_ONLYATTR => array( "id" ),
											FHOV_TABLE => "ud_cfv", FHOV_OBJECT => "CCFValue",
										) );
										if ( $tmp->HasResult( ) ) {
											$this->hCommon->DelObject( $tmp->GetResult( ), array( FHOV_TABLE => "ud_cfv" ) );
										}
										$this->hCommon->DelObject( array( $mxdCurrentData[ "current_field" ] ), array( FHOV_TABLE => "ud_fld" ) );
									}
								} break;
							}
						}
					}
					if ( empty( $arrErrors ) ) {
						Redirect( $objCMS->GetPath( "root_relative" )."/user/fields/" );
					}
				}
				$objCMS->SetWGIState( MF_CURRENT | MF_THIS );
				//
			} elseif ( preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\//', $szQuery ) ) {
				$tmp = NULL;
				// выборка текущего юзверя
				preg_match( '/^\/user\/([0-9a-zA-Z]{1,20})\//', $szQuery, $tmp );
				$szClientLogin = $tmp[ 1 ];
				$tmp = new CClient( );
				$szLoginIndex = $tmp->GetAttributeIndex( "login", NULL, FLEX_FILTER_FORM );
				$tmp1 = $tmp->Create( array( $szLoginIndex => $szClientLogin ), FLEX_FILTER_FORM );
				$szLoginValue = $tmp->GetAttributeValue( "login", FLEX_FILTER_DATABASE );
				$tmp1 = $this->hClient->GetObject( array( FHOV_WHERE => "`".$szLoginIndex."`=".$szLoginValue, FHOV_TABLE => "ud_client", FHOV_OBJECT => "CClient", FHOV_INDEXATTR => "id" ) );
				if ( $tmp1->HasResult( ) ) {
					$szCurrentMode = "Edit";
					$objCMS->SetWGIState( MF_CURRENT | MF_THIS );
					/**
					 * В этом случае нужно учесть, что редактируется текущий юзверь,
					 * а данные вводятся новые, где может смениться логин,
					 * что может привести к разрушению ссылок 
					 */
					$tmp2 = $tmp1->GetResult( );
					$mxdCurrentData = array( "current_user" => current( $tmp2 ) );
					//
					$tmp = $objCMS->GetLinkObjects( $mxdCurrentData[ "current_user" ]->graph_vertex_id );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( );
						$tmpZones = array( );
						foreach( $tmp as $i => $v ) {
							if ( intval( $v->label ) == WGI_ZONE ) {
								$tmpZones[ ] = $v->id;
							}
						}
						$hZone = new CFlexHandler( );
						$hZone->Create( array( "database" => $objCMS->database ) );
						$tmpZone = new CFileZone( );
						$szIdIndex = $tmpZone->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_DATABASE );
						$szTypeIndex = $tmpZone->GetAttributeIndex( "type", NULL, FLEX_FILTER_DATABASE );
						$arrOptions = array(
							FHOV_WHERE => "`".$szIdIndex."` IN(".join( ",", $tmpZones ).")",
							//FHOV_WHERE => "`".$szTypeIndex."`=".FZT_DIRECT." AND `".$szIdIndex."` IN(".join( ",", $tmpZones ).")",
							FHOV_TABLE => "ud_zone", FHOV_OBJECT => "CFileZone"
						);
						$tmp = $hZone->GetObject( $arrOptions );
						if ( $tmp->HasResult( ) ) {
							$tmp = $tmp->GetResult( );
							$szZonesIndex = $mxdCurrentData[ "current_user" ]->GetAttributeIndex( "zones" );
							$mxdCurrentData[ "current_user" ]->Create( array( $szZonesIndex => $tmp ) );
						}
					}
					
					// выгребем дополнительные поля учетки
					$mxdCurrentData[ "field_list" ] = array( );
					$tmp = $this->GetClientFields( $mxdCurrentData[ "current_user" ]->id );
					if ( $tmp->HasResult( ) ) {
						$mxdCurrentData[ "field_list" ] = $tmp->GetResult( "fields" );
					}
					
					if ( preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\/add_domain\//', $szQuery ) ) {
						if ( preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\/add_domain\/[^\/]*\//', $szQuery ) ) {
							$tmp = NULL;
							preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\/add_domain\/([^\/]*)\//', $szQuery, $tmp );
							$szName = $tmp[ 1 ];
							$arrZones = $mxdCurrentData[ "current_user" ]->zones;
							foreach( $arrZones as $v ) {
								if ( $v->name === $szName ) {
									$mxdCurrentData[ "filezone" ] = $v;
									break;
								}
							}
							$szCurrentMode = "DomainReg";
						} else {
							// страница добавления домена
							$modZone = new CHModZone( );
							$objFileZone = new CFileZone( );
							$szNameIndex = $objFileZone->GetAttributeIndex( "name", NULL, FLEX_FILTER_FORM );
							
							$modServer = new CHModLink( );
							$iN = $modServer->ServerCount( );
							if ( $iN ) {
								$modZone = new CHModZone( );
								$iN = $modZone->ExistsTplSoa( );
								if ( $iN ) {
									if ( isset( $_GET[ $szNameIndex ] ) ) {
										// ввели доменное имя
										$szZoneName = @strval( $_GET[ $szNameIndex ] );
										$tmp = $objFileZone->Create( array( $szNameIndex => $szZoneName ) );
										$mxdCurrentData[ "filezone" ] = $objFileZone;
										if ( $tmp->HasError( ) ) {
											$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
										} else {
											if ( $modZone->FreeDomain( $objFileZone->GetAttributeValue( "name" ) ) ) {
												// домен свободен
												//if ( isset( $_GET[ "reg" ] ) && intval( $_GET[ "reg" ] ) ) {
													$mxdLinks[ "reg" ] = true;
													$mxdLinks[ "zone_name" ] = $szZoneName;
												//} else {
													//$mxdLinks = array( "url" => "?zone_name=".$szZoneName."&reg=1" );
												//}
											} else {
												// домен занят
												$arrErrors[ ] = new CError( 100, "Доменное имя занято" );
											}
										}
									}
									if ( count( $_POST ) ) {
										$arrData = $_POST;
										$tmp = $modZone->AddFileZone( $arrData, $mxdCurrentData, FLEX_FILTER_FORM );
										if ( $tmp->HasError( ) ) {
											$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
										} else {
											$tmp = $tmp->GetResult( "zone_name" );
											DumbMail( "Added New Zone", '', $mxdCurrentData[ "current_user" ]->email, "Was added new zone for your account. See details at http://".$_SERVER[ "HTTP_HOST" ]."/" );
											Redirect( $objCMS->GetPath( "root_relative" )."/user/".$mxdCurrentData[ "current_user" ]->login."/add_domain/".$tmp."/" );
										}
									}
								} else {
									$arrErrors[ ] = new CError( 1, "Отсутствует шаблон SOA записи" );
								}
							} else {
								$arrErrors[ ] = new CError( 1, "Осутствуют сервера" );
							}
							
							$szCurrentMode = "Domain";
						}
					} elseif ( preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\/add_zone\//', $szQuery ) ) {
						if ( preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\/add_zone\/[^\/]*\//', $szQuery ) ) {
							$tmp = NULL;
							preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\/add_zone\/([^\/]*)\//', $szQuery, $tmp );
							$szName = $tmp[ 1 ];
							$arrZones = $mxdCurrentData[ "current_user" ]->zones;
							foreach( $arrZones as $v ) {
								if ( $v->name === $szName ) {
									$mxdCurrentData[ "filezone" ] = $v;
									break;
								}
							}
							$szCurrentMode = 'ZoneReg';
						} else {
							// страница добавления домена
							$modZone = new CHModZone( );
							$objFileZone = new CFileZone( );
							$szNameIndex = $objFileZone->GetAttributeIndex( 'name', NULL, FLEX_FILTER_FORM );
							
							$modServer = new CHModLink( );
							$iN = $modServer->ServerCount( );
							if ( $iN ) {
								$modZone = new CHModZone( );
								$iN = $modZone->ExistsTplSoa( );
								if ( $iN ) {
									if ( count( $_POST ) ) {
										$arrData = $_POST;
										$tmp = $modZone->AddFileZone2( $arrData, $mxdCurrentData, FLEX_FILTER_FORM );
										if ( $tmp->HasError( ) ) {
											$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
										} else {
											$tmp = $tmp->GetResult( 'zone_name' );
											DumbMail( 'Added New Zone', '', $mxdCurrentData[ 'current_user' ]->email, 'Was added new zone for your account. See details at http://'.$_SERVER[ 'HTTP_HOST' ].'/' );
											Redirect( $objCMS->GetPath( 'root_relative' ).'/user/'.$mxdCurrentData[ 'current_user' ]->login.'/add_zone/'.$tmp.'/' );
										}
									}
								} else {
									$arrErrors[ ] = new CError( 1, "Отсутствует шаблон SOA записи" );
								}
							} else {
								$arrErrors[ ] = new CError( 1, "Осутствуют сервера" );
							}
							
							$szCurrentMode = 'Zone';
						}
						//
					} elseif ( preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\/add_reverse\//', $szQuery ) ) {
						if ( preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\/add_reverse\/[^\/]*\//', $szQuery ) ) {
							$tmp = NULL;
							preg_match( '/^\/user\/[0-9a-zA-Z]{1,20}\/add_reverse\/([^\/]*)\//', $szQuery, $tmp );
							$iId = intval( $tmp[ 1 ] );
							$arrZones = $mxdCurrentData[ "current_user" ]->zones;
							foreach( $arrZones as $v ) {
								if ( $v->id === $iId ) {
									$mxdCurrentData[ "filezone" ] = $v;
									break;
								}
							}
							if ( isset( $mxdCurrentData[ "filezone" ] ) && $mxdCurrentData[ "filezone" ] ) {
								$szName = $mxdCurrentData[ "filezone" ]->name;
								if ( strpos( $szName, '/' ) !== false ) {
									$tmp = explode( '/', $szName );
									$iMask = intval( $tmp[ 1 ] );
									if ( $iMask > 24 ) {
										$mxdCurrentData[ "show_msg" ] = true;
									}
								}
							}
							$szCurrentMode = "ReverseReg";
						} else {
							// страница добавления домена
							$modZone = new CHModZone( );
							$objFileZone = new CFileZone( );
							$modServer = new CHModLink( );
							$iN = $modServer->ServerCount( );
							if ( $iN ) {
								$modZone = new CHModZone( );
								$iN = $modZone->ExistsTplSoa( );
								if ( $iN ) {
									$mxdCurrentData[ "reverse_zone_suffix" ] = $modZone->GetReverseSuffix( );
									$arrBlock = $mxdCurrentData[ "current_user" ]->GetIpBlockArray( );
									if ( !empty( $arrBlock ) ) {
										$mxdCurrentData[ "ip_block_list" ] = array( );
										$arrAllow = array( 8 => 8, 16 => 16, 24 => 24 );
										foreach( $arrBlock as $i => $v ) {
											$tmp = new CIpBlock( );
											$iMask = intval( $v[ "mask" ] );
											if ( isset( $arrAllow[ $iMask ] ) || ( $iMask > 24 ) ) {
												$szName = IpBlockReverseName( $v[ "ip" ]."/".$v[ "mask" ] );
												if ( $szName !== "" ) {
													$tmp->Create( array( "name" => $szName ) );
													$mxdCurrentData[ "ip_block_list" ][ ] = $tmp;
												}
											}
										}
									}
									if ( count( $_POST ) ) {
										$arrData = $_POST;
										$objFileZone = new CFileZone( );
										$szNameIndex = $objFileZone->GetAttributeIndex( 'name', NULL, FLEX_FILTER_FORM );
										$szName = trim( $arrData[ 'zone_name' ] );
										if ( get_magic_quotes_gpc( ) ) {
											$szName = stripslashes( $szName );
										}
										if ( $szName === '' && !isset( $arrData[ 'zone_block' ] ) ) {
											Redirect( $objCMS->GetPath( 'root_relative' ).'/user/'.$mxdCurrentData[ 'current_user' ]->login.'/add_reverse/' );
										}
										
										$bWasError = false;
										$bCheckAdditional = false;
										if ( $szName === '' ) {
											$szBlockName = $arrData[ 'zone_block' ];
											foreach( $mxdCurrentData[ 'ip_block_list' ] as $v ) {
												if ( $szBlockName === $v->name ) {
													$szName = $v->name;
												}
											}
											if ( $szName === '' ) {
												Redirect( $objCMS->GetPath( 'root_relative' ).'/user/'.$mxdCurrentData[ 'current_user' ]->login.'/add_reverse/' );
											}
											$arrData[ 'zone_name' ] = $szName;
											$bCheckAdditional = true;
										} else {
											if ( CValidator::IpMaskReverse( $szName ) ) {
												$szNetwork = IpReverseToNetwork( $szName );
												$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => 'ud_network', FHOV_OBJECT => 'CNetwork' ) );
												if ( $tmp->HasResult( ) ) {
													$tmp = $tmp->GetResult( );
													foreach( $tmp as $v ) {
														$szTmpNetwork = $v->ip.'/'.$v->mask;
														if ( CompareNetwork( $szNetwork, $szTmpNetwork ) && ( $szNetwork !== $szTmpNetwork ) ) {
															$arrErrors[ ] = new CError( 103, $szNetwork.' пересекается с существующей сетью: '.$szTmpNetwork );
															$bWasError = true;
															
														}
													}
												}
											} else {
												$arrErrors[ ] = new CError( 104, "Неверное имя обратной зоны" );
												$bWasError = true;
											}
										}
										
										$objFileZone->Create( $arrData, FLEX_FILTER_FORM );
										
										if ( $bWasError ) {
											$mxdCurrentData[ 'filezone' ] = $objFileZone;
										} else {
											$arrData[ $szNameIndex ] = $objFileZone->name;
											$tmp = $modZone->AddFileZone( $arrData, $mxdCurrentData, FLEX_FILTER_FORM, true, true );
											if ( $tmp->HasError( ) ) {
												$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
											} else {
												$tmp1 = explode( '/', $objFileZone->name );
												$tmp1 = explode( '.', $tmp1[ 1 ] );
												$tmp1 = intval( $tmp1[ 0 ] );
												if ( $tmp1 > 24 ) {
													$modZone->AdditionalReverse( $objFileZone->name, $mxdCurrentData );
												}
												$tmp = $tmp->GetResult( 'zone_id' );
												Redirect( $objCMS->GetPath( 'root_relative' ).'/user/'.$mxdCurrentData[ 'current_user' ]->login.'/add_reverse/'.$tmp.'/' );
											}
										}
									}
								} else {
									$arrErrors[ ] = new CError( 1, "Отсутствует шаблон SOA записи" );
								}
							} else {
								$arrErrors[ ] = new CError( 1, "Осутствуют сервера" );
							}
							//
							$szCurrentMode = "Reverse";
						}
						//
					} elseif ( !$tmp1->HasError( ) && count( $_POST ) && $mxdCurrentData !== NULL ) {
						// защита от кастомного запроса
						$arrData = $_POST;
						$szIdIndex = $mxdCurrentData[ "current_user" ]->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM );
						$arrData[ $szIdIndex ] = @strval( $mxdCurrentData[ "current_user" ]->id );
						$arrOptions = array( FHOV_IGNOREATTR => array( ) );
						$szPasswordIndex = $mxdCurrentData[ "current_user" ]->GetAttributeIndex( "password", NULL, FLEX_FILTER_FORM );
						if ( isset( $arrData[ $szPasswordIndex ] ) && ( $arrData[ $szPasswordIndex ] == "" ) ) {
							$arrData[ $szPasswordIndex ] = $mxdCurrentData[ "current_user" ]->password;
							$arrOptions[ FHOV_IGNOREATTR ] = array( "password" );
						}
						if ( !isset( $arrData[ $szPasswordIndex ] ) ) {
							$arrOptions[ FHOV_IGNOREATTR ] = array( "password" );
						}
						/**
						 * В этом контексте у учетки нельзя поменять некоторые атрибуты:
						 * 	1. id
						 * 	2. id вершины графа - служебный атрибут для системы
						 * 	3. зоны - служебный атрибут для xml
						 * 	4. даты ( рег, ред, лог ) - служебные атрибуты для системы
						 */
						$objInp = new CClient( );
						$arrFilter = array(
							"id" => $objInp->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
							"graph_vertex_id" => $objInp->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_FORM ),
							"zones" => $objInp->GetAttributeIndex( "zones", NULL, FLEX_FILTER_FORM ),
							"reg_date" => $objInp->GetAttributeIndex( "reg_date", NULL, FLEX_FILTER_FORM ),
							"last_edit" => $objInp->GetAttributeIndex( "last_edit", NULL, FLEX_FILTER_FORM ),
							"last_login" => $objInp->GetAttributeIndex( "last_login", NULL, FLEX_FILTER_FORM )
						);
						$fltArray = new CArrayFilter( $arrFilter );
						$arrData = $fltArray->Apply( $arrData );
						
						$arrData[ $arrFilter[ "id" ] ] = $mxdCurrentData[ "current_user" ]->id;
						$arrData[ $arrFilter[ "graph_vertex_id"  ] ] = $mxdCurrentData[ "current_user" ]->graph_vertex_id;
						$arrData[ $arrFilter[ "last_edit"  ] ] = date( "Y-m-d H:i:s" );
						
						$tmpResult = $objInp->Create( $arrData, FLEX_FILTER_FORM );
						if ( $tmpResult->HasError( ) ) {
							$mxdCurrentData[ "err_user" ] = $objInp;
							$arrErrors = array_merge( $arrErrors, $tmpResult->GetError( ) );
						} else {
							$bError = false;
							if ( $mxdCurrentData[ "current_user" ]->login !== $objInp->login ) {
								// смена логина требует проверки существования такого логина
								$szLoginIndex = $objInp->GetAttributeIndex( "login", NULL, FLEX_FILTER_DATABASE );
								$szLoginValue = $objInp->GetAttributeValue( "login", FLEX_FILTER_DATABASE );
								$tmp = $this->hClient->GetObject( array( FHOV_WHERE => $szLoginIndex."=".$szLoginValue, FHOV_TABLE => "ud_client", FHOV_OBJECT => "CClient" ) );
								if ( $tmp->HasResult( ) ) {
									$bError = true;
									$arrErrors[ ] = new CError( 1, "Учетная запись с таким логином уже существует" );
								}
							}
							if ( $mxdCurrentData[ 'current_user' ]->ip_block !== $objInp->ip_block ) {
								$arrIpBlock1 = $mxdCurrentData[ 'current_user' ]->GetIpBlockArray( );
								$arrIpBlock2 = $objInp->GetIpBlockArray( );
								$arrDelNetwork = array( );
								foreach( $arrIpBlock1 as $i => $v ) {
									$bFound = false;
									foreach( $arrIpBlock2 as $j => $w ) {
										if (  $v === $w ) {
											$bFound = true;
										}
									}
									if ( !$bFound ) {
										$arrDelNetwork[ ] = $v;
									}
								}
								$arrAddNetwork = array( );
								foreach( $arrIpBlock2 as $i => $v ) {
									$bFound = false;
									foreach( $arrIpBlock1 as $j => $w ) {
										if (  $v === $w ) {
											$bFound = true;
										}
									}
									if ( !$bFound ) {
										$arrAddNetwork[ ] = $v;
									}
								}
								//
								$tmp = new CNetwork( );
								$arrIndex = array( );
								$arrIndex[ FLEX_FILTER_FORM ] = $tmp->GetAttributeIndexList( FLEX_FILTER_FORM );
								$arrIndex[ FLEX_FILTER_DATABASE ] = $tmp->GetAttributeIndexList( FLEX_FILTER_DATABASE );
								$arrToDel = array( );
								foreach( $arrDelNetwork as $i => $v ) {
									$tmp->Create( array(
										$arrIndex[ FLEX_FILTER_FORM ][ 'ip' ] => $v[ 'ip' ],
										$arrIndex[ FLEX_FILTER_FORM ][ 'mask' ] => $v[ 'mask' ],
									) );
									$tmp1 = $this->hCommon->GetObject( array(
										FHOV_WHERE => '`'.$arrIndex[ FLEX_FILTER_DATABASE ][ 'ip' ].'`='.$tmp->GetAttributeValue( 'ip', FLEX_FILTER_DATABASE ).' AND `'.$arrIndex[ FLEX_FILTER_DATABASE ][ 'mask' ].'`='.$tmp->GetAttributeValue( 'mask', FLEX_FILTER_DATABASE ),
										FHOV_TABLE => 'ud_network', FHOV_OBJECT => 'CNetwork'
									) );
									if ( $tmp1->HasResult( ) ) {
										$tmp1 = $tmp1->GetResult( );
										$tmp1 = current( $tmp1 );
										$arrToDel[ $tmp1->ip.'/'.$tmp1->mask ] = $tmp1;
										//$objCMS->DelFromWorld( array( $tmp1->graph_vertex_id ) );
										//$this->hCommon->DelObject( array( $tmp1 ), array( FHOV_TABLE => 'ud_network' ) );
									}
									$tmp = new CNetwork( );
								}
								$arrToAdd = array( );
								foreach( $arrAddNetwork as $i => $v ) {
									$tmp->Create( array(
										$arrIndex[ FLEX_FILTER_FORM ][ 'ip' ] => $v[ 'ip' ],
										$arrIndex[ FLEX_FILTER_FORM ][ 'mask' ] => $v[ 'mask' ],
									) );
									$tmp1 = $this->hCommon->GetObject( array(
										FHOV_WHERE => '`'.$arrIndex[ FLEX_FILTER_DATABASE ][ 'ip' ].'`='.$tmp->GetAttributeValue( 'ip', FLEX_FILTER_DATABASE ).' AND `'.$arrIndex[ FLEX_FILTER_DATABASE ][ 'mask' ].'`='.$tmp->GetAttributeValue( 'mask', FLEX_FILTER_DATABASE ),
										FHOV_TABLE => 'ud_network', FHOV_OBJECT => 'CNetwork'
									) );
									if ( $tmp1->HasResult( ) ) {
										$tmp1 = $tmp1->GetResult( );
										$tmp1 = current( $tmp1 );
										$bError = true;
										//$objCMS->DelFromWorld( array( $tmp1->graph_vertex_id ) );
										//$this->hCommon->DelObject( array( $tmp1 ), array( FHOV_TABLE => 'ud_network' ) );
									} else {
										$arrToAdd[ ] = $tmp;
									}
									$tmp = new CNetwork( );
								}
								if ( !empty( $arrToAdd ) ) {
									$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => 'ud_network', FHOV_OBJECT => 'CNetwork' ) );
									if ( $tmp->HasResult( ) ) {
										$tmp = $tmp->GetResult( );
										/**
										 * 1. если есть персечение, то проверяем, а не на удалении ли участок из БД
										 * 2. если участок пересекается и поставлен на удаление, то все пучком
										 */
										foreach( $tmp as $i => $v ) {
											foreach( $arrToAdd as $j => $w ) {
												if ( CompareNetwork( $v->ip.'/'.$v->mask, $w->ip.'/'.$w->mask ) && ( !isset( $arrToDel[ $v->ip.'/'.$v->mask ] ) ) ) {
													$bError = true;
													$arrErrors[ $w->ip.'/'.$w->mask ] = new CError( 1, $w->ip.'/'.$w->mask.' пересекается с существующей сетью: '.$v->ip.'/'.$v->mask );
												}
											}
										}
									}
								}
								unset( $tmp );
								if ( !$bError ) {
									$arrIds = array( );
									foreach( $arrToDel as $v ) {
										$arrIds[ $v->graph_vertex_id ] = $v->graph_vertex_id;
									}
									$objCMS->DelFromWorld( $arrIds );
									$this->hCommon->DelObject( $arrToDel, array( FHOV_TABLE => 'ud_network' ) );
									
									foreach( $arrToAdd as &$v ) {
										$tmp = $objCMS->AddToWorld( WGI_NETWORK, 'NetworkList/Network' );
										if ( $tmp->HasResult( ) ) {
											$iNetworkVId = $tmp->GetResult( 'graph_vertex_id' );
											$v->Create( array( 'network_graph_vertex_id' => $iNetworkVId ) );
											$objCMS->LinkObjects( $mxdCurrentData[ 'current_user' ]->graph_vertex_id, $iNetworkVId, 'User/Network' );
										}
									}
									$this->hCommon->AddObject( $arrToAdd, array( FHOV_TABLE => 'ud_network' ) );
								}
								//ShowVarD( $bError, $arrIpBlock1, $arrIpBlock2, $arrToDel, $arrToAdd );
							}
							if ( $bError ) {
								$mxdCurrentData[ "err_user" ] = $objInp;
							} else {
								$arrOptions[ FHOV_TABLE ] = 'ud_client';
								$arrOptions[ FHOV_INDEXATTR ] = 'id';
								$arrOptions[ FHOV_IGNOREATTR ] = array_merge( $arrOptions[ FHOV_IGNOREATTR ], array( "id", "graph_vertex_id", "reg_date", "last_login", "zones", "fields" ) );
								$tmp = $this->hClient->UpdObject( array( $objInp ), $arrOptions );
								if ( $tmp->HasError( ) ) {
									$mxdCurrentData[ "err_user" ] = $objInp;
									$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
								} else {
									if ( isset( $arrData[ "fld" ] ) ) {
										$this->ProcClientFields( $objInp, $mxdCurrentData[ "field_list" ], $arrData[ "fld" ] );
									}
									DumbMail( "Changed Account Data", '', $mxdCurrentData[ "current_user" ]->email, "Your account settings were changed. See details at http://".$_SERVER[ "HTTP_HOST" ]."/" );
									Redirect( $objCMS->GetPath( "root_relative" )."/user/".$objInp->login."/" );
								}
							}
						}
					}
				}
				//
			} elseif ( preg_match( '/^\/admins\//', $szQuery ) && ( $iCurrentSysRank == SUR_ADMIN || $iCurrentSysRank == SUR_SUPERADMIN ) ) {
				$szCurrentMode = "AdminList";
				$objCMS->SetWGI( WGI_ADMINS );
				$objCMS->SetWGIState( MF_THIS );
				
				if ( preg_match( '/^\/admins\/\+\//', $szQuery ) ) {
					$szCurrentMode = "AdminEdit";
					$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
					$mxdCurrentData[ "current_admin" ] = new CAdmin( );
					$szRankIndex = $mxdCurrentData[ "current_admin" ]->GetAttributeIndex( "rank", NULL, FLEX_FILTER_FORM );
					
					if ( count( $_POST ) ) {
						$arrData = $_POST;
						if ( $iCurrentSysRank == SUR_ADMIN ) {
							$arrData[ $szRankIndex ] = UR_OPERATOR;
						}
						$this->AddAdmin( $arrData, $mxdCurrentData, $arrErrors );
					}
					//
				} elseif ( preg_match( '/^\/admins\/[0-9a-zA-Z]{1,20}\//', $szQuery ) ) {
					$tmp = NULL;
					preg_match( '/^\/admins\/([0-9a-zA-Z]{1,20})\//', $szQuery, $tmp );
					$szLogin = $tmp[ 1 ];
					$objAdmin = new CAdmin( );
					$szLoginIndex = $objAdmin->GetAttributeIndex( "login", NULL, FLEX_FILTER_FORM );
					$tmp = $objAdmin->Create( array( $szLoginIndex => $szLogin ), FLEX_FILTER_FORM );
					$arrOptions = array(
						FHOV_WHERE => "`".$szLoginIndex."`=".$objAdmin->GetAttributeValue( "login", FLEX_FILTER_DATABASE ),
						FHOV_TABLE => "ud_admin",
						FHOV_INDEXATTR => "id",
						FHOV_OBJECT => "CAdmin"
					);
					$tmp = $this->hAdmin->GetObject( $arrOptions );
					if ( $tmp->HasResult( ) ) {
						// такой админ существует
						$szCurrentMode = "AdminEdit";
						$objCMS->SetWGIState( MF_CURRENT | MF_THIS );
						$tmp = $tmp->GetResult( );
						$objCurAdmin = current( $tmp );
						$mxdCurrentData[ "current_admin" ] = clone $objCurAdmin;
						
						if ( count( $_POST ) ) {
							$arrData = $_POST;
							$arrOptions = array( FHOV_IGNOREATTR => array( ) );
							$szPasswordIndex = $mxdCurrentData[ "current_admin" ]->GetAttributeIndex( "password", NULL, FLEX_FILTER_FORM );
							if ( isset( $arrData[ $szPasswordIndex ] ) && ( $arrData[ $szPasswordIndex ] == "" ) ) {
								$arrData[ $szPasswordIndex ] = $mxdCurrentData[ "current_admin" ]->password;
								$arrOptions[ FHOV_IGNOREATTR ] = array( "password" );
							}
							if ( !isset( $arrData[ $szPasswordIndex ] ) ) {
								$arrOptions[ FHOV_IGNOREATTR ] = array( "password" );
							}
							$arrFilter = array(
								"id" => $objCurAdmin->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
								"graph_vertex_id" => $objCurAdmin->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_FORM ),
								"reg_date" => $objCurAdmin->GetAttributeIndex( "reg_date", NULL, FLEX_FILTER_FORM ),
								"last_edit" => $objCurAdmin->GetAttributeIndex( "last_edit", NULL, FLEX_FILTER_FORM ),
								"last_login" => $objCurAdmin->GetAttributeIndex( "last_login", NULL, FLEX_FILTER_FORM ),
							);
							$fltArray = new CArrayFilter( );
							$arrData = $fltArray->Apply( $arrData );
							$arrData[ $arrFilter[ "last_edit"  ] ] = date( "Y-m-d H:i:s" );
							$tmp = $objCurAdmin->Create( $arrData, FLEX_FILTER_FORM );
							if ( $tmp->HasError( ) ) {
								$mxdCurrentData[ "err_admin" ] = $objCurAdmin;
								$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
							} else {
								$bError = false;
								if ( $mxdCurrentData[ "current_admin" ]->login !== $objCurAdmin->login ) {
									// смена логина требует проверки существования такого логина
									$tmp = $this->GetUser( $objCurAdmin->login, FLEX_FILTER_FORM );
									if ( $tmp->HasResult( ) ) {
										$bError = true;
										$arrErrors[ ] = new CError( 1, "Учетная запись с таким логином уже существует" );
									}
								}
								if ( $bError ) {
									$mxdCurrentData[ "err_admin" ] = $objCurAdmin;
								} else {
									$arrOptions[ FHOV_TABLE ] = "ud_admin";
									$arrOptions[ FHOV_INDEXATTR ] = "id";
									$arrOptions[ FHOV_IGNOREATTR ] = array_merge( $arrOptions[ FHOV_IGNOREATTR ], array( "id", "graph_vertex_id", "reg_date", "last_login" ) );
									$tmp = $this->hAdmin->UpdObject( array( $objCurAdmin ), $arrOptions );
									if ( $tmp->HasError( ) ) {
										$mxdCurrentData[ "err_user" ] = $objCurAdmin;
										$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
									} else {
										Redirect( $objCMS->GetPath( "root_relative" )."/admins/" );
									}
								}
							}
						}
					}
					//
				} elseif ( count( $_POST ) ) {
					$arrData = $_POST;
					if ( isset( $arrData[ "del" ] ) && is_array( $arrData[ "del" ] ) ) {
						$arrOptions[ "ids" ] = $arrData[ "del" ];
						$this->DelAdmin( $arrOptions, $arrErrors );
					}
					Redirect( $objCMS->GetPath( "root_relative" )."/admins/" );
				}
				if ( $mxdCurrentData === NULL ) {
					$mxdCurrentData[ "admin_list" ] = array( );
					$tmp = array(
						SUR_ADMIN => UR_ADMIN,
						SUR_SUPERADMIN => UR_SUPERADMIN,
						SUR_OPERATOR => UR_OPERATOR
					);
					$iLimitRank = $tmp[ $iCurrentSysRank ];
					$tmp = $this->hAdmin->GetObject( array( FHOV_WHERE => "`admin_rank`>".$iLimitRank, FHOV_TABLE => "ud_admin", FHOV_INDEXATTR => "id", FHOV_OBJECT => "CAdmin" ) );
					if ( $tmp->HasResult( ) ) {
						$mxdCurrentData[ "admin_list" ] = $tmp->GetResult( );
					}
				}
				//
			} elseif ( count( $_POST ) && isset( $_POST[ "users" ] ) ) {
					// сначала сносим юзверей
					$bRedir = true;
					$arrToUpd = array( );
					
					if ( isset( $_POST[ "del" ] ) && is_array( $_POST[ "del" ] ) ) {
						$arrOptions[ "ids" ] = $_POST[ "del" ];
						$this->DelClient( $arrOptions, $arrErrors );
					}
					// потом обновляем оставшихся
					$tmp = $_POST[ "users" ];
					foreach( $tmp as $i => $v ) {
						if ( is_int( $i ) && !isset( $arrToDel[ $i ] ) ) {
							$arrToUpd[ $i ] = $v;
						}
					}
					
					$tmp1 = array_keys( $arrToUpd );
					$arrOptions = array( FHOV_WHERE => "client_id IN(".join( ",", $tmp1 ).")", FHOV_TABLE => "ud_client", FHOV_OBJECT => "CClient" );
					$tmp = $this->hClient->GetObject( $arrOptions );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( );
						$tmp1 = array( );
						$oldState = array( );
						foreach( $tmp as $v ) {
							$oldState[ $v->id ] = $v->state;
							if ( isset( $arrToUpd[ $v->id ] ) ) {
								$v->Create( $arrToUpd[ $v->id ] );
								$tmp1[ ] = $v;
							}
						}
						if ( !empty( $tmp1 ) ) {
							$arrOptions = array(
								FHOV_ONLYATTR => array( "state" ),
								FHOV_TABLE => "ud_client",
								FHOV_INDEXATTR => "id"
							);
							$tmp = $this->hClient->UpdObject( $tmp1, $arrOptions );
							if ( $tmp->HasError( ) ) {
								$bRedir = false;
								$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
							} else {
								foreach( $tmp1 as $v ) {
									if ( $v->state !== US_NOTACTIVE && ( $oldState[ $v->id ] !== $v->state ) ) {
										DumbMail( "Changed Account Data", '', $v->email, "Your account settings were changed. See details at http://".$_SERVER[ "HTTP_HOST" ]."/" );
									}
								}
							}
						}
					}
					if ( $bRedir ) {
						Redirect( $objCMS->GetPath( 'root_relative' ).'/user/' );
					}
			}

			if ( $mxdCurrentData === NULL ) {
				$mxdCurrentData = array( 'filter' => NULL, 'user_list' => array( ) );
				$arrOptions = array( FHOV_TABLE => 'ud_client', FHOV_OBJECT => 'CClient' );
				//
				$objFilter = new CClientFilter( );
				$objFilter->Create( $_GET, FLEX_FILTER_FORM );
				$szWhere = $objFilter->GetWhere( );
				if ( $szWhere !== '' ) {
					$arrOptions[ FHOV_WHERE ] = $szWhere;
				}
				$mxdCurrentData[ 'filter' ] = $objFilter;
				$szUrl = $objFilter->GetUrlAttr( );
				if ( $szUrl === '' ) {
					$szUrl = $objCMS->GetPath( 'root_relative' ).'/user/?';
				} else {
					$szUrl = $objCMS->GetPath( 'root_relative' )."/user/?$szUrl&";
				}
				//
				$iCount = $this->hClient->CountObject( $arrOptions );
				$iCount = $iCount->GetResult( 'count' );
				$objPager = new CPager( );
				$arrData = array(
					'url' => $szUrl,
					'page' => @$_GET[ 'page' ],
					'page_size' => 15,
					'total' => $iCount
				);
				$objPager->Create( $arrData, FLEX_FILTER_FORM );
				$szLimit = $objPager->GetSQLLimit( );
				if ( $szLimit !== '' ) {
					$arrOptions[ FHOV_LIMIT ] = $szLimit;
				}
				//
				$tmp = $this->hClient->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					$mxdCurrentData[ 'user_list' ] = $tmp->GetResult( );
					$mxdCurrentData[ 'pager' ] = $objPager;
				}
			}
			// передаем управление приложению
			$szFolder = $objCMS->GetPath( 'root_application' );
			if ( $szFolder !== false && file_exists( "$szFolder/index.php" ) ) {
				include_once( "$szFolder/index.php" );
			}
			return true;
		} // function Process
		
		/**
		 * 	Проверка верности имени обратной зоны
		 * 	@param $szName string имя обратной зоны
		 * 	@param $arrBlock array набор блоков ip ( сетей )
		 * 	@return CResult
		 */
		public function CheckReverseName( $szName, $arrBlock ) {
			$objRet = new CResult( );
			if ( preg_match( '/((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){0,3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/', $szName ) ) {
				/*
				if ( !empty( $arrBlock ) ) {
					$tmp = explode( ".", $szName );
					$n = count( $tmp ) - 1;
					$szIp = array( '0', '0', '0', '0' );
					for( $i = $n; $i > -1; --$i ) {
						$szIp[ $n - $i ] = $tmp[ $i ];
					}
					$szIp = join( ".", $szIp ); // получили ip сети
					$bFound = false;
					foreach( $arrBlock as $i => $v ) {
						if ( ( $szIp === $v[ "ip" ] ) || ( IpApplyMask( $szIp, $v[ "mask" ] ) === $v[ "ip" ] ) ) {
							$bFound = true;
							break;
						}
					}
					if ( !$bFound ) {
						$objRet->AddError( new CError( 1, "Данный адрес не принадлежит клиенту" ) );
					}
				}
				//*/
			} else {
				$objRet->AddError( new CError( 1, "Не допустимое имя обратной зоны" ) );
			}
			return $objRet;
		} // function CheckReverseName
		
		/**
		 * 	Обработка клиентских дополнительных полей
		 */
		public function ProcClientFields( $objClient, $arrFields, $arrFieldValues ) {
			if ( !empty( $arrFields ) && is_array( $arrFieldValues ) ) {
				if ( $this->hCommon === NULL ) {
					$this->InitObjectHandler( );
				}
				$tmp = new CCFValue( );
				$arrIndex = $tmp->GetAttributeIndexList( FLEX_FILTER_FORM );
				$iClientId = $objClient->id;
				$arrAdd = array( );
				$arrUpd = array( );
				$arrCurValues = $this->GetClientFieldValues( $iClientId );
				$arrCurValues = $arrCurValues->GetResult( );
				foreach( $arrFields as $i => $v ) {
					if ( isset( $arrFieldValues[ $v->name ] ) && ( trim( $arrFieldValues[ $v->name ] ) !== "" ) ) {
						$tmp = new CCFValue( );
						$arrInput = array(
							$arrIndex[ "fld_id" ] => $v->id,
							$arrIndex[ "cl_id" ] => $iClientId,
							$arrIndex[ "value" ] => trim( $arrFieldValues[ $v->name ] )
						);
						if ( $v->value === "" ) {
							$tmp->Create( $arrInput, FLEX_FILTER_FORM );
							$arrAdd[ ] = $tmp;
						} elseif ( isset( $arrCurValues[ $v->id ] ) ) {
							$arrInput[ $arrIndex[ "id" ] ] = $arrCurValues[ $v->id ]->id;
							$tmp->Create( $arrInput, FLEX_FILTER_FORM );
							$arrUpd[ ] = $tmp;
						}
					}
				}
				if ( !empty( $arrAdd ) ) {
					$tmp = $this->hCommon->AddObject( $arrAdd, array( FHOV_TABLE => "ud_cfv" ) );
					if ( $tmp->HasError( ) ) {
						//ShowVarD( $tmp->GetError( ) );
					}
				}
				if ( !empty( $arrUpd ) ) {
					$tmp = $this->hCommon->UpdObject( $arrUpd, array( FHOV_TABLE => "ud_cfv", FHOV_INDEXATTR => "id" ) );
					if ( $tmp->HasError( ) ) {
						//ShowVarD( $tmp->GetError( ) );
					}
				}
			}
		} // function ProcClientFields
		
		/**
		 * 	Получение списка дополнительных полей
		 */
		public function GetClientFieldList( ) {
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$tmp = $this->hCommon->GetObject( array(
				FHOV_TABLE => "ud_fld", FHOV_OBJECT => "CClientField", FHOV_INDEXATTR => "name"
			) );
			return $tmp;
		} // function GetClientFieldList
		
		/**
		 * 	Получение значений доп полей клиента, индексируются по id поля
		 */
		public function GetClientFieldValues( $iClientId ) {
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$iClientId = intval( $iClientId );
			$objFieldValue = new CCFValue( );
			$szClientIdIndex = $objFieldValue->GetAttributeIndex( "cl_id", NULL, FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => "`".$szClientIdIndex."`=".$iClientId,
				FHOV_TABLE => "ud_cfv", FHOV_OBJECT => "CCFValue", FHOV_INDEXATTR => "fld_id",
			) );
			return $tmp;
		} // function GetClientFieldValues
		
		/**
		 * 	Возвращает набор доп полей для учетки
		 */
		public function GetClientFields( $iClientId ) {
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$objRet = new CResult( );
			$tmp = $this->GetClientFieldList( );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$arrExtFields = array( );
				foreach( $tmp as $i => $v ) {
					$tmp2 = new CClientFieldValue( );
					$tmp2->Create( array(
						"id" => $v->id,
						"type" => $v->type,
						"name" => $v->name,
						"title" => $v->title,
						"value" => "",
						"options" => $v->options
					) );
					$arrExtFields[ $i ] = $tmp2;
				}
				$tmp = $this->GetClientFieldValues( $iClientId );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					foreach( $arrExtFields as $i => $v ) {
						if ( isset( $tmp[ $v->id ] ) ) {
							$arrExtFields[ $i ]->Create( array( "value" => $tmp[ $v->id ]->value ), FLEX_FILTER_DATABASE );
						}
					}
				}
				$objRet->AddResult( $arrExtFields, "fields" );
			}
			return $objRet;
		} // function GetClientFields
		
		/**
		 * 	Добавление клиентской учетки
		 * 	@param $arrData array набор данных
		 * 	@param $mxdCurrentData CClient текущие данные клиента
	 	 *	@param $arrErrors array массив для заполнения ошибок
	 	 *	@return void
		 */
		public function AddClient( $arrData, &$mxdCurrentData, &$arrErrors, $bSaveLog = true ) {
			global $objCMS;
			$iWgi = $objCMS->wgi;
			$objCMS->SetWGIState( WGI_USER );
			$objNewUser = new CClient( );
			$fltArray = new CArrayFilter( );
			$arrFilter = array(
				"id" => $objNewUser->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
				"graph_vertex_id" => $objNewUser->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_FORM ),
				"reg_date" => $objNewUser->GetAttributeIndex( "reg_date", NULL, FLEX_FILTER_FORM ),
				"last_edit" => $objNewUser->GetAttributeIndex( "last_edit", NULL, FLEX_FILTER_FORM ),
				"last_login" => $objNewUser->GetAttributeIndex( "last_login", NULL, FLEX_FILTER_FORM ),
			);
			$fltArray->SetArray( $arrFilter );
			$arrData = $fltArray->Apply( $arrData );
			$szDate = date( "Y-m-d H:i:s" );
			$arrData[ $arrFilter[ "reg_date" ] ] = substr( $szDate, 0, 10 );//date( "Y-m-d" );
			$arrData[ $arrFilter[ "last_edit" ] ] = //$szDate;
			$arrData[ $arrFilter[ "last_login" ] ] = $szDate;
			$tmp = $objNewUser->Create( $arrData, FLEX_FILTER_FORM );
			if ( $tmp->HasError( ) ) {
				$mxdCurrentData[ "current_user" ] = $objNewUser;
				$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
				$objCMS->SetWGIState( $iWgi );
			} else {
				$tmp = $this->GetUser( $objNewUser->login, FLEX_FILTER_FORM );
				if ( $tmp->HasResult( ) ) {
					$mxdCurrentData[ "current_user" ] = $objNewUser;
					$arrErrors[ ] = new CError( 1, "Учетная запись с таким логином уже существует" );
					$objCMS->SetWGIState( $iWgi );
				} else {
					$arrAddedNetworks = array( );
					$szIpBlock = $objNewUser->ip_block;
					if ( !empty( $szIpBlock ) ) {
						$arrIpBlock = preg_split( '/\r\n|\r|\n/', $szIpBlock );
						$arrIpBlock = array_map( 'SplitIpMask', $arrIpBlock );
						foreach( $arrIpBlock as $i => $v ) {
							$arrIpBlock[ $i ][ 'network_ip'   ] = $v[ 'ip' ];
							$arrIpBlock[ $i ][ 'network_mask' ] = $v[ 'mask' ];
							unset( $arrIpBlock[ $i ][ 'ip' ], $arrIpBlock[ $i ][ 'mask' ] );
						}
						$bWasError = false;
						$tmp = $this->hCommon->GetObject( array(
							FHOV_TABLE => 'ud_network', FHOV_OBJECT => 'CNetwork'
						) );
						if ( $tmp->HasResult( ) ) {
							/**
							 * 1. Строим массив блоков
							 * 2. Сравниваем каждый блок с каждым на предмет пересечения
							 * 3. Если есть пересечение, то злобно ругаемся
							 */
							$tmp = $tmp->GetResult( );
							foreach( $arrIpBlock as $i => $v ) {
								$tmpX = new CNetwork( );
								$tmpX->Create( $v );
								$arrIpBlock[ $i ] = $tmpX;
							}
							
							foreach( $tmp as $i => $v ) {
								foreach( $arrIpBlock as $j => $w ) {
									if ( CompareNetwork( $v->ip.'/'.$v->mask, $w->ip.'/'.$w->mask ) ) {
										$bWasError = true;
										$arrErrors[ $w->ip ] = new CError( 1, $w->ip.'/'.$w->mask.' пересекается с уже существующей сетью: '.$v->ip.'/'.$v->mask );
									} else {
										$arrAddedNetworks[ ] = $w;
									}
								}
							}
						} else {
							foreach( $arrIpBlock as $v ) {
								$tmp1 = new CNetwork( );
								$tmp = $tmp1->Create( $v );
								if ( $tmp->HasError( ) ) {
									$bWasError = true;
									$tmp = $tmp->GetError( );
									$tmp2 = array( );
									foreach( $tmp as $j => $w ) {
										$tmp2[ ] = $w->text;
									}
									$tmp2 = ''.$tmp1->ip.': '.join( ',', $tmp2 );
									$arrErrors[ $tmp1->ip ] = new CError( 1, $tmp2 );
								} else {
									$arrAddedNetworks[ ] = $tmp1;
								}
							}
						}
						if ( $bWasError ) {
							$mxdCurrentData[ 'current_user' ] = $objNewUser;
							return;
						}
					}
					$tmp = $objCMS->AddToWorld( WGI_USER, 'ModUser/Client' );
					if ( $tmp->HasResult( ) ) {
						$iClientVId = $tmp->GetResult( 'graph_vertex_id' );
						$tmp = $objNewUser->GetAttributeIndex( 'graph_vertex_id', NULL, FLEX_FILTER_DATABASE );
						$tmp = $objNewUser->Create( array( $tmp => $iClientVId ) );
						$tmp = $this->hClient->AddObject( array( $objNewUser ), array( FHOV_TABLE => 'ud_client', FHOV_IGNOREATTR => array( 'zones' ) ) );
						if ( $tmp->HasError( ) ) {
							// подчищаем, если возникли ошибки
							$mxdCurrentData[ 'current_user' ] = $objNewUser;
							$objCMS->DelFromWorld( array( $iClientVId ) );
							$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
							$objCMS->SetWGIState( $iWgi );
						} else {
							foreach( $arrAddedNetworks as $i => $v ) {
								$tmp = $objCMS->AddToWorld( WGI_NETWORK, 'NetworkList/Network' );
								if ( $tmp->HasResult( ) ) {
									$iNetworkVId = $tmp->GetResult( 'graph_vertex_id' );
									$szGVIdIndex = $arrAddedNetworks[ $i ]->GetAttributeIndex( 'graph_vertex_id', NULL, FLEX_FILTER_DATABASE );
									$arrAddedNetworks[ $i ]->Create( array( $szGVIdIndex => $iNetworkVId ) );
									$this->hCommon->AddObject( array( $arrAddedNetworks[ $i ] ), array( FHOV_TABLE => 'ud_network' ) );
									$objCMS->LinkObjects( $iClientVId, $iNetworkVId, 'User/Network' );
								}
							}
							if ( $bSaveLog ) {
								$modLogger = new CHModLogger( );
								$modLogger->AddLog(
									$objCMS->GetUserLogin( ),
									'ModUser',
									'ModUser::AddClient',
									'added new client, login: '.$objNewUser->login
								);
							}
							Redirect( $objCMS->GetPath( 'root_relative' ).'/user/' );
						}
					}
				}
			}
		} // function AddClient
		
		/**
		 * 	Добавление админской учетки
		 * 	@param $arrData array набор данных
		 * 	@param $mxdCurrentData mixed текущие данные админа
	 	 *	@param $arrErrors array массив для заполнения ошибок
	 	 *	@return void
		 */
		public function AddAdmin( $arrData, &$mxdCurrentData, &$arrErrors, $bSaveLog = true ) {
			global $objCMS;
			$arrFilter = array(
				"id" => $mxdCurrentData[ "current_admin" ]->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
				"graph_vertex_id" => $mxdCurrentData[ "current_admin" ]->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_FORM ),
				"reg_date" => $mxdCurrentData[ "current_admin" ]->GetAttributeIndex( "reg_date", NULL, FLEX_FILTER_FORM ),
				"last_edit" => $mxdCurrentData[ "current_admin" ]->GetAttributeIndex( "last_edit", NULL, FLEX_FILTER_FORM ),
				"last_login" => $mxdCurrentData[ "current_admin" ]->GetAttributeIndex( "last_login", NULL, FLEX_FILTER_FORM ),
			);
			$fltArray = new CArrayFilter( );
			$arrData = $fltArray->Apply( $arrData );
			$arrData[ $arrFilter[ "reg_date" ] ] = date( "Y-m-d" );
			$arrData[ $arrFilter[ "last_edit" ] ] = date( "Y-m-d H:i:s" );
			$tmp = $mxdCurrentData[ "current_admin" ]->Create( $arrData );
			if ( $tmp->HasError( ) ) {
				$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
			} else {
				$tmp = $this->GetUser( $mxdCurrentData[ "current_admin" ]->login, FLEX_FILTER_FORM );
				if ( $tmp->HasResult( ) ) {
					$arrErrors[ ] = new CError( 1, "Учетная запись с таким логином уже существует" );
				} else {
					$tmp = $objCMS->AddToWorld( WGI_USER, "ModUser/Admin" );
					if ( $tmp->HasResult( ) ) {
						$iAdminVId = $tmp->GetResult( "graph_vertex_id" );
						$tmp = $mxdCurrentData[ "current_admin" ]->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_DATABASE );
						$tmp = $mxdCurrentData[ "current_admin" ]->Create( array( $tmp => $iAdminVId ) );
						$tmp = $this->hClient->AddObject( array( $mxdCurrentData[ "current_admin" ] ), array( FHOV_TABLE => "ud_admin" ) );
						if ( $tmp->HasError( ) ) {
							// подчищаем, если возникли ошибки
							$objCMS->DelFromWorld( array( $iAdminVId ) );
							$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
						} else {
							if ( $bSaveLog ) {
								$modLogger = new CHModLogger( );
								$modLogger->AddLog(
									$objCMS->GetUserLogin( ),
									"ModUser",
									"ModUser::AddAdmin",
									"added new admin, login: ".$mxdCurrentData[ "current_admin" ]->login
								);
							}
							Redirect( $objCMS->GetPath( "root_relative" )."/admins/" );
						}
					}
				}
			}
		} // function AddAdmin
		
		/**
		 * 	Удаление клиентской учетки
		 * 	@return bool
		 */
		public function DelClient( $arrOptions, &$arrErros, $bSaveLog = true ) {
			global $objCMS;
			$bRedir = true;
			if ( isset( $arrOptions[ "ids" ] ) && is_array( $arrOptions[ "ids" ] ) && !empty( $arrOptions[ "ids" ] ) ) {
				$iIds = array( );
				foreach( $arrOptions[ "ids" ] as $i => $v ) {
					if ( is_int( $i ) ) {
						// индексы только целочисленные
						$iIds[ $i ] = $i;
					}
				}
				$objClient = new CClient( );
				$szIndex = $objClient->GetAttributeIndex( "id", NULL, FLEX_FILTER_DATABASE );
				$arrOptions = array( FHOV_WHERE => $szIndex." IN(".join( ",", $iIds ).")", FHOV_TABLE => "ud_client", FHOV_OBJECT => "CClient" );
				$tmp = $this->hClient->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					$iIds = array( );
					$modZone = new CHModZone( );
					$hZone = new CFlexHandler( );
					$hZone->Create( array( "database" => $objCMS->database ) );
					$objZone = new CFileZone( );
					$szVIdIndex = $objZone->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_DATABASE );
					$arrClient = $tmp->GetResult( );
					$objNetwork = new CNetwork( );
					$arrNetworkIndex = $objNetwork->GetAttributeIndexList( FLEX_FILTER_DATABASE );
					$arrDelClients = array( );
					foreach( $arrClient as $i => $v ) {
						$iIds[ $v->graph_vertex_id ] = $v->graph_vertex_id;
						$arrDelClients[ ] = $v->login;
						// попутно удалим файлы зон каждого юзверя
						$tmp = $objCMS->GetLinkObjects( $v->graph_vertex_id, true, 'User/Zone' );
						if ( $tmp->HasResult( ) ) {
							$tmp = $tmp->GetResult( );
							$arrIds = array( );
							foreach( $tmp as $j => $w ) {
								$arrIds[ ] = $w->id;
							}
							$tmp = $hZone->GetObject( array(
								FHOV_WHERE => '`'.$szVIdIndex.'` IN ('.join( ',', $arrIds ).')',
								FHOV_TABLE => 'ud_zone',
								FHOV_INDEXATTR => 'id',
								FHOV_OBJECT => 'CFileZone'
							) );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->GetResult( );
								$modZone->DelFileZone( $tmp );
							}
						}
						//
						$arrToDel = array( );
						$arrVIds = array( );
						$arrIpBlock = $v->GetIpBlockArray( );
						foreach( $arrIpBlock as $i => $v ) {
							$tmp = $this->hCommon->GetObject( array(
								FHOV_WHERE => '`'.$arrNetworkIndex[ 'ip' ].'`=\''.$v[ 'ip' ].'\' AND `'.$arrNetworkIndex[ 'mask' ].'`='.$v[ 'mask' ],
								FHOV_TABLE => 'ud_network', FHOV_OBJECT => 'CNetwork'
							) );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->GetResult( );
								$tmp = current( $tmp );
								$arrToDel[ ] = $tmp;
								$arrVIds[ $tmp->graph_vertex_id ] = $tmp->graph_vertex_id;
							}
						}
						$objCMS->DelFromWorld( $arrVIds );
						$this->hCommon->DelObject( $arrToDel, array( FHOV_TABLE => 'ud_network' ) );
					}
					
					$tmp = $this->hClient->DelObject( $arrClient, array( FHOV_TABLE => 'ud_client' ) );
					if ( $tmp->HasError( ) ) {
						$bRedir = false;
						$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
					} else {
						if ( $bSaveLog ) {
							$modLogger = new CHModLogger( );
							$modLogger->AddLog(
								$objCMS->GetUserLogin( ),
								'ModUser',
								'ModUser::DelClient',
								'deleted clients: '.join( ', ', $arrDelClients )
							);
						}
						$objCMS->DelFromWorld( $iIds );
					}
				}
			}
			return $bRedir;
		} // function DelClient
		
		/**
		 * 	Удаление админской учетки
		 */
		public function DelAdmin( $arrOptions, &$arrErrors, $bSaveLog = true ) {
			global $objCMS;
			$bRedir = true;
			if ( isset( $arrOptions[ "ids" ] ) && is_array( $arrOptions[ "ids" ] ) && !empty( $arrOptions[ "ids" ] ) ) {
				$iIds = array( );
				foreach( $arrOptions[ "ids" ] as $i => $v ) {
					if ( is_int( $i ) ) {
						// индексы только целочисленные
						$iIds[ $i ] = $i;
					}
				}
				$objAdmin = new CAdmin( );
				$szIndex = $objAdmin->GetAttributeIndex( "id", NULL, FLEX_FILTER_DATABASE );
				$szRankIndex = $objAdmin->GetAttributeIndex( "rank", NULL, FLEX_FILTER_DATABASE );
				$tmp = array(
					SUR_ADMIN => UR_ADMIN,
					SUR_SUPERADMIN => UR_SUPERADMIN,
					SUR_OPERATOR => UR_OPERATOR
				);
				$tmp = $tmp[ $objCMS->GetUserRank( ) ];
				$arrOptions = array( FHOV_WHERE => "`".$szRankIndex."`>".$tmp." AND ".$szIndex." IN(".join( ",", $iIds ).")", FHOV_TABLE => "ud_admin", FHOV_OBJECT => "CAdmin" );
				$tmp = $this->hClient->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					// выбраны будут только клиенты
					$iIds = array( );
					$arrDelClients = array( );
					$arrAdmin = $tmp->GetResult( );
					foreach( $arrAdmin as $i => $v ) {
						$iIds[ $v->graph_vertex_id ] = $v->graph_vertex_id;
						$arrDelClients[ ] = $v->login;
					}
					$tmp = $this->hAdmin->DelObject( $arrAdmin, array( FHOV_TABLE => "ud_admin" ) );
					if ( $tmp->HasError( ) ) {
						$bRedir = false;
						$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
					} else {
						if ( $bSaveLog ) {
							$modLogger = new CHModLogger( );
							$modLogger->AddLog(
								$objCMS->GetUserLogin( ),
								"ModUser",
								"ModUser::DelAdmin",
								"deleted admins: ".join( ", ", $arrDelClients )
							);
						}
						$objCMS->DelFromWorld( $iIds );
					}
				}
			}
			return $bRedir;
		} // function DelAdmin
		
		/**
		 * 	Проверяет существование пользователя системы
		 * 	@param $szLogin string логин пользователя
		 * 	@param $iMode int режим работы
		 * 	@param $arrOptions array набор настроек
		 * 	@return CResult
		 */
		public function GetUser( $szLogin, $iMode = FLEX_FILTER_DATABASE, $arrOptions = array( ) ) {
			if ( $this->hClient === NULL || $this->hAdmin === NULL ) {
				$this->InitObjectHandler( );
			}
			
			$objRet = new CResult( );
			$tmp = new CClient( );
			$szLoginIndex = $tmp->GetAttributeIndex( "login", NULL, $iMode );
			$tmp->Create( array( $szLoginIndex => $szLogin ), $iMode );
			$szLoginIndex = $tmp->GetAttributeIndex( "login", NULL, FLEX_FILTER_DATABASE );
			$szLoginValue = $tmp->GetAttributeValue( "login", FLEX_FILTER_DATABASE );
			$tmp1 = $this->hClient->GetObject( array(
				FHOV_WHERE => "`".$szLoginIndex."`=".$szLoginValue,
				FHOV_TABLE => "ud_client",
				FHOV_INDEXATTR => "id",
				FHOV_OBJECT => "CClient"
			) );
			if ( $tmp1->HasResult( ) ) {
				$tmp1 = $tmp1->GetResult( );
				$tmp1 = current( $tmp1 );
				$objRet->AddResult( $tmp1, $tmp1->id );
				$objRet->AddResult( "client", "type" );
			} else {
				$tmp = new CAdmin( );
				$szLoginIndex = $tmp->GetAttributeIndex( "login", NULL, $iMode );
				$tmp->Create( array( $szLoginIndex => $szLogin ), $iMode );
				$szLoginIndex = $tmp->GetAttributeIndex( "login", NULL, FLEX_FILTER_DATABASE );
				$szLoginValue = $tmp->GetAttributeValue( "login", FLEX_FILTER_DATABASE );
				$tmp1 = $this->hAdmin->GetObject( array(
					FHOV_WHERE => "`".$szLoginIndex."`=".$szLoginValue,
					FHOV_TABLE => "ud_admin",
					FHOV_INDEXATTR => "id",
					FHOV_OBJECT => "CAdmin"
				) );
				if ( $tmp1->HasResult( ) ) {
					$tmp1 = $tmp1->GetResult( );
					$tmp1 = current( $tmp1 );
					$objRet->AddResult( $tmp1, $tmp1->id );
					$objRet->AddResult( "admin", "type" );
				}
			}
			return $objRet;
		} // function GetUser
		
		/**
		 * 	Получение зон клиента
		 */
		public function GetClientZones( &$mxdCurrentData, &$arrErrors ) {
			$tmp = $objCMS->GetLinkObjects( $mxdCurrentData[ "current_user" ]->graph_vertex_id );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmpZones = array( );
				foreach( $tmp as $i => $v ) {
					if ( intval( $v->label ) == WGI_ZONE ) {
						$tmpZones[ ] = $v->id;
					}
				}
				$hZone = new CFlexHandler( );
				$hZone->Create( array( "database" => $objCMS->database ) );
				$tmpZone = new CFileZone( );
				$szIdIndex = $tmpZone->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_DATABASE );
				$szTypeIndex = $tmpZone->GetAttributeIndex( "type", NULL, FLEX_FILTER_DATABASE );
				$arrOptions = array(
					FHOV_WHERE => "`".$szIdIndex."` IN(".join( ",", $tmpZones ).")",
					//FHOV_WHERE => "`".$szTypeIndex."`=".FZT_DIRECT." AND `".$szIdIndex."` IN(".join( ",", $tmpZones ).")",
					FHOV_TABLE => "ud_zone", FHOV_OBJECT => "CFileZone"
				);
				$tmp = $hZone->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$szZonesIndex = $mxdCurrentData[ "current_user" ]->GetAttributeIndex( "zones" );
					$mxdCurrentData[ "current_user" ]->Create( array( $szZonesIndex => $tmp ) );
				}
			}
		} // function GetClientZones
		
		/**
		 * 	Получение аккаунта суперадмина
		 * 	@return CResult
		 */
		public function GetSuperAdmin( ) {
			if ( $this->hAdmin === NULL ) {
				$this->InitObjectHandler( );
			}
			
			$objRet = new CResult( );
			$objAdmin = new CAdmin( );
			$szRankIndex = $objAdmin->GetAttributeIndex( "rank", NULL, FLEX_FILTER_DATABASE );
			$tmp = $this->hAdmin->GetObject( array( FHOV_WHERE => "`".$szRankIndex."`=".UR_SUPERADMIN, FHOV_LIMIT => "1", FHOV_TABLE => "ud_admin", FHOV_INDEXATTR => "id", FHOV_OBJECT => "CAdmin" ) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmp = current( $tmp );
				$objRet->AddResult( $tmp, "superadmin" );
			}
			return $objRet;
		} // function GetSuperAdmin
		
		/**
		 * 	Проверяет существование суперадмина
		 * 	@return bool
		 */
		public function CheckSuperAdmin( ) {
			$bRet = false;
			$objAdmin = new CAdmin( );
			$szRankIndex = $objAdmin->GetAttributeIndex( "rank", NULL, FLEX_FILTER_DATABASE );
			$tmp = $this->hAdmin->CountObject( array( FHOV_WHERE => "`".$szRankIndex."`=".UR_SUPERADMIN, FHOV_TABLE => "ud_admin" ) );
			if ( $tmp->HasResult( ) ) {
				$tmp = intval( $tmp->GetResult( "count" ) );
				if ( $tmp ) {
					$bRet = true;
				}
			}
			return $bRet;
		} // function CheckSuperAdmin
		
	} // class CHModUser
	
?>