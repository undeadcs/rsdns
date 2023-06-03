<?php
	global $objCMS, $objCurrent, $iCurrentSysRank, $szCurrentMode, $mxdCurrentData, $arrErrors, $mxdLinks;

	header( 'Content-Type: text/html; charset=cp1251' );
	
	$objPage = new CPage( );
	$szRootRelative = $objCMS->GetPath( 'root_relative' );
	$objPage->SetTitle( 'РС ДНС' );
	$objPage->AddMeta( array( 'http_equiv' => 'Content-Type', 'content' => 'text/html; charset=cp1251' ) );
	$objPage->AddStyle( $szRootRelative.'/main.css' );
	$objPage->AddScript( $szRootRelative.'/jquery.js' );
	$objPage->AddScript( $szRootRelative.'/main.js' );
	
	$domDoc = new DOMDocument( );
	$domXsl = new DOMDocument( );
	$objXlst = new XSLTProcessor( );
	
	$objDoc = $domDoc->createElement( 'Doc' );
	$objDoc->setAttribute( 'logo_url', $objCMS->GetPath( 'root_relative' ).'/' );
	$objDoc->setAttribute( 'logo_src', $objCMS->GetPath( 'root_relative' ).'/skin/logo.gif' );
	$domDoc->appendChild( $objDoc );
	
	$domXsl->load( $objCMS->GetPath( 'root_application' ).'/main_client.xsl' );
	
	$tmp = $mxdCurrentData[ 'menu' ]->GetXML( $domDoc );
	if ( $tmp->HasResult( ) ) {
		$objDoc->appendChild( $tmp->GetResult( 'doc' ) );
	}
	
	if ( $objCurrent && $szCurrentMode ) {
		$doc = $domDoc->createElement( $objCurrent.$szCurrentMode );
		$objDoc->appendChild( $doc );
		
		if ( !empty( $arrErrors ) ) {
			foreach( $arrErrors as $i => $v ) {
				$tmp = $domDoc->createElement( "Error" );
				$tmp->setAttribute( "code", $v->code );
				$tmp->setAttribute( "text", iconv( "cp1251", "UTF-8", $v->text ) );
				$doc->appendChild( $tmp );
			}
		}
		if ( $szCurrentMode === 'ZoneReg' ) {
			$objPage->SetTitle( 'РС ДНС / Добавление зоны' );
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" ) );
			if ( isset( $mxdCurrentData[ "filezone" ] ) ) {
				$tmp = $mxdCurrentData[ "filezone" ]->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( "doc" ) );
				}
			}
			if ( isset( $mxdCurrentData[ "user_blocked" ] ) ) {
				$doc->setAttribute( "blocked", true );
			}
		}
		if ( $szCurrentMode === 'ZoneAdd' ) {
			$objPage->SetTitle( 'РС ДНС / Добавление зоны' );
			$doc->setAttribute( 'base_url', $objCMS->GetPath( 'root_relative' ) );
			if ( isset( $mxdCurrentData[ 'filezone' ] ) ) {
				$tmp = $mxdCurrentData[ 'filezone' ]->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( 'doc' ) );
				}
			}
			if ( isset( $mxdCurrentData[ 'not_allowed_add' ] ) ) {
				$doc->setAttribute( 'noadd', true );
			}
			if ( isset( $mxdCurrentData[ 'user_blocked' ] ) ) {
				$doc->setAttribute( 'blocked', true );
			}
		}
		if ( $szCurrentMode === "RegDomain" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" ) );
			if ( isset( $mxdCurrentData[ "url" ] ) ) {
				$doc->setAttribute( "reg_url", $mxdCurrentData[ "url" ] );
			}
			if ( isset( $mxdCurrentData[ "reg" ] ) ) {
				$doc->setAttribute( "reg", $mxdCurrentData[ "reg" ] );
			}
			if ( isset( $mxdCurrentData[ "current_domain" ] ) ) {
				$tmp = $mxdCurrentData[ "current_domain" ]->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( "doc" ) );
				}
			}
			if ( isset( $mxdCurrentData[ 'not_allowed_register' ] ) ) {
				$doc->setAttribute( 'noreg', true );
			}
			if ( isset( $mxdCurrentData[ "user_blocked" ] ) ) {
				$doc->setAttribute( "blocked", true );
			}
		}
		if ( $szCurrentMode === "DomainReg" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" ) );
			if ( isset( $mxdCurrentData[ "filezone" ] ) ) {
				$tmp = $mxdCurrentData[ "filezone" ]->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( "doc" ) );
				}
			}
			if ( isset( $mxdCurrentData[ "user_blocked" ] ) ) {
				$doc->setAttribute( "blocked", true );
			}
		}
		if ( $szCurrentMode === "Account" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/account" );
			$objPage->SetTitle( "РС ДНС / Персональные данные" );
			$tmp = $mxdCurrentData[ "current_user" ]->GetXML( $domDoc );
			if ( $tmp->HasResult( ) ) {
				$doc->appendChild( $tmp->GetResult( "doc" ) );
			}
			if ( isset( $mxdCurrentData[ "user_blocked" ] ) ) {
				$doc->setAttribute( "blocked", true );
			}
		}
		if ( $szCurrentMode === "ZoneList" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/zone" );
			$objPage->SetTitle( "РС ДНС / Зоны" );
			foreach( $mxdCurrentData[ "zone_list" ] as $i => $v ) {
				$tmp = $v->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( "doc" ) );
				}
			}
		}
		if ( $szCurrentMode === "ZoneEdit" ) {
			$iLocked = isset( $mxdCurrentData[ "zone_locked" ] ) ? 1 : 0;
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/zone" );
			if ( $iLocked ) {
				$objPage->SetTitle( "РС ДНС / Просмотр зоны" );
			} else {
				$objPage->SetTitle( "РС ДНС / Редактирование зоны" );
			}
			$doc->setAttribute( "locked", $iLocked );
			foreach( $mxdCurrentData[ "servers" ] as $v ) {
				$tmp = $v->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( "doc" ) );
				}
			}
			$doc->setAttribute( "last", $mxdCurrentData[ "rr_last" ] );
			$tmp = $mxdCurrentData[ "current_zone" ]->GetXML( $domDoc );
			if ( $tmp->HasResult( ) ) {
				$doc->appendChild( $tmp->GetResult( "doc" ) );
			}
		}
		if ( $szCurrentMode === "ZoneAddRR" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/zone" );
			$objPage->SetTitle( "РС ДНС / Добавление ресурсной записи" );
			$doc->setAttribute( "rr_pos", $mxdCurrentData[ "rr_pos" ] );
			$tmp = $mxdCurrentData[ "current_zone" ]->GetXML( $domDoc );
			if ( $tmp->HasResult( ) ) {
				$tmp = $tmp->GetResult( "doc" );
				$doc->appendChild( $tmp );
			}
			$doc->setAttribute( "mode", $mxdCurrentData[ "rr_mode" ] );
			if ( $mxdCurrentData[ "rr_mode" ] == "select" ) {
			} else {
				if ( isset( $mxdCurrentData[ "current_rr" ] ) ) {
					$tmp = $mxdCurrentData[ "current_rr" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( "doc" );
						$doc->appendChild( $tmp );
					}
				}
			}
		}
		if ( $szCurrentMode === "ZoneOldList" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/zone" );
			$objPage->SetTitle( "РС ДНС / Версии файлов зон" );
			$iLocked = isset( $mxdCurrentData[ "zone_locked" ] ) ? 1 : 0;
			$doc->setAttribute( "locked", $iLocked );
			$tmp = $mxdCurrentData[ "current_zone" ]->GetXML( $domDoc );
			if ( $tmp->HasResult( ) ) {
				$doc->appendChild( $tmp->GetResult( "doc" ) );
			}
			
			foreach( $mxdCurrentData[ "old_zone_list" ] as $i => $v ) {
				$tmp = $v->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( "doc" ) );
				}
			}
		}
		if ( $szCurrentMode === "ZoneOldView" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/zone" );
			$objPage->SetTitle( "РС ДНС / Версия файлов зон" );
			$iLocked = isset( $mxdCurrentData[ "zone_locked" ] ) ? 1 : 0;
			$doc->setAttribute( "locked", $iLocked );
			$tmp = $mxdCurrentData[ "current_zone" ]->GetXML( $domDoc );
			if ( $tmp->HasResult( ) ) {
				$doc->appendChild( $tmp->GetResult( "doc" ) );
			}
			$tmp = $mxdCurrentData[ "current_old_zone" ]->GetXML( $domDoc );
			if ( $tmp->HasResult( ) ) {
				$doc->appendChild( $tmp->GetResult( "doc" ) );
			}
		}
		if ( $szCurrentMode === "ZoneUpload" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/zone" );
			$objPage->SetTitle( "РС ДНС / Загрузка файла зон" );
			$tmp = $mxdCurrentData[ "current_zone" ]->GetXML( $domDoc );
			if ( $tmp->HasResult( ) ) {
				$doc->appendChild( $tmp->GetResult( "doc" ) );
			}
		}
		if ( $szCurrentMode === "ZoneExport" ) {
			$objPage->SetTitle( "РС ДНС / Экспорт" );
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/zone" );
			$tmp = $mxdCurrentData[ "current_zone" ]->GetXML( $domDoc );
			if ( $tmp->HasResult( ) ) {
				$doc->appendChild( $tmp->GetResult( "doc" ) );
			}
		}
		if ( $szCurrentMode === "Help" ) {
			$objPage->SetTitle( "РС ДНС / Помощь" );
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/help" );
		}
	}
	
	$objPage->StartBody( );
	
	$objXlst->importStylesheet( $domXsl );
	$szText = $objXlst->transformToXml( $domDoc );
	$szText = iconv( "UTF-8", "cp1251//TRANSLIT", $szText );
	$szText = preg_replace( '/<textarea([^>]*)\/>/', '<textarea$1></textarea>', $szText );
	$szText = preg_replace( '/<script([^>]*)\/>/', '<script$1></script>', $szText );
	$szText = preg_replace( '/<a([^>]*)\/>/', '<a$1></a>', $szText );
	echo $szText;
	
	$objPage->EndBody( );
	echo $objPage->GetDoc( );
	
?>
