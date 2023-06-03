<?php
	/**
	 *	Лог
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModLogger
	 */

	/**
	 *	Лог
	 */
	class CLog extends CFlex {
		protected $id = 0;
		//protected $graph_vertex_id = 0;
		protected $user = ""; // пользователь
		protected $module = ""; // модуль
		protected $action = ""; // действие
		protected $cr_date = ""; // дата создания
		protected $ip_address = ""; // ip инициатора
		protected $comment = ""; // комментарий
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE	] = "ud_log";
			$arrConfig[ FLEX_CONFIG_PREFIX	] = "log_";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = "Log";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "id"		][ FLEX_CONFIG_DIGITS	] = 10;
			/*$arrConfig[ "graph_vertex_id"	][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_DEFAULT;
			$arrConfig[ "graph_vertex_id"	][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ "graph_vertex_id"	][ FLEX_CONFIG_DEFAULT	] = 0;*/
			$arrConfig[ "user"		][ FLEX_CONFIG_LENGHT	] = 20;
			$arrConfig[ "user"		][ FLEX_CONFIG_TITLE	] = "Пользователь";
			$arrConfig[ "module"		][ FLEX_CONFIG_TITLE	] = "Модуль";
			$arrConfig[ "action"		][ FLEX_CONFIG_TITLE	] = "Действие";
			$arrConfig[ "cr_date"		][ FLEX_CONFIG_TITLE	] = "Время создания";
			$arrConfig[ "cr_date"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_DATE | FLEX_TYPE_TIME;
			$arrConfig[ "comment"		][ FLEX_CONFIG_TITLE	] = "Комментарий";
			return $arrConfig;
		} // function GetConfig
		
		/**
		 *	Получение значения атрибута объекта
		 *	@param $szName string имя атрибута
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return mixed
		 */
		protected function FilterAttr( $szName, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			if ( $iMode == FLEX_FILTER_XML ) {
				if ( $szName == "module" ) {
					$tmp = $this->module;
					switch( $tmp ) {
						case "ModUser":		$tmp = "Пользователи";		break;
						case "ModZone":		$tmp = "Зоны";			break;
						case "ModLink":		$tmp = "Сервера";		break;
						case "ModLogin":	$tmp = "Авторизация";		break;
						case "ModBackup":	$tmp = "Резервное копирование";	break;
					}
					$this->module = $tmp;
				}
				if ( $szName == "action" ) {
					$tmp = $this->action;
					
					if ( preg_match( '/ModUser::AddClient/', $tmp ) ) {
						$tmp = "Добавление клиента";
					} elseif ( preg_match( '/ModUser::AddAdmin/', $tmp ) ) {
						$tmp = "Добавление админа";
					} elseif ( preg_match( '/ModUser::DelClient/', $tmp ) ) {
						$tmp = "Удаление клиента";
					} elseif ( preg_match( '/ModUser::DelAdmin/', $tmp ) ) {
						$tmp = "Удаление админа";
					} elseif( preg_match( '/ModLink::AddServer/', $tmp ) ) {
						$tmp = "Добавление сервера";
					} elseif ( preg_match( '/ModLink::DelServer/', $tmp ) ) {
						$tmp = "Удаление сервера";
					} elseif ( preg_match( '/ModZone::AddFileZone/', $tmp ) ) {
						$tmp = "Добавление зоны";
					} elseif ( preg_match( '/ModZone::DelFileZone/', $tmp ) ) {
						$tmp = "Удаления зоны";
					} elseif ( preg_match( '/ModZone::UpdFileZone/', $tmp ) ) {
						$tmp = "Изменение зоны";
					} elseif ( preg_match( '/ModZone::StartGenerator/', $tmp ) ) {
						$tmp = "Генерация файлов зон";
					} elseif ( preg_match( '/ModBackup::AddBackup/', $tmp ) ) {
						$tmp = "Создание резервной копии";
					} elseif ( preg_match( '/ModBackup::DelBackup/', $tmp ) ) {
						$tmp = "Удаление резервной копии";
					} elseif ( preg_match( '/ModBackup::UseBackup/', $tmp ) ) {
						$tmp = "Восстановление из резервной копии";
					} elseif ( preg_match( '/ModLogin::Login/', $tmp ) ) {
						$tmp = "Авторизация";
					}
					
					$this->action = $tmp;
				}
			}
			return parent::FilterAttr( $szName, $arrConfig, $iMode );
		} // function FilterAttr
		
	} // class CLog
	
	/**
	 *	Обработчик логов
	 */
	class CHLog extends CFlexHandler {
	} // class CHLog
	
?>