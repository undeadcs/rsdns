<?php
	/**
	 *	Graceful Reload зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	/**
	 * Пример командной строки для прямого запуска:
	 * 	/usr/local/bin/php /var/www/named/www/includes/plugins/zone.gracefulreload.php
	 */

	if ( !defined( "UNDEAD_CS" ) ) {
		// запуск на прямую
		$tmp = dirname( __FILE__ )."/../../";
		chdir( $tmp );
		ini_set( "display_errors", 1 );
		define( "UNDEAD_CS", 1 );
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
		
		$modZone = new CHModZone( );
		$modLink = new CHModLink( );
		$arrFolders = array( );
		
		$objRsyncAcc = NULL;
		$hCommon = new CFlexHandler( );
		$hCommon->Create( array( "database" => $objCMS->database ) );
	
		$tmp = $hCommon->GetObject( array( FHOV_TABLE => "ud_acc_rsync", FHOV_OBJECT => "CRsyncAccount" ) );
		if ( $tmp->HasResult( ) ) {
			$tmp = $tmp->GetResult( );
			$objRsyncAcc = current( $tmp );
		}
		
		if ( $objRsyncAcc ) {
			$arrFolder = $modZone->GetFolders( );
			$objMaster = NULL;
			$objSlave = NULL;
			$tmp = $modLink->GetServers( );
			$tmp = $tmp->GetResult( );
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
			if ( $objMaster ) {
				exec( "rsync -a --delete ".$arrFolder[ "zone" ]."/ ".$objRsyncAcc->username."@".$objMaster->ip.":".$objMaster->root_prefix.$objMaster->zone_folder."/" );
			}
			if ( $objSlave ) {
				exec( "scp ".$arrFolder[ "config" ]."/slave.zones ".$objRsyncAcc->username."@".$objSlave->ip.":".$objSlave->config_file );
			}
			if ( $objMaster ) {
				exec( "scp ".$arrFolder[ "config" ]."/master.zones ".$objRsyncAcc->username."@".$objMaster->ip.":".$objMaster->config_file );
			}
		}
	}
?>