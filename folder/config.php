<?php
	global $objCMS;
	
	$objInclude = new CInclude( );
	$objInclude->Create( array(
		"suffix" => ".php",
		"labels" => array(
			"custom_handler" => $objCMS->GetPath( "root_application" )."/"
		),
		"items" => array(
			array( "label" => "custom_handler", "name" => "handler" )
		)
	) );
	$objInclude->Process( );
	// пока переопределяем перехватичики системы, в дальнейшем этого не нужно будет делать
	$objCMS->Create( array(	"arrHandler" => array(
		//array( "label" => "bot", "object" => "CHModBot" ),
		array( "label" => "client", "object" => "CHModClient" ), // клиентская часть системы
		array( "label" => "user", "object" => "CHModUser" ), // подключение модуля User
		array( "label" => "zone", "object" => "CHModZone" ), // подключение модуля Zone
		array( "label" => "link", "object" => "CHModLink" ), // подключение модуля Link
		array( "label" => "backup", "object" => "CHModBackup" ), // подключене модуля Backup
		array( "label" => "logger", "object" => "CHModLogger" ), // подключение модуля Logger
		array( "label" => "reports", "object" => "CHModReport" ), // подключение модуля Report
		array( "label" => "help", "object" => "CHModHelp" ), // помощь
		array( "label" => "default_javascript", "object" => "CHCustomJs" ), // скрипты
		array( "label" => "default_css", "object" => "CHCustomCss" ), // стили приложения
		array( "label" => "default_image", "object" => "CHCustomImage" ), // картинки приложения
	) ) );

	$objCMS->ApplyPath( "media_application", $objCMS->GetPath( "root_application" )."/media" );
	$objCMS->ApplyPath( "media_images", $objCMS->GetPath( "media_application" )."/images" );
?>