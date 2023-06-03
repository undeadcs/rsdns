<?php
	/**
	 *	Модуль файлов зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	require( 'zone.rrs.php' );
	require( 'zone.file.php' );
	require( 'zone.tplsoa.php' );
	require( 'zone.parser.php' );
	require( 'zone.old_zone.php' );
	require( 'zone.filter.php' );
	require( 'zone.lock.php' );
	require( 'zone.tmp.php' );
	require( 'zone.utils.php' );
	if ( !defined( 'UNDEAD_CS_SYNC' ) ) {
		require( 'zone.sync.php' );
	}
	
	/**
	 *	Перехватчик для модуля Zone
	 */
	class CHModZone extends CHandler {
		private	$hCommon	= NULL,
			$hZone		= NULL,
			$hRrs		= NULL,
			$hTplSoa	= NULL,
			$hDefaultTTL	= NULL,
			$hOldZone	= NULL,
			$hBindConf	= NULL,
			$hZoneLock	= NULL,
			$szZoneFolder	= '',
			$szConfigFolder	= '';
		
		public	$szReverseSuffix	= '.in-addr.arpa',
			$szFileZoneSuffix	= '.bind',
			$szDelAttrName		= 'del',
			$iZoneLockSeconds	= 18001,		// 30 минут + 1 секунда
			$iPageSize		= 15;
		
		/**
		 * 	Инициализация обработчиков
		 */
		private function InitObjectHandler( ) {
			global $objCMS;
			$arrIni = array( 'database' => $objCMS->database );
			$this->hCommon = new CFlexHandler( );
			$this->hCommon->Create( $arrIni );
			// файлы зон
			$this->hZone = $this->hCommon;
			$this->hZone->CheckTable( array( FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone' ) );
			// ресурсные записи
			$this->hRrs = new CHResourceRecord( );
			$this->hRrs->Create( $arrIni );
			$this->hRrs->CheckTable( array( FHOV_TABLE => 'ud_rr', FHOV_OBJECT => 'CResourceRecord' ) );
			// шаблон SOA
			$this->hTplSoa = $this->hCommon;
			$this->hTplSoa->CheckTable( array( FHOV_TABLE => 'ud_tplsoa', FHOV_OBJECT => 'CTplSoa' ) );
			// TTL по умолчанию
			$this->hDefaultTTL = $this->hCommon;
			$this->hDefaultTTL->CheckTable( array( FHOV_TABLE => 'ud_dttl', FHOV_OBJECT => 'CDefaultTTL' ) );
			// Старые версии
			$this->hOldZone = $this->hCommon;
			$this->hOldZone->CheckTable( array( FHOV_TABLE => 'ud_old_zone', FHOV_OBJECT => 'COldFileZone' ) );
			$this->hOldZone->CheckTable( array( FHOV_TABLE => 'ud_old_rr', FHOV_OBJECT => 'CResourceRecord', FHOV_FORCETABLE => true ) );
			// блокировка файлов
			$this->hZoneLock = $this->hCommon;
			$this->hZoneLock->CheckTable( array( FHOV_TABLE => 'ud_lock', FHOV_OBJECT => 'CZoneLock' ) );
			// временные файлы
			$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_tmp_zone', FHOV_OBJECT => 'CFileZoneTmp' ) );
			$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_tmp_rr', FHOV_OBJECT => 'CResourceRecord', FHOV_FORCETABLE => true ) );
			// папки, где хранятся файлы зон и конфиги
			$this->szZoneFolder = $objCMS->GetPath( 'root_system' ).'/zone';
			$this->szConfigFolder = $objCMS->GetPath( 'root_system' ).'/config';
			$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => 'ud_system', FHOV_OBJECT => 'CSystemConfig' ) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmp = current( $tmp );
				if ( $tmp->zone !== '' ) {
					$this->szZoneFolder = $tmp->zone;
				}
				if ( $tmp->config !== '' ) {
					$this->szConfigFolder = $tmp->config;
				}
			}
			$this->szZoneFolder = preg_replace( '/\/$/', '', $this->szZoneFolder );
			if ( !file_exists( $this->szZoneFolder ) ) {
				mkdir( $this->szZoneFolder, 0755 );
			}
			$this->szConfigFolder = preg_replace( '/^\/$/', '', $this->szConfigFolder );
			if ( !file_exists( $this->szConfigFolder ) ) {
				mkdir( $this->szConfigFolder, 0755 );
			}
			$this->hBindConf = new CHBindConfig( );
			$this->hBindConf->CheckTable( array( FHOV_TABLE => $this->szConfigFolder.'/master.zones' ) );
			$this->hBindConf->CheckTable( array( FHOV_TABLE => $this->szConfigFolder.'/slave.zones' ) );
		} // funciton InitObjectHandler
		
		public function GetFolders( ) {
			if ( $this->hBindConf === NULL ) {
				$this->InitObjectHandler( );
			}
			$arrFolder = array(
				'zone' => $this->szZoneFolder,
				'config' => $this->szConfigFolder
			);
			return $arrFolder;
		}
		
		public function GetFileZoneSuffix( ) {
			return $this->szFileZoneSuffix;
		} // function GetFileZoneSuffix
		
		public function GetReverseSuffix( ) {
			return $this->szReverseSuffix;
		} // function GetReverseSuffix
		
		/**
		 *	Проверка на срабатывание (перехват)
		 *	@param $szQuery string строка тестирования
		 *	@return bool
		 */
		public function Test( $szQuery ) {
			return ( preg_match( '/^\/zone\//', $szQuery ) ? true : false );
		} // function Test
		
		/**
		 *	Обработка
		 *	@param $szQuery string строка, на которой произошел перехват
		 *	@return bool
		 */
		public function Process( $szQuery ) {
			global $objCMS, $objCurrent, $mxdCurrentData, $szCurrentMode, $arrErrors;
			// выставляем текущий модуль
			$objCMS->SetWGI( WGI_ZONE );
			$objCMS->SetWGIState( MF_THIS );
			$objCurrent = 'Zone';
			$szCurrentMode = 'List';
			$arrErrors = array( );
			//
			$this->InitObjectHandler( );
			
			if ( preg_match( '/^\/zone\/conf\//', $szQuery ) ) {
				$this->ModeZoneConf( $szQuery, $mxdCurrentData, $arrErrors );
				$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
				$szCurrentMode = 'Conf';
				//
			} elseif ( preg_match( '/^\/zone\/generator\//', $szQuery ) ) {
				$this->ModeZoneGenerator( $mxdCurrentData, $arrErrors );
				$szCurrentMode = 'Generator';
				$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
			} elseif ( preg_match( '/^\/zone\/del\//', $szQuery ) ) {
				$this->ModeZoneDel( $szQuery, $mxdCurrentData, $arrErrors );
				if ( isset( $mxdCurrentData[ 'current_mode' ] ) ) {
					$szCurrentMode = $mxdCurrentData[ 'current_mode' ];
				}
			} elseif ( preg_match( '/^\/zone\/[^\/]*\//', $szQuery ) ) {
				$this->ModeZoneEdit( $szQuery, $mxdCurrentData, $arrErrors );
				if ( isset( $mxdCurrentData[ 'current_mode' ] ) ) {
					$szCurrentMode = $mxdCurrentData[ 'current_mode' ];
				}
			}
			if ( $szCurrentMode === 'List' ) {
				$this->ModeZoneList( $szQuery, $mxdCurrentData, $arrErrors );
			}
			
			// передаем управление приложению
			$szFolder = $objCMS->GetPath( 'root_application' );
			if ( $szFolder !== false && file_exists( "$szFolder/index.php" ) ) {
				include_once( "$szFolder/index.php" );
			}
			return true;
		} // function Process
		
		/**
		 * 	Проверка домена на свободность
		 * 	@param $szDomain string доменное имя
		 * 	@return bool
		 */
		public function FreeDomain( $szDomain ) {
			$szResult = shell_exec( 'whois '.escapeshellarg( $szDomain ) );
			if ( preg_match( '/(no entries)|(no matching record)|(no match for domain)|(not found)|(no match)/sUu', strtolower( $szResult ) ) ) {
				return true;
			} else {
				return false;
			}
		} // function CheckDomain
		
		/**
		 * 	Режим списка файлов зон
		 */
		public function ModeZoneList( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			$arrOptions = array( FHOV_TABLE => 'ud_zone', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CFileZone' );
			//
			$objFilter = new CZoneFilter( );
			$objFilter->Create( $_GET, FLEX_FILTER_FORM );
			$szWhere = $objFilter->GetWhere( );
			if ( $szWhere !== '' ) {
				$arrOptions[ FHOV_WHERE ] = $szWhere;
			}
			$mxdCurrentData[ 'filter' ] = $objFilter;
			$szUrl = $objFilter->GetUrlAttr( );
			if ( $szUrl === '' ) {
				$szUrl = $objCMS->GetPath( 'root_relative' ).'/zone/?';
			} else {
				$szUrl = $objCMS->GetPath( 'root_relative' )."/zone/?$szUrl&";
			}
			//
			$iCount = $this->hZone->CountObject( $arrOptions );
			$iCount = $iCount->GetResult( 'count' );
			$objPager = new CPager( );
			$arrData = array(
				'url'		=> $szUrl,
				'page'		=> @$_GET[ 'page' ],
				'page_size'	=> $this->iPageSize,
				'total'		=> $iCount
			);
			$objPager->Create( $arrData, FLEX_FILTER_FORM );
			$szLimit = $objPager->GetSQLLimit( );
			if ( $szLimit !== '' ) {
				$arrOptions[ FHOV_LIMIT ] = $szLimit;
			}
			//
			$tmp = $this->hZone->GetObject( $arrOptions );
			if ( $tmp->HasResult( ) ) {
				if ( count( $_POST ) && isset( $_POST[ 'del' ] ) && is_array( $_POST[ 'del' ] ) ) {
					$arrData = $_POST[ 'del' ];
					$arrIds = array( );
					foreach( $arrData as $i => $v ) {
						if ( is_int( $i ) ) {
							$arrIds[ $i ] = $i;
						}
					}
					
					if ( !empty( $arrIds ) ) {
						$tmp = $this->hCommon->GetObject( array(
							FHOV_WHERE => '`zone_id` IN('.join( ',', $arrIds ).')',
							FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone', FHOV_INDEXATTR => 'id'
						) );
						if ( $tmp->HasResult( ) ) {
							$arrToDel = $tmp->GetResult( );
							$tmpX = $this->DelFileZone( $arrToDel );
							if ( $tmpX->HasError( ) ) {
								$arrErrors = array_merge( $arrErrors, $tmpX->GetError( ) );
							} elseif ( $tmpX->HasResult( ) ) {
								$tmpX = $tmpX->GetResult( );
								$tmp = array( );
								foreach( $tmpX as $v ) {
									$tmp[ ] = $v->id;
								}
								$tmp = urlencode( join( ',', $tmp ) );
								Redirect( $objCMS->GetPath( 'root_relative' ).'/zone/del/?'.$this->szDelAttrName.'='.$tmp );
							} else {
								Redirect( $objCMS->GetPath( 'root_relative' ).'/zone/' );
							}
						}
					}
				}
				
				$mxdCurrentData[ 'pager' ] = $objPager;
				$tmp = $tmp->GetResult( );
				foreach( $tmp as $i => $v ) {
					$arrCurIni = NULL;
					$tmp1 = $v->GetArray( );
					$arrCurIni = $tmp1->GetResult( );
					$tmp1 = $objCMS->GetLinkObjects( $v->graph_vertex_id, false );
					if ( $tmp1->HasResult( ) ) {
						$tmp1 = $tmp1->GetResult( );
						$tmpClient = new CClient( );
						$szGVIIndex = $tmpClient->GetAttributeIndex( 'graph_vertex_id', NULL, FLEX_FILTER_DATABASE );
						foreach( $tmp1 as $j => $w ) {
							if ( intval( $w->label ) == WGI_USER ) {
								$tmp2 = $this->hCommon->GetObject( array( FHOV_WHERE => "`$szGVIIndex`=".$w->id, FHOV_TABLE => 'ud_client', FHOV_INDEXATTR => 'graph_vertex_id', FHOV_OBJECT => 'CClient' ) );
								if ( $tmp2->HasResult( ) ) {
									$tmp2 = $tmp2->GetResult( $w->id );
									$arrCurIni[ 'client_login' ] = $tmp2->login;
									$arrCurIni[ 'client_full_name' ] = $tmp2->full_name;
									unset( $tmp2 );
								}
								break;
							}
						}
					}
					$tmp1 = new CFileZoneListItem( );
					$tmp2 = $tmp1->Create( $arrCurIni );
					if ( $tmp2->HasError( ) ) {
					} else {
						$mxdCurrentData[ 'zone_list' ][ ] = $tmp1;
					}
				}
			}
		} // function ModeZoneList
		
		/**
		 * 	Режим подтверждения удаления
		 * 	@param $szQuery string путь запроса
		 * 	@param $mxdCurrentData array набор для данных
		 * 	@param $arrErrors array набор для ошибок
		 */
		public function ModeZoneDel( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$tmp = ( isset( $_GET[ $this->szDelAttrName ] ) ? $_GET[ $this->szDelAttrName ] : '' );
			if ( is_string( $tmp ) && !preg_match( '/[^0-9,]/', $tmp ) ) {
				$szOrigDel = $tmp;
				$objFileZone = new CFileZone( );
				$arrIndex = $objFileZone->GetAttributeIndexList( FLEX_FILTER_DATABASE );
				$tmp = explode( ',', $tmp );
				$tmp = array_map( 'intval', $tmp );
				$tmp = array_unique( $tmp, SORT_NUMERIC );
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => '`'.$arrIndex[ 'id' ].'` IN('.join( ',', $tmp ).') AND `'.$arrIndex[ 'type' ].'`='.FZT_REVERSE,
					FHOV_TABLE => 'ud_zone', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CFileZone'
				) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					
					if ( count( $_POST ) ) {
						if ( isset( $_POST[ 'y' ] ) ) {
							foreach( $tmp as $v ) {
								$this->UnlockZone( $v );
								$tmp1 = $this->GetZoneChildren( $v );
								if ( $tmp1->HasResult( ) ) {
									$tmp1 = $tmp1->GetResult( );
									foreach( $tmp1 as $j => $w ) {
										$this->UnlockZone( $w );
									}
									$this->DelFileZone( $tmp1 );
								}
							}
							$this->DelFileZone( $tmp );
							Redirect( $objCMS->GetPath( 'root_relative' ).'/zone/' );
						} else {
							foreach( $tmp as $v ) {
								$this->UnlockZone( $v );
								$tmp1 = $this->GetZoneChildren( $v );
								if ( $tmp1->HasResult( ) ) {
									$tmp1 = $tmp1->GetResult( );
									foreach( $tmp1 as $j => $w ) {
										$this->UnlockZone( $w );
									}
								}
							}
							Redirect( $objCMS->GetPath( 'root_relative' ).'/zone/' );
						}
					}
					
					$mxdCurrentData[ 'orig_del' ] = $szOrigDel;
					
					foreach( $tmp as $i => $v ) {
						$mxdCurrentData[ 'zone_list' ][ $i ][ '.' ] = $v;
						if ( !$this->ZoneIsLocked( $v ) ) {
						}
						$tmp1 = $this->GetZoneChildren( $v );
						if ( $tmp1->HasResult( ) ) {
							$mxdCurrentData[ 'zone_list' ][ $i ][ '*' ] = $tmp1->GetResult( );
						}
					}
					$mxdCurrentData[ 'current_mode' ] = 'Del';
				}
			}
		} // function ModeZoneDel
		
		/**
		 * 	Сохранение файла зон
		 */
		public function SaveZone( &$mxdCurrentData ) {
			global $objCMS;
			/**
			 * 1. Сохраняем текущую версию
			 * 2. Заменяем текущую версию временной
			 * 3. Разлочиваем файл + сносим временную версию
			 * 4. Редиректим на список файлов зон
			 */
			// берем текущую зону
			$tmp = $this->GetFileZone(
				$mxdCurrentData[ 'current_zone' ]->id,
				array( FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone' )
			);
			$objZone = $tmp->GetResult( 'zone' );
			$this->GetZoneRRs( $objZone );
			$bEqual = CompareZones( $objZone, $mxdCurrentData[ 'current_zone' ] );
			if ( !$bEqual ) {
				foreach( $mxdCurrentData[ 'current_zone' ]->rrs as $i => $v ) {
					if ( $v->type === 'SOA' ) {
						$mxdCurrentData[ 'current_zone' ]->rrs[ $i ]->UpdateSerial( );
						break;
					}
				}
				$this->ReplaceZone( $objZone, $mxdCurrentData[ 'current_zone' ] );
			}
			$this->UnlockZone( $mxdCurrentData[ 'current_zone' ] );
			
			if ( !$bEqual ) {
				// сохраняем лог об изменении файла зон
		     		$modLogger = new CHModLogger( );
	     			$modLogger->AddLog(
	     				$objCMS->GetUserLogin( ),
	     				'ModZone',
	     				'ModZone::UpdFileZone',
	     				'updated zone, name: '.$mxdCurrentData[ 'current_zone' ]->name
	     			);
	     			//
	     			$tmp = $this->GetZoneOwner( $mxdCurrentData[ 'current_zone' ]->graph_vertex_id );
	     			if ( $tmp->HasResult( ) ) {
	     				$tmp = $tmp->GetResult( 'client' );
	     				DumbMail( 'Zone was changed', '', $tmp->email, 'Your zone was changed. See details: http://'.$_SERVER[ 'HTTP_HOST' ].'/' );
	     			}
	     			$this->UpdFileZoneConf( $mxdCurrentData[ 'current_zone' ] );
			}
     			Redirect( $objCMS->GetPath( "root_relative" ).'/zone/' );
		} // function SaveZone
		
		/**
		 * 	Получение владельца зоны
		 * 	@param $iZoneVId int id зоны
		 * 	@return CResult
		 */
		public function GetZoneOwner( $iZoneVId ) {
			global $objCMS;
			$objRet = new CResult( );
			$tmp = $objCMS->GetLinkObjects( $iZoneVId, false, "User/Zone" );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$objVertex = current( $tmp );
				if ( intval( $objVertex->label ) === WGI_USER ) {
					$iClientVId = $objVertex->id;
					$objClient = new CClient( );
					$arrIndex = $objClient->GetAttributeIndexList( FLEX_FILTER_DATABASE );
					$tmp = $this->hCommon->GetObject( array(
						FHOV_WHERE => "`".$arrIndex[ "graph_vertex_id" ]."`=".$iClientVId,
						FHOV_TABLE => "ud_client", FHOV_OBJECT => "CClient"
					) );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( );
						$tmp = current( $tmp );
						$objRet->AddResult( $tmp, "client" );
					}
				}
			}
			return $objRet;
		} // function GetZoneOwner
		
		/**
		 * 	Разблокировка зон, которые редактировал юзверь
		 * 	@param $iUserVId int id пользователя
		 * 	@return void
		 */
		public function UnlockUserZone( $iUserVId ) {
			/**
			 * 1. Выгребаем локи
			 * 2. Выгребаем v_id зон
			 * 3. Сносим локи
			 * 4. Сносим временные зоны
			 * 	а. сносим ресурсные записи
			 * 	б. сносим сами зоны
			 */
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$objLock = new CZoneLock( );
			$arrIndex = $objLock->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => "`".$arrIndex[ "user_v_id" ]."`=".$iUserVId,
				FHOV_TABLE => "ud_lock", FHOV_OBJECT => "CZoneLock"
			) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$arrIds = array( );
				foreach( $tmp as $v ) {
					$arrIds[ $v->zone_v_id ] = $v->zone_v_id;
				}
				$this->hCommon->DelObject( $tmp, array( FHOV_TABLE => "ud_lock" ) );
				$objTmpZone = new CFileZoneTmp( );
				$arrIndex = $objTmpZone->GetAttributeIndexList( FLEX_FILTER_DATABASE );
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => "`".$arrIndex[ "graph_vertex_id" ]."` IN(".join( ",", $arrIds ).")",
					FHOV_TABLE => "ud_tmp_zone", FHOV_OBJECT => "CFileZoneTmp"
				) );
				$tmp = $tmp->GetResult( );
				foreach( $tmp as $i => $v ) {
					if ( $this->GetZoneRRs( $tmp[ $i ], "ud_tmp_rr" ) ) {
						$arrIgnoreAttr = array( );
						$arrRRs = $tmp[ $i ]->GetRRs( );
						$arrOptions1 = array( FHOV_TABLE => "ud_tmp_rr" );
						foreach( $arrRRs as $w ) {
							$arrIgnoreAttr = array_merge( $arrIgnoreAttr, $w->GetAttrIgnoreList( ) );
						}
						if ( !empty( $arrIgnoreAttr ) ) {
							$arrOptions1[ FHOV_IGNOREATTR ] = $arrIgnoreAttr;
						}
						$this->hCommon->DelObject( $arrRRs, $arrOptions1 );
						$tmp[ $i ]->ClearRRs( );
					}
				}
				$this->hCommon->DelObject( $tmp, array( FHOV_TABLE => "ud_tmp_zone" ) );
			}
		} // function UnlockUserZone
		
		/**
		 * 	Лочит файл зон (без создания временной копии)
		 * 	@param $objZone CFileZone зона
		 */
		private function LockZone( $objZone ) {
			global $objCMS;
			$objLock = new CZoneLock( );
			$objLock->Create( array(
				'lock_cr_date' => date( 'Y-m-d H:i:s' ),
				'lock_user_v_id' => $objCMS->GetUserVId( ),
				'lock_zone_v_id' => $objZone->graph_vertex_id,
				'lock_ip' => $_SERVER[ 'REMOTE_ADDR' ]
			) );
			$this->hCommon->AddObject( array( $objLock ), array( FHOV_TABLE => 'ud_lock' ) );
		} // function LockZone
		
		/**
		 * 	Разблокировка зоны
		 * 	@param $objZone CFileZone зона
		 * 	@return void
		 */
		public function UnlockZone( $objZone ) {
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$arrIndex = $objZone->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => "`".$arrIndex[ "name" ]."`=".$objZone->GetAttributeValue( "name", FLEX_FILTER_DATABASE ),
				FHOV_TABLE => "ud_tmp_zone", FHOV_INDEXATTR => "name", FHOV_OBJECT => "CFileZoneTmp"
			) );
			$objTmpZone = $tmp->GetResult( $objZone->name );
			/*$objZone->Create( array(
				$arrIndex[ "default_ttl" ] => $objTmpZone->default_ttl,
				$arrIndex[ "comment" ] => $objTmpZone->comment,
				$arrIndex[ "last_edit" ] => date( "Y-m-d H:i:s" )
			) );
			$tmp = $this->hCommon->UpdObject(
				array( $objZone ),
				array( FHOV_TABLE => "ud_zone", FHOV_INDEXATTR => "id", FHOV_IGNOREATTR => array( "rrs" ) )
			);*/
			// разлочиваем файл зон
			$objLock = new CZoneLock( );
			$arrIndex = $objLock->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => '`'.$arrIndex[ 'zone_v_id' ].'`='.$objZone->GetAttributeValue( 'graph_vertex_id', FLEX_FILTER_DATABASE ),
				FHOV_TABLE => 'ud_lock', FHOV_OBJECT => 'CZoneLock'
			) );
			$tmp = $tmp->GetResult( );
			$this->hCommon->DelObject( $tmp, array( FHOV_TABLE => 'ud_lock' ) );
			// удаляем ресурсные записи временного файла
			$tmp = $objZone->GetRRs( );
			$arrOptions = array( FHOV_TABLE => 'ud_tmp_rr' );
			$arrIgnoreAttr = array( );
			foreach( $tmp as $v ) {
				$arrIgnoreAttr = array_merge( $arrIgnoreAttr, $v->GetAttrIgnoreList( ) );
			}
			if ( !empty( $arrIgnoreAttr ) ) {
				$arrOptions[ FHOV_IGNOREATTR ] = $arrIgnoreAttr;
			}
			$this->hCommon->DelObject( $tmp, $arrOptions );
			$objZone->ClearRRs( );
			// удаляем файл зон
			$arrOptions = array( FHOV_TABLE => 'ud_tmp_zone' );
			$this->hCommon->DelObject( array( $objZone ), $arrOptions );
		} // function UnlockZone
		
		/**
		 * 	Проверка залоченности файла зон от редактирования
		 */
		public function CheckLockState( &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$iCurVId = $objCMS->GetUserVId( ); // v_id текущего юзверя
			$arrIndex1 = $mxdCurrentData[ 'current_zone' ]->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$szTable = 'ud_rr';
			$objLock = new CZoneLock( );
			$arrIndex = $objLock->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => '`'.$arrIndex[ 'zone_v_id' ].'`='.$mxdCurrentData[ 'current_zone' ]->GetAttributeValue( 'graph_vertex_id', FLEX_FILTER_DATABASE ),
				FHOV_TABLE => 'ud_lock', FHOV_OBJECT => 'CZoneLock'
			) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$objLock = current( $tmp );
				//
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => "`".$arrIndex1[ "name" ]."`=".$mxdCurrentData[ "current_zone" ]->GetAttributeValue( "name", FLEX_FILTER_DATABASE ),
					FHOV_TABLE => "ud_tmp_zone", FHOV_OBJECT => "CFileZoneTmp"
				) );
				$tmp = $tmp->GetResult( );
				$objTmpZone = current( $tmp );
				//
				$fMaxLockTime = $this->iZoneLockSeconds;
				$fCurTime = time( );
				$fLockTime = strtotime( $objLock->cr_date );
				if ( ( $fCurTime - $fLockTime ) > $fMaxLockTime ) {
					$this->GetZoneRRs( $objTmpZone, 'ud_tmp_rr' );
					$this->UnlockZone( $objTmpZone );
					$this->CheckLockState( $mxdCurrentData, $arrErrors );
					return;
				}
				// файл залочен
				if ( $iCurVId === $objLock->user_v_id ) {
					// редактору отдаем временный файл
					// обновляем лок
					$objLock->Create( array(
						"lock_cr_date" => date( "Y-m-d H:i:s" )
					) );
					$this->hCommon->UpdObject( array( $objLock ), array( FHOV_TABLE => "ud_lock", FHOV_INDEXATTR => "id" ) );
					//
					$mxdCurrentData[ "current_zone" ] = current( $tmp );
					$szTable = "ud_tmp_rr";
				} else {
					// просмотрщику оставляем старый файл зон
					$mxdCurrentData[ "zone_locked" ] = true;
				}
			} else {
				// файл не залочен, лочим, делаем редактором текущего юзверя, создаем временный файл
				// лочим для текущего юзверя
				$objLock->Create( array(
					"lock_cr_date" => date( "Y-m-d H:i:s" ),
					"lock_user_v_id" => $iCurVId,
					"lock_zone_v_id" => $mxdCurrentData[ "current_zone" ]->graph_vertex_id,
					"lock_ip" => $_SERVER[ "REMOTE_ADDR" ]
				) );
				$this->hCommon->AddObject( array( $objLock ), array( FHOV_TABLE => "ud_lock" ) );
				// создаем временный файл зон
				$tmp = $this->hCommon->AddObject( array( $mxdCurrentData[ "current_zone" ] ), array( FHOV_IGNOREATTR => array( "rrs" ), FHOV_TABLE => "ud_tmp_zone" ) );
				$this->GetZoneRRs( $mxdCurrentData[ "current_zone" ] );
				$arrRRs = $mxdCurrentData[ "current_zone" ]->rrs;
				$arrIgnoreAttr = array( );
				foreach( $arrRRs as $v ) {
					$v->Create( array( "rr_id" => 0 ) );
					$arrIgnoreAttr = array_merge( $arrIgnoreAttr, $v->GetAttrIgnoreList( ) );
				}
				$tmp = $this->hCommon->AddObject( $arrRRs, array( FHOV_IGNOREATTR => $arrIgnoreAttr, FHOV_TABLE => "ud_tmp_rr" ) );
				//
				$szTable = "ud_tmp_rr";
			}
			$mxdCurrentData[ "current_zone" ]->ClearRRs( );
			if ( $this->GetZoneRRs( $mxdCurrentData[ "current_zone" ], $szTable ) ) {
				$mxdCurrentData[ "rr_last" ] = $this->GetLastOrderIndex( $mxdCurrentData[ "current_zone" ] );
			}
		} // function CheckLockState
		
		/**
		 * 	Получение текущей зоны, над которой ведется работа
		 * 	@param $szQuery string строка запроса
		 * 	@return CResult
		 */
		public function GetCurrentZone( $szQuery ) {
			$objRet = new CResult( );
			$tmp = NULL;
			preg_match( '/^\/zone\/([^\/]*)\//', $szQuery, $tmp );
			$tmp = intval( $tmp[ 1 ] );
			$tmp = $this->GetFileZone( $tmp );
			if ( $tmp->HasResult( ) ) {
				$tmp1 = $tmp->GetResult( "zone" );
				$objRet->AddResult( $tmp1, "zone" );
			}
			if ( $tmp->HasError( ) ) {
				$objRet->AddError( $tmp );
			}
			return $objRet;
		} // function GetCurrentZone
		
		/**
		 * 	Получение файла зон
		 * 	@param $iId int id зоны
		 * 	@param $arrOptions array набор настроек
		 * 	@return CResult
		 */
		public function GetFileZone( $iId, $arrOptions = array( ) ) {
			if ( $this->hZone === NULL ) {
				$this->InitObjectHandler( );
			}
			$objRet = new CResult( );
			$objFileZone = new CFileZone( );
			$szIdIndex = $objFileZone->GetAttributeIndex( 'id', NULL, FLEX_FILTER_DATABASE );
			if ( !isset( $arrOptions[ FHOV_TABLE ] ) ) {
				$arrOptions[ FHOV_TABLE ] = 'ud_zone';
			}
			if ( !isset( $arrOptions[ FHOV_OBJECT ] ) ) {
				$arrOptions[ FHOV_OBJECT ] = 'CFileZone';
			}
			$arrOptions[ FHOV_WHERE ] = "`$szIdIndex`=$iId";
			$tmp = $this->hZone->GetObject( $arrOptions );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$objCurZone = current( $tmp );
				$objRet->AddResult( $objCurZone, 'zone' );
			}
			return $objRet;
		} // function GetFileZone
		
		/**
		 * 	Получение ресурсных записей файла зон
		 * 	@param $objFileZone CFileZone файл зон
		 * 	@param $szTable string таблица ресурсных записей
		 * 	@return bool
		 */
		public function GetZoneRRs( &$objFileZone, $szTable = "ud_rr", $szOrder = "" ) {
			if ( $this->hRrs === NULL ) {
				$this->InitObjectHandler( );
			}
			$tmpRR = new CResourceRecord( );
			$szId1Index = $tmpRR->GetAttributeIndex( "id", NULL, FLEX_FILTER_DATABASE );
			$szIdIndex = $tmpRR->GetAttributeIndex( "zone_file_id", NULL, FLEX_FILTER_DATABASE );
			$szIdValue = $objFileZone->GetAttributeValue( "id", FLEX_FILTER_DATABASE );
			$szOrderIndex = $tmpRR->GetAttributeIndex( "order", NULL, FLEX_FILTER_DATABASE );
			$arrOptions = array( FHOV_WHERE => "`".$szIdIndex."`=".$szIdValue, FHOV_TABLE => $szTable, FHOV_OBJECT => "CResourceRecord" );
			if ( empty( $szOrder ) ) {
				$arrOptions[ FHOV_ORDER ] = "`".$szOrderIndex."` ASC";
			} else {
				$arrOptions[ FHOV_ORDER ] = $szOrder;
			}
			$tmp = $this->hRrs->GetObject( $arrOptions );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$szRRsIndex = $objFileZone->GetAttributeIndex( "rrs", NULL, FLEX_FILTER_DATABASE );
				$objFileZone->Create( array( $szRRsIndex => $tmp ), FLEX_FILTER_DATABASE );
				return true;
			}
			return false;
		} // function GetZoneRRs
		
		/**
		 * 	Проверка ресурсных записей ( форма редактирования файла зон )
		 * 	@param $arrRRs array набор ресурсных записей
		 * 	@param $arrInput array набор входных данных
		 * 	@param $iMode int режим работы
		 * 	@param $mxdCurrentData array набор текущих данных
		 * 	@return CResult
		 */
		public function CheckRRs( &$arrRRs, &$arrInput, $iMode, &$mxdCurrentData ) {
			$objRet = new CResult( );
			$objRR = new CResourceRecord( );
			$fltArray = new CArrayFilter( );
			$arrFilter = array(
				'id' => $objRR->GetAttributeIndex( 'id', NULL, $iMode ),
				'zone_file_id' => $objRR->GetAttributeIndex( 'id', NULL, $iMode ),
			);
			
			foreach( $arrRRs as $i => $v ) {
				if ( isset( $arrInput[ $i ] ) ) {
					$tmp = $fltArray->Apply( $arrInput[ $i ] );
					if ( $v->type === 'SOA' ) {
						$szSerialIndex = $v->GetAttributeIndex( 'serial', NULL, $iMode );
						$szOrderIndex = $v->GetAttributeIndex( 'order', NULL, $iMode );
						$szOriginIndex = $v->GetAttributeIndex( 'origin', NULL, $iMode );
						$tmp[ $szSerialIndex ] = $v->GetAttributeValue( 'serial' );
						$tmp[ $szOrderIndex ] = 0;
						$tmp[ $szOriginIndex ] .= '.';
					}
					$oldVer = clone $v;
					$tmp = $arrRRs[ $i ]->Create( $tmp, $iMode );
					if ( $tmp->HasError( ) ) {
						$tmp1 = $tmp->GetError( );
						foreach( $tmp1 as $j => $w ) {
							$mxdCurrentData[ 'err_by_rr' ][ $i ][ $j ] = $w;
						}
						$objRet->AddError( $tmp, $i );
					}
					if ( $oldVer != $arrRRs[ $i ] ) {
						$objRet->AddResult( $arrRRs[ $i ], $v->id );
					}
				}
			}
			return $objRet;
		} // function CheckRRs
		
		/**
		 * 	Заменяет файл зон
		 * 	@param $objCurrentZone CFileZone текущий файл зон
		 * 	@param $objNewZone CFileZone новый файл зон
		 * 	@param $bSaveOld bool сохранять версию
		 * 	@param $szZoneTable string таблица файлов зон
		 * 	@param $szRRTable string таблица ресурсных записей
		 * 	@return CResult
		 */
		public function ReplaceZone( $objCurrentZone, $objNewZone, $bSaveOld = true, $szZoneTable = "ud_zone", $szRRTable = "ud_rr" ) {
			/**
			 * 1. Сохраняем текущую версию
			 * 2. Удаляем ресурсные записи
			 * 3. Обновляем текущий файл зон
			 * 4. Добавляем новые ресурсные записи
			 * 5. Выбрать ресурсные записи
			 * 6. Установить порядок
			 * 7. Обновить ресурсные записи
			 */
			$objRet = new CResult( );
			
			if ( $bSaveOld ) {
				$this->SaveOldZone( $objCurrentZone );
			}
			
			$this->DelFileZoneRRs( $objCurrentZone, $szRRTable );
			
			$objCurrentZone->ClearRRs( );
			$arrRRs = $objNewZone->GetRRs( );
			foreach( $arrRRs as $i => $v ) {
				$arrRRs[ $i ]->Create( array( "rr_zone_file_id" => $objCurrentZone->id ) );
			}
			$arrIni = array(
				"zone_default_ttl" => $objNewZone->default_ttl,
				"zone_comment" => $objNewZone->comment,
				"zone_last_edit" => date( "Y-m-d H:i:s" )
			);
			$objCurrentZone->Create( $arrIni );
			$this->hZone->UpdObject( array( $objCurrentZone ), array( FHOV_IGNOREATTR => array( "rrs" ), FHOV_TABLE => $szZoneTable, FHOV_INDEXATTR => "id" ) );
			$arrOptions = array( FHOV_TABLE => $szRRTable );
			$arrIgnoreAttr = array( );
			foreach( $arrRRs as $v ) {
				$arrIgnoreAttr = array_merge( $arrIgnoreAttr, $v->GetAttrIgnoreList( ) );
			}
			if ( !empty( $arrIgnoreAttr ) ) {
				$arrOptions[ FHOV_IGNOREATTR ] = $arrIgnoreAttr;
			}
			$this->hRrs->AddObject( $arrRRs, $arrOptions );
			
			if ( $this->GetZoneRRs( $objCurrentZone, $szRRTable, "`rr_id` DESC" ) ) {
				$arrRRs = $objCurrentZone->rrs;
				$this->SetRROrder( $arrRRs );
			     	$this->hRrs->UpdObject( $arrRRs, array( FHOV_ONLYATTR => array( "id", "order", "data" ), FHOV_TABLE => $szRRTable, FHOV_INDEXATTR => "id" ) );
			}
			return $objRet;
		} // function ReplaceZone
		
		/**
		 * 	Сравнивает файлы зон
		 * 	@param $objCurrentZone CFileZone текущий файл зон
		 * 	@param $objNewZone CFileZone новый файл зон (порядок записей 0)
		 * 	@return int
		 */
		public function CompareZones( $objCurrentZone, $objNewZone ) {
			return CompareZones( $objCurrentZone, $objNewZone );
		} // function CompareZones
		
		/**
		 * 	Сравнивает 2 ресурсные записи
		 * 	@return int
		 */
		public function CompareRRs( $objRR1, $objRR2 ) {
			return CompareRRs( $objRR1, $objRR2 );
		} // function CompareRRs
		
		/**
		 * 	Режим редактирования файла зоны
		 * 	@param $szQuery string запрос
		 * 	@param $mxdCurrentData array набор данных
		 * 	@param $arrErrors array массив ошибок
		 * 	@return void
		 */
		public function ModeZoneEdit( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			$tmp = $this->GetCurrentZone( $szQuery );
			if ( $tmp->HasResult( ) ) {
				$mxdCurrentData[ 'current_mode' ] = 'Edit';
				$mxdCurrentData[ 'current_zone' ] = $tmp->GetResult( 'zone' );
				//ShowVar( $mxdCurrentData );
				$this->CheckLockState( $mxdCurrentData, $arrErrors );
				//ShowVarD( $mxdCurrentData );
				$bLocked = isset( $mxdCurrentData[ 'zone_locked' ] ) ? true : false;
				
				$tmp1 = NULL;
				if ( !$bLocked && preg_match( '/^\/zone\/[^\/]*\/add_rr\/(\d*)\//', $szQuery, $tmp1 ) ) {
				     	$mxdCurrentData[ 'current_mode' ] = 'AddRR';
				     	$szRRTypeName = 'rr_type';
				     	$tmpAddRrPos = intval( $tmp1[ 1 ] );
				     	$mxdCurrentData[ "rr_pos" ] = $tmpAddRrPos;
				     	if ( isset( $_GET[ $szRRTypeName ] ) ) {
				     		$szInpType = strval( $_GET[ $szRRTypeName ] );
				     		$mxdCurrentData[ 'rr_mode' ] = 'input';
				     		$arrRRTypes = array(
							RRT_SOA => 'CRR_SOA', RRT_NS => 'CRR_NS', RRT_A => 'CRR_A', RRT_AAAA => 'CRR_AAAA',
							RRT_CNAME => 'CRR_CNAME', RRT_MX => 'CRR_MX', RRT_PTR => 'CRR_PTR', RRT_SRV => 'CRR_SRV',
							RRT_TXT => 'CRR_TXT',
							RRT_TTL => 'CRR__TTL', RRT_ORIGIN => 'CRR__ORIGIN', RRT_INCLUDE => 'CRR__INCLUDE'
						);
						$objTmp = NULL;
						if ( isset( $arrRRTypes[ $szInpType ] ) ) {
							$szClass = $arrRRTypes[ $szInpType ];
							$objTmp = new $szClass( );
						} else {
							$objTmp = new CResourceRecord( );
						}
						if ( $objTmp !== NULL ) {
							$szOrderIndex = $objTmp->GetAttributeIndex( 'order' );
							$objTmp->Create( array( $szOrderIndex => $tmpAddRrPos ) );
							$mxdCurrentData[ 'current_rr' ] = $objTmp;
						}
				     	} elseif ( count( $_POST ) ) {
				     		$arrData = $_POST;
				     		$tmpRR = new CResourceRecord( );
				     		$arrFilter = array(
				     			"id" => $tmpRR->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
				     			"zone_file_id" => $tmpRR->GetAttributeIndex( "zone_file_id", NULL, FLEX_FILTER_FORM ),
				     			"order" => $tmpRR->GetAttributeIndex( "order", NULL, FLEX_FILTER_FORM ) // в этом контексте порядок запрещено вводить
				     		);
				     		unset( $tmpRR );
				     		$fltArray = new CArrayFilter( );
				     		$arrData = $fltArray->Apply( $arrData );
				     		$arrData[ $arrFilter[ "zone_file_id" ] ] = $mxdCurrentData[ "current_zone" ]->GetAttributeValue( "id" );
				     		$arrData[ $arrFilter[ "order" ] ] = $tmpAddRrPos;
				     		$objRR = $this->hRrs->GenerateRR( $arrData );
				     		if ( $objRR->HasError( ) ) {
				     			if ( $objRR->HasResult( ) ) {
				     				$mxdCurrentData[ "current_rr" ] = $objRR->GetResult( "rr" );
				     			}
				     			$arrErrors = array_merge( $arrErrors, $objRR->GetError( ) );
				     		} else {
				     			$objRR = $objRR->GetResult( "rr" );
				     			$arrOptions = array( FHOV_TABLE => "ud_tmp_rr" );
				     			$tmpIgnoreAttr = $objRR->GetAttrIgnoreList( );
				     			if ( !empty( $tmpIgnoreAttr ) ) {
				     				$arrOptions[ FHOV_IGNOREATTR ] = $tmpIgnoreAttr;
				     			}
					     		$tmp = $this->hRrs->AddObject( array( $objRR ), $arrOptions );
					     		if ( $tmp->HasError( ) ) {
					     			$mxdCurrentData[ "current_rr" ] = $objRR;
					     			$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
					     		} else {
					     			$iId = $tmp->GetResult( "insert_id" );
					     			$arrCurRrs = $mxdCurrentData[ "current_zone" ]->rrs;
					     			//
					     			$arrInput[ "zone_last_edit" ] = date( "Y-m-d H:i:s" );
								$mxdCurrentData[ "current_zone" ]->Create( $arrInput );
								$tmp = $this->hZone->UpdObject( array( $mxdCurrentData[ "current_zone" ] ), array( FHOV_IGNOREATTR => array( "rrs" ), FHOV_TABLE => "ud_tmp_zone", FHOV_INDEXATTR => "id" ) );
								//
						     		$tmp = array( $objRR->GetAttributeIndex( "id" ) => $iId );
					     			$objRR->Create( $tmp );
						     		$arrCurRrs[ ] = $objRR;
						     		$this->SetRROrder( $arrCurRrs );
						     		$arrOptions = array( FHOV_ONLYATTR => array( "id", "order", "data" ), FHOV_TABLE => "ud_tmp_rr", FHOV_INDEXATTR => "id" );
						     		$this->hRrs->UpdObject( $arrCurRrs, $arrOptions );
						     		Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->id."/" );
					     		}
				     		}
				     	} else {
				     		$mxdCurrentData[ "rr_mode" ] = "select";
				     	}
				} elseif ( preg_match( '/^\/zone\/[^\/]*\/text\//', $szQuery ) ) {
					$mxdCurrentData[ "current_mode" ] = "Text";
					$mxdCurrentData[ "zone_text" ] = $mxdCurrentData[ "current_zone" ]->GetText( );
					
					if ( !$bLocked ) {
						if ( count( $_POST ) && isset( $_POST[ "zone_text" ] ) ) {
							$arrData = $_POST[ "zone_text" ];
							if ( is_string( $arrData ) ) {
								$szCurrentText = $mxdCurrentData[ "zone_text" ];
								if ( $arrData == $szCurrentText ) {
									Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->id."/" );
								} else {
									$objParser = new CZoneParser( );
									$tmp = $objParser->GetFileZone( $arrData );
									if ( $tmp->HasError( ) ) {
										$mxdCurrentData[ "zone_err_text" ] = $arrData;
										$arrErrors[ ] = new CError( 1, "Текст файла зон содержит ошибки" );
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
										if ( $this->CompareZones( $mxdCurrentData[ "current_zone" ], $objNewZone ) ) {
											Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$objCurrentZone->id."/" );
										} else {
											$tmp = $this->ReplaceZone( $objCurrentZone, $objNewZone, false, "ud_tmp_zone", "ud_tmp_rr" );
											if ( $tmp->HasError( ) ) {
												$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
											} else {
												Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$objCurrentZone->id."/" );
											}
										}
									}
								}
							}
						}
					}
					//
				} elseif ( preg_match( '/^\/zone\/[^\/]*\/old\//', $szQuery ) ) {
					$mxdCurrentData[ "current_mode" ] = "Old";
					$this->ModeZoneOld( $szQuery, $mxdCurrentData, $arrErrors );
					//
				} elseif ( preg_match( '/^\/zone\/[^\/]*\/upload\//', $szQuery ) ) {
					if ( !$bLocked ) {
						$mxdCurrentData[ "current_mode" ] = "Upload";
						$this->ModeZoneUpload( $szQuery, $mxdCurrentData, $arrErrors );
					}
					//
				} elseif ( preg_match( '/^\/zone\/[^\/]*\/export\//', $szQuery ) ) {
					$mxdCurrentData[ "current_mode" ] = "Export";
					$this->ModeZoneExport( $szQuery, $mxdCurrentData, $arrErrors );
					//
				} elseif ( preg_match( '/^\/zone\/[^\/]*\/save\//', $szQuery ) ) {
					if ( !$bLocked ) {
						$this->SaveZone( $mxdCurrentData );
					}
				} elseif ( preg_match( '/^\/zone\/[^\/]*\/exit\//', $szQuery ) ) {
					if ( !$bLocked ) {
						$this->UnlockZone( $mxdCurrentData[ "current_zone" ] );
						Redirect( $objCMS->GetPath( "root_relative" )."/zone/" );
					}
				} else {
					$modServer = new CHModLink( );
					$tmp = $modServer->GetServers( );
					if ( $tmp->HasResult( ) ) {
						$mxdCurrentData[ "servers" ] = $tmp->GetResult( );
					}
					unset( $tmp, $modServer );
					
					if ( count( $_POST ) ) {
						if ( $bLocked ) {
							Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->id."/" );
						}
						$bWasError = false;
						$arrData = $_POST;
						$szRRsIndex = $mxdCurrentData[ "current_zone" ]->GetAttributeIndex( "rrs", NULL, FLEX_FILTER_FORM );
						$tmp = $mxdCurrentData[ "current_zone" ]->rrs;
						$arrCurRRs = array( );
						foreach( $tmp as $i => $v ) {
							$arrCurRRs[ $i ] = clone $v;
						}
						$arrNewRRs = $arrData[ $szRRsIndex ];
						foreach( $arrNewRRs as $i => $v ) {
							if ( $v[ 'rr_type' ] === 'SOA' ) {
								break;
							}
						}
						$tmp = $this->CheckRRs( $arrCurRRs, $arrNewRRs, FLEX_FILTER_FORM, $mxdCurrentData );
						if ( $tmp->HasError( ) ) {
							$mxdCurrentData[ "current_zone" ]->ClearRRs( );
							$mxdCurrentData[ "current_zone" ]->Create( array( $szRRsIndex => $arrCurRRs ), FLEX_FILTER_FORM );
						} else {
							$bWasChanged = $tmp->HasResult( );
							$arrUpdRRs = $tmp->GetResult( );
							$arrInput = array( );
							
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
									$tmp = $this->hRrs->DelObject( $arrToDel, array( FHOV_TABLE => "ud_tmp_rr" ) );
								}
							}
							if ( $bWasChanged ) {
								//$this->SaveOldZone( $mxdCurrentData[ "current_zone" ] );
							}
							$arrInput[ "zone_last_edit" ] = date( "Y-m-d H:i:s" );
							$mxdCurrentData[ "current_zone" ]->Create( $arrInput, FLEX_FILTER_FORM );
							$tmp = $this->hZone->UpdObject( array( $mxdCurrentData[ "current_zone" ] ), array( FHOV_IGNOREATTR => array( "rrs" ), FHOV_TABLE => "ud_tmp_zone", FHOV_INDEXATTR => "id" ) );
							$arrIgnoreAttr = array( );
							foreach( $arrCurRRs as $i => $v ) {
								if ( $v->type === "SOA" && $bWasChanged ) {
									//$arrCurRRs[ $i ]->UpdateSerial( );
								}
								$arrIgnoreAttr = array_merge( $arrIgnoreAttr, $v->GetAttrIgnoreList( ) );
							}
							$arrOptions = array( FHOV_TABLE => "ud_tmp_rr", FHOV_INDEXATTR => "id" );
							if ( !empty( $arrIgnoreAttr ) ) {
								$arrOptions[ FHOV_IGNOREATTR ] = $arrIgnoreAttr;
							}
							
							$this->SetRROrder( $arrCurRRs );
							$tmp = $this->hRrs->UpdObject( $arrCurRRs, $arrOptions );
							if ( $tmp->HasError( ) ) {
								$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
							} else {
								if ( isset( $arrData[ "save" ] ) ) {
									Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->id."/save/" );
								} else {
									Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->id."/" );
								}
							}
						}
					}
				}
				
				$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
			}
		} // function ModeEditZone
		
		/**
		 * 	Режим загрузки файла зон
		 */
		public function ModeZoneUpload( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			$objCurrentZone = $mxdCurrentData[ "current_zone" ];
			
			if ( count( $_POST ) && isset( $_POST[ "load" ] ) ) {
				if ( is_uploaded_file( $_FILES[ "file_zone" ][ "tmp_name" ] ) ) {
					$szText = "";
					$hFile = fopen( $_FILES[ "file_zone" ][ "tmp_name" ], "rb" );
					if ( $hFile ) {
						if ( $_FILES[ "file_zone" ][ "size" ] > 0 ) {
							$szText = fread( $hFile, $_FILES[ "file_zone" ][ "size" ] );
						} else {
							$arrErrors[ ] = new CError( 1, "Пустой файл" );
						}
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
							if ( $this->CompareZones( $mxdCurrentData[ "current_zone" ], $objNewZone ) ) {
								Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$objCurrentZone->id."/" );
							} else {
								$tmp = $this->ReplaceZone( $objCurrentZone, $objNewZone, false, "ud_tmp_zone", "ud_tmp_rr" );
								if ( $tmp->HasError( ) ) {
									$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
								} else {
									Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$objCurrentZone->id."/" );
								}
							}
						}
					}
				}
			}
		} // function ModeZoneUpload
		
		/**
		 * 	Режим экспорта файла зон
		 */
		public function ModeZoneExport( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
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
		 * 	Режим просмотра версий
		 * 	@param $szQuery string запрос
		 * 	@param $mxdCurrentData array набор данных
		 * 	@param $arrErrors array массив ошибок
		 * 	@return void
		 */
		public function ModeZoneOld( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			$objOldZone = new COldFileZone( );
			if ( preg_match( '/^\/zone\/[^\/]*\/old\/\d*\//', $szQuery ) ) {
				$mxdCurrentData[ "current_mode" ] = "OldView";
				$tmp = NULL;
				preg_match( '/^\/zone\/[^\/]*\/old\/(\d*)\//', $szQuery, $tmp );
				$iId = intval( $tmp[ 1 ] );
				$szIdIndex = $objOldZone->GetAttributeIndex( "id", NULL, FLEX_FILTER_DATABASE );
				$tmp = $this->hOldZone->GetObject( array(
					FHOV_WHERE => "`".$szIdIndex."`=".$iId,
					FHOV_TABLE => "ud_old_zone",
					FHOV_INDEXATTR => "id",
					FHOV_OBJECT => "COldFileZone"
				) );
				if ( $tmp->HasResult( ) ) {
					$objOldZone = $tmp->GetResult( $iId );
					$this->GetZoneRRs( $objOldZone, "ud_old_rr" );
					$mxdCurrentData[ "current_old_zone" ] = $objOldZone;
					
					if ( preg_match( '/^\/zone\/[^\/]*\/old\/\d*\/load\//', $szQuery ) ) {
						if ( !isset( $mxdCurrentData[ "zone_locked" ] ) ) {
							$this->RestoreFileZone( $mxdCurrentData[ "current_zone" ], $mxdCurrentData[ "current_old_zone" ] );
						}
						Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->id."/" );
					}
				}
			} else {
				$mxdCurrentData[ "current_mode" ] = "OldList";
				$mxdCurrentData[ "old_zone_list" ] = array( );
				$tmp = $this->GetOldZones( $mxdCurrentData[ "current_zone" ] );
				if ( $tmp->HasResult( ) ) {
					$mxdCurrentData[ "old_zone_list" ] = $tmp->GetResult( );
					if ( count( $_POST ) && isset( $_POST[ "del" ] ) ) {
						if ( !isset( $mxdCurrentData[ "zone_locked" ] ) ) {
							$arrData = $_POST[ "del" ];
							$tmp = array( );
							foreach( $arrData as $i => $v ) {
								if ( is_int( $i ) && isset( $mxdCurrentData[ "old_zone_list" ][ $i ] ) ) {
									$tmp[ $i ] = $mxdCurrentData[ "old_zone_list" ][ $i ];
								}
							}
							$this->DelFileZone( $tmp, true, false );
						}
						Redirect( $objCMS->GetPath( "root_relative" )."/zone/".$mxdCurrentData[ "current_zone" ]->id."/old/" );
					}
				}
			}
		} // function ModeZoneOld
		
		/**
		 * 	Получение версий файла зон
		 */
		public function GetOldZones( $objFileZone ) {
			global $objCMS;
			if ( $this->hOldZone === NULL ) {
				$this->InitObjectHandler( );
			}
			
			$objRet = new CResult( );
			$objOldZone = new COldFileZone( );
			$iGraphVertexId = $objFileZone->graph_vertex_id;
			$tmp = $objCMS->GetLinkObjects( $iGraphVertexId, true, "Zone/OldZone" );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmp1 = array( );
				foreach( $tmp as $v ) {
					$tmp1[ ] = $v->id;
				}
				$szIndex = $objOldZone->GetAttributeIndex( "graph_vertex_id", NULL, FLEX_FILTER_DATABASE );
				$tmp = $this->hOldZone->GetObject( array(
					FHOV_WHERE => "`".$szIndex."` IN(".join( ",", $tmp1 ).")",
					FHOV_ORDER => "`old_zone_last_edit` DESC",//"`old_zone_version` DESC",
					FHOV_TABLE => "ud_old_zone", FHOV_INDEXATTR => "id", FHOV_OBJECT => "COldFileZone"
				) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					foreach( $tmp as $i => $v ) {
						$objRet->AddResult( $v, $i );
					}
				}
			}
			return $objRet;
		} // function GetOldZones
		
		/**
		 * 	Режим генерации файлов зон
		 */
		public function ModeZoneGenerator( &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			if ( !isset( $_GET[ "frm" ] ) ) {
				$modLogger = new CHModLogger( );
				$modLogger->AddLog(
					$objCMS->GetUserLogin( ),
					"ModZone",
					"ModZone::StartGenerator",
					"started generator"
				);
				return;
			}
			// Генератор
			if ( $this->hZone === NULL ) {
				$this->InitObjectHandler( );
			}
			$tmp = $this->hZone->CountObject( array( FHOV_TABLE => "ud_zone" ) );
			$iCount = $tmp->GetResult( "count" );
			if ( $iCount ) {
				$iPage = ( isset( $_GET[ "page" ] ) ? intval( $_GET[ "page" ] ) : 1 );
				if ( !$iPage ) {
					$iPage = 1;
				}
				$iPageSize = 50;
				$szLimit = ( ( $iPage - 1 ) * $iPageSize ).",".$iPageSize;
				$tmp = $this->hZone->GetObject( array(
					FHOV_LIMIT => $szLimit,
					FHOV_TABLE => "ud_zone",
					FHOV_INDEXATTR => "id",
					FHOV_OBJECT => "CFileZone"
				) );
				if ( $tmp->HasResult( ) ) {
					$arrZones = $tmp->GetResult( );
					foreach( $arrZones as $i => $v ) {
						$this->AddFileZoneConf( $arrZones[ $i ] );
					}
					unset( $arrZones );
					if ( ( $iPage + $iPageSize ) >= $iCount ) {
						echo '<p>Все файлы зон сгенерированы</p>';
						$objSync = new CZoneSync( );
					} else {
						Redirect( $objCMS->GetPath( "root_relative" )."/zone/generator/?frm=1&page=".( $iPage + 1 ) );
					}
				} else {
					echo '<p>Все файлы зон сгенерированы</p>';
				}
			}
			exit;
		} // function ModeZoneGenerator
		
		/**
		 * 	Режим настроек
		 */
		public function ModeZoneConf( $szQuery, &$mxdCurrentData, &$arrErrors ) {
			global $objCMS;
			$mxdCurrentData[ "current_soa" ] = new CTplSoa( );
			$mxdCurrentData[ "default_ttl" ] = new CDefaultTTL( );
			$modServer = new CHModLink( );
			$iN = $modServer->ServerCount( );
			if ( $iN ) {
				$tmp = $this->hTplSoa->GetObject( array( FHOV_TABLE => "ud_tplsoa", FHOV_OBJECT => "CTplSoa", FHOV_LIMIT => "1" ) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$mxdCurrentData[ "current_soa" ] = current( $tmp );
				}
				
				$tmp = $this->hDefaultTTL->GetObject( array( FHOV_TABLE => "ud_dttl", FHOV_OBJECT => "CDefaultTTL" ) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$mxdCurrentData[ "default_ttl" ] = current( $tmp );
				}
				
				$hServer = new CHServer( );
				$hServer->Create( array( "database" => $objCMS->database ) );
				$tmp = $hServer->GetObject( array( FHOV_TABLE => "ud_server", FHOV_OBJECT => "CServer", FHOV_INDEXATTR => "id" ) );
				if ( $tmp->HasResult( ) ) {
					$mxdCurrentData[ "servers" ] = $tmp->GetResult( );
				}

				if ( count( $_POST ) ) {
					$arrData = $_POST;
					$arrFilter = array(
						"id" => $mxdCurrentData[ "current_soa" ]->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
						"id1" => $mxdCurrentData[ "default_ttl" ]->GetAttributeIndex( "id", NULL, FLEX_FILTER_FORM ),
					);
					$fltArray = new CArrayFilter( $arrFilter );
					$arrData = $fltArray->Apply( $arrData );
					$tmp = $mxdCurrentData[ "current_soa" ]->Create( $arrData, FLEX_FILTER_FORM );
					$bWasError = false;
					if ( $tmp->HasError( ) ) {
						$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
						$bWasError = true;
					}
					
					$tmp = $mxdCurrentData[ "default_ttl" ]->Create( $arrData, FLEX_FILTER_FORM );
					if ( $tmp->HasError( ) ) {
						$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
						$bWasError = true;
					}
					if ( $bWasError ) {
						return;
					}
					
					// паранойя - проверим правильность введенного имени сервака
					$bFound = false;
					$szNSValue = "";
					$szNSValue = intval( $mxdCurrentData[ "current_soa" ]->GetAttributeValue( "origin" ) );
					foreach( $mxdCurrentData[ "servers" ] as $i => $v ) {
						$tmpNSName = $v->GetAttributeValue( "name" );
						if ( $szNSValue == $tmpNSName ) {
							$bFound = true;
							break;
						}
					}
					if ( $bFound ) {
						// действительно выбрали существующий
						$iId = $mxdCurrentData[ "current_soa" ]->GetAttributeValue( "id" );
						if ( $iId ) {
							$tmp = $this->hTplSoa->UpdObject( array( $mxdCurrentData[ "current_soa" ] ), array( FHOV_TABLE => "ud_tplsoa", FHOV_INDEXATTR => "id" ) );
							if ( $tmp->HasError( ) ) {
								$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
								$bWasError = true;
							}
						} else {
							$tmp = $this->hTplSoa->AddObject( array( $mxdCurrentData[ "current_soa" ] ), array( FHOV_TABLE => "ud_tplsoa", FHOV_INDEXATTR => "id" ) );
							if ( $tmp->HasError( ) ) {
								$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
								$bWasError = true;
							}
						}
					}
					if ( $mxdCurrentData[ "default_ttl" ]->GetAttributeValue( "id" ) ) {
						$tmp = $this->hDefaultTTL->UpdObject( array( $mxdCurrentData[ "default_ttl" ] ), array( FHOV_TABLE => "ud_dttl", FHOV_INDEXATTR => "id" ) );
						if ( $tmp->HasError( ) ) {
							$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
							$bWasError = true;
						}
					} else {
						$tmp = $this->hDefaultTTL->AddObject( array( $mxdCurrentData[ "default_ttl" ] ), array( FHOV_TABLE => "ud_dttl", FHOV_INDEXATTR => "id" ) );
						if ( $tmp->HasError( ) ) {
							$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
							$bWasError = true;
						}
					}
					if ( $bWasError ) {
						return;
					}
					Redirect( $objCMS->GetPath( "root_relative" )."/zone/" );
				}
			} else {
				$arrErrors[ ] = new CError( 1, "Отсутствуют сервера" );
			}
		} // function ModeZoneTplSoa
		
		/**
		 * 	Сохранение старой версии файла зон
		 */
		public function SaveOldZone( $objFileZone ) {
			global $objCMS;
			if ( $this->hOldZone === NULL ) {
				$this->InitObjectHandler( );
			}
			
			$objOldZone = new COldFileZone( );
			$tmp = $objFileZone->GetArray( );
			$arrOld = $tmp->GetResult( );
			foreach( $arrOld as $i => $v ) {
				$arrOld[ "old_zone_".$i ] = $v;
				unset( $arrOld[ $i ] );
			}
			$arrOld[ "old_zone_ip_edited" ] = $_SERVER[ "REMOTE_ADDR" ];
			$arrOld[ "old_zone_id" ] = 0;
			$arrOld[ "old_zone_last_edit" ] = date( "Y-m-d H:i:s" );
			foreach( $objFileZone->rrs as $v ) {
				if ( $v->type === "SOA" ) {
					$arrOld[ "old_zone_version" ] = $v->serial;
					break;
				}
			}
			//*
			$tmp = $objCMS->AddToWorld( WGI_ZONE, "ModZone/OldZone" );
			$iGVId = $tmp->GetResult( "graph_vertex_id" );
			$arrOld[ "old_zone_graph_vertex_id" ] = $iGVId;
			$objOldZone->Create( $arrOld );
			$arrOptions = array( FHOV_IGNOREATTR => array( "rrs" ), FHOV_TABLE => "ud_old_zone" );
			$tmp = $this->hOldZone->AddObject( array( $objOldZone ), $arrOptions );
			$iFileZoneId = $tmp->GetResult( "insert_id" );
			$objCMS->LinkObjects( $objFileZone->graph_vertex_id, $iGVId, "Zone/OldZone" );
			//*/
			$arrOptions = array( FHOV_IGNOREATTR => array( ), FHOV_TABLE => "ud_old_rr" );
			$tmp = array( );
			foreach( $objFileZone->rrs as $i => $v ) {
				$arrOptions[ FHOV_IGNOREATTR ] = array_merge( $arrOptions[ FHOV_IGNOREATTR ], $v->GetAttrIgnoreList( ) );
				$szIdIndex = $v->GetAttributeIndex( "id" );
				$szZoneFileIdIndex = $v->GetAttributeIndex( "zone_file_id" );
				$v->Create( array( $szIdIndex => 0, $szZoneFileIdIndex => $iFileZoneId ) );
				$tmp[ ] = clone $v;
			}
			$this->hOldZone->AddObject( $tmp, $arrOptions );
		} // function SaveOldZone
		
		/**
		 * 	Генерация последовательности ресурсных записей для родительского файла зоны
		 */
		public function GenerateAdditionalRRs( $objFileZone, $arrServers ) {
			$szName = $objFileZone->name;
			$szName = preg_replace( '/(\.in-addr\.arpa)$/', '', $szName );
			$tmp = explode( '/', $szName );
			$iBase = intval( $tmp[ 0 ] );
			$tmp = explode( '.', $tmp[ 1 ] );
			$iMask = intval( $tmp[ 0 ] );
			$arrRRs = array( );
			if ( $arrServers[ ST_MASTER ] ) {
				$objNS = new CRR_NS( );
				$objNS->Create( array(
					'rr_zone_file_id' => $objFileZone->id,
					'rr_name' => $iBase.'/'.$iMask,
					'rr_ttl' => $objFileZone->default_ttl,
					'rr_server' => $arrServers[ ST_MASTER ]->name.'.',
				) );
				$arrRRs[ ] = $objNS;
			}
			if ( $arrServers[ ST_SLAVE ] ) {
				$objNS = new CRR_NS( );
				$objNS->Create( array(
					'rr_zone_file_id' => $objFileZone->id,
					'rr_name' => $iBase.'/'.$iMask,
					'rr_ttl' => $objFileZone->default_ttl,
					'rr_server' => $arrServers[ ST_SLAVE ]->name.'.',
				) );
				$arrRRs[ ] = $objNS;
			}
			
			$n = floatval( pow( 2, 32 - $iMask ) );
			for( $i = 1; $i < $n; ++$i ) {
				$x = $iBase + $i;
				$tmp = new CRR_CNAME( );
				$tmp->Create( array(
					'rr_name' => $x,
					'rr_host' => "$x.$szName.in-addr.arpa.",
					'rr_zone_file_id' => $objFileZone->id
				) );
				$arrRRs[ ] = $tmp;
			}
			return $arrRRs;
		} // function GenerateAdditionalRRs
		
		/**
		 * 	Удаление дополнительных значений для масок > 24
		 */
		public function AdditionalReverseDel( $objFileZone ) {
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$szName = IpReverseToNetwork( $objFileZone->name );
			$szName = NetworkToArray( $szName );
			$iMask = intval( $szName[ 'mask' ] );
			unset( $szName[ 'ip' ][ 3 ] );
			$szName[ 'ip' ] = array_reverse( $szName[ 'ip' ] );
			$szName = join( '.', $szName[ 'ip' ] ).$this->szReverseSuffix;
			$arrIndex = $objFileZone->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => '`'.$arrIndex[ 'name' ].'`=\''.@mysql_real_escape_string( $szName ).'\'',
				FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone', FHOV_INDEXATTR => 'name'
			) );
			if ( $tmp->HasResult( ) ) {
				$objParentZone = $tmp->GetResult( $szName );
				$this->GetZoneRRs( $objParentZone );
				$modLink = new CHModLink( );
				$arrServers = $modLink->GetMasterSlave( );
				$arrRRs = $this->GenerateAdditionalRRs( $objFileZone, $arrServers );
				$arrToDel = array( );
				
				foreach( $objParentZone->rrs as $i => $v ) {
					$bFound = false;
					foreach( $arrRRs as $j => $w ) {
						if ( ( $v->type === $w->type ) && ( $v->name === $w->name ) ) {
							if ( $v->type === 'NS' ) {
								if ( $v->server === $w->server ) {
									$bFound = true;
								}
							} elseif ( $v->type === 'CNAME' ) {
								if ( $v->host === $w->host ) {
									$bFound = true;
								}
							}
						}
					}
					if ( $bFound ) {
						$arrToDel[ ] = clone $v;
					}
				}
				
				if ( !empty( $arrToDel ) ) {
					$arrOptions = array( FHOV_TABLE => 'ud_rr', FHOV_IGNOREATTR => array( ) );
					$tmp = new CRR_NS( );
					$arrOptions[ FHOV_IGNOREATTR ] = array_merge( $arrOptions[ FHOV_IGNOREATTR ], $tmp->GetAttrIgnoreList( ) );
					$tmp = new CRR_CNAME( );
					$arrOptions[ FHOV_IGNOREATTR ] = array_merge( $arrOptions[ FHOV_IGNOREATTR ], $tmp->GetAttrIgnoreList( ) );
					$this->hCommon->DelObject( $arrToDel, $arrOptions );
					$objParentZone->ClearRRs( );
					$this->GetZoneRRs( $objParentZone );
					$this->UpdFileZoneConf( $objParentZone );
				}
				unset( $objParentZone );
			}
		} // function AdditionalReverseDel
		
		/**
		 * 	Проверяет залоченность зоны
		 * 	@param $objFileZone CFileZone зона
		 * 	@return bool
		 */
		private function ZoneIsLocked( $objFileZone ) {
			global $objCMS;
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$bLocked = false;
			$iCurVId = $objCMS->GetUserVId( );
			$arrIndex1 = $objFileZone->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$szTable = 'ud_rr';
			$objLock = new CZoneLock( );
			$arrIndex = $objLock->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => '`'.$arrIndex[ 'zone_v_id' ].'`='.$objFileZone->GetAttributeValue( 'graph_vertex_id', FLEX_FILTER_DATABASE ),
				FHOV_TABLE => 'ud_lock', FHOV_OBJECT => 'CZoneLock'
			) );
			$bLocked = $tmp->HasResult( );
			return $bLocked;
		} // function ZoneIsLocked
		
		/**
		 * 	Проверяет залоченность родительской зоны при маске > 24
		 */
		private function ZoneLockedParent( $objFileZone ) {
			ShowVarD( $objFileZone );
		} // function ZoneLockedParent
		
		/**
		 * 	Получение зон потомков
		 */
		private function GetZoneChildren( $objFileZone ) {
			global $objCMS;
			$objRet = new CResult( );
			$tmp = $objCMS->GetLinkObjects( $objFileZone->graph_vertex_id, true, 'ReverseZone/ChildReverseZone' );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmp1 = array( );
				foreach( $tmp as $i => $v ) {
					$tmp1[ $v->id ] = $v->id;
				}
				$arrIndex = $objFileZone->GetAttributeIndexList( FLEX_FILTER_DATABASE );
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => '`'.$arrIndex[ 'graph_vertex_id' ].'` IN('.join( ',', $tmp1 ).')',
					FHOV_TABLE => 'ud_zone', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CFileZone'
				) );
				if ( $tmp->HasResult( ) ) {
					$objRet = $tmp;
				}
			}
			return $objRet;
		} // function GetZoneChildren
		
		/**
		 * 	Получение родителя зоны
		 * 	да похуй что повторяется, времени нет!
		 */
		private function GetZoneParent( $objFileZone ) {
			global $objCMS;
			$objRet = new CResult( );
			$tmp = $objCMS->GetLinkObjects( $objFileZone->graph_vertex_id, false, 'ReverseZone/ChildReverseZone' );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmp1 = array( );
				foreach( $tmp as $i => $v ) {
					$tmp1[ $v->id ] = $v->id;
				}
				$arrIndex = $objFileZone->GetAttributeIndexList( FLEX_FILTER_DATABASE );
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => '`'.$arrIndex[ 'graph_vertex_id' ].'` IN('.join( ',', $tmp1 ).')',
					FHOV_TABLE => 'ud_zone', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CFileZone'
				) );
				if ( $tmp->HasResult( ) ) {
					$objRet = $tmp;
				}
			}
			return $objRet;
		} // function GetZoneParent
		
		/**
		 * 	Удаление файлов зон
		 */
		public function DelFileZone( $arrInput, $bOld = false, $bSaveLog = true ) {
			global $objCMS;
			/**
			 * 1. Удаляем версии файла зон
			 * 2. Удаляем ресурсные записи файла зон
			 * 3. Удаляем файл зон
			 * 4. Удаляем объект файла зон из мира
			 * 
			 * при удалении зоны, которая заблокирована ругаемся
			 */
			$objRet = new CResult( );
			$szZoneTable = 'ud_zone';
			$szRRTable = 'ud_rr';
			if ( $bOld ) {
				$szZoneTable = 'ud_old_zone';
				$szRRTable = 'ud_old_rr';
			} else {
				// проверяем залоченность зон. выдаем сообщения, если надо
				$arrReverse = array( );
				foreach( $arrInput as $i => $v ) {
					if ( $v->type === FZT_REVERSE ) {
						$arrReverse[ $i ] = $v;
						unset( $arrInput[ $i ] );
					} elseif ( $this->ZoneIsLocked( $v ) ) {
						$objRet->AddError( new CError( 1, 'Зона '.$v->name.' редактируется' ), $v->graph_vertex_id );
						unset( $arrInput[ $i ] );
					} 
				}
				if ( !empty( $arrReverse ) ) {
					$arrParent = array( );
					$arrChild = array( );
					foreach( $arrReverse as $i => $v ) {
						$szName = IpReverseToNetwork( $v->name );
						$arrName = NetworkToArray( $szName );
						if ( $arrName[ 'mask' ] == 24 ) {
							$arrParent[ $i ] = $v;
						} elseif ( $arrName[ 'mask' ] > 24 ) {
							$arrChild[ $i ] = $v;
						} else {
							$arrInput[ $i ] = $v;
						}
						unset( $arrReverse[ $i ] );
					}
					/**
					 * 1. Пробегаемся по каждому паренту
					 * 2. Если парент залочен, то исключаем из удаления его чайлдов
					 * 3. Если парент не залочен, то проверяем чайлдов
					 * 4. Если хоть 1 чайлд залочен, ругаемся
					 * 5. Те зоны, что являются парентами будем лочить, чтоб другие не смогли их снести или изменить
					 */
					foreach( $arrParent as $i => $v ) {
						$bLocked = $this->ZoneIsLocked( $v );
						if ( $bLocked ) {
							$objRet->AddError( new CError( 1, 'Зона '.$v->name.' редактируется' ), $v->graph_vertex_id );
							unset( $arrParent[ $i ] );
						}
						$tmp = $this->GetZoneChildren( $v );
						if ( $tmp->HasResult( ) ) {
							$tmp = $tmp->GetResult( );
							foreach( $tmp as $j => $w ) {
								if ( isset( $arrChild[ $j ] ) ) {
									unset( $arrChild[ $j ] );
								}
								if ( !$bLocked && $this->ZoneIsLocked( $w ) ) {
									$objRet->AddError( new CError( 1, 'Зона '.$w->name.' редактируется' ), $w->graph_vertex_id );
									unset( $arrParent[ $i ] );
								}
							}
						}
						unset( $tmp );
					}
					foreach( $arrChild as $i => $v ) {
						$bLocked = $this->ZoneIsLocked( $v );
						if ( $bLocked ) {
							$objRet->AddError( new CError( 1, 'Зона '.$v->name.' редактируется' ), $v->graph_vertex_id );
							unset( $arrChild[ $i ] );
						}
						$tmp = $this->GetZoneParent( $v );
						if ( $tmp->HasResult( ) ) {
							$tmp = $tmp->GetResult( );
							foreach( $tmp as $j => $w ) {
								if ( !$bLocked && $this->ZoneIsLocked( $w ) ) {
									$objRet->AddError( new CError( 1, 'Зона '.$w->name.' редактируется' ), $w->graph_vertex_id );
									unset( $arrChild[ $i ] );
								}
							}
						}
						unset( $tmp );
					}
					
					foreach( $arrParent as $i => $v ) {
						$tmp = $this->GetZoneChildren( $v );
						if ( $tmp->HasResult( ) ) {
							$objRet->AddResult( $v, $v->id );
							// те что идут в confirm лочатся!
							$this->LockZone( $v );
							$tmp1 = $tmp->GetResult( );
							foreach( $tmp1 as $w ) {
								$this->LockZone( $w );
							}
							unset( $tmp1 );
						} else {
							$arrInput[ ] = $v;
						}
						unset( $tmp );
					}
					foreach( $arrChild as $i => $v ) {
						$arrInput[ ] = $v;
					}
				}
			}
			if ( empty( $arrInput ) ) {
				return $objRet;
			}
			
			$arrIds = array( );
			$arrDelZones = array( );
			$arrConfDel = array( );
			foreach( $arrInput as $i => $v ) {
				$bAllowDel = true;
				if ( $v->type == FZT_REVERSE ) {
					$szName = IpReverseToNetwork( $v->name );
					$szName = NetworkToArray( $szName );
					$iMask = intval( $szName[ 'mask' ] );
					if ( $iMask > 24 ) {
						$this->AdditionalReverseDel( $v );
					}
					unset( $szName, $iMask );
				}
				if ( $bAllowDel ) {
					$arrIds[ $v->graph_vertex_id ] = $v->graph_vertex_id;
					$arrDelZones[ ] = $v->name;
					if ( !$bOld ) {
						$tmp1 = $this->GetOldZones( $v );
						if ( $tmp1->HasResult( ) ) {
							$tmp1 = $tmp1->GetResult( );
							$this->DelFileZone( $tmp1, true );
						}
					}
					$this->DelFileZoneRRs( $v, $szRRTable );
					if ( !$bOld ) {
						$tmpZone = clone $v;
						$tmpZone->ClearRRs( );
						$this->GetZoneRRs( $tmpZone, 'ud_tmp_rr' );
						$this->UnlockZone( $tmpZone );
						$arrConfDel[ $v->name ] = true;
					}
				} else {
					unset( $arrInput[ $i ] );
				}
			}
			if ( !empty( $arrConfDel ) ) {
				$this->DelFileZoneConf( $arrConfDel );
			}
			$tmp = $this->hOldZone->DelObject( $arrInput, array( FHOV_TABLE => $szZoneTable ) );
			$modLogger = new CHModLogger( );
			if ( !$bOld && $tmp->HasError( ) ) {
				$modLogger->AddLog(
					$objCMS->GetUserLogin( ),
					'ModZone',
					'ModZone::DelFileZone,Error',
					'error occured when try to delete zones'
				);
			} elseif ( !$bOld && $bSaveLog ) {
				$modLogger->AddLog(
					$objCMS->GetUserLogin( ),
					'ModZone',
					'ModZone::DelFileZone',
					'deleted zones: '.join( ', ', $arrDelZones )
				);
				$arrOwners = array( );
				foreach( $arrIds as $v ) {
					$tmp = $this->GetZoneOwner( $v );
					if ( $tmp->HasResult( ) ) {
						$arrOwners[ ] = $tmp->GetResult( 'client' );
					}
				}
				foreach( $arrOwners as $v ) {
					DumbMail( 'Zone was deleted', '', $v->email, 'Your zone was deleted. See details: http://'.$_SERVER[ 'HTTP_HOST' ].'/' );
				}
			}
			$objCMS->DelFromWorld( $arrIds );
			return $objRet;
		} // function DelFileZone
		
		/**
		 * 	Удаление ресурсных записей файла зон
		 * 	@param $objFileZone CFileZone файл зон
		 * 	@param $szTable string таблица ресурсных записей
		 * 	@return void
		 */
		public function DelFileZoneRRs( $objFileZone, $szTable = 'ud_rr' ) {
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$objRR = new CResourceRecord( );
			$szFZIdIndex = $objRR->GetAttributeIndex( 'zone_file_id', NULL, FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => "`$szFZIdIndex`=".$objFileZone->id,
				FHOV_TABLE => $szTable,
				FHOV_INDEXATTR => 'id',
				FHOV_OBJECT => 'CResourceRecord'
			) );
			if ( $tmp->HasResult( ) ) {
				$this->hOldZone->DelObject( $tmp->GetResult( ), array( FHOV_TABLE => $szTable ) );
			}
		} // function DelFileZoneRRs

		/**
		 * 	Восстановление файла зон из версии
		 * 	@param $objCurrentZone CFileZone текущий файл зон
		 * 	@param $objOldZone COldFileZone восстановочный файл зон
		 * 	@return void
		 */
		public function RestoreFileZone( $objCurrentZone, $objOldZone ) {
			/**
			 * 1. Сохраняем новую запись в версии
			 * 2. Удаляем ресурсные записи
			 * 3. Обновляем данные текущего файла зон
			 * 4. Добавляем ресурсные записи из восстановочного файла
			 */
			$objRR = new CResourceRecord( );
			$iSerial = '';
			foreach( $objCurrentZone->rrs as $i => $v ) {
				if ( $v->type === 'SOA' ) {
					$iSerial = $v->serial;
					break;
				}
			}
			$this->DelFileZoneRRs( $objCurrentZone, 'ud_tmp_rr' ); // удаляем записи
			
			$arrIndex = array(
				'default_ttl' => $objCurrentZone->GetAttributeIndex( 'default_ttl' ),
				'comment' => $objCurrentZone->GetAttributeIndex( 'comment' ),
				'last_edit' => $objCurrentZone->GetAttributeIndex( 'last_edit' ),
			);
			$tmp = array( );
			$tmp[ $arrIndex[ 'default_ttl' ] ] = $objOldZone->default_ttl;
			$tmp[ $arrIndex[ 'comment' ] ] = $objOldZone->comment;
			$tmp[ $arrIndex[ 'last_edit' ] ] = date( 'Y-m-d H:i:s' );
			$objCurrentZone->Create( $tmp );
			$this->hZone->UpdObject( array( $objCurrentZone ), array( FHOV_TABLE => 'ud_tmp_zone', FHOV_INDEXATTR => 'id' ) );
			
			$arrIndex = array(
				'id' => $objRR->GetAttributeIndex( 'id' ),
				'zone_file_id' => $objRR->GetAttributeIndex( 'zone_file_id' ),
			);
			$arrCurRrs = $objOldZone->rrs;
			$arrIgnoreAttr = array( );
			foreach( $arrCurRrs as $i => $v ) {
				$arrIgnoreAttr = array_merge( $arrIgnoreAttr, $v->GetAttrIgnoreList( ) );
				if ( $v->type === 'SOA' ) {
					$arrCurRrs[ $i ]->Create( array( 'rr_serial' => $iSerial ) );
					$arrCurRrs[ $i ]->UpdateSerial( );
				}
				$tmp = array( );
				$tmp[ $arrIndex[ 'id' ] ] = 0;
				$tmp[ $arrIndex[ 'zone_file_id' ] ] = $objCurrentZone->id;
				$arrCurRrs[ $i ]->Create( $tmp );
			}
			$arrOptions = array( FHOV_TABLE => 'ud_tmp_rr' );
			if ( !empty( $arrIgnoreAttr ) ) {
				$arrOptions[ FHOV_IGNOREATTR ] = $arrIgnoreAttr;
			}
			$this->hRrs->AddObject( $arrCurRrs, $arrOptions );
		} // function RestoreFileZone
		
		/**
		 * 	Дополнительные действия при создании обратной зоны
		 * 	@param $szName string имя зоны
		 * 	@param $mxdCurrentData array набор текущих данных
		 */
		public function AdditionalReverse( $szName, &$mxdCurrentData ) {
			global $objCMS;
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$tmp = explode( '/', $szName );
			$iBase = intval( $tmp[ 0 ] );
			$tmp = explode( '.', $tmp[ 1 ] );
			$iMask = intval( $tmp[ 0 ] );
			$szName1 = $tmp[ 1 ].'.'.$tmp[ 2 ].'.'.$tmp[ 3 ];
			$objZone = new CFileZone( );
			$arrIndex = $objZone->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => '`'.$arrIndex[ 'name' ].'`=\''.$szName1.$this->szReverseSuffix.'\'',
				FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone'
			) );
			if ( !$tmp->HasResult( ) ) {
				$this->AddFileZone( array( 'zone_name' => $szName1 ), $mxdCurrentData, FLEX_FILTER_FORM, false, true );
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => '`'.$arrIndex[ 'name' ].'`=\''.$szName1.$this->szReverseSuffix.'\'',
					FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone'
				) );
			}
			$tmp = $tmp->GetResult( );
			$objFileZone = current( $tmp );
			$this->GetZoneRRs( $objFileZone );
			$arrRRs = $objFileZone->GetRRs( );
			// генерируем ресурсные записи
			$modLink = new CHModLink( );
			$arrServers = $modLink->GetServers( );
			$arrServers = $arrServers->GetResult( );
			$objMaster = NULL;
			$objSlave = NULL;
			foreach( $arrServers as $i => $v ) {
				if ( $v->type === ST_MASTER ) {
					$objMaster = clone $v;
				} elseif ( $v->type === ST_SLAVE ) {
					$objSlave = clone $v;
				}
				if ( $objMaster && $objSlave ) {
					break;
				}
			}
			unset( $arrServers );
			//
			$arrNewRrs = array( );
			$iLastOrder = $this->GetLastOrderIndex( $objFileZone );
			
			if ( $objMaster ) {
				$objNS = new CRR_NS( );
				$objNS->Create( array(
					'rr_zone_file_id' => $objFileZone->id,
					'rr_name' => $iBase.'/'.$iMask,
					'rr_ttl' => $objFileZone->default_ttl,
					'rr_server' => $objMaster->name.'.',
					'rr_order' => $iLastOrder++
				) );
				$bFound = false;
				foreach( $arrRRs as $v ) {
					if ( ( $v->type === 'NS' ) && ( $v->name == $objNS->name ) && ( $v->server == $objNS->server ) ) {
						$bFound = true;
					}
				}
				if ( !$bFound ) {
					$arrNewRrs[ ] = $objNS;
				}
			}
			if ( $objSlave ) {
				$objNS = new CRR_NS( );
				$objNS->Create( array(
					'rr_zone_file_id' => $objFileZone->id,
					'rr_name' => $iBase.'/'.$iMask,
					'rr_ttl' => $objFileZone->default_ttl,
					'rr_server' => $objSlave->name.'.',
					'rr_order' => $iLastOrder++
				) );
				$bFound = false;
				foreach( $arrRRs as $v ) {
				if ( ( $v->type === 'NS' ) && ( $v->name == $objNS->name ) && ( $v->server == $objNS->server ) ) {
						$bFound = true;
					}
				}
				if ( !$bFound ) {
					$arrNewRrs[ ] = $objNS;
				}
			}
			
			$n = floatval( pow( 2, 32 - $iMask ) );
			for( $i = 1; $i < $n; ++$i ) {
				$x = $iBase + $i;
				$tmp = new CRR_CNAME( );
				$tmp->Create( array(
					'rr_name' => $x,
					'rr_host' => "$x.$szName.in-addr.arpa.",
					'rr_zone_file_id' => $objFileZone->id
				) );
				$bFound = false;
				foreach( $arrRRs as $v ) {
					if ( ( $v->type === 'CNAME' ) && ( $v->name == $tmp->name ) && ( $v->host == $tmp->host ) ) {
						$bFound = true;
					}
				}
				if ( !$bFound ) {
					$tmp->Create( array( 'rr_order' => $iLastOrder++ ) );
					$arrNewRrs[ ] = $tmp;
				}
			}
			
			if ( !empty( $arrNewRrs ) ) {
				$arrOptions = array( FHOV_TABLE => 'ud_rr', FHOV_IGNOREATTR => array( ) );
				$tmp = new CRR_NS( );
				$arrOptions[ FHOV_IGNOREATTR ] = array_merge( $arrOptions[ FHOV_IGNOREATTR ], $tmp->GetAttrIgnoreList( ) );
				$tmp = new CRR_CNAME( );
				$arrOptions[ FHOV_IGNOREATTR ] = array_merge( $arrOptions[ FHOV_IGNOREATTR ], $tmp->GetAttrIgnoreList( ) );
				$this->hCommon->AddObject( $arrNewRrs, $arrOptions );
				
				$objFileZone->ClearRRs( );
				$this->GetZoneRRs( $objFileZone );
				$this->AddFileZoneConf( $objFileZone );
				// связываем дочернюю зону с родительской
				$szName .= $this->szReverseSuffix;
				$arrIndex = $objFileZone->GetAttributeIndexList( FLEX_FILTER_DATABASE );
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => '`'.$arrIndex[ 'name' ].'`=\''.@mysql_real_escape_string( $szName ).'\'',
					FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone', FHOV_INDEXATTR => 'name'
				) );
				$objChildZone = $tmp->GetResult( $szName );
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => '`'.$arrIndex[ 'name' ].'`='.$objFileZone->GetAttributeValue( 'name', FLEX_FILTER_DATABASE ),
					FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone', FHOV_INDEXATTR => 'name'
				) );
				$objParentZone = $tmp->GetResult( $objFileZone->name );
				$objCMS->LinkObjects( $objParentZone->graph_vertex_id, $objChildZone->graph_vertex_id, 'ReverseZone/ChildReverseZone' );
				unset( $objParentZone, $objChildZone, $objFileZone );
			}
		} // function AdditionalReverse
		
		/**
		 * 	Добавление зоны без отправки заявки регистрару
		 */
		public function AddFileZone2( $arrInput, &$mxdCurrentData, $iMode = FLEX_FILTER_PHP, $bSaveLog = true ) {
			global $objCMS;
			$objRet = new  CResult( );
			$objFileZone = new CFileZone( );
			$tmp = $objFileZone->GetAttributeIndex( 'name', NULL, $iMode );
			if ( !isset( $arrInput[ $tmp ] ) ) {
				$objRet->AddError( new CError( 1,  'Отсутствует доменное имя' ) );
				return $objRet;
			}
			$szRegDate = $objFileZone->GetAttributeIndex( 'reg_date', NULL, $iMode );
			$szLastEdit = $objFileZone->GetAttributeIndex( 'last_edit', NULL, $iMode );
			$arrInput = array(
				$tmp => $arrInput[ $tmp ],
				$szRegDate => date( 'Y-m-d' ),
				$szLastEdit => date( 'Y-m-d H:i:s' )
			);
			$arrIndex = array(
				'id' => $objFileZone->GetAttributeIndex( 'id', NULL, $iMode ),
				'graph_vertex_id' => $objFileZone->GetAttributeIndex( 'graph_vertex_id', NULL, $iMode ),
				'type' => $objFileZone->GetAttributeIndex( 'type', NULL, $iMode ),
				'comment' => $objFileZone->GetAttributeIndex( 'comment', NULL, $iMode ),
			);
			$fltArray = new CArrayFilter( $arrIndex );
			$arrInput = $fltArray->Apply( $arrInput );
			if ( $bReverse ) {
				$arrInput[ $arrIndex[ 'type' ] ] = FZT_REVERSE;
			}
			
			$tmp = $objFileZone->Create( $arrInput, $iMode );
			if ( $tmp->HasError( ) ) {
				$mxdCurrentData[ 'filezone' ] = $objFileZone;
				$objRet->AddError( $tmp );
				return $objRet;
			}
			
			$objUser = $mxdCurrentData[ 'current_user' ];
			
			$szNameIndex = $objFileZone->GetAttributeIndex( 'name', NULL, FLEX_FILTER_DATABASE );
			$szNameValue = $objFileZone->GetAttributeValue( 'name', FLEX_FILTER_DATABASE );
			if ( $objFileZone->type === FZT_REVERSE ) {
				$szNameValue = "'".@mysql_real_escape_string( $objFileZone->name.$this->szReverseSuffix )."'";
			}
			$arrOptions = array( FHOV_WHERE => "`$szNameIndex`=$szNameValue", FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone' );
			$tmp = $this->hCommon->GetObject( $arrOptions );
			if ( $tmp->HasResult( ) ) {
				$mxdCurrentData[ 'filezone' ] = $objFileZone;
				if ( $objFileZone->type === FZT_DIRECT ) {
					$objRet->AddError( new CError( 1, 'Зона уже существует' ) );
				} else {
					$objRet->AddError( new CError( 1, 'Обратная зона уже существует' ) );
				}
			} else {
				$objDefaultTLL = new CDefaultTTL( );
				$tmp = $this->hDefaultTTL->GetObject( array( FHOV_TABLE => 'ud_dttl', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CDefaultTTL' ) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$objDefaultTLL = current( $tmp );
				}
				//
				$iDirectZoneVId = 0;
				$objDirectFileZone = clone $objFileZone;
				$tmp = $objCMS->AddToWorld( WGI_ZONE, 'ModZone/Zone' );
				if ( $tmp->HasResult( ) ) {
					$iDirectZoneVId = $tmp->GetResult( 'graph_vertex_id' );
					$iFlags = 0;
					if ( isset( $mxdCurrentData[ 'client_add' ] ) ) {
						$iFlags |= FZF_CLIENTCREATE;
					}
					
					$tmp = array(
						'zone_graph_vertex_id' => $iDirectZoneVId,
						'zone_default_ttl' => $objDefaultTLL->ttl,
						'zone_flags' => $iFlags
					);
					if ( $objFileZone->type === FZT_REVERSE ) {
						$tmp[ 'zone_name' ] = $objFileZone->name.$this->szReverseSuffix;
					}
					$objDirectFileZone->Create( $tmp );
					$arrOptions = array( FHOV_IGNOREATTR => array( 'rrs' ), FHOV_TABLE => 'ud_zone' );
					$tmp = $this->hCommon->AddObject( array( $objDirectFileZone ), $arrOptions );
					if ( $tmp->HasError( ) ) {
						$objRet->AddError( new CError( 1, 'Добавление файла зоны провалилось' ) );
						$objCMS->DelFromWorld( array( $iDirectZoneVId ) );
					} else {
						if ( $bSaveLog ) {
							$modLogger = new CHModLogger( );
							$modLogger->AddLog(
								$objCMS->GetUserLogin( ),
								'ModZone',
								'ModZone::AddFileZone',
								'added new zone, name: '.$objDirectFileZone->name
							);
						}
						$objRet->AddResult( $objDirectFileZone->name, 'zone_name' );
						$tmp = $tmp->GetResult( 'insert_id' );
						$objRet->AddResult( intval( $tmp ), 'zone_id' );
						$tmp = array( 'zone_id' => $tmp );
						$objDirectFileZone->Create( $tmp );
						$objCMS->LinkObjects( $objUser->graph_vertex_id, $iDirectZoneVId, 'User/Zone' );
						$tmp = $this->AddDefaultRRs( $objDirectFileZone, $objUser );
						if ( $tmp->HasError( ) ) {
							$objRet->AddError( $tmp );
						} else {
							$this->AddFileZoneConf( $objDirectFileZone );
						}
					}
				}
			}
			//
			return $objRet;
		} // function AddFileZone2
		
		/**
		 * 	Добавление файла зон
		 * 	@param $arrInput array набор входных данных для файла
		 * 	@param $mxdCurrentData mixed текущие данные
		 * 	@param $iMode int режим работы объекта
		 * 	@return CResult
		 */
		public function AddFileZone( $arrInput, &$mxdCurrentData, $iMode = FLEX_FILTER_PHP, $bSaveLog = true, $bReverse = false ) {
			global $objCMS;
			$objRet = new  CResult( );
			$objFileZone = new CFileZone( );
			$tmp = $objFileZone->GetAttributeIndex( 'name', NULL, $iMode );
			if ( !isset( $arrInput[ $tmp ] ) ) {
				$objRet->AddError( new CError( 1,  'Отсутствует доменное имя' ) );
				return $objRet;
			}
			$szRegDate = $objFileZone->GetAttributeIndex( 'reg_date', NULL, $iMode );
			$szLastEdit = $objFileZone->GetAttributeIndex( 'last_edit', NULL, $iMode );
			$arrInput = array(
				$tmp => $arrInput[ $tmp ],
				$szRegDate => date( 'Y-m-d' ),
				$szLastEdit => date( 'Y-m-d H:i:s' )
			);
			$arrIndex = array(
				'id' => $objFileZone->GetAttributeIndex( 'id', NULL, $iMode ),
				'graph_vertex_id' => $objFileZone->GetAttributeIndex( 'graph_vertex_id', NULL, $iMode ),
				'type' => $objFileZone->GetAttributeIndex( 'type', NULL, $iMode ),
				'comment' => $objFileZone->GetAttributeIndex( 'comment', NULL, $iMode ),
			);
			$fltArray = new CArrayFilter( $arrIndex );
			$arrInput = $fltArray->Apply( $arrInput );
			if ( $bReverse ) {
				$arrInput[ $arrIndex[ 'type' ] ] = FZT_REVERSE;
			}
			
			$tmp = $objFileZone->Create( $arrInput, $iMode );
			if ( $tmp->HasError( ) ) {
				$mxdCurrentData[ 'filezone' ] = $objFileZone;
				$objRet->AddError( $tmp );
				return $objRet;
			}
			
			$objUser = $mxdCurrentData[ 'current_user' ];
			
			if ( $objFileZone->type === FZT_DIRECT ) {
				if ( !$this->FreeDomain( $objFileZone->GetAttributeValue( 'name' ) ) ) {
					$mxdCurrentData[ 'filezone' ] = $objFileZone;
					$objRet->AddError( new CError( 100, 'Доменное имя занято' ) );
					return $objRet;
				}
				
				$modRegru = new CHModRegRu( );
				$tmp = $modRegru->SendOrder( $objFileZone, $objUser );
				if ( $tmp->HasError( ) ) {
					$tmp = $tmp->GetError( 'error' );
					$mxdCurrentData[ 'filezone' ] = $objFileZone;
					$objRet->AddError( new CError( 101, $tmp->GetText( ) ) );
					return $objRet;
				}
			} else {
				if ( !CValidator::IpMaskReverse( $objFileZone->name ) ) {
					$mxdCurrentData[ 'filezone' ] = $objFileZone;
					$objRet->AddError( new CError( 102, 'Неверное имя обратной зоны' ) );
					return $objRet;
				} else {
					$szNetwork = IpReverseToNetwork( $objFileZone->name );
					if ( !CValidator::IpNetwork( $szNetwork ) ) {
						$mxdCurrentData[ 'filezone' ] = $objFileZone;
						$objRet->AddError( new CError( 103, "Неверное имя обратной зоны $szNetwork" ) );
						return $objRet;
					}
				}
			}
			
			$szNameIndex = $objFileZone->GetAttributeIndex( 'name', NULL, FLEX_FILTER_DATABASE );
			$szNameValue = $objFileZone->GetAttributeValue( 'name', FLEX_FILTER_DATABASE );
			if ( $objFileZone->type === FZT_REVERSE ) {
				$szNameValue = "'".@mysql_real_escape_string( $objFileZone->name.$this->szReverseSuffix )."'";
			}
			$arrOptions = array( FHOV_WHERE => "`$szNameIndex`=$szNameValue", FHOV_TABLE => 'ud_zone', FHOV_OBJECT => 'CFileZone' );
			$tmp = $this->hCommon->GetObject( $arrOptions );
			if ( $tmp->HasResult( ) ) {
				if ( $objFileZone->type === FZT_DIRECT ) {
					$objRet->AddError( new CError( 1, 'Доменное имя занято' ) );
				} else {
					$objRet->AddError( new CError( 1, 'Обратная зона уже существует' ) );
				}
			} else {
				$objDefaultTLL = new CDefaultTTL( );
				$tmp = $this->hDefaultTTL->GetObject( array( FHOV_TABLE => 'ud_dttl', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CDefaultTTL' ) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$objDefaultTLL = current( $tmp );
				}
				//
				$iDirectZoneVId = 0;
				$objDirectFileZone = clone $objFileZone;
				$tmp = $objCMS->AddToWorld( WGI_ZONE, 'ModZone/Zone' );
				if ( $tmp->HasResult( ) ) {
					$iDirectZoneVId = $tmp->GetResult( 'graph_vertex_id' );
					$iFlags = 0;
					if ( isset( $mxdCurrentData[ 'client_add' ] ) ) {
						$iFlags |= FZF_CLIENTCREATE;
					}
					if ( $objFileZone->type !== FZT_REVERSE ) {
						$iFlags |= FZF_REGISTERED;
					}
					
					$tmp = array(
						'zone_graph_vertex_id' => $iDirectZoneVId,
						'zone_default_ttl' => $objDefaultTLL->ttl,
						'zone_flags' => $iFlags
					);
					if ( $objFileZone->type === FZT_REVERSE ) {
						$tmp[ 'zone_name' ] = $objFileZone->name.$this->szReverseSuffix;
					}
					$objDirectFileZone->Create( $tmp );
					$arrOptions = array( FHOV_IGNOREATTR => array( 'rrs' ), FHOV_TABLE => 'ud_zone' );
					$tmp = $this->hCommon->AddObject( array( $objDirectFileZone ), $arrOptions );
					if ( $tmp->HasError( ) ) {
						$objRet->AddError( new CError( 1, 'Добавление файла зоны провалилось' ) );
						$objCMS->DelFromWorld( array( $iDirectZoneVId ) );
					} else {
						if ( $bSaveLog ) {
							$modLogger = new CHModLogger( );
							$modLogger->AddLog(
								$objCMS->GetUserLogin( ),
								'ModZone',
								'ModZone::AddFileZone',
								'added new zone, name: '.$objDirectFileZone->name
							);
						}
						$objRet->AddResult( $objDirectFileZone->name, 'zone_name' );
						$tmp = $tmp->GetResult( 'insert_id' );
						$objRet->AddResult( intval( $tmp ), 'zone_id' );
						$tmp = array( 'zone_id' => $tmp );
						$objDirectFileZone->Create( $tmp );
						$objCMS->LinkObjects( $objUser->graph_vertex_id, $iDirectZoneVId, 'User/Zone' );
						$tmp = $this->AddDefaultRRs( $objDirectFileZone, $objUser );
						if ( $tmp->HasError( ) ) {
							$objRet->AddError( $tmp );
						} else {
							$this->AddFileZoneConf( $objDirectFileZone );
						}
					}
				}
			}
			//
			return $objRet;
		} // function AddFileZone
		
		/**
		 * 	Добавление команд для конфигов в очередь
		 */
		public function AddConfCommand( $objMaster, $objSlave, $mxdDel = NULL ) {
			$arrConfigText = array(
				'master' => '',
				'slave' => ''
			);
			$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => 'ud_zone', FHOV_INDEXATTR => 'name', FHOV_OBJECT => 'CFileZone' ) );
			if ( $tmp->HasResult( ) ) {
				$arrObject = $tmp->GetResult( );
				if ( $objMaster ) {
					if ( $mxdDel ) {
						foreach( $arrObject as $i => $v ) {
							if ( isset( $mxdDel[ $i ] ) ) {
								unset( $arrObject[ $i ] );
							}
						}
					}
					$r = "";
					ob_start( );
					foreach( $arrObject as $i => $v ) {
						$szName = $v->name;
						if ( preg_match( '/\//', $szName ) ) {
							$szName = preg_replace( '/\//', '_', $szName ); 
						}
						$szName .= $this->szFileZoneSuffix;
						$objConfZone = new CBCSZone( );
						$objConfZone->Create( array(
							'zone_name' => $v->name,
							'type' => 'master',
							'file' => preg_replace( '/\/$/', '', $objMaster->zone_folder ).'/'.$szName,
						) );
						echo $objConfZone->GetText( );
					}
					$r = ob_get_clean( );
					if ( $r === false ) {
						$r = '';
					}
					$arrConfigText[ 'master' ] = $r;
					//
					if ( $objSlave ) {
						$r = '';
						ob_start( );
						foreach( $arrObject as $i => $v ) {
							$szName = $v->name;
							if ( preg_match( '/\//', $szName ) ) {
								$szName = preg_replace( '/\//', '_', $szName ); 
							}
							$szName .= $this->szFileZoneSuffix;
							$objConfZone = new CBCSZone( );
							$objConfZone->Create( array(
								'zone_name' => $v->name,
								'type' => 'slave',
								'masters' => $objMaster->ip,
							) );
							echo $objConfZone->GetText( );
						}
						$r = ob_get_clean( );
						if ( $r === false ) {
							$r = '';
						}
						$arrConfigText[ 'slave' ] = $r;
					}
				}
			}
			if ( $objMaster ) {
				$objSync = new CZoneSync( );
				$objSync->AddTicket( STT_SCP, STO_MASTERFILE, $this->szConfigFolder, $arrConfigText[ 'master' ] );
				if ( $objSlave ) {
					$objSync->AddTicket( STT_SCP, STO_SLAVEFILE, $this->szConfigFolder, $arrConfigText[ 'slave' ] );
				}
			}
		} // function AddConfCommand
		
		public function AddFileZoneConf( $objFileZone, $bSync = true ) {
			global $objCMS;
			$modLink = new CHModLink( );
			$arrServers = $modLink->GetMasterSlave( );
			$objMaster = $arrServers[ ST_MASTER ];
			$objSlave = $arrServers[ ST_SLAVE ];
			
			$szName = $objFileZone->name;
			if ( preg_match( '/\//', $szName ) ) {
				$szName = preg_replace( '/\//', '_', $szName ); 
			}
			$szName .= $this->szFileZoneSuffix;
			//
			$objFileZone->ClearRRs( );
			$this->GetZoneRRs( $objFileZone );
			
			if ( $bSync ) {
				$this->AddConfCommand( $objMaster, $objSlave );
				$szText = $objFileZone->GetText( FZM_BIND );
				$objSync = new CZoneSync( );
				$objSync->AddTicket( STT_SCP, STO_ZONEFILE, $szName, $szText );
			}
		} // function AddFileZoneConf
		
		public function DelFileZoneConf( $objFileZone, $bSync = true ) {
			/**
			 * 1. Удаляем запись из конфигов
			 * 2. Удаляем файл зоны
			 * 3. Синхронизируем конфиги
			 * 4. Удаляем файл зон с серверов
			 */
			$modLink = new CHModLink( );
			$arrServers = $modLink->GetMasterSlave( );
			$objMaster = $arrServers[ ST_MASTER ];
			$objSlave = $arrServers[ ST_SLAVE ];
			
			$arrName = array_keys( $objFileZone );
			foreach( $arrName as $i => $v ) {
				$szName = $v;
				if ( preg_match( '/\//', $szName ) ) {
					$szName = preg_replace( '/\//', '_', $szName ); 
				}
				$szName .= $this->szFileZoneSuffix;
				$arrName[ $i ] = $szName;
			}
			
			if ( $bSync ) {
				$this->AddConfCommand( $objMaster, $objSlave, $objFileZone );
				$objSync = new CZoneSync( );
				foreach( $arrName as $v ) {
					$objSync->AddTicket( STT_SSH, STO_ZONEFILE, $v, '' );
				}
			}
		} // function DelFileZoneConf
		
		/**
		 * 	Обновление данных файла зоны
		 * 	@param $objFileZone CFileZone зона
		 * 	@param $bSync bool выполнять ли синхронизацию ( true - да, false - нет )
		 * 	@return void
		 */
		public function UpdFileZoneConf( $objFileZone, $bSync = true ) {
			/**
			 * Обновление текста файла зон отправляется только главному серваку
			 * 1. Сохраняем текст зоны в файл
			 * 2. Синхронизируем файл зоны
			 */
			$szName = $objFileZone->name;
			if ( preg_match( '/\//', $szName ) ) {
				$szName = preg_replace( '/\//', '_', $szName ); 
			}
			$szName .= $this->szFileZoneSuffix;
			$objFileZone->ClearRRs( );
			$this->GetZoneRRs( $objFileZone );
			$modLink = new CHModLink( );
			$arrServers = $modLink->GetMasterSlave( );
			$objMaster = $arrServers[ ST_MASTER ];
			$objSlave = $arrServers[ ST_SLAVE ];
			
			if ( $bSync ) {
				$this->AddConfCommand( $objMaster, $objSlave );
				$szText = $objFileZone->GetText( FZM_BIND );
				$objSync = new CZoneSync( );
				$objSync->AddTicket( STT_SCP, STO_ZONEFILE, $szName, $szText );
			}
		} // function UpdFileZoneConf
		
		/**
		 * 	Добавление ресурсных записей по умолчанию
		 * 	@param $objFileZone CFileZone данные файла зон
		 * 	@param $objUser CClient владелец файла зон
		 * 	@return CResult
		 */
		private function AddDefaultRRs( &$objFileZone, &$objUser ) {
			global $objCMS;
			$objRet = new CResult( );
			$hServer = new CHServer( );
			$hServer->Create( array( 'database' => $objCMS->database ) );
			$hRRs = new CHResourceRecord( );
			$hRRs->Create( array( 'database' => $objCMS->database ) );
			$arrOptions = array( FHOV_TABLE => 'ud_server', FHOV_OBJECT => 'CServer' );
			$objServer = new CServer( );
			$arrServer = $hServer->GetObject( $arrOptions );
			if ( $arrServer->HasResult( ) ) {
				$arrServer = $arrServer->GetResult( );
			} else {
				$objRet->AddError( new CError( 1, 'Отсутствуют доступные сервера' ) );
				return $objRet;
			}
			// колбасим SOA запись из шаблона
			$objSOA = new CRR_SOA( );
			$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => 'ud_tplsoa', FHOV_OBJECT => 'CTplSoa' ) );
			if ( $tmp->HasResult( ) && !$tmp->HasError( ) ) {
				$objTplSoa = $tmp->GetResult( );
				$objTplSoa = current( $objTplSoa );
				$arrTplSoa = $objTplSoa->GetArray( );
				$arrTplSoa = $arrTplSoa->GetResult( );
				unset( $arrTplSoa[ 'id' ] );
				$arrSoa = array( );
				foreach( $arrTplSoa as $i => $v ) {
					$szIndex = $objSOA->GetAttributeIndex( $i );
					$arrSoa[ $szIndex ] = $v;
				}
				$arrSoa[ 'rr_zone_file_id' ] = $objFileZone->id;
				$arrSoa[ 'rr_name' ] = '@';
				$arrSoa[ 'rr_person' ] = ConvertEmailForSoa( $objUser->email ).'.';
				$arrSoa[ 'rr_serial' ] = date( 'Ymd' ).'01';
				$arrSoa[ 'rr_origin' ] .= '.';
				$tmp = $objSOA->Create( $arrSoa );
				if ( $tmp->HasError( ) ) {
					$objRet->AddError( $tmp );
					return $objRet;
				}
			}
			$tmp = $hRRs->AddObject( array( $objSOA ), array( FHOV_IGNOREATTR => array( 'origin', 'person', 'serial', 'refresh', 'retry', 'expire', 'minimum_ttl' ), FHOV_TABLE => 'ud_rr' ) );
			if ( $tmp->HasError( ) ) {
				$objRet->AddError( $tmp );
				return $objRet;
			}
			$szName = $objFileZone->name;
			// колбасим NS записи из серверов системы
			$j = 1;
			foreach( $arrServer as $i => $v ) {
				// для каждого сервака добавляем соответствующие ему NS
				$objNS = new CRR_NS( );
				$tmp = array(
					'rr_zone_file_id' => $objFileZone->id,
					'rr_name' => $szName.'.',
					'rr_ttl' => $objFileZone->default_ttl,
					'rr_server' => $v->name.'.',
					'rr_order' => $j++
				);
				$tmp = $objNS->Create( $tmp );
				if ( $tmp->HasError( ) ) {
					$objRet->AddError( $tmp );
				} else {
					$tmp = $hRRs->AddObject( array( $objNS ), array( FHOV_IGNOREATTR => array( 'server' ), FHOV_TABLE => 'ud_rr' ) );
					if ( $tmp->HasError( ) ) {
						$objRet->AddError( $tmp );
					}
				}
			}
			return $objRet;
		} // function AddDefaultRRs
		
		/**
		 * 	Установка порядка записей при обновлении
		 * 	@param $arrRrs array of CResourceRecord набор ресурсных записей
		 */
		public function SetRROrder( &$arrRrs ) {
			SetRROrder( $arrRrs );
		} // function SetRROrder
		
		/**
		 * 	Получение числа последнего индекса порядка
		 * 	@param $objFileZone CFileZone файл зон
		 * 	@return int
		 */
		public function GetLastOrderIndex( $objFileZone ) {
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$iZoneId = $objFileZone->GetAttributeValue( 'id' );
			$tmpRR = new CResourceRecord( );
			$szZoneFileIdIndex = $tmpRR->GetAttributeIndex( 'zone_file_id', NULL, FLEX_FILTER_DATABASE );
			$szOrderIndex = $tmpRR->GetAttributeIndex( 'order', NULL, FLEX_FILTER_DATABASE );
			$arrOptions = array(
				FHOV_WHERE => "`$szZoneFileIdIndex`=$iZoneId AND `$szOrderIndex`>0",
				FHOV_ONLYATTR => array( 'order', 'id' ),
				FHOV_ORDER => "`$szOrderIndex`",
				FHOV_TABLE => 'ud_rr',
				FHOV_INDEXATTR => 'id',
				FHOV_OBJECT => 'CResourceRecord'
			);
			$tmp = $this->hCommon->GetObject( $arrOptions );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmp = end( $tmp );
				return ( $tmp->order + 1 );
			}
			return 1;
		} // function GetLastOrderIndex
		
		/**
		 * 	Проверяет наличие шаблона SOA записи
		 */
		public function ExistsTplSoa( ) {
			if ( $this->hCommon === NULL ) {
				$this->InitObjectHandler( );
			}
			$tmp = $this->hCommon->CountObject( array( FHOV_TABLE => 'ud_tplsoa' ) );
			if ( $tmp->HasResult( ) ) {
				return $tmp->GetResult( 'count' );
			}
			return 0;
		} // function ExistsTplSoa
		
	} // class CHModZone
	
?>