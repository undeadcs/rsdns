<?php
	/**
	 *	Модуль авторизации
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModLogin
	 */

	class CHModLogin extends CHandler {
		private	$hSuperAdmin	= NULL,
			$hAdmin		= NULL,
			$hClient	= NULL,
			$hCommon	= NULL;
		
		public function InitHandlers( ) {
			global $objCMS;
			$this->hCommon = new CFlexHandler( );
			$this->hCommon->Create( array( 'database' => $objCMS->database ) );
			$this->hSuperAdmin	= //$this->hCommon;
			$this->hAdmin		= //$this->hCommon;
			$this->hClient		= $this->hCommon;
		} // function InitHandlers
		
		/**
		*	Проверка на срабатывание (перехват)
		*	@param $szQuery string строка тестирования
		*	@return bool
		*/
		public function Test( $szQuery ) {
			if ( !session_id( ) ) {
				session_start( );
			}
			$modInstall = new CHModInstall( );
			if ( $modInstall->IsSystemInstalled( ) ) {
				return true;
			}
			if ( isset( $_SESSION[ 'logged' ] ) ) {
				unset( $_SESSION[ 'logged' ] );
			}
			return false;
		} // function Test
		
		/**
		*	Обработка
		*	@param $szQuery string строка, на которой произошел перехват
		*	@return bool
		*/
		public function Process( $szQuery ) {
			global $objCMS, $objCurrent, $mxdCurrentData, $szCurrentMode, $arrErrors;
			$this->InitHandlers( );
			//
			if ( isset( $_SESSION[ 'logged' ] ) ) {
				$iId = intval( $_SESSION[ 'logged' ][ 'id' ] );
				$iVId = intval( $_SESSION[ 'logged' ][ 'v_id' ] );
				$iRank = intval( $_SESSION[ 'logged' ][ 'rank' ] );
				$szLogin = $_SESSION[ 'logged' ][ 'login' ];
				$szPassword = $_SESSION[ 'logged' ][ 'password' ];
				$objSysAcc = new CSystemAccount( );
				$objSysAcc->Create( array( 'id' => $iId, 'v_id' => $iVId, 'rank' => $iRank, 'login' => $szLogin ) );
				$objCMS->SetUser( $objSysAcc );
				
				if ( preg_match( '/^\/exit\//', $szQuery ) ) {
					$modZone = new CHModZone( );
					$modZone->UnlockUserZone( $iVId );
					unset( $_SESSION[ 'logged' ] );
					Redirect( $objCMS->GetPath( 'root_relative' ) );
					exit;
				}
			} else {
				if ( count( $_POST ) && isset( $_POST[ 'login' ], $_POST[ 'password' ] ) ) {
					$arrData[ 'login' ] = $_POST[ 'login' ];
					$arrData[ 'password' ] = $_POST[ 'password' ];
					
					$modLogger = new CHModLogger( );
					$modUser = new CHModUser( );
					$tmp = $modUser->GetUser( $arrData[ 'login' ], FLEX_FILTER_FORM );
					if ( $tmp->HasResult( ) ) {
						$szType = $tmp->GetResult( 'type' );
						$tmp = $tmp->GetResult( );
						$tmp = current( $tmp );
						if ( $tmp->IsPasswordEqual( $arrData[ 'password' ] ) ) {
							$bBlock = false;
							if ( $szType == 'client' && $tmp->state === US_NOTACTIVE ) {
								$bBlock = true;
							}
							if ( !$bBlock ) {
								$szLastLoginIndex = $tmp->GetAttributeIndex( 'last_login' );
								$tmp->Create( array( $szLastLoginIndex => date( 'Y-m-d H:i:s' ) ) );
								$tmp1 = array( );
								$tmp1[ 'id' ] = $tmp->id;
								$tmp1[ 'v_id' ] = $tmp->graph_vertex_id;
								$tmp1[ 'login' ] = $arrData[ 'login' ];
								$tmp1[ 'password' ] = $arrData[ 'password' ];
								if ( $tmp->rank === NULL ) {
									$tmp1[ 'rank' ] = SUR_CLIENT;
									$this->hClient->UpdObject( array( $tmp ), array( FHOV_ONLYATTR => array( 'id', 'last_login' ), FHOV_TABLE => 'ud_client', FHOV_INDEXATTR => 'id' ) );
								} else {
									$this->hAdmin->UpdObject( array( $tmp ), array( FHOV_ONLYATTR => array( 'id', 'last_login' ), FHOV_TABLE => 'ud_admin', FHOV_INDEXATTR => 'id' ) );
									$tmp2 = array(
										UR_ADMIN => SUR_ADMIN,
										UR_SUPERADMIN => SUR_SUPERADMIN,
										UR_OPERATOR => SUR_OPERATOR
									);
									$tmp1[ 'rank' ] = $tmp2[ $tmp->rank ];
								}
								$_SESSION[ 'logged' ] = $tmp1;
							}
						} else {
							$modLogger->AddLog(
								$arrData[ 'login' ],
								'ModLogin',
								'ModLogin::Login',
								'wrong password: '.@$arrData[ 'password' ]
							);
						}
					} else {
						$modLogger->AddLog(
							'',
							'ModLogin',
							'ModLogin::Login',
							'wrong data, login: '.@$arrData[ 'login' ].' password: '.@$arrData[ 'password' ]
						);
					}
					Redirect( $objCMS->GetPath( 'root_relative' ) );
				}
				
				$objCurrent = 'Login';
				$szCurrentMode = 'Form';
				
				$szFolder = $objCMS->GetPath( 'root_application' );
				if ( $szFolder !== false && file_exists( $szFolder.'/index.php' ) ) {
					include_once( $szFolder.'/index.php' );
				}
				return true;
			}
			
			return false;
		} // function Process
		
	} // class CHModLogin
	
?>