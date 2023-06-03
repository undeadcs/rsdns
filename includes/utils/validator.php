<?php
	/**
	 * 	Валидатор различных элементов
	 */
	class CValidator {
		
		/**
		 * 	Проверяет логин
		 * 	@param $szLogin string логин
		 * 	@param $iMinLen int минимальная длина логина
		 * 	@param $iMaxLen int максимальная длина логина
		 * 	@return bool
		 */
		public static function Login( $szLogin, $iMinLen = 1, $iMaxLen = 20 ) {
			$szRegExp = '/[^0-9a-zA-Z]/sU';
			return ( bool) !( preg_match( $szRegExp, $szLogin ) || ( strlen( $szLogin ) > $iMaxLen ) || ( strlen( $szLogin ) < $iMinLen ) );
		} // function Login
		
		/**
		 * 	Проверяет доменное имя
		 * 	@param $szDomain string доменное имя
		 * 	@param $bUseFilter bool использовать фильтр ( true ) или регулярку ( false )
		 * 	@param $bIgnoreDot bool игнорировать ли точку в конце имени
		 * 	@return bool
		 */
		public static function DomainName( $szDomain, $bUseFilter = false, $bIgnoreDot = false ) {
			// RFC 1033
			if ( preg_match( '/[^0-9a-zA-Z\-_.\/]/', $szDomain ) ) {
				return false;
			}
			if ( $bIgnoreDot ) {
				$szDomain = preg_replace( '/\.$/sU', '', $szDomain );
			}
			if ( $bUseFilter ) {
				return CValidator::Email( "test@".$szDomain, $bUseFilter );
			} else {
				return ( bool ) preg_match( '/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/', $szDomain );
			}
		} // function DomainName
		
		/**
		 * 	Проверяет IP адрес на верность
		 * 	@param $szIp string строка ip адрес
		 * 	@param $bIpv4 bool адрес формата IPv4 ( true ), IPv6 - false
		 * 	@return bool
		 */
		public static function IpAddress( $szIp, $bIPv4 = true ) {
			return filter_var( $szIp, FILTER_VALIDATE_IP, ( $bIPv4 ? FILTER_FLAG_IPV4 : FILTER_FLAG_IPV6 ) | /*FILTER_FLAG_NO_PRIV_RANGE |*/ FILTER_FLAG_NO_RES_RANGE );
		} // function IpAddress
		
		/**
		 * 	Проверяет правильность e-mail адреса
		 * 	@param $szEmail string строка e-mail
		 * 	@param $bUseFilter bool использовать фильтр ( true ) или регулярку ( false )
		 * 	@return bool
		 */
		public static function Email( $szEmail, $bUseFilter = true ) {
			// регулярка проверки email
			$szRegExp = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';
			if ( $bUseFilter ) {
				return ( bool ) filter_var( $szEmail, FILTER_VALIDATE_EMAIL );
			} else {
				return ( bool ) preg_match( $szRegExp, $szEmail );
			}
		} // function Email
		
		/**
		 * 	Проверяет телефонный номер
		 * 	@param $szPhone string строка, которая должна быть телефоном
		 */
		public static function Phone( $szPhone ) {
			if ( preg_match( '/[^0-9\s+]/sU', $szPhone ) ) {
				return false;
			}
			return true;
		} // function Phone
		
		/**
		 * 	Проверяет, что текст содержит только английские буквы
		 * 	@param $szText string строка, которая должна содержать только латинские буквы и пунктуацию
		 */
		public static function EngOnly( $szText ) {
			$szRegExp = '/[^a-zA-Z0-9;,\\\.\/\-"\'\s]/sU';
			return ( bool ) !preg_match( $szRegExp, $szText );
		} // funciton EngOnly
		
		/**
		 * 	Проверяет IP адрес на формат: ip_address/mask
		 * 	@param $szIp string IP-адрес
		 * 	@param $bStrict bool строгость проверки, true - маска обязательна
		 * 	@return bool
		 */
		public static function IpAddressA( $szIp, $bStrict = true ) {
			$szRegExp = '((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)';
			if ( $bStrict ) {
				$szRegExp .= '\/([0-9]|[12][0-9]|3[0-2])';
			} else {
				$szRegExp .= '(\/([0-9]|[12][0-9]|3[0-2]))?';
			}
			return ( bool ) preg_match( '/^'.$szRegExp.'$/', $szIp );
		} // function IpAddressA
		
		/**
		 * 	Проверяет на правильность задания адреса сети в обратном порядке
		 */
		public static function IpMaskReverse( $szIp ) {
			$szRegExp = '((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/([0-9]|[12][0-9]|3[0-2])\.)?((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){0,2}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)';
			//ShowVarD( preg_match( '/^'.$szRegExp.'$/', $szIp ) );
			return ( bool ) preg_match( '/^'.$szRegExp.'$/', $szIp );
		} // function IpMaskReverse
		
		/**
		 * 	Проверяет текст на соответствие набору блоков ip-адресов
		 */
		public static function IpBlock( $szBlock ) {
			if ( preg_match( '/[^0-9\.\/\r\n]/sU', $szBlock ) ) {
				return false;
			}
			$arrBlocks = preg_split( '/\r\n|\r|\n/', $szBlock );
			foreach( $arrBlocks as $i => $v ) {
				// '/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/'
				if ( !CValidator::IpNetwork( $v ) ) {
					return false;
				}
			}
			return true;
		} // function IpBlock
		
		/**
		 * 	Проверяет правильность задания адреса сети (маска учавствует)
		 */
		public static function IpNetwork( $szNetworkAddress ) {
			if ( !CValidator::IpAddressA( $szNetworkAddress ) ) {
				return false;
			}
			$tmp = explode( '/', $szNetworkAddress );
			$arrIp = explode( '.', $tmp[ 0 ] );
			$iMask = intval( $tmp[ 1 ] );
			if ( $iMask > 32 ) {
				return false;
			}
			// проверяем октеты
			$iOctet = 0;
			$iMustZero = array( );
			if ( $iMask < 8 ) {
				$iOctet = intval( $arrIp[ 0 ] );
				$iMustZero[ ] = 1;
				$iMustZero[ ] = 2;
				$iMustZero[ ] = 3;
			} elseif ( $iMask < 16 ) {
				$iOctet = intval( $arrIp[ 1 ] );
				$iMustZero[ ] = 2;
				$iMustZero[ ] = 3;
			} elseif ( $iMask < 24 ) {
				$iOctet = intval( $arrIp[ 2 ] );
				$iMustZero[ ] = 3;
			} else {
				$iOctet = intval( $arrIp[ 3 ] );
			}
			foreach( $iMustZero as $v ) {
				if ( intval( $arrIp[ $v ] ) ) {
					return false;
				}
			}
			$iParts = pow( 2, $iMask % 8 );
			$iStep = 256 / $iParts;
			$arrOctet = array( );
			for( $i = 0; $i < $iParts; ++$i ) {
				$arrOctet[ ] = $i * $iStep;
			}
			return in_array( $iOctet, $arrOctet );
		} // function IpNetwork
		
	} // class CValidator

?>