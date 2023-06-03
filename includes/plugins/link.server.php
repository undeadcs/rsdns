<?php
	/**
	 *	Сервер
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModLink
	 */

	// типы серверов SST - System Server Type
	define( "ST_MASTER",		0	); // master
	define( "ST_SLAVE",		1	); // slave

	/**
	 *	Сервер
	 */
	class CServer extends CFlex {
		protected $id = 0;
		protected $graph_vertex_id = 0; // id вершины в графе
		protected $name = ''; // имя
		protected $ip = ''; // ip адрес
		protected $config_file = ""; // конфиг - файл куда будут писаться зоны
		protected $zone_folder = ""; // папка в которой будут лежать файлы зон
		protected $type = ST_MASTER; // тип сервера
		protected $root_prefix = ""; // префикс корня, если вдруг бинд сделан через chroot
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'id' => true,
				'graph_vertex_id' => true,
				'name' => true,
				'ip' => true,
				'config_file' => true,
				'zone_folder' => true,
				'type' => true,
				'root_prefix' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 * 	Устанавливает конфиг сервака
		 * 	@param $szConfig string текст конфига сервера
		 * 	@return void
		 */
		public function SetServerConfig( $szConfig ) {
			if ( is_string( $szConfig ) ) {
				$this->server_config = $szConfig;
			}
		} // function SetServerConfig
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_server";
			$arrConfig[ FLEX_CONFIG_PREFIX ] = "server_";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "Server";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] |= FLEX_TYPE_NOTNULL | FLEX_TYPE_UNSIGNED | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "name"		][ FLEX_CONFIG_TITLE	] = "Имя";
			$arrConfig[ "ip"		][ FLEX_CONFIG_TITLE	] = "IP адрес";
			$arrConfig[ "config_file"	][ FLEX_CONFIG_TITLE	] = "Путь к конфигурационному файлу";
			$arrConfig[ "zone_folder"	][ FLEX_CONFIG_TITLE	] = "Путь к папке файлов зон";
			$arrConfig[ "type"		][ FLEX_CONFIG_TITLE	] = "Тип";
			$arrConfig[ "root_prefix"	][ FLEX_CONFIG_TITLE	] = "Префикс пути";
			return $arrConfig;
		} // function GetConfig
		
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
			$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
			if ( $szName == "name" ) {
				// проверяем доменное имя
				$szTitle = ( isset( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) ? @strval( $arrConfig[ $szName ][ FLEX_CONFIG_TITLE ] ) : $szName );
				if ( isset( $arrInput[ $szIndex ] ) && is_string( $arrInput[ $szIndex ] ) ) {
					$szValue = @strval( $arrInput[ $szIndex ] );
					if ( !CValidator::DomainName( $szValue ) ) {
						$objRet->AddError( new CError( 1, "Неверное значение поля '".$szTitle."'" ) );
					}
				} else {
					$objRet->AddError( new CError( 1, "Отсутствует поле '".$szTitle."'" ) );
				}
			} elseif ( $szName == "zone_folder" ) {
			} elseif ( $szName == "config_file" ) {
			}
			return $objRet;
		} // function InitAttr
		
	} // class CServer
	
	/**
	 *	Обработчик серверов
	 */
	class CHServer extends CFlexHandler {
	} // class CHServer
	
?>