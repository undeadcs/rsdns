<?php
	/**
	 *	Модуль резервных копий
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModBackup
	 */

	require( 'backup.backup.php' );
	require( 'backup.filter.php' );
	
	/**
	 *	Перехватчик для модуля Backup
	 */
	class CHModBackup extends CHandler {
		private	$szBackupFolder	= '',
			$hCommon	= NULL,
			$hBackup	= NULL;
			
		public	$fBackupTTL	= 2592000.0,
			$iPageSize	= 15;
		
		/**
		 * 	Инициализация обработчиков
		 */
		public function InitHandlers( ) {
			global $objCMS;
			$this->hCommon = new CFlexHandler( );
			$this->hCommon->Create( array( 'database' => $objCMS->database ) );
			$this->hBackup = new CHBackup( );
			$this->hBackup->Create( array( 'database' => $objCMS->database ) );
			$this->szBackupFolder = $objCMS->GetPath( 'root_system' ).'/backup';
			
			$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => 'ud_system', FHOV_OBJECT => 'CSystemConfig' ) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmp = current( $tmp );
				if ( $tmp->backup !== '' ) {
					$this->szBackupFolder = $tmp->backup;
				}
				unset( $tmp );
			}
			$this->szBackupFolder = preg_replace( '/\/$/', '', $this->szBackupFolder );
			$this->hBackup->CheckTable( array( FHOV_TABLE => $this->szBackupFolder ) );
		} // function InitHandlers
		
		/**
		 *	Проверка на срабатывание (перехват)
		 *	@param $szQuery string строка тестирования
		 *	@return bool
		 */
		public function Test( $szQuery ) {
			return ( preg_match( '/^\/backup\//', $szQuery ) ? true : false );
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
			$objCMS->SetWGI( WGI_BACKUP );
			$objCMS->SetWGIState( MF_THIS );
			$objCurrent = 'Backup';
			$szCurrentMode = 'List';
			$modLogger = new CHModLogger( );
			
			if ( preg_match( '/^\/backup\/\+\//', $szQuery ) ) {
				if ( count( $_POST ) && isset( $_POST[ 'backup' ] ) ) {
					$arrData = $_POST[ 'backup' ];
					$iComponents = 0;
					if ( isset( $arrData[ 'db' ] ) ) {
						$iComponents |= BCT_DBDUMP;
					}
					if ( isset( $arrData[ 'source' ] ) ) {
						$iComponents |= BCT_SOURCE;
					}
					if ( isset( $arrData[ 'zone' ] ) ) {
						$iComponents |= BCT_FILEZONE;
					}
					if ( $iComponents ) {
						$objBackup = new CBackup( );
						$szDate = date( 'YmdHis' );
						$objBackup->Create( array(
							'id'		=> $szDate,
							'cr_date'	=> $szDate,
							'components'	=> $iComponents
						) );
						$tmp = $this->hBackup->AddObject( array( $objBackup ), array( FHOV_TABLE => $this->szBackupFolder ) );
						if ( !$tmp->HasError( ) ) {
							$modLogger->AddLog(
								$objCMS->GetUserLogin( ),
								'ModBackup',
								'ModBackup::AddBackup',
								'created new backup'
							);
						}
					}
					Redirect( $objCMS->GetPath( "root_relative" )."/backup/" );
				}
				
				$szCurrentMode = 'Add';
				$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
				//
			} elseif ( preg_match( '/^\/backup\/export\/\d*\//', $szQuery ) ) {
				$tmp = NULL;
				preg_match( '/^\/backup\/export\/(\d*)\//', $szQuery, $tmp );
				$szId = $tmp[ 1 ];
				$tmp = $this->hBackup->GetObject( array(
					FHOV_WHERE => $szId,
					FHOV_TABLE => $this->szBackupFolder,
				) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$objBackup = current( $tmp );
					$szFolder = $this->szBackupFolder;
					$szName = $objBackup->id.'.tar.lzma';
					$szFileName = $szFolder.'/'.$szName;
					$iSize = filesize( $szFileName );
					
					header( 'Content-Description: File Transfer' );
					header( 'Content-Type: application/octet-stream' );
					header( 'Content-Disposition: attachment; filename="'.$szName.'"' );
					header( 'Content-Transfer-Encoding: binary' );
					header( 'Expires: 0' );
					header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
					header( 'Pragma: public' );
					header( 'Content-Length: '.$iSize );
					@ob_clean( );
					@flush( );
					readfile( $szFileName );
					exit;
				}
				//
			} elseif ( preg_match( '/^\/backup\/restore\/\d*\//', $szQuery ) ) {
				$tmp = NULL;
				preg_match( '/^\/backup\/restore\/(\d*)\//', $szQuery, $tmp );
				$szId = $tmp[ 1 ];
				$tmp = $this->hBackup->GetObject( array(
					FHOV_WHERE => $szId,
					FHOV_TABLE => $this->szBackupFolder,
				) );
				if ( $tmp->HasResult( ) ) {
					$szCurrentMode = 'Restore';
					$objCMS->SetWGIState( MF_THIS | MF_CURRENT );
					$mxdCurrentData[ 'current_backup' ] = $tmp->GetResult( $szId );
					
					if ( count( $_POST ) && isset( $_POST[ 'backup' ] ) ) {
						$arrData = $_POST[ 'backup' ];
						$modZone = new CHModZone( );
						$arrZoneFolder = $modZone->GetFolders( );
						
						$iComponents = $mxdCurrentData[ 'current_backup' ]->components;
						$iSelected = 0;
						if ( ( $iComponents & BCT_DBDUMP ) && isset( $arrData[ 'db' ] ) ) {
							$iSelected |= BCT_DBDUMP;
						}
						if ( ( $iComponents & BCT_SOURCE ) && isset( $arrData[ 'source' ] ) ) {
							$iSelected |= BCT_SOURCE;
						}
						if ( ( $iComponents & BCT_FILEZONE ) && isset( $arrData[ 'zone' ] ) ) {
							$iSelected |= BCT_FILEZONE;
						}
						
						$szFolder = $this->szBackupFolder;
						$szName = $mxdCurrentData[ 'current_backup' ]->id;
						$szZipName = $szFolder.'/'.$szName.'.tar.lzma';
						if ( file_exists( $szZipName ) ) {
							$bExtracted = true;
							$cmd = 'lzma -dk '.$szName.'.tar.lzma';
							$szOldFolder = getcwd( );
							chdir( $szFolder );
							exec( $cmd, $tmp, $tmp1 );
							if ( $tmp1 ) {
								$modLogger->AddLog(
									$objCMS->GetUserLogin( ),
									'ModBackup',
									'ModBackup::UseBackup',
									'failed restore from backup '.$szName
								);
								Redirect( $objCMS->GetPath( 'root_relative' ).'/backup/' );
							}
							$cmd = 'tar -xf '.$szName.'.tar';
							exec( $cmd, $tmp, $tmp1 );
							if ( $tmp1 ) {
								$modLogger->AddLog(
									$objCMS->GetUserLogin( ),
									'ModBackup',
									'ModBackup::UseBackup',
									'failed restore from backup '.$szName
								);
								Redirect( $objCMS->GetPath( 'root_relative' ).'/backup/' );
							} else {
								clearstatcache( );
								unlink( $szName.'.tar' );
							}
							chdir( $szOldFolder );
							if ( $bExtracted ) {
								$arrFolders = array(
									'db'		=> $szFolder.'/'.$szName.'/database_dump',
									'source'	=> $szFolder.'/'.$szName.'/source',
									'zone'		=> $szFolder.'/'.$szName.'/zone',
									'config'	=> $szFolder.'/'.$szName.'/config'
								);
								if ( $iSelected & BCT_DBDUMP ) {
									$this->hBackup->RestoreTables( $arrFolders[ 'db' ] );
								}
								if ( $iSelected & BCT_FILEZONE ) {
									DirClear( $arrZoneFolder[ 'zone' ] );
									DirClear( $arrZoneFolder[ 'config' ] );
									if ( !file_exists( $arrZoneFolder[ 'zone' ] ) ) {
										mkdir( $arrZoneFolder[ 'zone' ], 0755 );
									}
									if ( !file_exists( $arrZoneFolder[ 'config' ] ) ) {
										mkdir( $arrZoneFolder[ 'config' ], 0755 );
									}
									DirCopy( $arrFolders[ 'zone' ], $arrZoneFolder[ 'zone' ] );
									DirCopy( $arrFolders[ 'config' ], $arrZoneFolder[ 'config' ] );
									//
									$modLink = new CHModLink( );
									$tmp = $modLink->GetServers( );
									if ( $tmp->HasResult( ) ) {
										$tmp = $tmp->GetResult( );
										$objMaster = NULL;
										$objSlave = NULL;
										$szIp = '';
										foreach( $tmp as $i => $v ) {
											if ( $v->type === ST_MASTER ) {
												if ( !$objMaster ) {
													$objMaster = clone $v;
												}
											} elseif ( $v->type === ST_SLAVE ) {
												if ( !$objSlave ) {
													$objSlave = clone $v;
												}
											}
											if ( $objMaster && $objSlave ) {
												break;
											}
										}
										unset( $tmp );
										$objRsyncAcc = NULL;
										$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => "ud_acc_rsync", FHOV_OBJECT => "CRsyncAccount" ) );
										if ( $tmp->HasResult( ) ) {
											$tmp = $tmp->GetResult( );
											$objRsyncAcc = current( $tmp );
											
											if ( $objMaster ) {
												exec( "rsync -a --delete ".$arrFolders[ "zone" ]."/ ".$objRsyncAcc->username."@".$objMaster->ip.":".$objMaster->root_prefix.preg_replace( '/\/$/', '', $objMaster->zone_folder )."/" );
												exec( "scp ".$arrFolders[ "config" ]."/master.zones ".$objRsyncAcc->username."@".$objMaster->ip.":".$objMaster->config_file );
											}
											if ( $objSlave ) {
												exec( "scp ".$arrFolders[ "config" ]."/slave.zones ".$objRsyncAcc->username."@".$objSlave->ip.":".$objSlave->config_file );
											}
										}
									}
								}
								if ( $iSelected & BCT_SOURCE ) {
									$this->hBackup->RestoreSource( $arrFolders[ 'source' ] );
								}
								DirClear( $szFolder.'/'.$szName );
								$modLogger->AddLog(
									$objCMS->GetUserLogin( ),
									'ModBackup',
									'ModBackup::UseBackup',
									'restored from backup'
								);
							}
						}
						Redirect( $objCMS->GetPath( 'root_relative' ).'/backup/' );
					}
				}
				//
			} elseif ( count( $_POST ) && isset( $_POST[ 'del' ] ) ) {
				$arrData = $_POST[ 'del' ];
				$tmp = $this->hBackup->DelObject( $arrData, array( FHOV_TABLE => $this->szBackupFolder ) );
				if ( !$tmp->HasResult( ) ) {
					$modLogger->AddLog(
						$objCMS->GetUserLogin( ),
						'ModBackup',
						'ModBackup::DelBackup',
						'deleted backups'
					);
				}
				Redirect( $objCMS->GetPath( 'root_relative' ).'/backup/' );
			}
			
			if ( $mxdCurrentData === NULL ) {
				$mxdCurrentData[ 'backup_list' ] = array( );
				$arrOptions = array( FHOV_TABLE => $this->szBackupFolder );
				//
				$objFilter = new CBackupFilter( );
				$objFilter->Create( $_GET, FLEX_FILTER_FORM );
				$szWhere = $objFilter->GetWhere( );
				if ( $szWhere !== '' ) {
					$arrOptions[ FHOV_WHERE ] = $szWhere;
				}
				$mxdCurrentData[ 'filter' ] = $objFilter;
				$szUrl = $objFilter->GetUrlAttr( );
				if ( $szUrl === '' ) {
					$szUrl = $objCMS->GetPath( 'root_relative' ).'/backup/?';
				} else {
					$szUrl = $objCMS->GetPath( 'root_relative' ).'/backup/?'.$szUrl.'&';
				}
				//
				$iCount = $this->hBackup->CountObject( $arrOptions );
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
				$tmp = $this->hBackup->GetObject( $arrOptions );
				if ( $tmp->HasResult( ) ) {
					$mxdCurrentData[ 'backup_list' ] = $tmp->GetResult( );
					$mxdCurrentData[ 'pager' ] = $objPager;
				}
			}
			
			// передаем управление приложению
			$szFolder = $objCMS->GetPath( 'root_application' );
			if ( $szFolder !== false && file_exists( $szFolder.'/index.php' ) ) {
				include_once( $szFolder.'/index.php' );
			}
			return true;
		} // function Process
		
		public function MakeBackup( $iComponents, $szDateFormat = 'YmdHis', $bSaveLog = true ) {
			global $objCMS;
			if ( $this->hBackup === NULL ) {
				$this->InitHandlers( );
			}
			
			$szDate = date( $szDateFormat );
			$objBackup = new CBackup( );
			$objBackup->Create( array(
				'id'		=> $szDate,
				'cr_date'	=> $szDate,
				'components'	=> $iComponents
			) );
			$tmp = $this->hBackup->AddObject( array( $objBackup ), array( FHOV_TABLE => $this->szBackupFolder ) );
		} // function MakeBackup
		
		public function DeleteOldBackups( ) {
			global $objCMS;
			if ( $this->hBackup === NULL ) {
				$this->InitHandlers( );
			}
			$iCurDate = time( );
			$tmp = $this->hBackup->GetObject( array( FHOV_TABLE => $this->szBackupFolder ) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				echo 'current time: '.$iCurDate.' '.date( 'Y-m-d H:i:s', $iCurDate )."\n";
				$arrToDel = array( );
				foreach( $tmp as $i => $v ) {
					$szDate = "";
					if ( preg_match( '/\d{12}/', $i ) ) {
						$szDate = $i;
					} elseif ( preg_match( '/\d{8}/', $i ) ) {
						$szDate = $i.'000000';
					}
					if ( $szDate !== '' ) {
						$iDate = strtotime( $szDate );
						$fDiff = floatval( $iCurDate - $iDate );
						echo date( 'Y-m-d H:i:s', $iDate ).' '.$iDate.' '.$fDiff.' '.( $fDiff > $this->fBackupTTL ? 'delete' : 'stay' )."\n";
						if ( $fDiff > $this->fBackupTTL ) {
							$arrToDel[ $i ] = $i;
						}
					}
				}
				$this->hBackup->DelObject( $arrToDel, array( FHOV_TABLE => $this->szBackupFolder ) );
			} else {
				echo 'backup list is empty'."\n";
			}
		} // function DeleteOldBackups
		
	} // class CHModZone
?>