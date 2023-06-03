<?php
	/**
	 *	Бот
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModBot
	 */

	require( "bot.generator.php" );

	/**
	 * 
	 */
	class CHModBot extends CHandler {
		
		/**
		 *	Проверка на срабатывание (перехват)
		 *	@param $szQuery string строка тестирования
		 *	@return bool
		 */
		public function Test( $szQuery ) {
			global $objCMS;
			$iRank = $objCMS->GetUserRank( );
			if ( $iRank !== SUR_SUPERADMIN ) {
				return false;
			}
			return ( preg_match( '/^\/bot\//', $szQuery ) ? true : false );
		} // function Test
		
		/**
		 *	Обработка
		 *	@param $szQuery string строка, на которой произошел перехват
		 *	@return bool
		 */
		public function Process( $szQuery ) {
			$this->GetCurrentState( );
			//
			$objGenerator = new CRandomGenerator( );
			ShowVar( $objGenerator->Client( ) );
			//
			return true;
		} // function Process
		
		public function XConvert( $matches ) {
			$iChar = intval( $matches[ 1 ] );
			//$iChar = octdec( $matches[ 1 ] );
			$szChar = "";//( $iChar < 32 ? "\\".$iChar : chr( $iChar ) );
			if ( $iChar < 32 || $iChar == 127 ) {
				$szChar = "\\".$matches[ 1 ];
			} else {
				$szChar = chr( $iChar );
			}
			$szChar = chr( $iChar );
			//$szChar = "&#".$iChar.";";
			return $szChar;
		} // function XConvert
		
		/**
		 * 	Текущее состояние
		 */
		private function GetCurrentState( ) {
			global $objCMS;
			$hCommon = new CFlexHandler( );
			$hCommon->Create( array( "database" => $objCMS->database ) );
			$hCommon->CheckTable( array( FHOV_TABLE => "ud_queries", FHOV_OBJECT => "CQueries" ) );
			$hCommon->CheckTable( array( FHOV_TABLE => "ud_qdomains", FHOV_OBJECT => "CQDomain" ) );
			$hCommon->CheckTable( array( FHOV_TABLE => "ud_qitem", FHOV_OBJECT => "CQueryItem" ) );
			$hCommon->CheckTable( array( FHOV_TABLE => "ud_qcount", FHOV_OBJECT => "CQueryItemCount" ) );
			//
			$szFile = $objCMS->GetPath( "root_system" )."/2.txt";
			$objQItem = new CQueryItem( );
			$hFile = fopen( $szFile, "rb" );
			$szText = fread( $hFile, filesize( $szFile ) );
			fclose( $hFile );
			//$arrLines = file( $szFile );
			$tmp = new CQueryItem( );
			$arrIndex = $tmp->GetAttributeIndexList( );
			$arrObj = array( );
			$arrLines = explode( "\r\n", $szText );
			/*header( "Content-Type: text/html; charset=ASCII", true );
			for( $i = 128; $i < 256; ++$i ) {
				ShowVar( $i." ".chr( $i ) );
			}
			exit;*/
			header( "Content-Type: text/html; charset=UTF-8", true );
			foreach( $arrLines as $i => $v ) {
				$szTmp = $v;
				$szTmp = preg_replace_callback( '/\\\(\d{3})/', array( &$this, "XConvert" ), $szTmp );
				$szEncoding = mb_detect_encoding( $szTmp );
				//$szTmp = html_entity_decode( $szTmp );
				//$szEncoding = "cp1252";
				//$szTmp = iconv( $szEncoding, "UTF-8", $szTmp );
				if ( $szEncoding !== "UTF-8" ) {
					//$szEncoding = "cp1251";
					//$szTmp = iconv( $szEncoding, "UTF-8", $szTmp );
				}
				//ShowVar( $szEncoding, $szTmp );
				$tmp->Create( array(
					$arrIndex[ "value" ] => $szTmp
				) );
				//ShowVar( $tmp );
				$arrObj[ ] = $tmp;
				$tmp = new CQueryItem( );
			}
			ShowVar( $arrObj );
			//$hCommon->AddObject( $arrObj, array( FHOV_TABLE => "ud_qitem" ) );
			$tmp = $hCommon->GetObject( array( FHOV_TABLE => "ud_qitem", FHOV_OBJECT => "CQueryItem" ) );
			$arrObj = $tmp->GetResult( );
			ShowVarD( $arrLines, $arrObj );
			//
			$szTablePrefix = "ud_";
			$arrTables = array(
				"struct" => array( "vertex", "edge" ),
				"user" => array( "admin", "client" ),
				"zone" => array( "zone", "rr" ),
				"server" => array( "server" ),
			);
			echo "<h1>Current state</h1>";
			echo "<h2>Tables</h2>";
			echo $this->GetDivClear( );
			foreach( $arrTables as $i => $v ) {
				echo "<div style=\"float: left; width: 120px; height: 70px; margin: 0 5px 5px 0;\">--- <b>".$i."</b> ---<br/>";
				foreach( $v as $j => $w ) {
					$tmp = $hCommon->CountObject( array( FHOV_TABLE => $szTablePrefix.$w ) );
					echo "<i>".$szTablePrefix.$w ."</i>: ".$tmp->GetResult( "count" )."<br/>";
				}
				echo "</div>";
			}
			echo $this->GetDivClear( );
		} // function GetCurrentState
		
		private function GetDivClear( ) {
			return "<div style=\"clear: both; line-height: 1px; font-size: 1px;\">&nbsp;</div>";
		} // function GetDivClear
		
	} // class CHModBot
?>