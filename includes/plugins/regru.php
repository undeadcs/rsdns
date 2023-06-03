<?php
	/**
	 *	Модуль работы с регистратором reg.ru
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModRegru
	 */

	/**
	 * 	Аккаунт для работы с reg.ru
	 */
	class CRegRuAccount extends CFlex {
		protected	$id		= 0,
				$username	= '',
				$password	= '',
				$gateway	= '';	// 'http://www.reg.ru/api/regru'
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'id' => true, 'username' => true, 'password' => true, 'gateway' => true
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
			$arrConfig[ FLEX_CONFIG_TABLE	] = 'ud_acc_regru';
			$arrConfig[ FLEX_CONFIG_PREFIX	] = 'regru_';
			$arrConfig[ FLEX_CONFIG_SELECT	] = 'id';
			$arrConfig[ FLEX_CONFIG_UPDATE	] = 'id';
			$arrConfig[ FLEX_CONFIG_DELETE	] = 'id';
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML ][ FLEX_CONFIG_XMLNODENAME ] = 'Regru';
			// настройки атрибутов
			$arrConfig[ 'id'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ 'id'		][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ 'username'		][ FLEX_CONFIG_TITLE	] = 'Логин';
			$arrConfig[ 'password'		][ FLEX_CONFIG_TITLE	] = 'Пароль';
			return $arrConfig;
		} // function GetConfig
		
	} // class CRegRuAccount
	
	/**
	 * 	Модуль работы с reg.ru
	 */
	class CHModRegRu extends CHandler {
		protected $hCommon = NULL;
		
		private function SendForm( $arrData, $szAPIUrl ) {
			$ch = curl_init( );
			curl_setopt( $ch, CURLOPT_URL, $szAPIUrl );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $arrData );
			$data = curl_exec( $ch );
			curl_close($ch);
			
			return $data;
		}
		
		public function InitHandlers( ) {
			global $objCMS;
			$this->hCommon = new CFlexHandler( );
			$this->hCommon->Create( array( 'database' => $objCMS->database ) );
			$this->hCommon->CheckTable( array( FHOV_TABLE => 'ud_acc_regru', FHOV_OBJECT => 'CRegRuAccount' ) );
		} // function InitHandlers
		
		public function SendOrder( $objFileZone, $objClient ) {
			global $objCMS;
			if ( $this->hCommon === NULL ) {
				$this->InitHandlers( );
			}
			$objRet = new CResult( );
			$tmp = $this->hCommon->GetObject( array( FHOV_LIMIT => '2', FHOV_TABLE => 'ud_server', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CServer' ) );
			if ( $tmp->HasError( ) || !$tmp->HasResult( ) ) {
				$objRet->AddError( new CError( 1, '' ), 'error' );
				return $objRet;
			}
			$tmp = $tmp->GetResult( );
			$arrServers = array( );
			foreach( $tmp as $v ) {
				$arrServers[ ] = $v;
			}
			
			$tmp = $this->hCommon->GetObject( array( FHOV_TABLE => 'ud_acc_regru', FHOV_INDEXATTR => 'id', FHOV_OBJECT => 'CRegRuAccount' ) );
			if ( $tmp->HasError( ) || !$tmp->HasResult( ) ) {
				$objRet->AddError( new CError( 1, '' ), 'error' );
				return $objRet;
			}
			$tmp = $tmp->GetResult( );
			$objRegRuAcc = current( $tmp );
			$arrData = array( );
			if ( preg_match( '/(\.ru|\.su)$/', $objFileZone->name ) ) {
				$arrData = array(
					'action'	=> 'domain_create',
					'username'	=> $objRegRuAcc->username,
					'password'	=> $objRegRuAcc->password,
					'domain_name'	=> $objFileZone->name,
					'period'	=> 1,
					'descr'		=> 'test domain',
					'org'		=> $objClient->full_name_en,
					'org_r'		=> $objClient->full_name,
					'code'		=> $objClient->inn,
					'kpp'		=> $objClient->kpp,
					'country'	=> $objClient->country,
					'address_r'	=> $objClient->addr,
					'p_addr'	=> $objClient->postcode.' '.$objClient->region.' '.$objClient->city.' '.$objClient->street,
					'phone'		=> $objClient->phone,
					'fax'		=> $objClient->fax,
					'e_mail'	=> $objClient->email,
				);
			} else {
				$arrData = array(
					'action'	=> 'domain_create',
					'username'	=> $objRegRuAcc->username,
					'password'	=> $objRegRuAcc->password,
					'domain_name'	=> $objFileZone->name,
					'period'	=> 1,
				);
				$tmp = array( 'o', 'a', 't', 'b' );
				$tmp1 = array( 
					'company' => 'full_name_en', 'first_name' => 'first_name', 'last_name' => 'last_name',
					'email' => 'email', 'phone' => 'phone', 'fax' => 'fax', 'addr' => 'street',
					'city' => 'city', 'state' => 'region', 'postcode' => 'postcode', 'country_code' => 'country'
				);
				foreach( $tmp as $i => $v ) {
					foreach( $tmp1 as $j => $w ) {
						$tmp2 = $objClient->$w;
						if ( $j == 'phone' || $j == 'fax' ) {
							$tmp2 = str_replace( ' ', '', $tmp2 );
							$tmp2 = str_replace( '+7', '+7.', $tmp2 );
						} else {
							$tmp2 = translit( $tmp2 );
						}
						$arrData[ "{$v}_$j" ] = $tmp2;
					}
				}
			}
			if ( $objRegRuAcc->gateway != 'http://www.reg.ru/api/regru' ) {
				$arrData[ 'ns0' ] = $arrServers[ 0 ]->name;
				if ( isset( $arrServers[ 1 ] ) ) {
					$arrData[ 'ns1' ] = $arrServers[ 1 ]->name;
				}
			}
			$tmp = $this->SendForm( $arrData, $objRegRuAcc->gateway );
			if ( preg_match( '/success/i', $tmp ) ) {
				$objRet->AddResult( 'success', 'success' );
			} else {
				$objRet->AddError( new CError( 1, $tmp ), 'error' );
			}
			return $objRet;
		} // function SenOrder
		
		
	} // class CHModRegRu
	
?>