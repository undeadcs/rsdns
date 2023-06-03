<?php
	/**
	 *	IP адреса сети
	 *	@author UndeadCS
	 *	@package UndeadCS
	 *	@subpackage Network
	 */

	/**
	 * 	IP адрес сети
	 */
	class CNetwork extends CFlex {
		protected $id = 0; // id записи в БД
		protected $graph_vertex_id = 0; // id вершины графа в мире
		protected $ip = ''; // ip адрес сети
		protected $mask = 0; // маска сети
		
		public function __get( $szName ) {
			$arrReadOnly = array(
				'id' => true,
				'graph_vertex_id' => true,
				'ip' => true,
				'mask' => true,
			);
			if ( isset( $arrReadOnly[ $szName ] ) && $arrReadOnly[ $szName ] ) {
				return $this->$szName;
			} else {
				return parent::__get( $szName );
			}
		} // function __get
		
		/**
		 *	Получение конфига
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = 'ud_network';
			$arrConfig[ FLEX_CONFIG_PREFIX ] = 'network_';
			$arrConfig[ FLEX_CONFIG_SELECT ] = 'id';
			$arrConfig[ FLEX_CONFIG_UPDATE ] = 'id';
			$arrConfig[ FLEX_CONFIG_DELETE ] = 'id';
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = 'Network';
			// настройки атрибутов
			$arrConfig[ 'id'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ 'id'		][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ 'graph_vertex_id'	][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_DEFAULT;
			$arrConfig[ 'graph_vertex_id'	][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ 'graph_vertex_id'	][ FLEX_CONFIG_DEFAULT	] = 0;
			$arrConfig[ 'mask'		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL;
			$arrConfig[ 'ip'		][ FLEX_CONFIG_TITLE	] = 'IP адрес';
			$arrConfig[ 'mask'		][ FLEX_CONFIG_TITLE	] = 'Маска сети';
			return $arrConfig;
		} // function GetConfig
		
		/**
		 *	Инициализация атрибута объекта
		 *	@param $szName string имя атрибута
		 *	@param $arrInput mixed некое значение
		 *	@param $arrConfig array конфиг объекта
		 *	@param $iMode int режим фильтрации
		 *	@return CResult
		 */
		protected function InitAttr( $szName, &$arrInput, &$arrConfig, $iMode = FLEX_FILTER_PHP ) {
			$objRet = parent::InitAttr( $szName, $arrInput, $arrConfig, $iMode );
			$arrMust = array( 'ip', 'mask' );
			if ( in_array( $szName, $arrMust ) ) {
				$szIndex = $this->GetAttributeIndex( $szName, $arrConfig, $iMode );
				$szTitle = $this->GetAttributeTitle( $szName, $arrConfig );
				if ( !isset( $arrInput[ $szIndex ] ) ) {
					$objRet->AddError( new CError( 1, "Отсутствует поле '$szTitle'" ), $szName );
				} elseif ( $this->$szName === '' ) {
					$objRet->AddError( new CError( 1, "Поле '$szTitle' пусто" ), $szName );
				} elseif ( $szName == 'ip' ) {
					if ( !CValidator::IpAddressA( $this->$szName, false ) ) {
						$objRet->AddError( new CError( 1, "Поле '$szTitle' содержит не допустимое значение" ), $szName );
					}
				} elseif ( $szName == 'mask' ) {
					if ( $this->$szName > 32 ) {
						$objRet->AddError( new CError( 2, "Поле '$szTitle' содержит не допустимое значение" ), $szName );
					}
				}
			} 
			return $objRet;
		} // function InitAttr
		
	} // class CIpBlock
	
	
?>