<?php
	/**
	 *	Модуль файлов зон
	 *	@author UndeadCS
	 *	@package Undead Content System
	 *	@subpackage ModZone
	 */

	class COrderingRow {
		public $row = array( );
		
		public function Add( $iPos, $mxdItem ) {
			if ( isset( $this->row[ $iPos ] ) ) {
				$this->ShiftArray( $iPos, $mxdItem );
			}
			$this->row[ $iPos ] = $mxdItem;
			ksort( $this->row );
		}
		
		private function ShiftArray( $iPos, $mxdItem ) {
			$tmp = $this->row;
			$szIndex = array_search( $mxdItem, $this->row );
			if ( $szIndex === false ) {
				$szIndex = count( $this->row );
			}
			for( $i = $szIndex + 1; $i > $iPos; --$i ) {
				$tmp[ $i ] = $tmp[ $i - 1 ];
			}
			$this->row = $tmp;
		}
		
	} // class COrderingRow
	
	class CDefaultTTL extends CFlex {
		protected $id = 0;
		public $ttl = 0;
		
		/**
		 *	Возвращает настройки класса
		 *	@return array
		 */
		public function GetConfig( ) {
			$arrConfig = parent::GetConfig( );
			// общие настройки
			$arrConfig[ FLEX_CONFIG_TABLE ] = "ud_dttl";
			$arrConfig[ FLEX_CONFIG_PREFIX ] = "dttl_";
			$arrConfig[ FLEX_CONFIG_SELECT ] = "id";
			$arrConfig[ FLEX_CONFIG_UPDATE ] = "id";
			$arrConfig[ FLEX_CONFIG_DELETE ] = "id";
			// настройки режимов
			$arrConfig[ FLEX_CONFIG_XML	][ FLEX_CONFIG_XMLNODENAME ] = "DefaultTTL";
			// настройки атрибутов
			$arrConfig[ "id"		][ FLEX_CONFIG_TYPE	] = FLEX_TYPE_INT | FLEX_TYPE_UNSIGNED | FLEX_TYPE_NOTNULL | FLEX_TYPE_AUTOINCREMENT | FLEX_TYPE_PRIMARYKEY;
			$arrConfig[ "id"		][ FLEX_CONFIG_DIGITS	] = 10;
			$arrConfig[ "ttl"		][ FLEX_CONFIG_LENGHT	] = 255;
			$arrConfig[ "ttl"		][ FLEX_CONFIG_TITLE	] = "Default TTL";
			return $arrConfig;
		} // function GetConfig
		
	} // class CDefaultTTL
	
	/**
	 * 	Сравнивает файлы зон
	 * 	@param $objCurrentZone CFileZone текущий файл зон
	 * 	@param $objNewZone CFileZone новый файл зон
	 * 	@return int
	 */
	function CompareZones( $objCurrentZone, $objNewZone ) {
		/**
		 * возврат:
		 * 	0 - разные зоны
		 * 	1 - одинаковые зоны
		 * признаки:
		 * 	1. разные DefaultTTL
		 * 	2. Разное количество ресурсных записей
		 * 	3. Разные ресурсные записи (самый ебнутый вариант)
		 */
		$iDefaultTTL1 = intval( $objCurrentZone->default_ttl );
		$iDefaultTTL2 = intval( $objNewZone->default_ttl );
		$iRRsNum1 = $objCurrentZone->CountRRs( );
		$iRRsNum2 = $objCurrentZone->CountRRs( );
		if ( $iDefaultTTL1 != $iDefaultTTL2 || $iRRsNum1 != $iRRsNum2 ) {
			return 0;
		}
		/**
		 * 1. Разное количество различных типов записей
		 * 2. Одинаковые наборы типов записей, сравним порядок
		 * 3. Самый крайний случай: сравниваем каждую ресурсную запись по порядку
		 */
		// 1
		$arrTypes = array( );
		foreach( $objCurrentZone->rrs as $i => $v ) {
			if ( !isset( $arrTypes[ $v->type ] ) ) {
				$arrTypes[ $v->type ] = 0;
			}
			++$arrTypes[ $v->type ];
		}
		$arrTypes1 = array( );
		foreach( $objCurrentZone->rrs as $i => $v ) {
			if ( !isset( $arrTypes1[ $v->type ] ) ) {
				$arrTypes1[ $v->type ] = 0;
			}
			++$arrTypes1[ $v->type ];
		}
		if ( $arrTypes !== $arrTypes1 ) {
			return 0;
		}
		// 2
		$arrOrder = array( );
		foreach( $objCurrentZone->rrs as $v ) {
			$arrOrder[ ] = $v->type;
		}
		$arrOrder1 = array( );
		foreach( $objNewZone->rrs as $v ) {
			$arrOrder1[ ] = $v->type;
		}
		if ( $arrOrder !== $arrOrder1 ) {
			return 0;
		}
		// 3
		$arrRRs1 = $objCurrentZone->GetRRs( true, true );
		$arrRRs2 = $objNewZone->GetRRs( true, true );
		foreach( $arrRRs1 as $i => $v ) {
			if ( !CompareRRs( $v, $arrRRs2[ $i ] ) ) {
				return 0;
			}
		}
		return 1;
	} // function CompareZones
	
	/**
	 * 	Сравнивает 2 ресурсные записи
	 * 	@return int
	 */
	function CompareRRs( $objRR1, $objRR2 ) {
		/**
		 * 0 - не равны
		 * 1 - равны
		 */
		// сравним базовые параметры
		$arrBase = array( 'name', 'ttl', 'class', 'type', 'data' );
		foreach( $arrBase as $v ) {
			if ( $objRR1->$v !== $objRR2->$v ) {
				return 0;
			}
		}
		
		return 1;
	} // function CompareRRs
	
	/**
	 * 	Установка порядка записей при обновлении
	 * 	@param $arrRrs array of CResourceRecord набор ресурсных записей
	 */
	function SetRROrder( &$arrRrs ) {
		$tmp = new COrderingRow( );
		foreach( $arrRrs as $i => $v ) {
			if ( $v->type !== "SOA" ) {
				$tmp->Add( $v->order, $i );
			}
		}
		$tmpRow = $tmp->row;
		$tmpRow = array_unique( $tmpRow );
		$tmpRow1 = array( );
		$i = 1;
		foreach( $tmpRow as $v ) {
			$tmpRow1[ $i++ ] = $v;
		}
		foreach( $tmpRow1 as $i => $v ) {
			if ( isset( $arrRrs[ $v ] ) ) {
				$szOrderIndex = $arrRrs[ $v ]->GetAttributeIndex( "order" );
				$tmp1 = array( $szOrderIndex => $i );
				$arrRrs[ $v ]->Create( $tmp1 );
			}
		}
	} // function SetRROrder
	
?>