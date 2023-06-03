<?php
	/**
	 *	Бэкап запись
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModBackup
	 */

	// флаги резервной записи BCT - Backup Component Type
	define( "BCT_SOURCE",		bindec( "00000000000000000000000000000001" ) );
	define( "BCT_DBDUMP",		bindec( "00000000000000000000000000000010" ) );
	define( "BCT_FILEZONE",		bindec( "00000000000000000000000000000100" ) );

	/**
	 *	Резервная копия
	 */
	class CBackup extends CFlex {
		protected $id = "";
		protected $cr_date = "";
		protected $components = "";
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				"id" => true,
				"cr_date" => true,
				"components" => true,
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
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = "Record";
			return $arrConfig;
		} // function GetConfig
		
		/**
		 *	Получение XML экземпляра
		 *	@param $domDoc DOMDocument экземпляр данного класса
		 *	@return CResult
		 */
		public function GetXML( &$domDoc ) {
			$objRet = parent::GetXML( $domDoc );
			$tmp = $objRet->GetResult( "doc" );
			// дата
			$tmp1 = $this->cr_date;
			if ( preg_match( '/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $tmp1 ) ) {
				$tmp1 = preg_replace( '/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1-$2-$3 $4:$5:$6', $tmp1 );
			} elseif ( preg_match( '/(\d{4})(\d{2})(\d{2})/', $tmp1 ) ) {
				$tmp1 = preg_replace( '/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $tmp1 );
			}
			$tmp->setAttribute( "_cr_date", $tmp1 );
			// компоненты
			$tmp1 = $this->components;
			$tmp2 = array( );
			if ( $tmp1 & BCT_DBDUMP ) {
				$tmp2[ ] = "клиенты, зоны, сервера, администраторы, настройки системы";
				$tmp->setAttribute( "_db", 1 );
			}
			if ( $tmp1 & BCT_SOURCE ) {
				$tmp2[ ] = "исходные коды";
				$tmp->setAttribute( "_source", 1 );
			}
			if ( $tmp1 & BCT_FILEZONE ) {
				$tmp2[ ] = "файлы зон";
				$tmp->setAttribute( "_zone", 1 );
			}
			$tmp1 = join( "; ", $tmp2 );
			$tmp1 = iconv( "cp1251", "UTF-8", $tmp1 );
			$tmp->setAttribute( "_components", $tmp1 );
			//
			$objRet->AddResult( $tmp, "doc" );
			return $objRet;
		} // function GetXML
		
	} // class CBackup

	/**
	 *	Обработчик резервных копий
	 *	<p>Имя таблицы для данного обработчика - это имя папки, где складируются записи</p>
	 */
	class CHBackup extends CFlexHandler {
		
		/**
		 * 	Дампит нужные таблички
		 * 	@param $szFolder sting путь к папке
		 * 	@return void
		 */
		private function DumpTables( $szFolder ) {
			global $objCMS;
			chmod( $szFolder, 0777 );
			exec( "mysqldump -u ".$objCMS->database->objAccount->username." -p".$objCMS->database->objAccount->password." --opt ".$objCMS->database->objAccount->database." > ".$szFolder."/dump.sql" );
			chmod( $szFolder, 0755 );
		} // function DumpTables
		
		/**
		 * 	Восстановление таблиц
		 */
		public function RestoreTables( $szFolder ) {
			global $objCMS;
			exec( "mysql -u ".$objCMS->database->objAccount->username." -p".$objCMS->database->objAccount->password." ".$objCMS->database->objAccount->database." < ".$szFolder."/dump.sql", $tmp, $tmp1 );
		} // function RestoreTables
		
		/**
		 * 	Дампит исходные коды
		 * 	@param $szFolder string путь к папке
		 * 	@return void
		 */
		private function DumpSource( $szFolder ) {
			global $objCMS;
			$szRoot = $objCMS->GetPath( "root_system" );
			// папки которые копируются полностью
			$arrFolderToSave = array(
				"includes", "scripts", "styles", "folder"
			);
			foreach( $arrFolderToSave as $v ) {
				DirCopy( $szRoot."/".$v, $szFolder."/".$v );
			}
			// конкретные файлы на сохранение
			$arrFilesToSave = array(
				"index.php", ".htaccess", "db.php", "config.php",
				"folder/config.php", "folder/handler.php", "folder/index.php",
				"folder/custom.js", "folder/main.css", "folder/main.xsl",
				"folder/custom2.js", "folder/main_client.xsl", "folder/index_client.php"
			);
			foreach( $arrFilesToSave as $v ) {
				copy( $szRoot."/".$v, $szFolder."/".$v );
			}
		} // function DumpSource
		
		/**
		 * 	Восстанавливает исходники
		 * 	@param $szFolder string путь к папке
		 * 	@return void
		 */
		public function RestoreSource( $szFolder ) {
			global $objCMS;
			$szRoot = $objCMS->GetPath( "root_system" );
			// папки которые копируются полностью
			$arrFolderToSave = array(
				"includes", "scripts", "styles", "folder"
			);
			foreach( $arrFolderToSave as $v ) {
				DirCopy( $szFolder."/".$v, $szRoot."/".$v );
			}
			// конкретные файлы на сохранение
			$arrFilesToSave = array(
				"index.php", ".htaccess", "db.php", "config.php",
				"folder/config.php", "folder/handler.php", "folder/index.php",
				"folder/custom.js", "folder/main.css", "folder/main.xsl",
				"folder/custom2.js", "folder/main_client.xsl", "folder/index_client.php"
			);
			foreach( $arrFilesToSave as $v ) {
				copy( $szFolder."/".$v, $szRoot."/".$v );
			}
		} // function RestoreSource
		
		/**
		 * 	Создает архив
		 */
		private function MakeArchive( $szSrcFolder, $szDstFolder, $szArchiveName ) {
			$szCurFolder = getcwd( );
			chdir( $szDstFolder );
			//$cmd = "tar -czf ".$szArchiveName.".tar.gz ".$szSrcFolder."/";
			$cmd = "tar -cf ".$szArchiveName.".tar ".$szSrcFolder."/";
			exec( $cmd, $tmp, $tmp1 );
			$cmd = "lzma -8z ".$szArchiveName.".tar";
			exec( $cmd, $tmp, $tmp1 );
			chdir( $szCurFolder );
		} // function MakeArchive
		
		public function __get( $szName ) {
			if ( $szName == "folder" ) {
				return $this->folder;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Проверяет таблицу
		 *	@param $arrOptions array набор настроек
		 *	@return void
		 */
		public function CheckTable( $arrOptions ) {
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			
			if ( !empty( $szTable ) ) {
				if ( !file_exists( $szTable ) ) {
					mkdir( $szTable );
				}
				if ( !file_exists( $szTable."/index.xml" ) || !filesize( $szTable."/index.xml" ) ) {
					$domDoc = new DOMDocument( );
					$hFile = fopen( $szTable."/index.xml", "wb" );
					$doc = $domDoc->createElement( "Records" );
					$domDoc->appendChild( $doc );
					$szText = $domDoc->saveXML( );
					fwrite( $hFile, $szText, strlen( $szText ) );
					fclose( $hFile );
				}
			}
		} // function CheckTable
		
		/**
		 *	Получение объектов
		 *	@param $arrOptions array массив настроек
		 *	@return CResult
		 */
		public function GetObject( $arrOptions = array( ) ) {
			$objRet = new CResult( );
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			
			if ( !empty( $szTable ) ) {
				$domDoc = new DOMDocument( );
				$domDoc->load( $szTable.'/index.xml' );
				$objNodeList = $domDoc->getElementsByTagName( 'Record' );
				$szId = '';
				$szWhere = '';
				if ( isset( $arrOptions[ FHOV_WHERE ] ) ) {
					if ( preg_match( '/^:/', $arrOptions[ FHOV_WHERE ] ) ) {
						$szWhere = preg_replace( '/^:/', '', $arrOptions[ FHOV_WHERE ] );
					} else {
						$szId = $arrOptions[ FHOV_WHERE ];
					}
				}
				$iLen = $objNodeList->length;
				
				$iStart = 0;
				$iEnd = $iLen + 1;
				if ( isset( $arrOptions[ FHOV_LIMIT ] ) ) {
					$tmp = explode( ',', $arrOptions[ FHOV_LIMIT ] );
					$tmp[ 0 ] = trim( $tmp[ 0 ] );
					$tmp[ 1 ] = trim( $tmp[ 1 ] );
					$iStart = intval( $tmp[ 0 ] );
					$iEnd = $iStart + intval( $tmp[ 1 ] ) + 1;
				}
				//
				$domXPath = new DOMXPath( $domDoc );
				$szQuery = '//Records/Record';
				if ( $szId !== "" ) {
					$szQuery .= "[@id = '".$szId."'][1]";
				} elseif ( $szWhere !== '' ) {
					$szQuery .= '['.$szWhere.']';
					if ( $iEnd ) {
						$szQuery .= '[position( ) > '.$iStart.' and position( ) < '.$iEnd.']';
					}
				} elseif ( $iEnd ) {
					$szQuery .= '[position( ) > '.$iStart.' and position( ) < '.$iEnd.']';
				}
				$objNodeList = $domXPath->query( $szQuery );
				$iLen = $objNodeList->length;
				for( $i = 0; $i < $iLen; ++$i ) {
					$tmp = $objNodeList->item( $i );
					if ( $tmp !== NULL ) {
						$arrInput = array(
							"id" => $tmp->getAttribute( "id" ),
							"cr_date" => $tmp->getAttribute( "cr_date" ),
							"components" => $tmp->getAttribute( "components" )
						);
						$tmp = new CBackup( );
						$tmp->Create( $arrInput, FLEX_FILTER_XML );
						if ( $szId === "" ) {
							$objRet->AddResult( $tmp, $tmp->id );
						} else {
							$objRet->AddResult( $tmp, $tmp->id );
						}
					}
				}
			}
			
			return $objRet;
		} // function GetObject
		
		/**
		 *	Добавление объектов
		 *	@param $arrInput array набор новых объектов
		 *	@param $arrOptions array массив настроек
		 *	@return CResult
		 */
		public function AddObject( $arrInput, $arrOptions = array( ) ) {
			$mxdRet = new CResult( );
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			
			if ( !empty( $szTable ) ) {
				/**
				 * 1. Обновим xml файл в корне папки архивов
				 * 2. Сохраним резервную копию, если ее папки еще нет
				 */
				//
				$domDoc = new DOMDocument( );
				$domDoc->load( $szTable."/index.xml" );
				$doc = $domDoc->getElementsByTagName( "Records" )->item( 0 );
				$arrAdded = array( );
				$modZone = new CHModZone( );
				$arrZoneFolder = $modZone->GetFolders( );
				//
				foreach( $arrInput as $i => $v ) {
					if ( !file_exists( $szTable."/".$v->cr_date ) ) {
						mkdir( $szTable."/".$v->cr_date );
					}
					
					if ( $v->components & BCT_DBDUMP ) {
						mkdir( $szTable."/".$v->cr_date."/database_dump", 0777 );
						$this->DumpTables( $szTable."/".$v->cr_date."/database_dump" );
					}
					if ( $v->components & BCT_SOURCE ) {
						mkdir( $szTable."/".$v->cr_date."/source", 0777 );
						$this->DumpSource( $szTable."/".$v->cr_date."/source" );
					}
					if ( $v->components & BCT_FILEZONE ) {
						mkdir( $szTable."/".$v->cr_date."/zone", 0777 );
						DirCopy( $arrZoneFolder[ "zone" ], $szTable."/".$v->cr_date."/zone" );
						DirCopy( $arrZoneFolder[ "config" ], $szTable."/".$v->cr_date."/config" );
						//$this->DumpZones( $szTable."/".$v->cr_date."/zone" );
					}
					if ( ( $v->components & BCT_DBDUMP ) || ( $v->components & BCT_SOURCE || $v->components & BCT_FILEZONE ) ) {
						//*
						$domDoc1 = new DOMDocument( );
						$doc1 = $domDoc1->createElement( "Record" );
						$doc1->setAttribute( "id", $v->id );
						$doc1->setAttribute( "cr_date", $v->cr_date );
						$doc1->setAttribute( "components", $v->components );
						$domDoc1->insertBefore( $doc1, $domDoc1->getElementsByTagName( "Record" )->item( 0 ) );
						$hFile = fopen( $szTable."/".$v->cr_date."/index.xml", "wb" );
						$szText = $domDoc1->saveXML( );
						fwrite( $hFile, $szText, strlen( $szText ) );
						fclose( $hFile );
						//*
						$this->MakeArchive( $v->cr_date, $szTable, $v->cr_date );
						if ( file_exists( $szTable."/".$v->cr_date ) ) {
							DirClear( $szTable."/".$v->cr_date );
						}
						//*
						$doc1 = $domDoc->createElement( "Record" );
						$doc1->setAttribute( "id", $v->id );
						$doc1->setAttribute( "cr_date", $v->cr_date );
						$doc1->setAttribute( "components", $v->components );
						$doc->insertBefore( $doc1, $domDoc->getElementsByTagName( "Record" )->item( 0 ) );
						$arrAdded[ ] = $v;
						//*/
					}
				}
				if ( !empty( $arrAdded ) ) {
					$hFile = fopen( $szTable."/index.xml", "wb" );
					$szText = $domDoc->saveXML( );
					fwrite( $hFile, $szText, strlen( $szText ) );
					fclose( $hFile );
				}
			}
			
			return $mxdRet;
		} // function AddObject
		
		/**
		 *	Удаление объекта
		 *	@param $arrInput array массив экземпляров класса
		 *	@param $arrOptions array массив настроек
		 *	@return CResult
		 */
		public function DelObject( $arrInput, $arrOptions = array( ) ) {
			$objRet = new CResult( );
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			
			if ( !empty( $szTable ) ) {
				$domDoc = new DOMDocument( );
				$domDoc->load( $szTable."/index.xml" );
				$domXPath = new DOMXPath( $domDoc );
				$domRecords = $domXPath->query( '//Records[1]' );
				$domRecords = $domRecords->item( 0 );
				foreach( $arrInput as $i => $v ) {
					DirClear( $szTable."/".$i );
					if ( file_exists( $szTable."/".$i.".tar.lzma" ) ) {
						clearstatcache( );
						unlink( $szTable."/".$i.".tar.lzma" );
					}
					$szQuery = "//Records/Record[@id='".$i."']";
					$tmp = $domXPath->query( $szQuery );
					$domRecords->removeChild( $tmp->item( 0 ) );
				}
				$szText = $domDoc->saveXML( );
				file_put_contents( $szTable."/index.xml", $szText );
			}
						
			return $objRet;
		} // function DelObject
		
		/**
		 * 	Подсчитывает количество объектов
		 * 	@return CResult
		 */
		public function CountObject( $arrOptions ) {
			$objRet = new CResult( );
			$szTable = strval( isset( $arrOptions[ FHOV_TABLE ] ) ? $arrOptions[ FHOV_TABLE ] : "" );
			if ( !empty( $szTable ) ) {
				$domDoc = new DOMDocument( );
				$domDoc->load( $szTable."/index.xml" );
				$objNodeList = $domDoc->getElementsByTagName( "Record" );
				$objRet->AddResult( intval( $objNodeList->length ), "count" );
			}
			
			return $objRet;
		} // function CountObject
		
	} // class CHFileZone
	
?>