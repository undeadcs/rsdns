<?php
	/**
	 *	Общалка между частями системы
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage ModTalk
	 */

	// TODO: прикрутить шифорвание/дешифрование

	// режимы отсылки данных DTM - Data Transfer Mode
	define( "DTM_GET",		1	); // запрос GET
	define( "DTM_POST",		2	); // запрос POST

	/**
	 * 	Отсыльщик данных
	 */
	class CTransfer extends CFlex {
		protected $url = array( );
		
		public function SendData( $iMode = DTM_GET, $arrData = array( ) ) {
			$objRet = new CResult( );
			$resCurl = curl_init( );
			curl_setopt_array( $resCurl, array(
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.9) Gecko/2009040821 Firefox/3.0.9",
				CURLOPT_ENCODING => "*/*",
			) );
			if ( $iMode == DTM_GET ) {
				curl_setopt( $resCurl, CURLOPT_HTTPGET, true );
			} else {
				curl_setopt( $resCurl, CURLOPT_POST, true );
				//curl_setopt( $resCurl, CURLOPT_POSTFIELDS, $arrData );
			}
			
			foreach( $this->url as $i => $v ) {
				curl_setopt( $resCurl, CURLOPT_URL, $v );
				if ( isset( $arrData[ $i ] ) ) {
					curl_setopt( $resCurl, CURLOPT_POSTFIELDS, $arrData[ $i ] );
				}
				$tmp = curl_exec( $resCurl );
				$objRet->AddResult( $tmp, $v );
			}
			
			curl_close( $resCurl );
			return $objRet;
		} // function SendData
		
	} // class CTransfer
	
	/**
	 * 	Приемних сообщений
	 */
	class CTalkSender extends CFlex {
		protected $hosts = array( ); // хосты, которым будут слаться данные
		
		/**
		 * 	Процесс работы
		 */
		public function Proc( $arrData ) {
			$arrUrl = array( );
			foreach( $this->hosts as $v ) {
				$arrUrl[ ] = "http://".$v."/";
			}
			$objTransfer = new CTransfer( );
			$objTransfer->Create( array( "url" => $arrUrl ) );
			return $objTransfer->SendData( DTM_POST, $arrData );
		} // function Proc
		
	} // class CTalkSender

	/**
	 * 	Общалка между частями системы
	 */
	class CHModTalk extends CHandler {
		
		/**
		 * 	Получение серверов
		 */
		private function GetServers( ) {
			global $objCMS;
			$hServer = new CHServer( );
			$hServer->Create( array( "database" => $objCMS->database ) );
			return $hServer->GetObject( array( FHOV_TABLE => "ud_server", FHOV_OBJECT => "CServer" ) );
		} // function GetServers
		
		/**
		 * 	Отдает команду добавления файла зон
		 * 	@param $szName string имя зоны
		 * 	@param $szText string текст файла зоны
		 * 	@param $arrOptions array набор настроек
		 * 	@return void
		 */
		public function AddFileZone( $szName, $szText, $arrOptions = array( ) ) {
			/**
			 * 1. выгребаем сервера, которым будут слаться сообщения
			 * 2. составляем сообщения, учитывая тип сервака
			 * 3. инициализируем отправщик
			 * 4. отправляем сообщения серверам
			 */
			$objSender = new CTalkSender( );
			$tmp = $this->GetServers( );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$arrData = array( );
				$tmp1 = array( );
				foreach( $tmp as $i => $v ) {
					$tmp1[ $i ] = $v->name."/talk";
					$arrData[ $i ] = array( "action" => "add_zone" );
					$arrData[ $i ][ "name" ] = $szName;
					$arrData[ $i ][ "config" ] = $v->config_file;
					$arrData[ $i ][ "type" ] = $v->type;
					if ( $v->type == ST_MASTER ) {
						$arrData[ $i ][	"folder" ] = $v->zone_folder;
						$arrData[ $i ][	"file" ] = $v->zone_folder."/".$szName.".bind";
						$arrData[ $i ][	"text" ] = $szText;
					} else {
						$arrData[ $i ][	"ip" ] = $v->ip;
					}
				}
				$objSender->Create( array( "hosts" => $tmp1 ) );
				$tmp = $objSender->Proc( $arrData );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
				} else {
					//ShowVarD( "Error after objSender Proc ", $tmp->GetError( ) );
				}
			} else {
				//ShowVarD( "Error getting servers", $tmp->GetError( ) );
			}
		} // function AddFileZone
		
		/**
		 * 	Обработка команды добавления файла зон
		 */
		private function ActionAddZone( $arrData ) {
			$szName = $arrData[ "name" ];
			$szConfig = $arrData[ "config" ];
			$bIsMaster = false;
			$arrConfig = array( "zone_name" => $szName, "type" => "", "file" => "", "masters" => "" );
			if ( $arrData[ "type" ] ) {
				$arrConfig[ "type" ] = "slave";
				$arrConfig[ "masters" ] = $arrData[ "ip" ];
			} else {
				$arrConfig[ "type" ] = "master";
				$arrConfig[ "file" ] = $arrData[ "file" ];
				$bIsMaster = true;
			}
			$objConfZone = new CBCSZone( );
			$objConfZone->Create( $arrConfig );
			
			$hBindConf = new CHBindConfig( );
			$hBindConf->CheckTable( array( FHOV_TABLE => $szConfig ) );
			$hBindConf->AddObject(
				array( $objConfZone->zone_name => $objConfZone ),
				array( FHOV_WHERE => "zone", FHOV_TABLE => $szConfig )
			);
			
			if ( $bIsMaster ) {
				$szFolder = $arrData[ "folder" ];
				if ( !file_exists( $szFolder ) ) {
					mkdir( $szFolder );
				}
				
				$szName1 = $szName.".bind";
				$szText = $arrData[ "text" ];
				if ( get_magic_quotes_gpc( ) ) {
					$szText = stripslashes( $szText );
				}
				file_put_contents( $szFolder."/".$szName1, $szText );
			}
		} // function ActionAddZone
		
		/**
		 * 	Удаление зоны
		 */
		public function DelFileZone( $szName, $arrOptions = array( ) ) {
			/**
			 * 1. выгребаем сервера, которым будут слаться сообщения
			 * 2. составляем сообщения
			 * 3. инициализируем отправщик
			 * 4. отправляем сообщения серверам
			 */
			$objSender = new CTalkSender( );
			$tmp = $this->GetServers( );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$arrData = array( );
				$tmp1 = array( );
				foreach( $tmp as $i => $v ) {
					$tmp1[ $i ] = $v->name."/talk";
					$arrData[ $i ] = array( "action" => "del_zone" );
					$arrData[ $i ][ "name" ] = $szName;
					$arrData[ $i ][ "config" ] = $v->config_file;
					$arrData[ $i ][ "type" ] = $v->type;
					if ( $v->type == ST_MASTER ) {
						$arrData[ $i ][	"folder" ] = $v->zone_folder;
						$arrData[ $i ][	"file" ] = $v->zone_folder."/".$szName.".bind";
					} else {
						$arrData[ $i ][	"ip" ] = $v->ip;
					}
				}
				$objSender->Create( array( "hosts" => $tmp1 ) );
				$tmp = $objSender->Proc( $arrData );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
				} else {
					//ShowVarD( "Error after objSender Proc ", $tmp->GetError( ) );
				}
			} else {
				//ShowVarD( "Error getting servers", $tmp->GetError( ) );
			}
		} // function DelFileZone
		
		/**
		 * 	Выполнение удаления зоны
		 */
		private function ActionDelFileZone( $arrData ) {
			$szName = $arrData[ "name" ];
			$szConfig = $arrData[ "config" ];
			$bIsMaster = false;
			$arrConfig = array( "zone_name" => $szName, "type" => "", "file" => "", "masters" => "" );
			if ( $arrData[ "type" ] ) {
				$arrConfig[ "type" ] = "slave";
				$arrConfig[ "masters" ] = $arrData[ "ip" ];
			} else {
				$arrConfig[ "type" ] = "master";
				$arrConfig[ "file" ] = $arrData[ "file" ];
				$bIsMaster = true;
			}
			$objConfZone = new CBCSZone( );
			$objConfZone->Create( $arrConfig );
			
			$hBindConf = new CHBindConfig( );
			$hBindConf->CheckTable( array( FHOV_TABLE => $szConfig ) );
			$hBindConf->DelObject(
				array( $objConfZone->zone_name => $objConfZone ),
				array( FHOV_WHERE => "zone", FHOV_TABLE => $szConfig )
			);
			
			if ( $bIsMaster ) {
				$szFolder = $arrData[ "folder" ];
				if ( !file_exists( $szFolder ) ) {
					mkdir( $szFolder );
				}
				
				$szName1 = $szName.".bind";
				if ( file_exists( $szFolder."/".$szName1 ) ) {
					clearstatcache( );
					unlink( $szFolder."/".$szName1 );
				}
			}
		} // function ActionDelFileZone
		
		/**
		 * 	Обновление зоны
		 */
		public function UpdFileZone( $szName, $szText, $arrOptions = array( ) ) {
			/**
			 * 1. выгребаем сервера, которым будут слаться сообщения
			 * 2. составляем сообщения, учитявая тип сервера
			 * 3. инициализируем отправщик
			 * 4. отправляем сообщения серверам
			 */
			$objSender = new CTalkSender( );
			$tmp = $this->GetServers( );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( );
				$arrData = array( );
				$tmp1 = array( );
				foreach( $tmp as $i => $v ) {
					if ( $v->type == ST_MASTER ) {
						$tmp1[ $i ] = $v->name."/talk";
						$arrData[ $i ] = array( "action" => "upd_zone" );
						$arrData[ $i ][ "name" ] = $szName;
						$arrData[ $i ][	"folder" ] = $v->zone_folder;
						$arrData[ $i ][	"file" ] = $v->zone_folder."/".$szName.".bind";
						$arrData[ $i ][ "type" ] = $v->type;
						$arrData[ $i ][ "text" ] = $szText;
					}
				}
				$objSender->Create( array( "hosts" => $tmp1 ) );
				$tmp = $objSender->Proc( $arrData );
				if ( $tmp->HasResult( ) ) {
					$tmp = $tmp->GetResult( );
					//ShowVarD( $tmp );
				} else {
					//ShowVarD( "Error after objSender Proc ", $tmp->GetError( ) );
				}
			} else {
				//ShowVarD( "Error getting servers", $tmp->GetError( ) );
			}
		} // function UpdFileZone
		
		/**
		 * 	Выполнение обновления зоны
		 */
		private function ActionUpdFileZone( $arrData ) {
			$szName = $arrData[ "name" ];
			if ( intval( $arrData[ "type" ] ) === ST_MASTER ) {
				$szFolder = $arrData[ "folder" ];
				if ( !file_exists( $szFolder ) ) {
					mkdir( $szFolder );
				}
				
				$szName1 = $szName.".bind";
				$szText = $arrData[ "text" ];
				if ( get_magic_quotes_gpc( ) ) {
					$szText = stripslashes( $szText );
				}
				file_put_contents( $szFolder."/".$szName1, $szText );
			}
		} // function UpdFileZone
		
		/**
		 *	Проверка на срабатывание (перехват)
		 *	@param $szQuery string строка тестирования
		 *	@return bool
		 */
		public function Test( $szQuery ) {
			return ( preg_match( '/^\/talk\//', $szQuery ) ? true : false );
		} // function Test
		
		/**
		 *	Обработка
		 *	@param $szQuery string строка, на которой произошел перехват
		 *	@return bool
		 */
		public function Process( $szQuery ) {
			global $objCMS;
			$szQuery = preg_replace( '/^\/talk/', '', $szQuery );
			if ( preg_match( '/^\/sender\//', $szQuery ) ) {
			} else {
				if ( count( $_POST ) ) {
					$arrData = $_POST;
					$szAction = $arrData[ "action" ];
					switch( $szAction ) {
						case "add_zone": {
							$this->ActionAddZone( $arrData );
						} break;
						case "del_zone": {
							$this->ActionDelFileZone( $arrData );
						} break;
						case "upd_zone": {
							$this->ActionUpdFileZone( $arrData );
						} break;
					}
				}
			}
			
			return true;
		} // function Process
		
	} // class CHModTalk
	
?>