<?php
	/**
	*	Аккаунт FTP
	*/
	class CFtpAccount extends CAccount {
		public $id = 0;
		public $port = 0; // порт на котором крутится ftp
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_acc_ftp";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "id"		][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ "login"		][ FLEX_CONFIG_TITLE	] = "Порт";
			return $arrConfig;
		} // function GetConfig
		
	} // class CFtpAccount
	
	/**
	*	Обработчик аккаунтов FTP
	*/
	class CHFtpAccount extends CFlexHandler {
	} // class CHFtpAccount
?>