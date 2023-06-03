<?php
	/**
	 *	Дополнительные поля
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModUser
	 */

	// CFT - Client Field Type
	define( "CFT_INPUTTEXT",	0	); // поле input типа text
	define( "CFT_TEXTAREA",		1	); // поле textarea
	define( "CFT_SELECT",		2	); // поле select

	/**
	 * 	Дополнительное поле
	 */
	class CClientField extends CFlex {
		protected $id = 0; // id поля
		protected $type = CFT_INPUTTEXT; // тип
		protected $name = ""; // имя
		protected $title = ""; // заголовок
		protected $options = ""; // опции
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true, "type" => true, "name" => true, "title" => true, "options" => true,
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
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_fld";
			$arrConfig[ FLEX_CONFIG_PREFIX ] = "fld_";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = "ClField";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "id"		][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ "type"		][ FLEX_CONFIG_TYPE	] = "Тип";
			$arrConfig[ "name"		][ FLEX_CONFIG_TITLE	] = "Имя";
			$arrConfig[ "title"		][ FLEX_CONFIG_TITLE	] = "Заголовок";
			$arrConfig[ "options"		][ FLEX_CONFIG_TITLE	] = "Опции";
			$arrConfig[ "options"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_TEXT;
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
			if ( $szName == "name" || $szName == "title" ) {
				$szTitle = $this->GetAttributeTitle( $szName, $arrConfig );
				if ( $this->$szName === "" ) {
					$objRet->AddError( new CError( 1, "Поле '".$szTitle."' пусто" ), $szName );
				} elseif ( $szName == "name" ) {
					$this->$szName = preg_replace( '/\s+/', '', $this->$szName );
				}
			}
			if ( $szName == "options" ) {
				$this->$szName = preg_replace( '/\s+/', ' ', $this->$szName );
			}
			return $objRet;
		} // function InitAttr
		
	} // class CClientField
	
	/**
	 * 	Значения поля
	 */
	class CCFValue extends CFlex {
		protected $id = 0; // id
		protected $fld_id = 0; // id типа поля
		protected $cl_id = 0; // id учетки клиента
		protected $value = ""; // значение поля
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true, "fld_id" => true, "cl_id" => true, "value" => true
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
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_cfv";
			$arrConfig[ FLEX_CONFIG_PREFIX ] = "cfv_";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = "CFValue";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "id"		][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ "value"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_TEXT;
			return $arrConfig;
		} // function GetConfig
		
	} // class CCFValue
	
	/**
	 * 	Вспомогательный класс для формы клиента
	 */
	class CClientFieldValue extends CFlex {
		protected $id = 0;
		protected $type = CFT_INPUTTEXT;
		protected $name = "";
		protected $title = "";
		protected $value = "";
		protected $options = "";
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true, "type" => true, "name" => true, "title" => true, "value" => true, "options" => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Получение XML экземпляра
		 *	@param $domDoc DOMDocument экземпляр данного класса
		 *	@return CResult
		 */
		public function GetXML( &$domDoc ) {
			$objRet = parent::GetXML( $domDoc );
			if ( $objRet->HasResult( ) && $this->type === CFT_SELECT ) {
				$arrOptions = explode( ",", $this->options );
				if ( !empty( $arrOptions ) ) {
					$tmp = $objRet->GetResult( "doc" );
					foreach( $arrOptions as $i => $v ) {
						$tmp1 = trim( $v );
						$tmp2 = $domDoc->createElement( "ExtFieldOption" );
						$tmp2->setAttribute( "value", iconv( "cp1251", "UTF-8", $tmp1 ) );
						$tmp->appendChild( $tmp2 );
					}
					$objRet->AddResult( $tmp, "doc" );
				}
			}
			return $objRet;
		} // function GetXML
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = "ExtField";
			return $arrConfig;
		} // function GetConfig
		
	} // class CClientFieldValue
	
?>