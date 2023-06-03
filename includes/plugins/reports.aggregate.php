<?php
	/**
	 *	Отчеты
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModReport
	 */

	/**
	 * Пример командной строки для прямого запуска:
	 * 	/usr/local/bin/php /var/www/named/www/includes/plugins/reports.aggregate.php
	 */

	if ( !defined( "UNDEAD_CS" ) ) {
		// запуск на прямую
		$tmp = dirname( __FILE__ )."/../../";
		chdir( $tmp );
		//chdir( "/var/www/named/www/" );
		ini_set( "display_errors", 1 );
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
				array( "label" => "core", "name" => "object" ), // самы простой вариант объекта ( в разработке )
				array( "label" => "core", "name" => "output" ), // вывод
				array( "label" => "core", "name" => "filter" ), // фильтр
				array( "label" => "core", "name" => "syspath" ), // системные пути
				array( "label" => "core", "name" => "system" ), // система
				array( "label" => "core", "name" => "handler" ), // обработчик ( перехватчик = обработчик запроса )
				array( "label" => "core", "name" => "page" ), // страница
				array( "label" => "core", "name" => "html" ), // html
				array( "label" => "core", "name" => "account" ), // аккаунт
				array( "label" => "core", "name" => "database" ), // работа с базой данных
				array( "label" => "core", "name" => "ftp" ), // работа с ftp
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
				//array( "label" => "plugin", "name" => "zone" ), // файлы зон
				array( "label" => "plugin", "name" => "link" ), // сервера
				array( "label" => "plugin", "name" => "backup" ), // резервные копии
				array( "label" => "plugin", "name" => "logger" ), // логи
				array( "label" => "plugin", "name" => "regru" ), // своим перехватчиком не обладает
				array( "label" => "plugin", "name" => "login" ), // форма входа
				array( "label" => "plugin", "name" => "client" ), // клиентская часть системы
				array( "label" => "plugin", "name" => "bot" ), // бот
				array( "label" => "plugin", "name" => "reports.report" ),
				array( "label" => "plugin", "name" => "reports.queries" ),
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
	$objCMS->ApplyPath( "root_relative", str_replace( "/index.php", "", $_SERVER[ "SCRIPT_NAME" ] ) );
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

	class CReportAggregator extends CFlex {
		protected $log_file = "";
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"log_file" => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		public function Proc( ) {
			$this->AggrSystem( );
			$this->AggrSyslog( );
		} // function Proc
		
		private function AggrSystem( ) {
			global $objCMS;
			$objReport = new CReport( );
			$arrReport = array(
				"report_cr_date" => date( "Y-m-d" ),
				"report_cl_count" => 0,
				"report_cl_active" => 0,
				"report_cl_inactive" => 0,
				"report_cl_blocked" => 0,
				"report_zones" => 0
			);
			$hCommon = new CFlexHandler( );
			$hCommon->Create( array( "database" => $objCMS->database ) );
			$hCommon->CheckTable( array( FHOV_TABLE => "ud_report", FHOV_OBJECT => "CReport" ) );
			
			$tmp = $hCommon->CountObject( array( FHOV_TABLE => "ud_client" ) );
			$tmp = $tmp->GetResult( "count" );
			$arrReport[ "report_cl_count" ] = $tmp;
			$tmp = $hCommon->CountObject( array( FHOV_WHERE => "`client_state`=".US_ACTIVE, FHOV_TABLE => "ud_client" ) );
			$tmp = $tmp->GetResult( "count" );
			$arrReport[ "report_cl_active" ] = $tmp;
			$tmp = $hCommon->CountObject( array( FHOV_WHERE => "`client_state`=".US_NOTACTIVE, FHOV_TABLE => "ud_client" ) );
			$tmp = $tmp->GetResult( "count" );
			$arrReport[ "report_cl_inactive" ] = $tmp;
			$tmp = $hCommon->CountObject( array( FHOV_WHERE => "`client_state`=".US_BLOCKED, FHOV_TABLE => "ud_client" ) );
			$tmp = $tmp->GetResult( "count" );
			$arrReport[ "report_cl_blocked" ] = $tmp;
			$tmp = $hCommon->CountObject( array( FHOV_TABLE => "ud_zone" ) );
			$tmp = $tmp->GetResult( "count" );
			$arrReport[ "report_zones" ] = $tmp;
			$objReport->Create( $arrReport );
			//
			$szCrDateIndex = $objReport->GetAttributeIndex( "cr_date", NULL, FLEX_FILTER_DATABASE );
			$tmp = $hCommon->GetObject( array(
				FHOV_WHERE => "`".$szCrDateIndex."`=".$objReport->GetAttributeValue( "cr_date", FLEX_FILTER_DATABASE ),
				FHOV_TABLE => "ud_report", FHOV_INDEXATTR => "id", FHOV_OBJECT => "CReport"
			) );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$tmp = current( $tmp );
				$objReport->Create( array( "id" => $tmp->id ) );
				$hCommon->UpdObject( array( $objReport ), array( FHOV_TABLE => "ud_report", FHOV_INDEXATTR => "id" ) );
			} else {
				$hCommon->AddObject( array( $objReport ), array( FHOV_TABLE => "ud_report" ) );
			}
		} // function AggrSystem
		
		private function AggrSyslog( ) {
			global $objCMS;
			if ( file_exists( $this->log_file ) ) {
				$hFile = @fopen( $this->log_file, "rb" );
				if ( !$hFile ) {
					return;
				}
				$szText = fread( $hFile, filesize( $this->log_file ) );
				fclose( $hFile );
				$arrLines = explode( "\r\n", $szText );
				/*$iSize = filesize( $this->log_file );
				if ( !$iSize ) {
					return;
				}
				$arrLines = file( $this->log_file );
				if ( $arrLines === false ) {
					return;
				}*/
				$this->ClearFile( );	
				$arrReqPerMins = array( );
				$arrDomains = array( );
				$l = 0;
				foreach( $arrLines as $i => $v ) {
					$tmp = $v;
					if ( preg_match( '/queries:/', $tmp ) ) {
						++$l;
						$tmp = preg_split( '/queries:/', $tmp );
						$tmp1 = NULL;
						preg_match( '/(\d{2})-(\w{3})-(\d{4})/', $tmp[ 0 ], $tmp1 );
						$szDate = str_replace( "-", " ", $tmp1[ 0 ] );
						$arrDate = array( $tmp1[ 1 ], $tmp1[ 2 ], $tmp1[ 3 ] );
						//ShowVar( $arrDate );
						preg_match( '/(\d{2}):(\d{2}):(\d{2})/', $tmp[ 0 ], $tmp1 );
						$arrTime = array( $tmp1[ 1 ],  $tmp1[ 2 ], $tmp1[ 3 ] );
						//ShowVar( $arrTime );
						$tmp = preg_split( '/query:/', $tmp[ 1 ] );
						$szInfo = trim( preg_replace( array( '/info:\s/', '/client\s/', '/:$/' ), '', trim( $tmp[ 0 ] ) ) );
						//ShowVar( $szInfo );
						preg_match( '/^([^#]*)\#/', $szInfo, $szIp );
						$szIp = $szIp[ 1 ];
						$szQuery = trim( $tmp[ 1 ] );
						//ShowVar( $szQuery );
						preg_match( '/^(\S)+/', $szQuery, $tmp );
						$szDomain = $tmp[ 0 ];
						//ShowVar( $szDomain );
						//
						$iDate = floatval( date( "Ymd", strtotime( $szDate ) ) );
						$iTime = floatval( $arrTime[ 0 ].$arrTime[ 1 ] );
						if ( !isset( $arrReqPerMins[ $iDate ][ $iTime ] ) ) {
							$arrReqPerMins[ $iDate ][ $iTime ] = 0.0;
						}
						++$arrReqPerMins[ $iDate ][ $iTime ];
						//
						$iTime = floatval( $arrTime[ 0 ] );
						if ( !isset( $arrDomains[ $iDate ][ $iTime ][ $szDomain ] ) ) {
							$arrDomains[ $iDate ][ $iTime ][ $szDomain ] = array(
								"count" => 0.0,
								"ip" => array( )
							);
						}
						++$arrDomains[ $iDate ][ $iTime ][ $szDomain ][ "count" ];
						if ( !isset( $arrDomains[ $iDate ][ $iTime ][ $szDomain ][ "ip" ][ $szIp ] ) ) {
							$arrDomains[ $iDate ][ $iTime ][ $szDomain ][ "ip" ][ $szIp ] = 0.0;
						}
						++$arrDomains[ $iDate ][ $iTime ][ $szDomain ][ "ip" ][ $szIp ];
					}
				}
				
				$hCommon = new CFlexHandler( );
				$hCommon->Create( array( "database" => $objCMS->database ) );
				$hCommon->CheckTable( array( FHOV_TABLE => "ud_queries", FHOV_OBJECT => "CQueries" ) );
				$hCommon->CheckTable( array( FHOV_TABLE => "ud_qdomains", FHOV_OBJECT => "CQDomain" ) );
				$hCommon->CheckTable( array( FHOV_TABLE => "ud_qitem", FHOV_OBJECT => "CQueryItem" ) );
				$hCommon->CheckTable( array( FHOV_TABLE => "ud_qcount", FHOV_OBJECT => "CQueryItemCount" ) );
				//
				$arrQueries = $this->CalcReqPerMin( $arrReqPerMins );
				$this->SaveCalcReqPerMin( $hCommon, $arrQueries );
				unset( $arrQueries );
				//
				$tmpObject = new CQDomain( );
				$arrIndex = array(
					FLEX_FILTER_PHP => $tmpObject->GetAttributeIndexList( FLEX_FILTER_PHP ),
					FLEX_FILTER_DATABASE => $tmpObject->GetAttributeIndexList( FLEX_FILTER_DATABASE )
				);
				$tmpObject2 = new CQueryItem( );
				$arrIndex2 = array(
					FLEX_FILTER_PHP => $tmpObject2->GetAttributeIndexList( FLEX_FILTER_PHP ),
					FLEX_FILTER_DATABASE => $tmpObject2->GetAttributeIndexList( FLEX_FILTER_DATABASE )
				);
				$tmpObject3 = new CQueryItemCount( );
				$arrIndex3 = array(
					FLEX_FILTER_PHP => $tmpObject3->GetAttributeIndexList( FLEX_FILTER_PHP ),
					FLEX_FILTER_DATABASE => $tmpObject3->GetAttributeIndexList( FLEX_FILTER_DATABASE )
				);
				$arrResult = array( );
				foreach( $arrDomains as $i => $v ) {
					$tmp = array( );
					$tmp[ $arrIndex[ FLEX_FILTER_PHP ][ "cr_date" ] ] = preg_replace( '/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', strval( $i ) );
					foreach( $v as $j => $w ) {
						// сохраняем элемент: день - час
						$tmp[ $arrIndex[ FLEX_FILTER_PHP ][ "hour" ] ] = $j;
						$tmpObject->Create( $tmp );
						// получим id слепка день - час
						$iQDomainId = 0;
						$tmp1 = $hCommon->GetObject( array(
							FHOV_WHERE =>
								"`".$arrIndex[ FLEX_FILTER_DATABASE ][ "cr_date" ]."`=".$tmpObject->GetAttributeValue( "cr_date", FLEX_FILTER_DATABASE )
								." AND `".$arrIndex[ FLEX_FILTER_DATABASE ][ "hour" ]."`=".$tmpObject->GetAttributeValue( "hour", FLEX_FILTER_DATABASE ),
							FHOV_TABLE => "ud_qdomains", FHOV_OBJECT => "CQDomain"
						) );
						if ( $tmp1->HasResult( ) ) {
							$tmp1 = $tmp1->GetResult( );
							$tmp1 = current( $tmp1 );
							$iQDomainId = $tmp1->GetAttributeValue( "id" );
						} else {
							$tmp1 = $hCommon->AddObject( array( $tmpObject ), array( FHOV_TABLE => "ud_qdomains" ) );
							$iQDomainId = intval( $tmp1->GetResult( "insert_id" ) );
						}
						//
						$tmpObject2 = new CQueryItem( );
						foreach( $w as $i1 => $v1 ) {
							// сохраняем элементы: домен, ip
							$tmpObject2->Create( array(
								$arrIndex2[ FLEX_FILTER_PHP ][ "type" ] => QIT_DOMAIN,
								$arrIndex2[ FLEX_FILTER_PHP ][ "value" ] => $i1
							) );
							//
							$iQItemId = 0;
							$tmp1 = $hCommon->GetObject( array(
								FHOV_WHERE => "`".$arrIndex2[ FLEX_FILTER_DATABASE ][ "type" ]."`=".QIT_DOMAIN." AND `".$arrIndex2[ FLEX_FILTER_DATABASE ][ "value" ]."`=".$tmpObject2->GetAttributeValue( "value", FLEX_FILTER_DATABASE ),
								FHOV_TABLE => "ud_qitem", FHOV_OBJECT => "CQueryItem"
							) );
							if ( $tmp1->HasResult( ) ) {
								$tmp1 = $tmp1->GetResult( );
								$tmp1 = current( $tmp1 );
								$iQItemId = $tmp1->GetAttributeValue( "id" );
							} else {
								$tmp1 = $hCommon->AddObject( array( $tmpObject2 ), array( FHOV_TABLE => "ud_qitem" ) );
								$iQItemId = intval( $tmp1->GetResult( "insert_id" ) );
							}
							unset( $tmp1 );
							//
							$iQItemCount = $v1[ "count" ];
							// связываем день-час с количеством запросов к домену
							$iId2 = 0;
							$tmp1 = $hCommon->GetObject( array(
								FHOV_WHERE => "`".$arrIndex3[ FLEX_FILTER_DATABASE ][ "qdomain_id" ]."`=".$iQDomainId
									." AND `".$arrIndex3[ FLEX_FILTER_DATABASE ][ "qitem_id" ]."`=".$iQItemId
									." AND `".$arrIndex3[ FLEX_FILTER_DATABASE ][ "label" ]."`='Time/Domain'",
								FHOV_TABLE => "ud_qcount", FHOV_OBJECT => "CQueryItemCount"
							) );
							if ( $tmp1->HasResult( ) ) {
								$tmp1 = $tmp1->GetResult( );
								$tmp1 = current( $tmp1 );
								$iId2 = $tmp1->GetAttributeValue( "id" );
								// аккумулировать!
								$iQItemCount += $tmp1->count;
								$tmp1->Create( array(
									$arrIndex3[ FLEX_FILTER_PHP ][ "count" ] => $iQItemCount
								) );
								$hCommon->UpdObject( array( $tmp1 ), array( FHOV_TABLE => "ud_qcount", FHOV_INDEXATTR => "id" ) );
							} else {
								$tmpObject3 = new CQueryItemCount( );
								$tmpObject3->Create( array(
									$arrIndex3[ FLEX_FILTER_PHP ][ "qdomain_id" ] => $iQDomainId,
									$arrIndex3[ FLEX_FILTER_PHP ][ "qitem_id" ] => $iQItemId,
									$arrIndex3[ FLEX_FILTER_PHP ][ "count" ] => $iQItemCount,
									$arrIndex3[ FLEX_FILTER_PHP ][ "label" ] => "Time/Domain"
								) );
								$tmp1 = $hCommon->AddObject( array( $tmpObject3 ), array( FHOV_TABLE => "ud_qcount" ) );
								$iId2 = intval( $tmp1->GetResult( "insert_id" ) );
							}
							unset( $tmp1 );
							//
							$tmpObject2 = new CQueryItem( );
							foreach( $v1[ "ip" ] as $j1 => $w1 ) {
								//
								$tmpObject2->Create( array(
									$arrIndex2[ FLEX_FILTER_PHP ][ "type" ] => QIT_IP,
									$arrIndex2[ FLEX_FILTER_PHP ][ "value" ] => $j1
								) );
								// получаем id запроса к домену
								$iQIpId = 0;
								$tmp1 = $hCommon->GetObject( array(
									FHOV_WHERE => "`".$arrIndex2[ FLEX_FILTER_DATABASE ][ "type" ]."`=".QIT_IP." AND `".$arrIndex2[ FLEX_FILTER_DATABASE ][ "value" ]."`=".$tmpObject2->GetAttributeValue( "value", FLEX_FILTER_DATABASE ),
									FHOV_TABLE => "ud_qitem", FHOV_OBJECT => "CQueryItem"
								) );
								if ( $tmp1->HasResult( ) ) {
									$tmp1 = $tmp1->GetResult( );
									$tmp1 = current( $tmp1 );
									$iQIpId = $tmp1->GetAttributeValue( "id" );
								} else {
									$tmp1 = $hCommon->AddObject( array( $tmpObject2 ), array( FHOV_TABLE => "ud_qitem" ) );
									$iQIpId = intval( $tmp1->GetResult( "insert_id" ) );
								}
								//
								// связываем запросы к домену с ip
								$fCount = $w1;
								$tmp1 = $hCommon->GetObject( array(
									FHOV_WHERE => "`".$arrIndex3[ FLEX_FILTER_DATABASE ][ "qdomain_id" ]."`=".$iId2//$iQItemId
										." AND `".$arrIndex3[ FLEX_FILTER_DATABASE ][ "qitem_id" ]."`=".$iQIpId
										." AND `".$arrIndex3[ FLEX_FILTER_DATABASE ][ "label" ]."`='Domain/Ip'",
									FHOV_TABLE => "ud_qcount", FHOV_OBJECT => "CQueryItemCount"
								) );
								if ( $tmp1->HasResult( ) ) {
									$tmp1 = $tmp1->GetResult( );
									$tmp1 = current( $tmp1 );
									// аккумулировать!
									$fCount += $tmp1->count;
									$tmp1->Create( array(
										$arrIndex3[ FLEX_FILTER_PHP ][ "count" ] => $fCount
									) );
									$hCommon->UpdObject( array( $tmp1 ), array( FHOV_TABLE => "ud_qcount", FHOV_INDEXATTR => "id" ) );
								} else {
									$tmpObject3 = new CQueryItemCount( );
									$tmpObject3->Create( array(
										$arrIndex3[ FLEX_FILTER_PHP ][ "qdomain_id" ] => $iId2,//$iQItemId,
										$arrIndex3[ FLEX_FILTER_PHP ][ "qitem_id" ] => $iQIpId,
										$arrIndex3[ FLEX_FILTER_PHP ][ "count" ] => $w1,
										$arrIndex3[ FLEX_FILTER_PHP ][ "label" ] => "Domain/Ip"
									) );
									$tmp1 = $hCommon->AddObject( array( $tmpObject3 ), array( FHOV_TABLE => "ud_qcount" ) );
								}
								$tmpObject2 = new CQueryItem( );
							}
						}
						//
						$tmpObject = new CQDomain( );
					}
				}
				//
				unset( $arrDomains );
			}
		} // function AggrSyslog
		
		/**
		 * 	Подсчитывает количество запросов за каждые 5 минут, для каждого дня
		 */
		private function CalcReqPerMin( $arrReqPerMin, $iInterval = 5 ) {
			$r = array( );
			foreach( $arrReqPerMin as $i => $v ) {
				$fTotal = 0.0;
				$tmp1 = array( );
				$tmp = $v;
				ksort( $tmp );
				foreach( $tmp as $j => $w ) {
					$iIndex = $j - ( $j % $iInterval );
					if ( !isset( $tmp1[ $iIndex ] ) ) {
						$tmp1[ $iIndex ] = 0;
					}
					$tmp1[ $iIndex ] += $w;
					$fTotal += floatval( $w );
				}
				$tmp1[ "maximum" ] = max( $tmp1 );
				$tmp1[ "total" ] = $fTotal;
				$r[ $i ] = $tmp1;
			}
			ksort( $r );
			return $r;
		} // function CalcReqPerMin
		
		/**
		 * 	Сохраняет результаты подсчета запросов
		 */
		private function SaveCalcReqPerMin( $hCommon, $arrQueries ) {
			$tmpObject = new CQueries( );
			$arrIndex = $tmpObject->GetAttributeIndexList( );
			$arrIndex1 = $tmpObject->GetAttributeIndexList( FLEX_FILTER_DATABASE );
			foreach( $arrQueries as $i => $v ) {
				$tmp = $v;
				$fTotal = $tmp[ "total" ];
				$fMax = $tmp[ "maximum" ];
				unset( $tmp[ "total" ], $tmp[ "maximum" ] );
				$szCrDate = preg_replace( '/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', strval( $i ) );
				$tmpObject->Create( array(
					$arrIndex[ "cr_date" ] => $szCrDate,
					$arrIndex[ "maximum" ] => $fMax,
					$arrIndex[ "total" ] => $fTotal,
					$arrIndex[ "queries" ] => $tmp
				) );
				$tmp = $hCommon->GetObject( array(
					FHOV_WHERE => "`".$arrIndex1[ "cr_date" ]."`=".$tmpObject->GetAttributeValue( "cr_date", FLEX_FILTER_DATABASE ),
					FHOV_TABLE => "ud_queries", FHOV_OBJECT => "CQueries"
				) );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					$tmp = current( $tmp );
					// аккумуляция!
					$fTotal = $tmpObject->total + $tmp->total;
					$arrNewQueries = $tmp->queries;
					$arrOrigQueries = $tmpObject->queries;
					/*foreach( $arrOrigQueries as $i => $v ) {
						if ( isset( $arrNewQueries[ $i ] ) ) {
							$arrOrigQueries[ $i ] += $arrNewQueries[ $i ];
						}
					}*/
					foreach( $arrNewQueries as $i => $v ) {
						if ( isset( $arrOrigQueries[ $i ] ) ) {
							$arrOrigQueries[ $i ] += $v;
						} else {
							$arrOrigQueries[ $i ] = $v;
						}
					}
					ksort( $arrOrigQueries );
					
					$fMax = max( $arrOrigQueries );
					$tmpObject->ClearQueries( );
					$tmpObject->Create( array(
						$arrIndex[ "id" ] => $tmp->GetAttributeValue( "id" ),
						$arrIndex[ "maximum" ] => $fMax,
						$arrIndex[ "total" ] => $fTotal,
						$arrIndex[ "queries" ] => $arrOrigQueries,
					) );
					//ShowVarD( $fMax, $fTotal, $arrNewQueries, $arrOrigQueries, $tmp, $tmpObject );
					//
					$hCommon->UpdObject( array( $tmpObject ), array( FHOV_TABLE => "ud_queries", FHOV_INDEXATTR => "id" ) );
				} else {
					$hCommon->AddObject( array( $tmpObject ), array( FHOV_TABLE => "ud_queries" ) );
				}
				$tmpObject = new CQueries( );
			}
			unset( $tmpObject );
		} // function SaveCalcReqPerMin
		
		private function ClearFile( ) {
			if ( file_exists( $this->log_file ) ) {
				$hFile = fopen( $this->log_file, "w" );
				if ( $hFile ) {
					fclose( $hFile );
				}
			}
		} // function ClearFile
		
	} // function CReportAggregator
	
	// запуск на прямую
	if ( !defined( "UNDEAD_CS" ) ) {
		global $objCMS;
		$hCommon = new CFlexHandler( );
		$hCommon->Create( array( "database" => $objCMS->database ) );
		$tmp = $hCommon->GetObject( array( FHOV_TABLE => "ud_system", FHOV_OBJECT => "CSystemConfig" ) );
		if ( $tmp->HasResult( ) ) {
			$tmp = $tmp->GetResult( );
			$tmp = current( $tmp );
			if ( $tmp->logfile !== "" && file_exists( $tmp->logfile ) ) {
				$objAggregator = new CReportAggregator( );
				$objAggregator->Create( array(
					"log_file" => $tmp->logfile
				) );
				$objAggregator->Proc( );
			}
		}
		exit;
	}
	
?>