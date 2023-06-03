<?php
	/**
	 * 	Включает полный режим отладки, вывод всех ошибок
	 */
	function SwitchOn( ) {
		ini_set( "display_errors", 1 );
		error_reporting( E_ALL );
	} // function SwitchOn
	
	/**
	 * 	Редирект на заданный урл
	 * 	@param $szUrl string адрес, на который сделать редирект
	 * 	@param $iCode int код возврата ( по умолчанию 301 )
	 */
	function Redirect( $szUrl, $iCode = 301 ) {
		if ( empty( $szUrl ) ) {
			$szUrl = "/";
		}
		//ShowVarD( headers_sent( ) );
		header( 'Location: '.$szUrl, true, $iCode );
		exit;
	} // function Redirect
	
	function AccForSoa( $szAcc ) {
		return str_replace( '.', "\\\.", $szAcc[ 1 ] ).'.';
	} // function AccForSoa
	
	/**
	 * 	Преобразование e-mail для SOA записи
	 */
	function ConvertEmailForSoa( $szEmail ) {
		$szEmail = preg_replace_callback( '/([^@]*)@/U', 'AccForSoa', $szEmail );
		return $szEmail;
	} // function ConvertEmailForSoa
	
	/**
	 * 	Редирект на основе js
	 */
	function RedirectJs( $szUrl, $iDelaySeconds = 1 ) {
		ob_start( );
		echo '<div>seconds to redirect:&nbsp;<span id="xcount">';
		echo $iDelaySeconds;
		echo '</span></div>';
		echo '<noscript><a href="';
		echo $szUrl; 
		echo '">next step</a></noscript>';
		echo '<script>
		<!--';
		echo "var szUrl = '$szUrl';";
		echo "var iStep = $iDelaySeconds;";
		echo 'function xredir(){if (iStep){iStep -= 1;document.getElementById("xcount").innerHTML=iStep;}else{window.location = szUrl;return;}setTimeout("xredir()",1000);}xredir();
		//-->
		</script>';
		$r = ob_get_clean( );
		if ( $r === false ) {
			$r = '';
		}
		return $r;
	}
	
	/**
	 * 	Копирует директирию
	 */
	function DirCopy( $szSrcFolder, $szDstFolder ) {
		if ( !file_exists( $szSrcFolder ) || !is_dir( $szSrcFolder ) ) {
			return;
		}
		$arrFolder = scandir( $szSrcFolder );
		if ( !file_exists( $szDstFolder ) ) {
			mkdir( $szDstFolder );
		}
		foreach( $arrFolder as $i => $v ) {
			if ( $v == "." || $v == ".." ) {
				unset( $arrFolder[ $i ] );
			} elseif ( is_dir( $szSrcFolder."/".$v ) ) {
				DirCopy( $szSrcFolder."/".$v, $szDstFolder."/".$v );
			} else {
				copy( $szSrcFolder."/".$v, $szDstFolder."/".$v );
			}
		}
	} // function DirCopy
	
	/**
	 * 	Архивирует папку, сохраняя ее структуру
	 */
	function DirArchive( &$objArchive, $szFolder, $szDelFromStart ) {
		$arrFolder = scandir( $szFolder );
		$szArchiveFolder = str_replace( $szDelFromStart, '', $szFolder );
		$objArchive->addEmptyDir( $szArchiveFolder );
		foreach( $arrFolder as $i => $v ) {
			if ( $v == "." || $v === ".." ) {
				unset( $arrFolder[ $i ] );
			} elseif ( is_dir( $szFolder."/".$v ) ) {
				DirArchive( $objArchive, $szFolder."/".$v, $szDelFromStart );
			} else {
				$objArchive->addFile( $szFolder."/".$v, $szArchiveFolder."/".$v );
			}
		}
	} // function DirArchive
	
	/**
	 * 	Очищает папку и удаляет ее
	 */
	function DirClear( $szFolder ) {
		if ( file_exists( $szFolder ) && is_dir( $szFolder ) ) {
			$arrFolder = scandir( $szFolder );
			foreach( $arrFolder as $i => $v ) {
				if ( $v == "." || $v == ".." ) {
					unset( $arrFolder[ $i ] );
				} elseif ( is_dir( $szFolder."/".$v ) ) {
					DirClear( $szFolder."/".$v );
				} else {
					if ( file_exists( $szFolder."/".$v ) ) {
						clearstatcache( );
						unlink( $szFolder."/".$v );
					}
				}
			}
			rmdir( $szFolder );
		}
	} // function DirClear
	
	function translit($str){
		static $transl = array(
			'А' => 'A',  'Б' => 'B',  'В' => 'V',  'Г' => 'G',  'Д' => 'D',  'Е' => 'E',  'Ё' => 'JO',  'Ж' => 'ZH',  'З' => 'Z',  'И' => 'I',
			'Й' => 'J', 'К' => 'K',  'Л' => 'L',  'М' => 'M',  'Н' => 'N',  'О' => 'O',  'П' => 'P',   'Р' => 'R',   'С' => 'S',  'Т' => 'T',
			'У' => 'U',  'Ф' => 'F',  'Х' => 'H',  'Ц' => 'C',  'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '',   'Ы' => 'Y',  'Ь' => '',
			'Э' => 'EH', 'Ю' => 'JU', 'Я' => 'JA', 'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g',   'д' => 'd',   'е' => 'e',  'ё' => 'jo',
			'ж' => 'zh', 'з' => 'z',  'и' => 'i',  'й' => 'j', 'к' => 'k',  'л' => 'l',  'м' => 'm',   'н' => 'n',   'о' => 'o',  'п' => 'p',
			'р' => 'r',  'с' => 's',  'т' => 't',  'у' => 'u',  'ф' => 'f',  'х' => 'h',  'ц' => 'c',   'ч' => 'ch',  'ш' => 'sh', 'щ' => 'shh',
			'ъ' => '',  'ы' => 'y',  'ь' => '',  'э' => 'eh', 'ю' => 'ju', 'я' => 'ja'
		);
		return strtr($str, $transl);
	}
	
	function ConvertEsc( $arrMatch ) {
		$iChar = intval( $arrMatch[ 1 ] );
		//$iChar = octdec( $matches[ 1 ] );
		//$szChar = "";//( $iChar < 32 ? "\\".$iChar : chr( $iChar ) );
		/*if ( $iChar < 32 || $iChar == 127 ) {
			$szChar = "\\".$arrMatch[ 1 ];
		} else {
			$szChar = chr( $iChar );
		}*/
		//$szChar = chr( $iChar );
		//$szChar = "&#".$iChar.";";
		//return $szChar;
		return chr( $iChar );
	} // function ConvertEsc
	
	/**
	 * 	Конвертирует строку в формате IP/Mask в массив
	 */
	function SplitIpMask( $mxdItem ) {
		$tmp = explode( '/', $mxdItem );
		return array( 'ip' => $tmp[ 0 ], 'mask' => $tmp[ 1 ] );
	} // function SplitIpMask
	
	/**
	 * 	Применяет к IP-адресу маску сети
	 * 	@param $szIp string IP-адрес
	 * 	@param $szMask string маска сети
	 * 	@return string
	 */
	function IpApplyMask( $szIp, $szMask ) {
		$szResult = array( );
		$arrIp = explode( '.', $szIp );
		$arrMask = explode( '.', $szMask );
		foreach( $arrIp as $i => $v ) {
			$iPart = intval( $v );
			$iPart1 = intval( $arrMask[ $i ] );
			$szResult[ ] = ( $iPart & $iPart1 );
		}
		return join( '.', $szResult );
	} // function IpApplyMask
	
	/**
	 * 	Применяет к IP-адресу, где маска целое число
	 * 	@param $szIp string IP-адрес
	 * 	@param $iMask int маска
	 * 	@return string
	 */
	function IpBitMask( $szIp, $iMask ) {
		$arrMask = array( );
		$tmp = '';
		for( $i = 0; $i < $iMask; ++$i ) {
			if ( $i && !( $i % 8 ) ) {
				$arrMask[ ] = bindec( $tmp );
				$tmp = '';
			}
			$tmp .= '1';
		}
		for( $i = $iMask; $i < 33; ++$i ) {
			if ( $i && !( $i % 8 ) ) {
				$arrMask[ ] = bindec( $tmp );
				$tmp = '';
			}
			$tmp .= '0';
		}
		return IpApplyMask( $szIp, join( '.', $arrMask ) );
	} // function IpBitMask
	
	/**
	 * 	Преобразует строку ip_address/mask и возвращает в виде массива
	 */
	function NetworkToArray( $szNetwork ) {
		$tmp = explode( '/', $szNetwork );
		$tmp[ 'ip' ] = explode( '.', $tmp[ 0 ] );
		$tmp[ 'mask' ] = intval( $tmp[ 1 ] );
		return $tmp;
	} // function NetworkToArray
	
	/**
	 * 	Возвращает количество хостов при заданной маске
	 */
	function CountItemsByMask( $iMask ) {
		return floatval( pow( 2, 32 - $iMask ) );
	} // function CountItemsByMask
	
	/**
	 * 	Проверяет на пересечение сети, которые задаются в формате: ip_address/mask
	 * 	0 - сети не пересекаются
	 * 	1 - сети пересекаются
	 * 	@param $szNetwork1 string адрес сети + маска
	 * 	@param $szNetwork1 string адрес сети + маска
	 * 	@return int
	 */
	function CompareNetwork( $szNetwork1, $szNetwork2 ) {
		$arrN1 = NetworkToArray( $szNetwork1 );
		$arrN2 = NetworkToArray( $szNetwork2 );
		// вычисляем границы диапозонов
		$n1 = CountItemsByMask( $arrN1[ 'mask' ] );
		$n2 = CountItemsByMask( $arrN2[ 'mask' ] );
		$fIp1 = floatval( sprintf( '%u', ip2long( $arrN1[ 0 ] ) ) );
		$fIp2 = floatval( sprintf( '%u', ip2long( $arrN2[ 0 ] ) ) );
		$fIp1Up = $fIp1 + $n1;
		$fIp2Up = $fIp2 + $n2;
		//ShowVar( $szNetwork1.' '.$fIp1.' - '.$fIp1Up, $szNetwork2.' '.$fIp2.' - '.$fIp2Up );
		if ( ( ( $fIp2 >= $fIp1 ) && ( $fIp2 < $fIp1Up ) ) ||
		     ( ( $fIp2Up > $fIp1 ) && ( $fIp2Up <= $fIp1Up ) ) ||
		     ( ( $fIp1 >= $fIp2 ) && ( $fIp1 < $fIp2Up ) ) ||
		     ( ( $fIp1Up > $fIp2 ) && ( $fIp1Up <= $fIp2Up ) ) ) {
			return 1;
		}
		return 0;
	} // function CompareNetwork
	
	/**
	 *	Получение имени обратной зоны для блока
	 */
	function IpBlockReverseName( $szName ) {
		$r = '';
		if ( CValidator::IpAddressA( $szName ) ) {
			$arrBlock = explode( '/', $szName );
			$arrIpParts = explode( '.', $arrBlock[ 0 ] );
			$iMask = intval( $arrBlock[ 1 ] );
			if ( $iMask < 9 ) {
				$r = $arrIpParts[ 0 ];
			} elseif ( $iMask < 17 ) {
				$r = $arrIpParts[ 1 ].'.'.$arrIpParts[ 0 ];
			} elseif ( $iMask < 25 ) {
				$r = $arrIpParts[ 2 ].'.'.$arrIpParts[ 1 ].'.'.$arrIpParts[ 0 ];
				/*if ( $iMask == 24 ) {
					$r = $arrIpParts[ 2 ].'.'.$arrIpParts[ 1 ].'.'.$arrIpParts[ 0 ];
				} else {
					$r = "0/".$iMask.'.'.$arrIpParts[ 2 ].'.'.$arrIpParts[ 1 ].'.'.$arrIpParts[ 0 ];
				}*/
			} else {
				// < 32
				$r = $arrIpParts[ 3 ]."/".$iMask.'.'.$arrIpParts[ 2 ].'.'.$arrIpParts[ 1 ].'.'.$arrIpParts[ 0 ];
			}
		}
		return $r;
	} // function IpBlockReverseName
	
	/**
	 * 	Преобразование имени обратной зоны в ее IP/Mask адрес
	 */
	function IpReverseToNetwork( $szName, $bIgnoreArpa = true ) {
		$tmp = NULL;
		$iCount = 0;
		if ( $bIgnoreArpa ) {
			$szName = preg_replace( '/(\.in-addr\.arpa)$/', '', $szName );
		}
		if ( strpos( $szName, '/' ) !== false ) {
			$tmp = explode( '/', $szName );
			$tmp[ 'ip' ] = explode( '.', $tmp[ 1 ] );
			$tmp[ 'mask' ] = intval( $tmp[ 'ip' ][ 0 ] );
			$tmp[ 'ip' ][ 0 ] = $tmp[ 0 ];
			$iCount = count( $tmp[ 'ip' ] );
			unset( $tmp[ 0 ], $tmp[ 1 ] );
		} else {
			$tmp[ 'ip' ] = explode( '.', $szName );
			$iCount = count( $tmp[ 'ip' ] );
			$arrMask = array( 0, 8, 16, 24 );
			$tmp[ 'mask' ] = intval( $arrMask[ $iCount ] );
		}
		$tmp[ 'ip' ] = array_reverse( $tmp[ 'ip' ], true );
		if ( $iCount < 4 ) {
			$iCount = 4 - $iCount;
			for( $i = 0; $i < $iCount; ++$i ) {
				$tmp[ 'ip' ][ ] = '0';
			}
		}
		return join( '.', $tmp[ 'ip' ] ).'/'.$tmp[ 'mask' ];
	} // function IpReverseToNetwork
	
?>