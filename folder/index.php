<?php
	global $objCMS, $objCurrent, $iCurrentSysRank, $szCurrentMode, $mxdCurrentData, $arrErrors, $mxdLinks;

	header( 'Content-Type: text/html; charset=windows-1251' );
	
	$objPage = new CPage( );
	$objPage->SetTitle( 'РС ДНС' );
	$objPage->AddMeta( array( 'http_equiv' => 'Content-Type', 'content' => 'text/html; charset=windows-1251' ) );
	$objPage->AddStyle( $objCMS->GetPath( 'root_relative' ).'/main.css' );
	$objPage->AddScript( $objCMS->GetPath( 'root_relative' ).'/jquery.js' );
	
	$domDoc = new DOMDocument( );
	$domXsl = new DOMDocument( );
	$objXlst = new XSLTProcessor( );
	
	$objDoc = $domDoc->createElement( 'Doc' );
	$objDoc->setAttribute( 'logo_url', $objCMS->GetPath( 'root_relative' ).'/' );
	$objDoc->setAttribute( 'logo_src', $objCMS->GetPath( 'root_relative' ).'/skin/logo.gif' );
	$domDoc->appendChild( $objDoc );
	
	$domXsl->load( $objCMS->GetPath( 'root_application' ).'/main.xsl' );
	
	$tmp = $objCMS->GetMenu( $domDoc );
	if ( $tmp->HasResult( ) ) {
		$objDoc->appendChild( $tmp->GetResult( 'doc' ) );
	}
	
	if ( $objCurrent && $szCurrentMode ) {
		$doc = $domDoc->createElement( $objCurrent.$szCurrentMode );
		$objDoc->appendChild( $doc );
		
		if ( !empty( $arrErrors ) ) {
			foreach( $arrErrors as $i => $v ) {
				$tmp = $domDoc->createElement( 'Error' );
				$tmp->setAttribute( 'code', $v->code );
				$tmp->setAttribute( 'text', iconv( 'cp1251', 'UTF-8', $v->text ) );
				$doc->appendChild( $tmp );
			}
		}
		if ( $objCurrent === 'Install' ) {
			$domDoc = new DOMDocument( );
			$doc = $domDoc->createElement( $objCurrent.$szCurrentMode );
			$doc->setAttribute( 'logo_url', $objCMS->GetPath( 'root_relative' ).'/' );
			$doc->setAttribute( 'logo_src', $objCMS->GetPath( 'root_relative' ).'/skin/logo.gif' );
			$domDoc->appendChild( $doc );
			
			if ( !empty( $arrErrors ) ) {
				foreach( $arrErrors as $i => $v ) {
					$tmp = $domDoc->createElement( 'Error' );
					$tmp->setAttribute( 'text', iconv( 'cp1251', 'UTF-8', $v->text ) );
					$doc->appendChild( $tmp );
				}
			}
			
			$doc->setAttribute( 'post_url', $objCMS->GetPath( 'root_relative' ).'/$/' );
			
			$arrNeed = array( 'db', 'superadmin', 'regru', 'rsync', 'system' );
			foreach( $arrNeed as $v ) {
				$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( 'doc' ) );
				}
			}
			
			$objPage->StartBody( );
			
			$objXlst->importStylesheet( $domXsl );
			$szText = $objXlst->transformToXml( $domDoc );
			$szText = iconv( 'UTF-8', 'cp1251//TRANSLIT', $szText );
			$szText = preg_replace( '/<textarea([^>]*)\/>/', '<textarea$1></textarea>', $szText );
			echo $szText;
			
			$objPage->EndBody( );
			echo $objPage->GetDoc( );
			return;
		}
		if ( $objCurrent === 'Login' ) {
			if ( $szCurrentMode === 'Form' ) {
				$objPage->SetTitle( 'РС ДНС / Вход в систему' );
				$domDoc = new DOMDocument( );
				$doc = $domDoc->createElement( $objCurrent.$szCurrentMode );
				$doc->setAttribute( 'logo_url', $objCMS->GetPath( 'root_relative' ).'/' );
				$doc->setAttribute( 'logo_src', $objCMS->GetPath( 'root_relative' ).'/skin/logo.gif' );
				$domDoc->appendChild( $doc );
				
				$objPage->StartBody( );
				
				$objXlst->importStylesheet( $domXsl );
				$szText = $objXlst->transformToXml( $domDoc );
				$szText = iconv( 'UTF-8', 'cp1251//TRANSLIT', $szText );
				$szText = preg_replace( '/<textarea([^>]*)\/>/', '<textarea$1></textarea>', $szText );
				echo $szText;
				
				$objPage->EndBody( );
				echo $objPage->GetDoc( );
				return;
			}
			if ( $szCurrentMode === 'Exit' ) {
				$objPage->SetTitle( 'РС ДНС / Выход из системы' );
				$doc->setAttribute( 'post_url', $objCMS->GetPath( 'root_relative' ).'/exit/' );
			}
		}
		if ( $objCurrent === 'User' ) {
			$doc->setAttribute( 'root_relative', $objCMS->GetPath( 'root_relative' ) );
			$doc->setAttribute( 'base_url', $objCMS->GetPath( 'root_relative' ).'/user' );
			$doc->setAttribute( 'base_url_zone', $objCMS->GetPath( 'root_relative' ).'/zone' );
			
			if ( $mxdCurrentData !== NULL ) {
				if ( $szCurrentMode === 'Edit' ) {
					$objPage->AddScript( $objCMS->GetPath( 'root_relative' ).'/calendar.js' );
					$objPage->AddScript( $objCMS->GetPath( 'root_relative' ).'/custom.js' );
					$tmp1 = $mxdCurrentData[ "current_user" ]->GetXML( $domDoc );
					if ( $tmp1->HasResult( ) ) {
						$tmp2 = $tmp1->GetResult( "doc" );
						if ( $mxdCurrentData[ "current_user" ]->id !== 0 ) {
							$objPage->SetTitle( "РС ДНС / Настройки пользователя" );
							$doc->setAttribute( "mode", "edit" );
							$doc->setAttribute( "base_url_client", $objCMS->GetPath( "root_relative" )."/user/".$mxdCurrentData[ "current_user" ]->login );
							$doc->setAttribute( "post_url", $objCMS->GetPath( "root_relative" )."/user/".$mxdCurrentData[ "current_user" ]->login."/" );
							if ( isset( $mxdCurrentData[ "err_user" ] ) ) {
								$tmpErrUser = $mxdCurrentData[ "err_user" ]->GetXML( $domDoc );
								if ( $tmpErrUser->HasResult( ) ) {
									$tmpErrUser = $tmpErrUser->GetResult( "doc" );
									$tmpErrUser->setAttribute( "main", 1 );
									$doc->appendChild( $tmpErrUser );
								}
							} else {
								$tmp2->setAttribute( "main", 1 );
							}
						} else {
							$objPage->SetTitle( "РС ДНС / Добавление нового пользователя" );
							$doc->setAttribute( "mode", "add" );
							$doc->setAttribute( "post_url", $objCMS->GetPath( "root_relative" )."/user/+/" );
							$tmp2->setAttribute( "main", 1 );
						}
						
						foreach( $mxdCurrentData[ "field_list" ] as $v ) {
							$tmp = $v->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp2->appendChild( $tmp->GetResult( "doc" ) );
							}
						}
						$doc->appendChild( $tmp2 );
					}
				}
				if ( $szCurrentMode === "List" ) {
					$objPage->AddScript( $objCMS->GetPath( "root_relative" )."/calendar.js" );
					$objPage->AddScript( $objCMS->GetPath( "root_relative" )."/custom.js" );
					$objPage->SetTitle( "РС ДНС / Управление клиентами" );
					$fltDate = new CDateFilter( );
					foreach( $mxdCurrentData[ "user_list" ] as $i => $v ) {
						$tmp = $v->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$tmp1 = $tmp->GetResult( "doc" );
							$arrDate = array( "reg_date" );
							foreach( $arrDate as $w ) {
								$szIndex = $v->GetAttributeIndex( $w, NULL, FLEX_FILTER_XML );
								$szDate = $tmp1->getAttribute( $szIndex );
								if ( !empty( $szDate ) ) {
									$szDate = $fltDate->Apply( $szDate );
									$tmp1->setAttribute( $szIndex, $szDate );
								}
							}
							$doc->appendChild( $tmp1 );
						}
					}
					if ( isset( $mxdCurrentData[ "pager" ] ) ) {
						$tmp = $mxdCurrentData[ "pager" ]->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
					if ( isset( $mxdCurrentData[ "filter" ] ) ) {
						$tmp = $mxdCurrentData[ "filter" ]->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				if ( $szCurrentMode === "Fields" ) {
					$objPage->SetTitle( "РС ДНС / Дополнительные поля" );
					$tmp = $mxdCurrentData[ "current_field" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$doc->appendChild( $tmp->GetResult( "doc" ) );
					}
					foreach( $mxdCurrentData[ "field_list" ] as $i => $v ) {
						$tmp = $v->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				if ( $szCurrentMode === 'Zone' ) {
					$objPage->SetTitle( "РС ДНС / Добавление зоны" );
					if ( !empty( $mxdLinks ) ) {
						if ( isset( $mxdLinks[ "url" ] ) ) {
							$doc->setAttribute( "reg_url", $mxdLinks[ "url" ] );
						}
						if ( isset( $mxdLinks[ "reg" ] ) ) {
							$doc->setAttribute( "reg", $mxdLinks[ "reg" ] );
						}
					}
					$arrNeed = array( "current_user", "filezone" );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->result[ "doc" ];
								$doc->appendChild( $tmp );
							}
						}
					}
				}
				if ( $szCurrentMode === 'ZoneReg' ) {
					$objPage->SetTitle( "РС ДНС / Добавление зоны" );
					$arrNeed = array( "current_user", "filezone" );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->result[ "doc" ];
								$doc->appendChild( $tmp );
							}
						}
					}
				}
				if ( $szCurrentMode === "Domain" ) {
					$objPage->SetTitle( "РС ДНС / Регистрация домена" );
					if ( !empty( $mxdLinks ) ) {
						if ( isset( $mxdLinks[ "url" ] ) ) {
							$doc->setAttribute( "reg_url", $mxdLinks[ "url" ] );
						}
						if ( isset( $mxdLinks[ "reg" ] ) ) {
							$doc->setAttribute( "reg", $mxdLinks[ "reg" ] );
						}
					}
					$arrNeed = array( "current_user", "filezone" );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->result[ "doc" ];
								$doc->appendChild( $tmp );
							}
						}
					}
				}
				if ( $szCurrentMode === "DomainReg" ) {
					$objPage->SetTitle( "РС ДНС / Регистрация домена" );
					$arrNeed = array( "current_user", "filezone" );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->result[ "doc" ];
								$doc->appendChild( $tmp );
							}
						}
					}
				}
				if ( $szCurrentMode === "Reverse" ) {
					$objPage->SetTitle( "РС ДНС / Добавление обратной зоны" );
					if ( isset( $mxdCurrentData[ "reverse_zone_suffix" ] ) ) {
						$doc->setAttribute( "reverse_zone_suffix", $mxdCurrentData[ "reverse_zone_suffix" ] );
					}
					$arrNeed = array( "current_user", "filezone" );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->result[ "doc" ];
								$doc->appendChild( $tmp );
							}
						}
					}
					if ( isset( $mxdCurrentData[ "ip_block_list" ] ) ) {
						foreach( $mxdCurrentData[ "ip_block_list" ] as $v ) {
							$tmp = $v->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$doc->appendChild( $tmp->GetResult( "doc" ) );
							}
						}
					}
				}
				if ( $szCurrentMode === "ReverseReg" ) {
					$objPage->SetTitle( "РС ДНС / Добавление обратной зоны" );
					if ( isset( $mxdCurrentData[ "show_msg" ] ) ) {
						$doc->setAttribute( "show_msg", true );
					}
					$arrNeed = array( "current_user", "filezone" );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->result[ "doc" ];
								$doc->appendChild( $tmp );
							}
						}
					}
				}
				if ( $szCurrentMode === "AdminList" ) {
					$doc->setAttribute( "user_rank", $iCurrentSysRank );
					if ( $iCurrentSysRank == SUR_ADMIN ) {
						$objPage->SetTitle( "РС ДНС / Управление операторами" );
					} else {
						$objPage->SetTitle( "РС ДНС / Управление администраторами" );
					}
					$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/admins" );
					$doc->setAttribute( "post_url", $objCMS->GetPath( "root_relative" )."/admins/" );
					$fltDate = new CDateFilter( );
					foreach( $mxdCurrentData[ "admin_list" ] as $i => $v ) {
						$tmp = $v->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$tmp = $tmp->GetResult( "doc" );
							$szIndex = $v->GetAttributeIndex( "reg_date", NULL, FLEX_FILTER_XML );
							$szDate = $tmp->getAttribute( $szIndex );
							if ( !empty( $szDate ) ) {
								$szDate = $fltDate->Apply( $szDate );
								$tmp->setAttribute( $szIndex, $szDate );
							}
							$doc->appendChild( $tmp );
						}
					}
				}
				if ( $szCurrentMode === "AdminEdit" ) {
					$doc->setAttribute( "user_rank", $iCurrentSysRank );
					$tmp = $mxdCurrentData[ "current_admin" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$tmp2 = $tmp->GetResult( "doc" );
						if ( $mxdCurrentData[ "current_admin" ]->id != 0 ) {
							if ( $iCurrentSysRank == SUR_ADMIN ) {
								$objPage->SetTitle( "РС ДНС / Настройки оператора" );
							} else {
								$objPage->SetTitle( "РС ДНС / Настройки администратора" );
							}
							$doc->setAttribute( "mode", "edit" );
							$doc->setAttribute( "base_url_client", $objCMS->GetPath( "root_relative" )."/admins/".$mxdCurrentData[ "current_admin" ]->login );
							$doc->setAttribute( "post_url", $objCMS->GetPath( "root_relative" )."/admins/".$mxdCurrentData[ "current_admin" ]->login."/" );
							if ( isset( $mxdCurrentData[ "err_admin" ] ) ) {
								$tmpErrUser = $mxdCurrentData[ "err_admin" ]->GetXML( $domDoc );
								if ( $tmpErrUser->HasResult( ) ) {
									$tmpErrUser = $tmpErrUser->GetResult( "doc" );
									$tmpErrUser->setAttribute( "main", 1 );
									$doc->appendChild( $tmpErrUser );
								}
							} else {
								$tmp2->setAttribute( "main", 1 );
							}
						} else {
							if ( $iCurrentSysRank == SUR_ADMIN ) {
								$objPage->SetTitle( "РС ДНС / Добавление нового оператора" );
							} else {
								$objPage->SetTitle( "РС ДНС / Добавление нового админа" );
							}
							$doc->setAttribute( "mode", "add" );
							$doc->setAttribute( "post_url", $objCMS->GetPath( "root_relative" )."/admins/+/" );
							$tmp2->setAttribute( "main", 1 );
						}
						$doc->appendChild( $tmp2 );
					}
				}
			}
		}
		if ( $objCurrent === 'Zone' ) {
			$szRootRelative = $objCMS->GetPath( 'root_relative' );
			$doc->setAttribute( 'root_relative', $szRootRelative );
			$doc->setAttribute( 'base_url', $szRootRelative.'/zone' );
			$doc->setAttribute( 'base_url_client', $szRootRelative.'/user' );
			$objPage->SetTitle( 'РС ДНС / Управление зонами' );
			unset( $szRootRelative );
			
			if ( $mxdCurrentData !== NULL ) {
				if ( $szCurrentMode == "List" ) {
					if ( isset( $mxdCurrentData[ "zone_list" ] ) ) {
						foreach( $mxdCurrentData[ "zone_list" ] as $i => $v ) {
							$tmp = $v->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp1 = $tmp->GetResult( "doc" );
								$doc->appendChild( $tmp1 );
							}
						}
					}
					if ( isset( $mxdCurrentData[ "filter" ] ) ) {
						$tmp = $mxdCurrentData[ "filter" ]->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
					if ( isset( $mxdCurrentData[ "pager" ] ) ) {
						$tmp = $mxdCurrentData[ "pager" ]->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				if ( $szCurrentMode == "Edit" ) {
					$iLocked = ( isset( $mxdCurrentData[ "zone_locked" ] ) ) ? 1 : 0;
					if ( $iLocked ) {
						$objPage->SetTitle( "РС ДНС / Просмотр зоны" );
					} else {
						$objPage->SetTitle( "РС ДНС / Редактирование зоны" );
					}
					$tmp = $mxdCurrentData[ "current_zone" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( "doc" );
						$doc->appendChild( $tmp );
					}
					$doc->setAttribute( "locked", $iLocked );
					$doc->setAttribute( "last", $mxdCurrentData[ "rr_last" ] );
					if ( isset( $mxdCurrentData[ "err_by_rr" ] ) ) {
						foreach( $mxdCurrentData[ "err_by_rr" ] as $i => $v ) {
							$tmp = $domDoc->createElement( "ErrorById" );
							$tmp->setAttribute( "id", $i );
							foreach( $v as $j => $w ) {
								$tmp1 = $domDoc->createElement( "ErrorByAttr" );
								$tmp1->setAttribute( "attr", $j );
								$tmp1->setAttribute( "text", iconv( "cp1251", "UTF-8", $w->GetText( ) ) );
								$tmp->appendChild( $tmp1 );
							}
							$doc->appendChild( $tmp );
						}
					}
					if ( isset( $mxdCurrentData[ "servers" ] ) ) {
						foreach( $mxdCurrentData[ "servers" ] as $v ) {
							$tmp = $v->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->GetResult( "doc" );
								$doc->appendChild( $tmp );
							}
						}
					}
				}
				if ( $szCurrentMode == 'Text' ) {
					$iLocked = ( isset( $mxdCurrentData[ "zone_locked" ] ) ) ? 1 : 0;
					if ( $iLocked ) {
						$objPage->SetTitle( "РС ДНС / Просмотр зоны" );
					} else {
						$objPage->SetTitle( "РС ДНС / Редактирование зоны" );
					}
					$tmp = $mxdCurrentData[ "current_zone" ]->GetXML( $domDoc );
					$doc->setAttribute( "locked", $iLocked );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( "doc" );
						$tmp1 = NULL;
						if ( isset( $mxdCurrentData[ "zone_err_text" ] ) ) {
							$tmp1 = new DOMText( $mxdCurrentData[ "zone_err_text" ] );
						} else {
							$tmp1 = new DOMText( $mxdCurrentData[ "zone_text" ] );
						}
						if ( $tmp1 !== NULL ) {
							$tmp->appendChild( $tmp1 );
						}
						$doc->appendChild( $tmp );
					}
				}
				if ( $szCurrentMode == 'Conf' ) {
					$objPage->SetTitle( 'РС ДНС / Шаблон SOA' );
					$arrNeed = array( 'current_soa', 'servers', 'default_ttl' );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							if ( $v == 'servers' ) {
								foreach( $mxdCurrentData[ $v ] as $w ) {
									$tmp = $w->GetXML( $domDoc );
									if ( $tmp->HasResult( ) ) {
										$tmp = $tmp->result[ 'doc' ];
										$doc->appendChild( $tmp );
									}
								}
							} else {
								$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
								if ( $tmp->HasResult( ) ) {
									$tmp = $tmp->result[ 'doc' ];
									$doc->appendChild( $tmp );
								}
							}
						}
					}
				}
				if ( $szCurrentMode == 'AddRR' ) {
					$objPage->SetTitle( 'РС ДНС / Добавление ресурсной записи' );
					$doc->setAttribute( 'rr_pos', $mxdCurrentData[ 'rr_pos' ] );
					$tmp = $mxdCurrentData[ 'current_zone' ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( 'doc' );
						$doc->appendChild( $tmp );
					}
					$doc->setAttribute( 'mode', $mxdCurrentData[ 'rr_mode' ] );
					if ( $mxdCurrentData[ 'rr_mode' ] == 'select' ) {
					} else {
						if ( isset( $mxdCurrentData[ 'current_rr' ] ) ) {
							$tmp = $mxdCurrentData[ 'current_rr' ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->GetResult( 'doc' );
								$doc->appendChild( $tmp );
							}
						}
					}
				}
				if ( $szCurrentMode === "OldList" ) {
					$iLocked = ( isset( $mxdCurrentData[ "zone_locked" ] ) ) ? 1 : 0;
					$objPage->SetTitle( "РС ДНС / Сохраненные версии" );
					$doc->setAttribute( "locked", $iLocked );
					$tmp = $mxdCurrentData[ "current_zone" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$doc->appendChild( $tmp->GetResult( "doc" ) );
					}
					foreach( $mxdCurrentData[ "old_zone_list" ] as $v ) {
						$tmp = $v->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				if ( $szCurrentMode === "OldView" ) {
					$iLocked = ( isset( $mxdCurrentData[ "zone_locked" ] ) ) ? 1 : 0;
					$objPage->SetTitle( "РС ДНС / Сохраненная версия" );
					$doc->setAttribute( "locked", $iLocked );
					$arrNeed = array( "current_zone", "current_old_zone" );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$doc->appendChild( $tmp->GetResult( "doc" ) );
							}
						}
					}
				}
				if ( $szCurrentMode === "Upload" ) {
					$iLocked = ( isset( $mxdCurrentData[ "zone_locked" ] ) ) ? 1 : 0;
					$objPage->SetTitle( "РС ДНС / Загрузка" );
					$doc->setAttribute( "locked", $iLocked );
					$arrNeed = array( "current_zone" );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$doc->appendChild( $tmp->GetResult( "doc" ) );
							}
						}
					}
				}
				if ( $szCurrentMode === "Export" ) {
					$objPage->SetTitle( "РС ДНС / Экспорт" );
					$arrNeed = array( "current_zone" );
					foreach( $arrNeed as $v ) {
						if ( isset( $mxdCurrentData[ $v ] ) ) {
							$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$doc->appendChild( $tmp->GetResult( "doc" ) );
							}
						}
					}
				}
				if ( $szCurrentMode === 'Generator' ) {
					$objPage->SetTitle( 'РС ДНС / Генератор' );
				}
				if ( $szCurrentMode === 'Del' ) {
					$objPage->SetTitle( 'РС ДНС / Удаление зон' );
					if ( isset( $mxdCurrentData[ 'orig_del' ] ) ) {
						$doc->setAttribute( 'orig_del', $mxdCurrentData[ 'orig_del' ] );
					}
					if ( isset( $mxdCurrentData[ 'zone_list' ] ) ) {
						foreach( $mxdCurrentData[ 'zone_list' ] as $i => $v ) {
							$tmp = $v[ '.' ]->GetXML( $domDoc );
							if ( $tmp->HasResult( ) ) {
								$tmp = $tmp->GetResult( 'doc' );
								if ( isset( $v[ '*' ] ) ) {
									foreach( $v[ '*' ] as $j => $w ) {
										$tmp1 = $w->GetXML( $domDoc );
										if ( $tmp1->HasResult( ) ) {
											$tmp->appendChild( $tmp1->GetResult( 'doc' ) );
										}
									}
								}
								$doc->appendChild( $tmp );
							}
						}
					}
				}
			}
		}
		if ( $objCurrent === 'Link' ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/link" );
			$objPage->SetTitle( "РС ДНС / Управление серверами" );
			
			if ( $mxdCurrentData !== NULL ) {
				if ( $szCurrentMode == "List" ) {
					foreach( $mxdCurrentData[ "server_list" ] as $i => $v ) {
						$tmp = $v->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$tmp1 = $tmp->GetResult( "doc" );
							$doc->appendChild( $tmp1 );
						}
					}
				}
				
				
				if ( $szCurrentMode == "Edit" ) {
					if ( $mxdCurrentData[ "current_server" ]->id ) {
						$objPage->SetTitle( "РС ДНС / Данные сервера" );
						$doc->setAttribute( "mode", "edit" );
					} else {
						$objPage->SetTitle( "РС ДНС / Добавление нового сервера" );
						$doc->setAttribute( "mode", "add" );
					}
					$tmp = $mxdCurrentData[ "current_server" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$tmp = $tmp->GetResult( "doc" );
						$doc->appendChild( $tmp );
					}
				}
			}
		}
		if ( $objCurrent === "Backup" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/backup" );
			$objPage->SetTitle( "РС ДНС / Управление резервными копиями системы" );
			
			if ( $szCurrentMode === "List" ) {
				$objPage->AddScript( $objCMS->GetPath( "root_relative" )."/calendar.js" );
				$objPage->AddScript( $objCMS->GetPath( "root_relative" )."/custom.js" );
				foreach( $mxdCurrentData[ "backup_list" ] as $v ) {
					$tmp = $v->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$doc->appendChild( $tmp->GetResult( "doc" ) );
					}
				}
				if ( isset( $mxdCurrentData[ "filter" ] ) ) {
					$tmp = $mxdCurrentData[ "filter" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$doc->appendChild( $tmp->GetResult( "doc" ) );
					}
				}
				if ( isset( $mxdCurrentData[ "pager" ] ) ) {
					$tmp = $mxdCurrentData[ "pager" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$doc->appendChild( $tmp->GetResult( "doc" ) );
					}
				}
			}
			if ( $szCurrentMode === "Restore" ) {
				$tmp = $mxdCurrentData[ "current_backup" ]->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( "doc" ) );
				}
			}
		}
		if ( $objCurrent === "Logger" ) {
			$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/logger" );
			$objPage->SetTitle( "РС ДНС / Управление логами системы" );
			
			if ( $szCurrentMode === "List" ) {
				$objPage->AddScript( $objCMS->GetPath( "root_relative" )."/calendar.js" );
				$objPage->AddScript( $objCMS->GetPath( "root_relative" )."/custom.js" );
				foreach( $mxdCurrentData[ "log_list" ] as $i => $v ) {
					$tmp = $v->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$doc->appendChild( $tmp->GetResult( "doc" ) );
					}
				}
				if ( isset( $mxdCurrentData[ "filter" ] ) ) {
					$tmp = $mxdCurrentData[ "filter" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$doc->appendChild( $tmp->GetResult( "doc" ) );
					}
				}
				if ( isset( $mxdCurrentData[ "pager" ] ) ) {
					$tmp = $mxdCurrentData[ "pager" ]->GetXML( $domDoc );
					if ( $tmp->HasResult( ) ) {
						$doc->appendChild( $tmp->GetResult( "doc" ) );
					}
				}
			}
			if ( $szCurrentMode === "View" ) {
				$tmp = $mxdCurrentData[ "current_log" ]->GetXML( $domDoc );
				if ( $tmp->HasResult( ) ) {
					$doc->appendChild( $tmp->GetResult( "doc" ) );
				}
			}
		}
		if ( $objCurrent === "Report" ) {
			$objPage->AddScript( $objCMS->GetPath( "root_relative" )."/calendar.js" );
			$objPage->AddScript( $objCMS->GetPath( "root_relative" )."/custom.js" );
				
			if ( $szCurrentMode === "List" ) {
				$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/reports" );
				$objPage->SetTitle( "РС ДНС / Отчеты" );
				$arrNeed = array( "current_report", "filter" );
				foreach( $arrNeed as $v ) {
					if ( isset( $mxdCurrentData[ $v ] ) ) {
						$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
			}
			if ( $szCurrentMode === "Servers" ) {
				$doc->setAttribute( "root_relative", $objCMS->GetPath( "root_relative" ) );
				$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/reports" );
				$objPage->AddScript( $objCMS->GetPath( "root_relative" )."/jquery_flot_pack.js" );
				$objPage->SetTitle( "РС ДНС / Отчеты" );
				
				$arrNeed = array( "filter", "current_queries" );
				foreach( $arrNeed as $v ) {
					if ( isset( $mxdCurrentData[ $v ] ) ) {
						$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				
				if ( isset( $mxdCurrentData[ "queries_list" ] ) ) {
					foreach( $mxdCurrentData[ "queries_list" ] as $v ) {
						$tmp = $v->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				if ( isset( $mxdCurrentData[ "url_for_ip" ] ) ) {
					$doc->setAttribute( "url_for_ip", $mxdCurrentData[ "url_for_ip" ] );
				}
			}
			if ( $szCurrentMode === "Domains" ) {
				$doc->setAttribute( "root_relative", $objCMS->GetPath( "root_relative" ) );
				$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/reports" );
				$objPage->SetTitle( "РС ДНС / Обращения к доменам" );
				
				$arrNeed = array( "filter" );
				foreach( $arrNeed as $v ) {
					if ( isset( $mxdCurrentData[ $v ] ) ) {
						$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				
				if ( isset( $mxdCurrentData[ "query_domain" ] ) ) {
					foreach( $mxdCurrentData[ "query_domain" ] as $i => $v ) {
						$tmp = $v->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				if ( isset( $mxdCurrentData[ "query_ip_domain" ] ) ) {
					foreach( $mxdCurrentData[ "query_ip_domain" ] as $i => $v ) {
						$tmp = $v->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				if ( isset( $mxdCurrentData[ "url_for_ip" ] ) ) {
					$doc->setAttribute( "url_for_ip", $mxdCurrentData[ "url_for_ip" ] );
				}
			}
			if ( $szCurrentMode === "DomainView" ) {
				$doc->setAttribute( "root_relative", $objCMS->GetPath( "root_relative" ) );
				$doc->setAttribute( "base_url", $objCMS->GetPath( "root_relative" )."/reports" );
				$objPage->SetTitle( "РС ДНС / Обращения к доменам" );
				
				if ( isset( $mxdCurrentData[ "selected_domain" ] ) ) {
					$doc->setAttribute( "selected_domain", $mxdCurrentData[ "selected_domain" ] );
				}
				if ( isset( $mxdCurrentData[ "selected_domain_id" ] ) ) {
					$doc->setAttribute( "selected_domain_id", $mxdCurrentData[ "selected_domain_id" ] );
				}
				
				$arrNeed = array( "filter" );
				foreach( $arrNeed as $v ) {
					if ( isset( $mxdCurrentData[ $v ] ) ) {
						$tmp = $mxdCurrentData[ $v ]->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
				
				if ( isset( $mxdCurrentData[ "query_ip_domain" ] ) ) {
					foreach( $mxdCurrentData[ "query_ip_domain" ] as $i => $v ) {
						$tmp = $v->GetXML( $domDoc );
						if ( $tmp->HasResult( ) ) {
							$doc->appendChild( $tmp->GetResult( "doc" ) );
						}
					}
				}
			}
		}
		if ( $objCurrent === "Help" ) {
			$objPage->SetTitle( "РС ДНС / Помощь" );
			$doc->setAttribute( "root_relative", $objCMS->GetPath( "root_relative" ) );
		}
	}
	
	$objPage->StartBody( );
	
	if ( $objCurrent == "Report" && $szCurrentMode == "Servers" ) {
		echo '<!--[if IE]><script language="javascript" type="text/javascript" src="'.$objCMS->GetPath( "root_relative" ).'excanvas_pack.js"></script><![endif]-->';
	}
	
	$objXlst->importStylesheet( $domXsl );
	$szText = $objXlst->transformToXml( $domDoc );
	$szText = iconv( "UTF-8", "cp1251//TRANSLIT", $szText );
	$szText = preg_replace( '/<textarea([^>]*)\/>/', '<textarea$1></textarea>', $szText );
	$szText = preg_replace( '/<script([^>]*)\/>/', '<script$1></script>', $szText );
	$szText = preg_replace( '/<a([^>]*)\/>/', '<a$1></a>', $szText );
	echo $szText;
	
	$objPage->EndBody( );
	echo $objPage->GetDoc( );
	echo '<!-- '._usr_time_work( ).'-->';
?>
