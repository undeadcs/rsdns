<?php
	/**
	 *	Модуль установки
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModInstall
	 */

	class CHDbAccount extends CFlexHandler {
		protected	$path		= '',
				$account	= NULL;
		
		/**
		 *	Инициализация атрибута объекта
		 *	@param $szName string имя атрибута
		 *	@param $arrInput mixed некое значение
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return CResult
		 */
		protected function InitAttr( $szName, &$arrInput, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::InitAttr( $szName, $arrInput, $arrConfig, $iMode );
			if ( $szName === 'account' ) {
				if ( $this->account === NULL ) {
					$this->account = new CDbAccount( );
				}
				$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
				if ( isset( $arrInput[ $szIndex ] ) ) {
					$this->account->Create( $arrInput[ $szIndex ], $iMode );
				} else {
					$this->account = $this->GetAccount( );
				}
			}
			return $objRet;
		} // function InitAttr
		
		/**
		 * 	Парсит файл с настройками аккаунта к СУБД
		 * 	@return CDbAccount
		 */
		public function GetAccount( ) {
			$objRet = new CDbAccount( );
			if ( !empty( $this->path ) && file_exists( $this->path ) ) {
				$iSize = filesize( $this->path );
				if ( $iSize ) {
					$hFile = fopen( $this->path, 'rb' );
					$szText = fread( $hFile, $iSize );
					fclose( $hFile );
					$tmp = NULL;
					preg_match_all( '/"([^"]*)"\s*=>\s*"([^"]*)"/sU', $szText, $tmp );
					if ( count( $tmp ) == 3 && ( count( $tmp[ 1 ] ) == 4 ) && ( count( $tmp[ 2 ] ) == 4 ) ) {
						$tmp1 = array( );
						foreach( $tmp[ 1 ] as $i => $v ) {
							$tmp1[ $v ] = $tmp[ 2 ][ $i ];
						}
						$objRet->Create( $tmp1 );
					}
				}
			}
			return $objRet;
		} // function ParseConfigFile
		
		public function GetText( ) {
			ob_start( );
?>

//INSTALLED {:date='<?=date( 'Y-m-d H:i:s' )?>'}
global $g_arrConfig;
$g_arrConfig[ "system" ][ "objDatabase" ] = array(
	"server" => "<?=$this->account->server?>",
	"username" => "<?=$this->account->username?>",
	"password" => "<?=$this->account->password?>",
	"database" => "<?=$this->account->database?>"
);
			
<?
			$r = ob_get_clean( );
			if ( $r === false ) {
				$r = '';
			}
			return $r;
		} // function GetText
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ 'account' ][ FLEX_CONFIG_TYPE ] = FLEX_TYPE_OBJECT;
			return $arrConfig;
		} // function GetConfig
		
	} // class CHDbAccount
	
	/**
	 * 	Акк для rsync
	 */
	class CRsyncAccount extends CFlex {
		protected	$id		= 0,	// id записи
				$username	= '',	// логин
				$password	= '',	// пароль
				$key		= '',	// ключ
				$port		= 0;	// порт
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'id' => true, 'username' => true, 'password' => true, 'key' => true, 'port' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE	] = 'ud_acc_rsync';
			$arrConfig[ FLEX_CONFIG_SELECT	] = 'id';
			$arrConfig[ FLEX_CONFIG_UPDATE	] = 'id';
			$arrConfig[ FLEX_CONFIG_DELETE	] = 'id';
			// настройки атрибутов
			$arrConfig[ 'id'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ 'id'		][ FLEX_CONFIG_DIGITS	] = 10;
			return $arrConfig;
		} // function GetConfig
		
	} // class CRsyncAccount
	
	/**
	 * 	Конфиг системы
	 */
	class CSystemConfig extends CFlex {
		protected	$id		= 0,
				$backup		= '', // папка где хранятся бэкапы
				$zone		= '', // папка где хранятся файлы зон
				$config		= '', // папка где хранятся конфиги для бинда
				$logfile	= ''; // файл сислога для агрегатора
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'id' => true, 'backup' => true, 'zone' => true,	'config' => true, 'logfile' => true
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE	] = 'ud_system';
			$arrConfig[ FLEX_CONFIG_SELECT	] = 'id';
			$arrConfig[ FLEX_CONFIG_UPDATE	] = 'id';
			$arrConfig[ FLEX_CONFIG_DELETE	] = 'id';
			// настройки атрибутов
			$arrConfig[ 'id'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ 'id'		][ FLEX_CONFIG_DIGITS	] = 10;
			return $arrConfig;
		} // function GetConfig
		
	} // class CSystemConfig
	
	class CHModInstall extends CHandler {
		private $hDbAccount = NULL;
		private $hCommon = NULL;
		
		private function InitHandlers( ) {
			global $objCMS;
			$this->hDbAccount = new CHDbAccount( );
			$this->hDbAccount->Create( array( 'database' => $objCMS->database, 'path' => $objCMS->GetPath( 'root_system' ).'/db.php' ) );
			
			$this->hCommon = new CFlexHandler( );
			$this->hCommon->Create( array( 'database' => $objCMS->database ) );
			
			if ( $objCMS->database ) {
				$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_acc_regru',	FHOV_OBJECT => 'CRegRuAccount'	) );
				$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_acc_rsync',	FHOV_OBJECT => 'CRsyncAccount'	) );
				$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_system',		FHOV_OBJECT => 'CSystemConfig'	) );
				$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_admin',		FHOV_OBJECT => 'CAdmin'		) );
			}
		}
		
		/**
		 * 	Проверяет установлена ли система
		 */
		public function IsSystemInstalled( ) {
			global $objCMS;
			if ( !file_exists( $objCMS->GetPath( 'root_system' ).'/db.php' ) ) {
				return false;
			}
			return true;
		} // function IsSystemInstalled
		
		public function Test( $szQuery ) {
			global $objCMS;
			if ( !$this->IsSystemInstalled( ) ) {
				return true;
			}
			$iUserRank = $objCMS->GetUserRank( );
			if ( $iUserRank != SUR_SUPERADMIN ) {
				return false;
			}
			return ( preg_match( '/^\/\$\//', $szQuery ) ? true : false );
		} // function Test
		
		public function Process( $szQuery ) {
			global $objCMS, $objCurrent, $mxdCurrentData, $szCurrentMode, $arrErrors;
			$this->InitHandlers( );
			$modUser	= new CHModUser( );
			$objCurrent	= 'Install';
			$szCurrentMode	= '1';
			$arrErrors	= array( );
			$mxdCurrentData	= array( );
			
			$mxdCurrentData[ 'db'		] = new CDbAccount( );
			$mxdCurrentData[ 'superadmin'	] = new CAdmin( );
			$mxdCurrentData[ 'regru'	] = new CRegRuAccount( );
			$mxdCurrentData[ 'rsync'	] = new CRsyncAccount( );
			$mxdCurrentData[ 'system'	] = new CSystemConfig( );
			
			$arrNeed = array(
				'regru'		=> array( 'table' => 'ud_acc_regru',	'class' => 'CRegRuAccount' ),
				'rsync'		=> array( 'table' => 'ud_acc_rsync',	'class' => 'CRsyncAccount' ),
				'system'	=> array( 'table' => 'ud_system',	'class' => 'CSystemConfig' )
			);
			
			$mxdCurrentData[ 'db' ] = $this->hDbAccount->GetAccount( );
			
			$tmp = $modUser->GetSuperAdmin( );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( 'superadmin' );
				$mxdCurrentData[ 'superadmin' ] = $tmp;
			}
			
			foreach( $arrNeed as $i => $v ) {
				$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => $v[ "table" ], FHOV_OBJECT => $v[ "class" ] ) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$mxdCurrentData[ $i ] = current( $tmp );
				}
			}
			//
			
			if ( count( $_POST ) && isset( $_POST[ 'db' ], $_POST[ 'superadmin' ], $_POST[ 'regru' ], $_POST[ 'rsync' ], $_POST[ 'system' ] ) ) {
				$bWasError = false;
				$fltArray = new CArrayFilter( );
				$arrData = $_POST[ 'db' ];
				$tmp = $mxdCurrentData[ 'db' ]->Create( $arrData, FLEX_FILTER_FORM );
				if ( $tmp->HasError( ) ) {
					$bWasError = true;
					$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
				}
				
				$arrData = $_POST[ 'superadmin' ];
				$arrConfig = $mxdCurrentData[ 'superadmin' ]->GetConfig( );
				$arrFilter = array(
					'id'			=> $mxdCurrentData[ 'superadmin' ]->GetAttributeIndex( 'id',			$arrConfig, FLEX_FILTER_FORM ),
					'graph_vertex_id'	=> $mxdCurrentData[ 'superadmin' ]->GetAttributeIndex( 'graph_vertex_id',	$arrConfig, FLEX_FILTER_FORM ),
					'reg_date'		=> $mxdCurrentData[ 'superadmin' ]->GetAttributeIndex( 'reg_date',		$arrConfig, FLEX_FILTER_FORM ),
					'last_edit'		=> $mxdCurrentData[ 'superadmin' ]->GetAttributeIndex( 'last_edit',		$arrConfig, FLEX_FILTER_FORM ),
					'last_login'		=> $mxdCurrentData[ 'superadmin' ]->GetAttributeIndex( 'last_login',		$arrConfig, FLEX_FILTER_FORM ),
					'rank'			=> $mxdCurrentData[ 'superadmin' ]->GetAttributeIndex( 'rank',			$arrConfig, FLEX_FILTER_FORM )
				);
				unset( $arrConfig );
				$fltArray->SetArray( $arrFilter );
				$arrData = $fltArray->Apply( $arrData );
				$szPasswordIndex = $mxdCurrentData[ 'superadmin' ]->GetAttributeIndex( 'password', NULL, FLEX_FILTER_FORM );
				$arrIgnoreAttr = array( );
				if ( $mxdCurrentData[ 'superadmin' ]->id && ( !isset( $arrData[ $szPasswordIndex ] ) || empty( $arrData[ $szPasswordIndex ] ) ) ) {
					$arrData[ $szPasswordIndex ] = $mxdCurrentData[ 'superadmin' ]->password;
					$arrIgnoreAttr[ ] = 'password';
				}
				$arrData[ $arrFilter[ 'rank' ] ] = UR_SUPERADMIN;
				$tmp = $mxdCurrentData[ 'superadmin' ]->Create( $arrData, FLEX_FILTER_FORM );
				if ( $tmp->HasError( ) ) {
					$bWasError = true;
					$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
				}
				
				foreach( $arrNeed as $i => $v ) {
					$arrData = $_POST[ $i ];
					$arrFilter = array(
						'id' => $mxdCurrentData[ $i ]->GetAttributeIndex( 'id', NULL, FLEX_FILTER_FORM )
					);
					$fltArray->SetArray( $arrFilter );
					$arrData = $fltArray->Apply( $arrData );
					$tmp = $mxdCurrentData[ $i ]->Create( $arrData, FLEX_FILTER_FORM );
					if ( $tmp->HasError( ) ) {
						$bWasError = true;
						$arrErrors = array_merge( $arrErrors, $tmp->GetError( ) );
					}
				}
				
				if ( !$bWasError ) {
					if ( $objCMS->database === NULL ) {
						$objCMS->Create( array( 'objDatabase' => $mxdCurrentData[ 'db' ] ) );
						$this->InitHandlers( );
					}
					
					$hAdmin = new CFlexHandler( );
					$hAdmin->Create( array( 'database' => $objCMS->database ) );
					$szLastEditIndex = $mxdCurrentData[ 'superadmin' ]->GetAttributeIndex( 'last_edit', NULL, FLEX_FILTER_DATABASE );
					$szRegIndex = $mxdCurrentData[ 'superadmin' ]->GetAttributeIndex( 'reg_date', NULL, FLEX_FILTER_DATABASE );
					if ( $mxdCurrentData[ 'superadmin' ]->id ) {
						$mxdCurrentData[ 'superadmin' ]->Create( array( $szLastEditIndex => date( 'Y-m-d H:i:s' ) ) );
						$arrOptions = array( FHOV_TABLE => 'ud_admin', FHOV_INDEXATTR => 'id' );
						if ( !empty( $arrIgnoreAttr ) ) {
							$arrOptions[ FHOV_IGNOREATTR ] = $arrIgnoreAttr;
						}
						$arrOptions[ FHOV_IGNOREATTR ][ ] = 'last_login';
						$hAdmin->UpdObject( array( $mxdCurrentData[ 'superadmin' ] ), $arrOptions );
					} else {
						$tmp = array( );
						$tmp[ $szRegIndex	] = date( 'Y-m-d'	);
						$tmp[ $szLastEditIndex	] = date( 'Y-m-d H:i:s'	);
						$mxdCurrentData[ 'superadmin' ]->Create( $tmp );
						$this->hCommon->AddObject( array( $mxdCurrentData[ 'superadmin' ] ), array( FHOV_IGNOREATTR => array( 'last_login' ), FHOV_TABLE => 'ud_admin' ) );
					}
					
					$this->hDbAccount->Create( array( 'account' => $mxdCurrentData[ 'db' ] ), FLEX_FILTER_FORM );
					$hFile = fopen( $objCMS->GetPath( 'root_system' ).'/db.php', 'wb' );
					if ( $hFile ) {
						$szText = "<?php".$this->hDbAccount->GetText( )."?>";
						fwrite( $hFile, $szText, strlen( $szText ) );
						fclose( $hFile );
					}
					
					foreach( $arrNeed as $i => $v ) {
						if ( $mxdCurrentData[ $i ]->id ) {
							$tmp = $this->hCommon->UpdObject( array( $mxdCurrentData[ $i ] ), array( FHOV_TABLE => $v[ 'table' ], FHOV_INDEXATTR => 'id' ) );
							if ( $tmp->HasError( ) ) {
								ShowVarD( $tmp->GetError( ) );
							}
						} else {
							$tmp = $this->hCommon->AddObject( array( $mxdCurrentData[ $i ] ), array( FHOV_TABLE => $v[ 'table' ] ) );
							if ( $tmp->HasError( ) ) {
								ShowVarD( $tmp->GetError( ) );
							}
						}
					}
					
					Redirect( $objCMS->GetPath( 'root_relative' ).'/$/' );
				}
			}
			
			$szFolder = $objCMS->GetPath( 'root_application' );
			if ( $szFolder !== false && file_exists( $szFolder.'/index.php' ) ) {
				include_once( $szFolder.'/index.php' );
			}
			
			return true;
		} // function Process
		
		/**
		 * 	В ручную сносим
		 */
		private function CustomDestroy( ) {
			$tmp = array(
				'server' => 'localhost',
				'username' => 'root',
				'password' => 'ahchi0ieXieng6',
				'database' => 'undeadcs'
			);
			
			$hCon = mysql_connect( $tmp[ 'server' ], $tmp[ 'username' ], $tmp[ 'password' ] );
			mysql_query( 'DROP DATABASE `'.$tmp[ 'database' ].'`', $hCon );
			echo mysql_errno( );
			mysql_close( $hCon );
			exit;
		} // function CustomDestroy
		
	} // class CHModInstall
	
?>