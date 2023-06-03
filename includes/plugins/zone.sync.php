<?php
	/**
	 *	Синхронизатор файлов зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	/**
	 * Пример командной строки для прямого запуска:
	 * 	/usr/local/bin/php /var/www/named/www/includes/plugins/zone.sync.php
	 */

	if ( !defined( "UNDEAD_CS" ) ) {
		// запуск на прямую
		$tmp = dirname( __FILE__ )."/../../";
		chdir( $tmp );
		//chdir( "/var/www/named/www/" );
		ini_set( "display_errors", 1 );
		define( "UNDEAD_CS_SYNC", 1 );
		error_reporting( E_ALL );
		set_time_limit( 0 );
		//
	require( "includes/sdk/error.php" ); // ошибки
	require( "includes/sdk/result.php" ); // результаты
	require( "includes/sdk/flex.php" ); // суперкласс
	//
	$g_arrConfig = array(
		// includes
		"include" => array(
			"suffix" => ".php",
			"labels" => array(
				"root" => "",
				"core" => "includes/core/",
				"util" => "includes/utils/",
				"plugin" => "includes/plugins/",
				"sdk" => "includes/sdk/"
			),
			"items" => array(
				//
				array( "label" => "root", "name" => "db" ),
				// core - ядро
				array( "label" => "core", "name" => "output" ), // вывод
				array( "label" => "core", "name" => "filter" ), // фильтр
				array( "label" => "core", "name" => "system" ), // система
				array( "label" => "core", "name" => "handler" ), // обработчик ( перехватчик = обработчик запроса )
				array( "label" => "core", "name" => "page" ), // страница
				array( "label" => "core", "name" => "html" ), // html
				array( "label" => "core", "name" => "account" ), // аккаунт
				array( "label" => "core", "name" => "database" ), // работа с базой данных
				array( "label" => "core", "name" => "graph" ), // граф
				// utils - утилиты
				array( "label" => "util", "name" => "showvar" ), // показ переменных
				array( "label" => "util", "name" => "filter" ), // фильтры
				array( "label" => "util", "name" => "handler" ), // обработчики
				array( "label" => "util", "name" => "menu" ), // меню
				array( "label" => "util", "name" => "validator" ), // проверялка различных переменных
				array( "label" => "util", "name" => "misc" ), // всякая всячина
				array( "label" => "util", "name" => "pager" ), // пейджер
				array( "label" => "util", "name" => "archive" ),
				// plugins
				array( "label" => "plugin", "name" => "install" ), // установщик
				array( "label" => "plugin", "name" => "user" ), // пользоватлеи
				array( "label" => "plugin", "name" => "zone" ), // файлы зон
				array( "label" => "plugin", "name" => "link" ), // сервера
				array( "label" => "plugin", "name" => "backup" ), // резервные копии
				array( "label" => "plugin", "name" => "logger" ), // логи
				array( "label" => "plugin", "name" => "regru" ), // своим перехватчиком не обладает
				array( "label" => "plugin", "name" => "login" ), // форма входа
				array( "label" => "plugin", "name" => "client" ), // клиентская часть системы
				array( "label" => "plugin", "name" => "bot" ), // бот
				array( "label" => "plugin", "name" => "help" ) // помощь
				
			)
		),
		// system
		"system" => array(
			"arrHProc" => array(
				"test" => "Test",
				"proc" => "Process"
			),
			"arrPath" => array(
			),
			"arrHandler" => array(
				array( "label" => "default_javascript", "object" => "CJavaScriptHandler" ), // запросы к js файлам
				array( "label" => "default_css", "object" => "CCSSHandler" ), // запросы к css файлам
				array( "label" => "default_image", "object" => "CImageHandler" ), // запросы к картинкам jpg, png, gif
				array( "label" => "login", "object" => "CHModLogin" ), // вход
				array( "label" => "install", "object" => "CHModInstall" ), // установщик
			),
			"arrConfig" => array(
				"graph" => array(
					"vertex" => array(
						"table" => "ud_vertex",
						"object_name" => "CVertex",
						"index_attr" => "vertex_id"
					),
					"edge" => array(
						"table" => "ud_edge",
						"object_name" => "CEdge",
						"index_attr" => "edge_id"
					)
				)
			),
		),
	);
	//
	
	/**
	 *	Элемент инклуда
	 */
	class CIncludeItem extends CFlex {
		protected $label = "";
		protected $name = "";
	} // class CIncludeItem
	
	/**
	 *	Подключалка файлов
	 */
	class CInclude extends CFlex {
		protected $suffix = "";
		protected $labels = array( );
		protected $items = array( );
		
		/**
		 *	Фильтрует значение для выбранного атрибута, используется для ввода данных в объект
		 *	@param $szName string имя атрибута
		 *	@param $arrInput mixed некое значение
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 */
		protected function InitAttr( $szName, &$arrInput, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			$objRet = new CResult( );
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			if ( $szName == "labels" ) {
				if ( isset( $arrInput[$szIndex ] ) && is_array( $arrInput[ $szIndex ] ) ) {
					foreach( $arrInput[ $szIndex ] as $i => $v ) {
						if ( is_string( $i ) && is_string( $v ) ) {
							$this->labels[ $i ] = $v;
						}
					}
				}
			} else if ( $szName == "items" ) {
				if ( isset( $arrInput[ $szIndex ] ) && is_array( $arrInput[ $szIndex ] ) ) {
					foreach( $arrInput[ $szIndex ] as $i => $v ) {
						if ( is_array( $v ) ) {
							$tmp = new CIncludeItem( );
							$tmp->Create( $v );
							$this->items[ $i ] = $tmp;
						}
					}
				}
			} else {
				$objRet = parent::InitAttr( $szName, $arrInput, $arrConfig, $iMode );
			}
			return $objRet;
		} // function InitAttr
		
		/**
		 *	Запуск подключения файлов
		 */
		public function Process( ) {
			foreach( $this->items as $i => $v ) {
				$tmp = $v->GetArray( );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$szPrefix = ( isset( $this->labels[ $tmp[ "label" ] ] ) ? $this->labels[ $tmp[ "label" ] ] : "" );
					if ( file_exists( $szPrefix.$tmp[ "name" ].$this->suffix ) ) {
						include_once( $szPrefix.$tmp[ "name" ].$this->suffix );
					}
				}
			}
		} // function Process
	}
	//*
	// includes
	$objSystemInclude = new CInclude( );
	$objSystemInclude->Create( $g_arrConfig[ "include" ] );
	$objSystemInclude->Process( );
	//*
	// system
	$objCMS = new CSystem( );
	$objCMS->Create( $g_arrConfig[ "system" ] );
	unset( $tmp );
	
	// relative [str_here] /
	$objCMS->ApplyPath( "root_relative", dirname( $_SERVER[ "SCRIPT_NAME" ] )."/../.." );
	// system [str_here] /relative/
	if ( $objCMS->GetPath( "root_relative" ) === "" ) {
		$tmp = preg_replace( '/\/$/', '', $_SERVER[ "DOCUMENT_ROOT" ] );
		$objCMS->ApplyPath( "root_system", $tmp );
	} else {
		$objCMS->ApplyPath( "root_system", $_SERVER[ "DOCUMENT_ROOT" ].$objCMS->GetPath( "root_relative" ) );
	}
	// http [str_here] /
	// application [str_here] /
	$objCMS->ApplyPath( "root_application", $objCMS->GetPath( "root_system" )."/folder" );
	// scripts [str_here] /system/
	$objCMS->ApplyPath( "system_scripts", $objCMS->GetPath( "root_system" )."/scripts" );
	// styles [str_here] /system/
	$objCMS->ApplyPath( "system_styles", $objCMS->GetPath( "root_system" )."/styles" );
		//*/
	}

	// STT - Sync Ticket Type
	define( "STT_SCP",	0	); // scp
	define( "STT_SSH",	1	); // ssh - юзается только для удаления
	define( "STT_RSYNC",	2	); // rsync
	// STS - Sync Ticket State
	define( "STS_START",	0	); // простой билет, который требует выполнения
	define( "STS_PROG",	1	); // производится процессинг билета
	define( "STS_ERROR",	2	); // была ошибка в предыдущем выполнении
	define( "STS_DONE",	3	); // тикет отработал свое, он в истории
	// STO - Sync Ticket Object
	define( "STO_MASTERFILE",	0	); // конфиг master
	define( "STO_SLAVEFILE",	1	); // конфиг slave
	define( "STO_ZONEFILE",		2	); // файл зоны
	
	/**
	 * Сущности:
	 * 	1. конфиг для мастера
	 * 	2. конфиг для слэйва
	 * 	3. файл зон
	 */

	/**
	 * 	Билет на синкание
	 */
	class CSyncTicket extends CFlex {
		protected $id = 0;
		protected $type = STT_SCP;
		protected $state = STS_START;
		protected $obj_type = STO_MASTERFILE;
		protected $info = "";
		//
		protected $cr_date = ""; // дата создания тикета
		protected $closed_at = ""; // дата закрытия тикета
		protected $process_start = ""; // дата начала обработки
		protected $process_end = ""; // дата окончания обработки
		protected $return_code = 0; // код возврата команды при обработке
		protected $return_info = ""; // информация об ошибке при обработке
		protected $last_state = STS_START; // последнее состояние
		protected $history = ""; // история изменения тикета - например была ошибка, потом бац пришла замена и его закрыли
		protected $prev_ticket = 0; // предыдущий тикет
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true, "type" => true, "state" => true, "obj_type" => true,
				"info" => true, "cr_date" => true, "process_start" => true, "process_end" => true,
				"return_code" => true, "return_info" => true, "last_state" => true, "history" => true,
				"prev_ticket" => true, "closed_at" => true
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Получение конфига
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_sticket";
			$arrConfig[ FLEX_CONFIG_PREFIX ] = "sticket_";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "id"		][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ "cr_date"		][ FLEX_CONFIG_TYPE	] = //FLEX_TYPE_DATE | FLEX_TYPE_TIME;
			$arrConfig[ "closed_at"		][ FLEX_CONFIG_TYPE	] = //FLEX_TYPE_DATE | FLEX_TYPE_TIME;
			$arrConfig[ "process_start"	][ FLEX_CONFIG_TYPE	] = //FLEX_TYPE_DATE | FLEX_TYPE_TIME;
			$arrConfig[ "process_end"	][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_DATE | FLEX_TYPE_TIME;
			return $arrConfig;
		} // function GetConfig
		
	} // class CSyncTicket

	/**
	 * 	Синхронизатор
	 */
	class CZoneSync extends CFlex {
		protected $iTicketCache = 1500;
		protected $szSyncFolder = "sync";
		protected $hCommon = NULL;
		
		public function InitHandlers( ) {
			global $objCMS;
			$this->hCommon = new CFlexHandler( );
			$this->hCommon->Create( array( "database" => $objCMS->database ) );
			$this->hCommon->CheckTable( array( FHOV_TABLE => "ud_sticket", FHOV_OBJECT => "CSyncTicket" ) );
			if ( !file_exists( $objCMS->GetPath( "root_system" )."/sync" ) ) {
				mkdir( $objCMS->GetPath( "root_system" )."/sync", 0755 );
			}
		} // function InitHandlers
		
		/**
		 * 	Обработка тикетов
		 */
		public function Proc( ) {
			global $objCMS;
			/**
			 * 1. Считываем тикеты в состоянии добавлен или ошибка
			 * 2. Помечаем тикеты как в процессе
			 * 3. Выполняем каждый по порядку
			 */
			if ( $this->hCommon === NULL ) {
				$this->InitHandlers( );
			}
			$objTicket = new CSyncTicket( );
			$arrIndex = $objTicket->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			// сразу помечаем объекты как в обработке, чтоб не было фэйков, пока возимся с объектами
			// пометили и потом с ними работаем, а параллельно будут добавляться новые тикеты
			$szQuery = "UPDATE `ud_sticket` SET `".$arrIndex[ "state" ]."`=".STS_PROG." WHERE `".$arrIndex[ "state" ]."`=".STS_START;
			$objCMS->database->Query( $szQuery );
			// выгребаем нужные нам тикеты
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => "`".$arrIndex[ "state" ]."` IN(".STS_PROG.",".STS_ERROR.")",
				FHOV_TABLE => "ud_sticket", FHOV_INDEXATTR => "id", FHOV_OBJECT => "CSyncTicket"
			) );
			if ( $tmp->HasResult( ) ) {
				$arrTickets = $tmp->GetResult( );
				$modZone = new CHModZone( );
				$modLink = new CHModLink( );
				$arrFolder = $modZone->GetFolders( );
				$tmp = $modLink->GetServers( );
				$tmp = $tmp->GetResult( );
				$objMaster = NULL;
				$objSlave = NULL;
				$szIp = "";
				foreach( $tmp as $i => $v ) {
					if ( $v->type === ST_MASTER ) {
						$objMaster = clone $v;
					} elseif ( $v->type === ST_SLAVE ) {
						$objSlave = clone $v;
					}
					if ( $objMaster && $objSlave ) {
						break;
					}
				}
				unset( $tmp );
				//
				$objRsyncAcc = NULL;
				$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => "ud_acc_rsync", FHOV_OBJECT => "CRsyncAccount" ) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$objRsyncAcc = current( $tmp );
				}
				if ( $objRsyncAcc ) {
					$szSyncFolder = $objCMS->GetPath( "root_system" );
					$szSyncFolder = str_replace( '/includes/plugins/../..', '', $szSyncFolder );
					$szSyncFolder .= "/sync";
					$arrServ = array( );
					$arrCmd = array( );
					$arrObj = array( );
					$bWasSync = false;
					foreach( $arrTickets as $i => $v ) {
						$cmd = "";
						if ( !isset( $arrObj[ $v->info ] ) ) {
							$arrObj[ $v->info ] = array( "count" => 0, "id" => array( ) );
						}
						++$arrObj[ $v->info ][ "count" ];
						$arrObj[ $v->info ][ "id" ][ ] = $v->id;
						if ( $v->type === STT_SCP ) {
							if ( $v->obj_type === STO_MASTERFILE ) {
								if ( $objMaster ) {
									$x1 = escapeshellarg( $szSyncFolder."/".$v->id."/master.zones" );
									$x2 = escapeshellarg( $objRsyncAcc->username."@".$objMaster->ip.":".$objMaster->config_file );
									$arrServ[ $i ] = "scp ".$x1." ".$x2;
									if ( file_exists( $objMaster->config_file ) ) {
										copy( $szSyncFolder."/".$v->id."/master.zones", $arrFolder[ "config" ]."/master.zones" );
									} else {
										//file_put_contents( $objMaster->config_file, file_get_contents( $szSyncFolder."/".$v->id."/master.zones" ) );
										file_put_contents( $arrFolder[ "config" ]."/master.zones", file_get_contents( $szSyncFolder."/".$v->id."/master.zones" ) );
									}
								} else {
									$this->CloseTicket( $v );
								}
							} elseif ( $v->obj_type === STO_SLAVEFILE ) {
								if ( $objSlave ) {
									$arrServ[ $i ] = "scp ".$szSyncFolder."/".$v->id."/slave.zones ".$objRsyncAcc->username."@".$objSlave->ip.":".$objSlave->config_file;
									if ( file_exists( $objSlave->config_file ) ) {
										copy( $szSyncFolder."/".$v->id."/slave.zones", $arrFolder[ "config" ]."/slave.zones" );
									} else {
										//file_put_contents( $objSlave->config_file, file_get_contents( $szSyncFolder."/".$v->id."/slave.zones" ) );
										file_put_contents( $arrFolder[ "config" ]."/slave.zones", file_get_contents( $szSyncFolder."/".$v->id."/slave.zones" ) );
									}
								} else {
									$this->CloseTicket( $v );
								}
							} elseif ( $v->obj_type === STO_ZONEFILE ) {
								if ( $objMaster ) {
									$arrCmd[ $i ] = "scp ".$szSyncFolder."/".$v->id."/".$v->info." ".$objRsyncAcc->username."@".$objMaster->ip.":".$objMaster->zone_folder."/".$v->info;
									if ( file_exists( $arrFolder[ "zone" ]."/".$v->info ) ) {
										copy( $szSyncFolder."/".$v->id."/".$v->info, $arrFolder[ "zone" ]."/".$v->info );
									} else {
										file_put_contents( $arrFolder[ "zone" ]."/".$v->info, file_get_contents( $szSyncFolder."/".$v->id."/".$v->info ) );
									}
								}  else {
									$this->CloseTicket( $v );
								}
							}
						} elseif ( $v->type === STT_SSH ) {
							if ( $v->obj_type === STO_ZONEFILE ) {
								if ( $objMaster ) {
									$arrCmd[ $i ] = "ssh ".$objMaster->ip." rm -f ".$objMaster->zone_folder."/".$v->info;
									if ( file_exists( $arrFolder[ "zone" ]."/".$v->info ) ) {
										clearstatcache( );
										//unlink( $objMaster->zone_folder."/".$v->info );
										unlink( $arrFolder[ "zone" ]."/".$v->info );
									}
								}  else {
									$this->CloseTicket( $v );
								}
							}
						} elseif ( $v->type === STT_RSYNC ) {
							$bWasSync = true;
							$arrCmd[ $i ] = "rsync";
						}
					}
					// закрываем тикеты для одиночных файлов, по ним отсележиваем только изменения файлов
					// в последний тикет суем инфу по результатам
					if ( $objMaster && !empty( $arrCmd ) ) {
						$return_var = 0;
						$output = array( );
						$tmp = end( $arrCmd );
						$iKey = key( $arrCmd );
						$szProcessStart = date( "Y-m-d H:i:s" );
						$cmd = "rsync -a --delete ".$arrFolder[ "zone" ]."/ ".$objRsyncAcc->username."@".$objMaster->ip.":".$objMaster->root_prefix.$objMaster->zone_folder."/";
						exec( $cmd, $output, $return_var );
						$szProcessEnd = date( "Y-m-d H:i:s" );
						foreach( $arrCmd as $i => $v ) {
							$arrTickets[ $i ]->Create( array(
								$arrIndex[ "process_start" ] => $szProcessStart,
								$arrIndex[ "process_end" ] => $szProcessEnd,
								$arrIndex[ "return_code" ] => $return_var,
								$arrIndex[ "return_info" ] => join( "\r\n", $output )
							) );
							$szData = "";
							if ( $arrTickets[ $i ]->type === STT_SCP && $arrTickets[ $i ]->obj_type === STO_ZONEFILE ) {
								$szData = file_get_contents( $szSyncFolder."/".$arrTickets[ $i ]->id."/".$arrTickets[ $i ]->info );
							}
							$this->CloseTicket( $arrTickets[ $i ] );
							if ( $return_var !== 0 ) {
								$this->AddErrorTicket( $arrTickets[ $i ], $szData );
							}
						}
					}
					foreach( $arrServ as $i => $v ) {
						$szProcessStart = date( "Y-m-d H:i:s" );
						$return_var = 0;
						$output = array( );
						$cmd = $v;//preg_replace( '/^scp/', '/usr/bin/scp', $v );
						echo 'executing command: '.$cmd."\n";
						echo 'last:'.exec( $cmd, $output, $return_var )."\n";
						echo 'return_var: '.$return_var."\n";
						echo 'output: '."\n";
						if ( is_array( $output ) ) {
							foreach( $output as $w ) {
								echo $w."\n";
							}
						}
						echo "\n\n";
						
						$szProcessEnd = date( "Y-m-d H:i:s" );
						$arrTickets[ $i ]->Create( array(
							$arrIndex[ "process_start" ] => $szProcessStart,
							$arrIndex[ "process_end" ] => $szProcessEnd,
							$arrIndex[ "return_code" ] => $return_var,
							$arrIndex[ "return_info" ] => join( "\r\n", $output )
						) );
						$szFilename = "";
						if ( $arrTickets[ $i ]->obj_type === STO_MASTERFILE ) {
							$szFilename = $szSyncFolder."/".$arrTickets[ $i ]->id."/master.zones";
						} else {
							$szFilename = $szSyncFolder."/".$arrTickets[ $i ]->id."/slave.zones";
						}
						$szData = file_get_contents( $szFilename );
						$this->CloseTicket( $arrTickets[ $i ] );
						if ( $return_var !== 0 ) {
							$this->AddErrorTicket( $arrTickets[ $i ], $szData );
						}
					}
				}
			}
			//$this->Optimize( );
		} // function Proc
		
		/**
		 * 	Оптимизация тикетов
		 */
		private function Optimize( ) {
			global $objCMS;
			$objTicket = new CSyncTicket( );
			$arrIndex = $objTicket->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->CountObject( array(
				FHOV_WHERE => "`".$arrIndex[ "state" ]."`=".STS_DONE,
				FHOV_TABLE => "ud_sticket"
			) );
			$iCount = $tmp->GetResult( "count" );
			if ( $iCount > $this->iTicketCache ) {
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => "`".$arrIndex[ "state" ]."`=".STS_DONE,
					FHOV_ORDER => "`".$arrIndex[ "id" ]."` ASC",
					FHOV_LIMIT => "0,".$this->iTicketCache,
					FHOV_TABLE => "ud_sticket", FHOV_INDEXATTR => "id", FHOV_OBJECT => "CSyncTicket"
				) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$szSyncFolder = $objCMS->GetPath( "root_system" )."/sync";
					foreach( $tmp as $i => $v ) {
						DirClear( $szSyncFolder."/".$v->id );
					}
				}
			}
		} // function Rotate
		
		/**
		 * 	Добавление тикета
		 */
		public function AddTicket( $iType, $iObjType, $szInfo, $szData ) {
			/**
			 * 1. Ищем подобный тикет, который не закрыт
			 * 2. Если найден, то:
			 * 	а. закрываем его, если это ошибка или старт
			 * 	б. добавляем новый тикет
			 * 3. Иначе добавляем новый тикет ( если есть последний закрытый, то связываем его с новым )
			 */
			if ( $this->hCommon === NULL ) {
				$this->InitHandlers( );
			}
			$objTicket = new CSyncTicket( );
			$arrIndex = $objTicket->GetAttributeIndexList( );
			$objTicket->Create( array(
				$arrIndex[ "type" ] => $iType,
				$arrIndex[ "obj_type" ] => $iObjType,
				$arrIndex[ "info" ] => $szInfo
			) );
			$arrState = array( STS_START, STS_PROG, STS_ERROR );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => 	"`".$arrIndex[ "type" ]."`=".$objTicket->GetAttributeValue( "type", FLEX_FILTER_DATABASE )
						." AND `".$arrIndex[ "state" ]."` IN(".join( ",", $arrState ).")"
						." AND `".$arrIndex[ "obj_type" ]."`=".$objTicket->GetAttributeValue( "obj_type", FLEX_FILTER_DATABASE )
						." AND `".$arrIndex[ "info" ]."`=".$objTicket->GetAttributeValue( "info", FLEX_FILTER_DATABASE ),
				FHOV_TABLE => "ud_sticket", FHOV_OBJECT => "CSyncTicket"
			) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmp = current( $tmp );
				if ( $tmp->state === STS_START || $tmp->state === STS_ERROR ) {
					$this->CloseTicket( $tmp );
				}
				// потом когда тикет закроется, то повторной обработки не будет, а она произойдет при следующем старте
				$this->AddNewTicket( $objTicket, $tmp->id, $szData );
			} else {
				// берем самый последний тикет для данного объекта, связываем с текущим
				$iPrevId = 0;
				// ищем предыдущий обработанный тикет
				$tmp = $this->hCommon->GetObject( array(
					FHOV_WHERE => 	"`".$arrIndex[ "type" ]."`=".$objTicket->type
							." AND `".$arrIndex[ "state" ]."`=".STS_DONE
							." AND `".$arrIndex[ "obj_type" ]."`=".$objTicket->obj_type
							." AND `".$arrIndex[ "info" ]."`=".$objTicket->GetAttributeValue( "info", FLEX_FILTER_DATABASE ),
					FHOV_ORDER => "`".$arrIndex[ "cr_date" ]."` DESC, `".$arrIndex[ "process_end" ]."` DESC",
					FHOV_LIMIT => "1",
					FHOV_TABLE => "ud_sticket", FHOV_OBJECT => "CSyncTicket"
				) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$tmp = current( $tmp );
					$iPrevId = $tmp->id;
					unset( $tmp );
				}
				// добавляем тикет
				$this->AddNewTicket( $objTicket, $iPrevId, $szData );
			}
		} // function AddTicket
		
		/**
		 * 	Добавление ошибочного тикета
		 */
		private function AddErrorTicket( $objTicket, $szData ) {
			$arrState = array( STS_START );
			$arrIndex = $objTicket->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			$tmp = $this->hCommon->GetObject( array(
				FHOV_WHERE => 	"`".$arrIndex[ "type" ]."`=".$objTicket->GetAttributeValue( "type", FLEX_FILTER_DATABASE )
						." AND `".$arrIndex[ "state" ]."` IN(".join( ",", $arrState ).")"
						." AND `".$arrIndex[ "obj_type" ]."`=".$objTicket->GetAttributeValue( "obj_type", FLEX_FILTER_DATABASE )
						." AND `".$arrIndex[ "info" ]."`=".$objTicket->GetAttributeValue( "info", FLEX_FILTER_DATABASE ),
				FHOV_TABLE => "ud_sticket", FHOV_OBJECT => "CSyncTicket"
			) );
			if ( !$tmp->HasResult( ) ) {
				/**
				 * Добавим в очередь, только если там нет нового тикета для этого же объекта
				 * Возникла ошибка?
				 * добавим новый тикет с маркером ошибка
				 */
				$iOrigState = $tmp->state;
				$iId = $objTicket->id;
				$objTicket->Create( array(
					$arrIndex[ "id" ] => 0,
					$arrIndex[ "state" ] => STS_ERROR,
					$arrIndex[ "last_state" ] => $iOrigState,
				) );
				$this->AddNewTicket( $objTicket, $iId, $szData );
			}
		} // function AddErrorTicket
		
		/**
		 * 	Добавление нового тикета
		 */
		private function AddNewTicket( $objTicket, $iPrevId, $szData ) {
			$szCurDate = date( "Y-m-d H:i:s" );
			$arrIndex = $objTicket->GetAttributeIndexList( );
			$objTicket->Create( array(
				$arrIndex[ "cr_date" ] => $szCurDate,
				$arrIndex[ "prev_ticket" ] => $iPrevId,
			) );
			$tmp = $this->hCommon->AddObject( array( $objTicket ), array( FHOV_TABLE => "ud_sticket" ) );
			$iId = $tmp->GetResult( "insert_id" );
			$objTicket->Create( array( $arrIndex[ "id" ] => $iId ) );
			$this->AddTicketData( $objTicket, $szData );
		} // function AddNewTicket
		
		/**
		 * 	Закрытие тикета
		 */
		private function CloseTicket( $objTicket ) {
			$szCurDate = date( "Y-m-d H:i:s" );
			$arrIndex = $objTicket->GetAttributeIndexList( );
			$objTicket->Create( array(
				$arrIndex[ "state" ] => STS_DONE,
				$arrIndex[ "last_state" ] => $objTicket->state,
				$arrIndex[ "closed_at" ] => $szCurDate,
			) );
			$this->DelTicketData( $objTicket );
			$this->hCommon->UpdObject( array( $objTicket ), array( FHOV_TABLE => "ud_sticket", FHOV_INDEXATTR => "id" ) );
		} // function CloseTicket
		
		/**
		 * 	Добавление данных, связанных с тикетом
		 */
		private function AddTicketData( $objTicket, $szData ) {
			global $objCMS;
			/**
			 * Кэшируются только лишь данные команды SCP
			 */
			if ( $objTicket->type === STT_SCP ) {
				// кэшируем файл
				$szFolder = $objCMS->GetPath( "root_system" )."/sync/".$objTicket->id;
				if ( !file_exists( $szFolder ) ) {
					mkdir( $szFolder, 0755 );
				}
				$szSrcFile = "";
				$szDstFile = "";
				$modZone = new CHModZone( );
				$arrFolder = $modZone->GetFolders( );
				if ( $objTicket->obj_type === STO_MASTERFILE ) {
					$szSrcFile = $arrFolder[ "config" ]."/master.zones";
					$szDstFile = $szFolder."/master.zones";
				} elseif ( $objTicket->obj_type === STO_SLAVEFILE ) {
					$szSrcFile = $arrFolder[ "config" ]."/slave.zones";
					$szDstFile = $szFolder."/slave.zones";
				} elseif ( $objTicket->obj_type === STO_ZONEFILE ) {
					$szSrcFile = $arrFolder[ "zone" ]."/".$objTicket->info;
					$szDstFile = $szFolder."/".$objTicket->info;
				}
				if ( $szSrcFile !== "" ) {
					file_put_contents( $szDstFile, $szData );
					//copy( $szSrcFile, $szDstFile );
				}
			}
		} // function AddTicketData
		
		/**
		 * 	Удаление данных тикета
		 */
		private function DelTicketData( $objTicket ) {
			global $objCMS;
			if ( $objTicket->type === STT_SCP ) {
				$szFolder = $objCMS->GetPath( "root_system" )."/sync/".$objTicket->id;
				if ( file_exists( $szFolder ) ) {
					DirClear( $szFolder );
				}
			}
		} // function DelTicketData
		
	} // class CZoneSync
	
	// запуск на прямую
	if ( !defined( "UNDEAD_CS" ) ) {
		$objSync = new CZoneSync( );
		$objSync->Proc( );
		exit;
	}
	
?>