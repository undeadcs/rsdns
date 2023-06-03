<?php
	/**
	 *	Модуль пользователей
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModUser
	 */

	/**
	 * 	Помощь
	 */
	class CHelp extends CFlex {
		protected $path = ""; // путь к файлу с html
		
		/**
		 *	Получение XML экземпляра
		 *	@param $domDoc DOMDocument экземпляр данного класса
		 *	@return CResult
		 */
		public function GetXML( &$domDoc ) {
			global $objCMS;
			$objRet = parent::GetXML( $domDoc );
			$tmp = $objRet->GetResult( "doc" );
			if ( file_exists( $this->path ) ) {
				/*$szText = file_get_contents( $this->path );
				$szRootRelative = $objCMS->GetPath( "root_relative" );
				$szText = preg_replace( '/\{\:root_relative\}/', $szRootRelative, $szText );
				$szText = iconv( "cp1251", "UTF-8", $szText );
				$tmp1 = new DOMDocument( );
				$tmp1->loadHTML( $szText );
				$tmp2 = $tmp1->getElementsByTagName( "body" )->item( 0 );
				$tmp2 = $domDoc->importNode( $tmp2, true );
				$tmp->appendChild( $tmp2 );*/
			}
			$objRet->AddResult( $tmp, "doc" );
			return $objRet;
		} // function GetXML
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "Help";
			return $arrConfig;
		} // function GetConfig
		
	} // class CHelp

	class CHModHelp extends CHandler {
		
		/**
		 *	Проверка на срабатывание (перехват)
		 *	@param $szQuery string строка тестирования
		 *	@return bool
		 */
		public function Test( $szQuery ) {
			return ( preg_match( '/\/help\//', $szQuery ) ? true : false );
		} // function Test
		
		/**
		 *	Обработка
		 *	@param $szQuery string строка, на которой произошел перехват
		 *	@return bool
		 */
		public function Process( $szQuery ) {
			global $objCMS, $objCurrent, $iCurrentSysRank, $mxdCurrentData, $szCurrentMode, $arrErrors, $mxdLinks;
			// выставляем текущий модуль
			$objCMS->SetWGI( WGI_HELP );
			$objCMS->SetWGIState( MF_THIS );
			$objCurrent = "Help";
			$szCurrentMode = "View";
			$arrErrors = array( );
			$iCurrentSysRank = $objCMS->GetUserRank( );
			
			//$mxdCurrentData[ "help" ] = new CHelp( );
			//$mxdCurrentData[ "help" ]->Create( array( "path" => $objCMS->GetPath( "root_application" )."/help/admin.html" ) );
			
			// передаем управление приложению
			$szFolder = $objCMS->GetPath( "root_application" );
			if ( $szFolder !== false && file_exists( $szFolder."/index.php" ) ) {
				include_once( $szFolder."/index.php" );
			}
			return true;
		} // function Process
		
	} // class CHHelp
	
?>