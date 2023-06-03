<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes"/>
	
	<xsl:template match="@*">
		<xsl:value-of select="name()"/>(<xsl:value-of select="."/>)
	</xsl:template>
	
	<xsl:template match="Error">
		<p><b><xsl:value-of select="@text"/></b></p>
	</xsl:template>
	
	<xsl:template match="Pager">
		<div class="pager">
			<div class="clear">&#160;</div>
			<xsl:for-each select="PagerPrev">
				<xsl:choose>
					<xsl:when test="@page = 0">
						<span class="page_prev">Предыдущая</span>
					</xsl:when>
					<xsl:otherwise>
						<a class="page_prev" href="{../@url}page={@page}">Предыдущая</a>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
			<xsl:for-each select="PagerOption">
				<xsl:choose>
					<xsl:when test="@cur = 1">
						<span><xsl:value-of select="@page"/></span>
					</xsl:when>
					<xsl:otherwise>
						<a href="{../@url}page={@page}"><xsl:value-of select="@page"/></a>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
			<xsl:for-each select="PagerNext">
				<xsl:choose>
					<xsl:when test="@page = 0">
						<span class="page_next">Следующая</span>
					</xsl:when>
					<xsl:otherwise>
						<a class="page_next" href="{../@url}page={@page}">Следующая</a>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
			<div class="clear">&#160;</div>
		</div>
	</xsl:template>

	<xsl:template match="CMenu">
		<div class="menu_wrap"><table><tr>
			<td class="menu_lcol"><div class="menu"><!--table><tr-->
			<div class="clear">&#160;</div>
			<xsl:for-each select="*[ position( ) &lt; 6 ]">
				<!--td class="c1"-->
				<xsl:choose>
					<xsl:when test="@flags = 2"><!-- текущий элемент выбран -->
						<span>
						<xsl:attribute name="id">menu_item<xsl:value-of select="position( )"/></xsl:attribute>
						<xsl:value-of select="@title"/></span>
					</xsl:when>
					<xsl:when test="@flags = 3"><!-- выбран дочерний элемент текущего -->
						<a href="{@url}" class="cur">
						<xsl:attribute name="id">menu_item<xsl:value-of select="position( )"/></xsl:attribute>
						<xsl:value-of select="@title"/></a>
					</xsl:when>
					<xsl:otherwise>
						<a href="{@url}">
						<xsl:attribute name="id">menu_item<xsl:value-of select="position( )"/></xsl:attribute>
						<xsl:value-of select="@title"/></a>
					</xsl:otherwise>
				</xsl:choose>
				<!--/td-->
			</xsl:for-each>
			<div class="clear">&#160;</div>
			<!--/tr></table--></div></td>
			<td class="menu_rcol">
			<xsl:for-each select="*[ position( ) &gt; 5 ]">
				<xsl:choose>
					<xsl:when test="@flags = 2"><!-- текущий элемент выбран -->
						<span>
						<xsl:attribute name="id">menu_item<xsl:value-of select="position( ) + 5"/></xsl:attribute>
						<xsl:value-of select="@title"/></span>
					</xsl:when>
					<xsl:when test="@flags = 3"><!-- выбран дочерний элемент текущего -->
						<a href="{@url}" class="cur">
						<xsl:attribute name="id">menu_item<xsl:value-of select="position( ) + 5"/></xsl:attribute>
						<xsl:value-of select="@title"/></a>
					</xsl:when>
					<xsl:otherwise>
						<a href="{@url}">
						<xsl:attribute name="id">menu_item<xsl:value-of select="position( ) + 5"/></xsl:attribute>
						<xsl:value-of select="@title"/></a>
					</xsl:otherwise>
				</xsl:choose>
				&#160;
			</xsl:for-each>
			</td>
			</tr>
		</table></div>
	</xsl:template>
	
	<xsl:template match="Doc">
		<div class="bodyWrap"><div class="wrap">
			<div class="header">
				<div class="logo"><a href="{@logo_url}"><img src="{@logo_src}" alt="Rostelecom"/></a></div>
			</div>
			<xsl:apply-templates select="CMenu"/>
			<xsl:apply-templates select="*[name()!='CMenu']"/>
		</div></div>
	</xsl:template>
	
	<xsl:template match="ClientFilter">
		<div class="client_filter"><form action="{../@base_url}/" method="get"><table><tr>
			<td class="col_dates">
				Даты:<br/>
				<input type="text" id="date1" name="d1" value="{@d1}"/>	- <input type="text" id="date2" name="d2" value="{@d2}"/>
			</td>
			<td class="col_state">
				Статус:<br/>
				<select name="st">
					<option value="0">
					<xsl:if test="@st = 0">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					Любой</option>
					<option value="1">
					<xsl:if test="@st = 1">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					Не активирован</option>
					<option value="2">
					<xsl:if test="@st = 2">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					Активен</option>
					<option value="3">
					<xsl:if test="@st = 3">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					Заблокирован</option>
				</select>
			</td>
			<td class="col_kw">
				Ключевые слова:<br/>
				<input type="text" name="kw" value="{@kw}"/>
			</td>
			<td class="col_submit"><input type="submit" value="Выбрать"/></td>
		</tr></table></form></div>
	</xsl:template>
	
	<xsl:template match="LogFilter">
		<div class="log_filter"><form action="{../@base_url}/" method="get"><table><tr>
			<td class="col_dates">
				Даты:<br/>
				<input type="text" id="date1" name="d1" value="{@d1}"/> - <input type="text" id="date2" name="d2" value="{@d2}"/>
			</td>
			<td class="col_module">
				Модуль:<br/>
				<select name="m">
					<option value="">любой</option>
					<option value="ModUser">
					<xsl:if test="@m = 'ModUser'">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					Пользователи</option>
					<option value="ModZone">
					<xsl:if test="@m = 'ModZone'">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					Зоны</option>
					<option value="ModLink">
					<xsl:if test="@m = 'ModLink'">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					Сервера</option>
					<option value="ModBackup">
					<xsl:if test="@m = 'ModBackup'">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					Backup</option>
				</select>
			</td>
			<td class="col_user">
				Пользователь:<br/>
				<input type="text" name="u" value="{@u}"/>
			</td>
			<td class="col_ip">
				IP-адрес:<br/>
				<input type="text" name="ip" value="{@ip}"/>
			</td>
			<td class="col_submit"><input type="submit" value="Выбрать"/></td>
		</tr></table></form></div>
	</xsl:template>
	
	<xsl:template match="BackupFilter">
		<div class="backup_filter"><form action="{../@base_url}/" method="get"><table><tr>
			<td class="col_dates">
				Даты:<br/>
				<input type="text" id="date1" name="d1" value="{@d1}"/>	- <input type="text" id="date2" name="d2" value="{@d2}"/>
			</td>
			<td class="col_module">
				Компоненты:<br/>
				<select name="m">
				<option value="">все</option>
				<option value="db">
				<xsl:if test="@m = 'db'">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>
				бд системы</option>
				<option value="source">
				<xsl:if test="@m = 'source'">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>
				исходные коды</option>
				<option value="zone">
				<xsl:if test="@m = 'zone'">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>
				файлы зон</option>
			</select>
			</td>
			<td class="col_submit"><input type="submit" value="Выбрать"/></td>
		</tr></table></form></div>
	</xsl:template>
	
	<xsl:template match="ReportFilter">
		<div class="report_filter"><form action="{../@base_url}/" method="get"><table><tr>
			<td class="col_dates">
				Дата:<br/>
				<input type="text" id="date1" name="d1" value="{@d1}"/>
			</td>
			<td class="col_submit"><input type="submit" value="Выбрать"/></td>
		</tr></table></form></div>
	</xsl:template>
	
	<xsl:template match="QueriesFilter">
		<div class="report_filter"><form action="{../@base_url}/servers/" method="get"><table><tr>
			<td class="col_dates">
				Дата:<br/>
				<input type="text" id="date1" name="d1" value="{@d1}"/>
			</td>
			<td class="col_submit"><input type="submit" value="Выбрать"/></td>
		</tr></table></form></div>
	</xsl:template>
	
	<xsl:template match="QueriesFilter2">
		<div class="report_filter2"><form action="{../@base_url}/domains/" method="get">
		<xsl:if test="../@selected_domain_id">
			<xsl:attribute name="action"><xsl:value-of select="../@base_url"/>/domains/<xsl:value-of select="../@selected_domain_id"/>/</xsl:attribute>
		</xsl:if>
		<table><tr>
			<td class="col_dates">
				Дата:<br/>
				<input type="text" id="date1" name="d1" value="{@d1}"/>
			</td>
			<td class="col_hours">
				Время:<br/>
				<select name="h1">
				<xsl:for-each select="sel1/*">
					<option value="{@value}">
					<xsl:if test="@selected">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="@title"/></option>
				</xsl:for-each>
				</select> - <select name="h2">
				<xsl:for-each select="sel2/*">
					<option value="{@value}">
					<xsl:if test="@selected">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="@title"/></option>
				</xsl:for-each>
				</select>
			</td>
			<td class="col_submit"><input type="submit" value="Выбрать"/></td>
		</tr></table></form></div>
	</xsl:template>
	
	<!-- Модуль учетных записей User ModUser -->
	<xsl:template match="UserList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
		<h1>Управление клиентами</h1>
		<div class="vmenu">
			<a href="{@base_url}/fields/">Добавление полей</a>
		</div>
			</div></td>
			<td class="ccol"><div class="content">
		
		<div class="cont_top"><table><tr>
			<td class="filter"><xsl:apply-templates select="ClientFilter"/></td>
			<td><div class="add_client"><a href="{@base_url}/+/">Добавить клиента</a></div></td>
		</tr></table></div>
		
		<xsl:apply-templates select="Error"/>
		<form action="{@base_url}/" method="post">
		<div class="list"><table>
			<tr>
				<th class="col_name"><div>Название</div></th>
				<th class="col_email"><div>E-mail</div></th>
				<th class="col_state"><div>Статус</div></th>
				<th class="col_reg"><div>Дата рег.</div></th>
				<th class="col_del"><div>Удалить</div></th>
			</tr>
			<xsl:for-each select="Client">
				<tr>
				<td class="col_name"><div><a href="{../@base_url}/{@client_login}/"><xsl:value-of select="@client_full_name"/></a></div></td>
				<td class="col_email"><div><xsl:value-of select="@client_email"/></div></td>
				<td class="col_state"><div><select name="users[{@client_id}][client_state]">
					<option value="2">
					<xsl:if test="@client_state = 2">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
						Активирован
					</option>
					<option value="1">
					<xsl:if test="@client_state = 1">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
						Не активирован
					</option>
					<option value="3">
					<xsl:if test="@client_state = 3">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
						Заблокирован
					</option>
				</select></div></td>
				<td class="col_reg"><div><xsl:value-of select="@client_reg_date"/></div></td>
				<td class="col_del"><div><input name="del[{@client_id}]" type="checkbox"/></div></td>
				</tr>
			</xsl:for-each>
		</table></div>
		<div class="client_list_save"><table><tr>
			<td class="col1"><div class="clinfo">Будут сохранены новые значения, внесенные в поля "Статус". Учетные записи, помеченные флажком "Удалить", будут удалены.</div></td>
			<td class="col2"><div><input type="submit" class="sendquery" value="Сохранить"/></div></td>
		</tr></table></div>
		</form>
		<xsl:apply-templates select="Pager"/>
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="UserEdit">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
		<h1>Управление клиентами</h1>
		
		<xsl:if test="@mode = 'edit'">
<div class="vmenu">
	<a href="{@base_url_client}/add_domain/">Зарегистрировать домен</a>
	<a href="{@base_url_client}/add_zone/">Добавить зону</a>
	<a href="{@base_url_client}/add_reverse/">Добавить обратную зону</a>
</div>
<xsl:choose>
	<xsl:when test="count( Client ) = 2">
<p>Файлы зон:</p>
<xsl:for-each select="Client[not(@main)][1]/Zone">
	<p>&#160;&#160;&#160;&#160;<a href="{../../@base_url_zone}/{@zone_id}/"><xsl:value-of select="@zone_name"/></a></p>
</xsl:for-each>
	</xsl:when>
	<xsl:otherwise>
<p>Файлы зон:</p>
<xsl:for-each select="Client[1]/Zone">
	<p>&#160;&#160;&#160;&#160;<a href="{../../@base_url_zone}/{@zone_id}/"><xsl:value-of select="@zone_name"/></a></p>
</xsl:for-each>
	</xsl:otherwise>
</xsl:choose>
			</xsl:if>
		
			</div></td>
			<td class="ccol"><div class="content">
		<h2><xsl:choose>
			<xsl:when test="@mode = 'add'">Добавление нового пользователя</xsl:when>
			<xsl:otherwise>Настройки пользователя <xsl:choose>
				<xsl:when test="count( Client ) = 2">
				<xsl:value-of select="Client[not(@main)][1]/@client_login"/>
				</xsl:when>
				<xsl:otherwise>
				<xsl:value-of select="Client[1]/@client_login"/>
				</xsl:otherwise>
			</xsl:choose>
			</xsl:otherwise>
		</xsl:choose></h2>
			
		<xsl:apply-templates select="Error"/>
		<xsl:for-each select="Client[@main]">
		<div class="client_form"><form action="{../@post_url}" method="post">
			
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td>
				<td class="c"><span>Настройки доступа</span></td>
			<td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont"><table>
				<tr>
					<td class="lbl"><div>Состояние:</div></td>
					<td class="sel"><div><select name="client_state">
						<option value="2">
						<xsl:if test="@client_state = 2">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						Активирован
						</option>
						<option value="1">
						<xsl:if test="@client_state = 1">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						Не активирован
						</option>
						<option value="3">
						<xsl:if test="@client_state = 3">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						Заблокирован
						</option>
					</select></div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Логин:</div></td>
					<td class="inp"><div><input type="text" class="text" name="client_login" value="{@client_login}" autocomplete="off"/></div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Пароль:</div></td>
					<td class="inp"><div><input type="password" class="text" name="client_password" value="" autocomplete="off"/></div></td>
				</tr>
			</table></div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div>
		
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td>
				<td class="c"><span>Имя контактного лица</span></td>
			<td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont"><table>
				<tr>
					<td class="lbl"><div>Имя:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_first_name" value="{@client_first_name}"/>
						<span class="info">Заполняется по-английски, например, Vitaly</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Фамилия:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_last_name" value="{@client_last_name}"/>
						<span class="info">Заполняется по-английски, например, Sergeev</span>
					</div></td>
				</tr>
			</table></div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div>
		
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td>
				<td class="c"><span>Название</span></td>
			<td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont"><table>
				<tr>
					<td class="lbl"><div>Организация (по-русски):</div></td>
					<td class="txt"><div>
						<textarea name="client_full_name"><xsl:value-of select="@client_full_name"/></textarea>
						<span class="info">Полное наименование организации-администратора домена на русском языке в соответствии с учредительными документами. Для нерезидентов РФ допускается написание на национальном языке (либо на английском языке). Например, Закрытое Акционерное Общество "Новое время"</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Организация (латиницей):</div></td>
					<td class="txt"><div>
						<textarea name="client_full_name_en"><xsl:value-of select="@client_full_name_en"/></textarea>
						<span class="info">Например, New Time Co Ltd.</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>ИНН:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_inn" value="{@client_inn}"/>
						<span class="info">Например, 7701107259</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>КПП:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_kpp" value="{@client_kpp}"/>
						<span class="info">Например, 632946014</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Юридический адрес:</div></td>
					<td class="txt"><div>
						<textarea name="client_addr"><xsl:value-of select="@client_addr"/></textarea>
						<span class="info">Например, 123456, Москва, ул.Собачкина, д. 13а</span>
					</div></td>
				</tr>
			</table></div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div>
		
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td>
				<td class="c"><span>Контактная информация</span></td>
			<td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont"><table>
				<tr>
					<td class="lbl"><div>Страна:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_country" value="{@client_country}"/>
						<span class="info">Укажите двубуквенный код страны латинским буквами в соответствии со стандартом <a href="http://ru.wikipedia.org/wiki/ISO_3166-1">ISO 3166-1 alpha 1</a></span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>E-mail:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_email" value="{@client_email}"/>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Телефон:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_phone" value="{@client_phone}"/>
						<span class="info">Например, +7 495 8102233</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Факс:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_fax" value="{@client_fax}"/>
						<span class="info">Например, +7 3432 811221</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Индекс:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_postcode" value="{@client_postcode}"/>
						<span class="info">Например, 101000</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Область:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_region" value="{@client_region}"/>
						<span class="info">Например, Московская обл.</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Город, населенный пункт:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_city" value="{@client_city}"/>
						<span class="info">Например, Москва</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Улица, дом, офис:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_street" value="{@client_street}"/>
						<span class="info">Например, ул. Ленина, д. 13а, оф. 222</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Получатель:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="client_person" value="{@client_person}"/>
						<span class="info">Например, Сергеев Виталий Павлович</span>
					</div></td>
				</tr>
			</table></div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div>
		
		<div class="client_end">
		<table>
			<tr>
				<td class="lbl"><div>Дополнительная информация:</div></td>
				<td class="txt"><div><textarea name="client_add_info"><xsl:value-of select="@client_add_info"/></textarea></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Блоки ip-адресов, выделенные клиенту:</div></td>
				<td class="txt"><div>
					<textarea name="client_ip_block"><xsl:value-of select="@client_ip_block"/></textarea>
					<span class="info">например 80.92.162.0/24, каждый пул адресов разделяется переводом строки</span>
				</div></td>
			</tr>
		</table>
		
		<xsl:if test="count( ExtField )">
		<table>
		<xsl:for-each select="ExtField">
			<tr>
				<td class="lbl"><div><xsl:value-of select="@title"/>:</div></td>
				<td>
			<xsl:choose>
				<xsl:when test="@type = 0">
				<!-- text -->
				<xsl:attribute name="class">inp</xsl:attribute>
				<div><input type="text" class="text" name="fld[{@name}]" value="{@value}"/></div>
				</xsl:when>
				<xsl:when test="@type = 1">
				<!-- textarea -->
				<xsl:attribute name="class">txt</xsl:attribute>
				<div><textarea name="fld[{@name}]"><xsl:value-of select="@value"/></textarea></div>
				</xsl:when>
				<xsl:otherwise>
				<!-- select -->
				<xsl:attribute name="class">sel</xsl:attribute>
				<div><select name="fld[{@name}]">
				<xsl:for-each select="*">
					<option value="{@value}">
					<xsl:if test="@value = ../@value">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="@value"/></option>
				</xsl:for-each>
				</select></div>
				</xsl:otherwise>
			</xsl:choose>
				</td>
			</tr>
		</xsl:for-each>
		</table>
		</xsl:if>
		
		<table>
			<tr><td class="lbl"><div>&#160;</div></td>
			<td class="sbm"><div>
		
			<input value="Добавить" type="submit">
				<xsl:if test="../@mode = 'edit'">
					<xsl:attribute name="value">Сохранить</xsl:attribute>
				</xsl:if>
			</input>
			
			</div></td></tr>
		</table>
		</div>
		</form></div>
		</xsl:for-each>
		</div></td>
	</tr></table></div>
		
	</xsl:template>
	
	<xsl:template match="UserFields">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление клиентами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Добавление полей</h2>
		<xsl:apply-templates select="Error"/>
		<xsl:for-each select="ClField[1]">
		<form action="{../@base_url}/fields/" method="post"><div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
			
			<table>
				<tr>
					<td class="lbl"><div>Имя:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="fld[fld_name]" value="{@fld_name}"/>
						<span class="info">Имя поля (используется в качестве переменной).</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Тип:</div></td>
					<td class="sel"><div>
						<select name="fld[fld_type]">
						<option value="0">
						<xsl:if test="@fld_type = 0">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						text</option>
						<option value="1">
						<xsl:if test="@fld_type = 1">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						textarea</option>
						<option value="2">
						<xsl:if test="@fld_type = 2">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						select</option>
						</select>
						<span class="info">Тип поля, text - текстовое поле в одну строчку, textarea - поле, в которое можно вводить несколько строк текста, select - выпадающий список</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Опции:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="fld[fld_options]" value="{@fld_options}"/>
						<span class="info">Для поля select значения через запятую</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Заголовок:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="fld[fld_title]" value="{@fld_title}"/>
						<span class="info">Название поля, которое выводится на странице, например, ИНН, ОГРН, название организации и т.п.</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl">&#160;</td>
					<td class="sbm2"><div><input type="submit" class="sendquery" name="act[add]" value="Добавить"/></div></td>
				</tr>
			</table>
		
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div></form>
		</xsl:for-each>
		
		<h2>Добавленные поля</h2>
		<div class="cl_fld_list">
		<xsl:for-each select="ClField[ position( ) &gt; 1 ]">
		<form action="{../@base_url}/fields/" method="post"><div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td><td class="c">
				<span>
					Имя: <xsl:value-of select="@fld_name"/>,
					Тип: <xsl:choose>
					<xsl:when test="@fld_type = 0">text</xsl:when>
					<xsl:when test="@fld_type = 1">textarea</xsl:when>
					<xsl:otherwise>select</xsl:otherwise>
					</xsl:choose>
				</span>
			</td><td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
		
		<table>
		<xsl:if test="string-length( @fld_options ) &gt; 0">
			<tr>
				<td class="lbl"><div>Опции:</div></td>
				<td class="inp"><div><xsl:value-of select="@fld_options"/></div></td>
			</tr>
		</xsl:if>
			<tr>
				<td class="lbl"><div>Заголовок:</div></td>
				<td class="inp"><div><input type="text" class="text" name="fld[fld_title]" value="{@fld_title}"/></div></td>
			</tr>
			<tr>
				<td class="lbl">&#160;</td>
				<td class="sbm2"><div>
			<input type="submit" class="sendqeury" name="act[upd][{@fld_id}]" value="Сохранить"/>
			&#160;
			<input type="submit" class="sendqeury" name="act[del][{@fld_id}]" value="Удалить"/>
				</div></td>
			</tr>
		</table>
		
		<input type="hidden" name="fld[fld_name]" value="{@fld_name}"/>
		
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div></form>
		</xsl:for-each>
		</div>
		
		</div></td></tr></table></div>
	</xsl:template>
	
	<xsl:template match="UserDomain">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление клиентами</h1>
			</div></td>
			<td class="ccol"><div class="content">
		<h2>Регистрация домена</h2>
		<p>Клиент: <a href="{@base_url}/{Client[1]/@client_login}/"><xsl:value-of select="Client[1]/@client_full_name"/></a></p>
		<!--xsl:apply-templates select="Error"/-->
		<xsl:if test="count(Error)">
			<xsl:choose>
				<xsl:when test="Error[1]/@code = '101'">
			<p>При регистрации домена возникли проблемы. Пожалуйста, проверьте корректность заполнения всех полей профиля пользователя <a href="{@base_url}/{Client[1]/@client_login}/"><xsl:value-of select="Client[1]/@client_full_name"/></a> и попробуйте зарегистрировать домен еще раз. </p>
				</xsl:when>
				<xsl:otherwise>
			<xsl:apply-templates select="Error"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
		<xsl:if test="@reg_url"><p><a href="{@reg_url}">Зарегистрировать</a></p></xsl:if>
		<xsl:choose>
			<xsl:when test="@reg">
		<p>Домен <b><xsl:value-of select="Zone[1]/@zone_name"/></b>&#160;свободен.</p>
		
		<form action="{@base_url}/{Client[1]/@client_login}/add_domain/" method="post">
			<p><input type="submit" class="sendquery" name="ok" value="Зарегистрировать"/></p>
			<input type="hidden" name="zone_name" value="{Zone[1]/@zone_name}"/>
		</form>
			</xsl:when>
			<xsl:otherwise>
		<form action="{@base_url}/{Client[1]/@client_login}/add_domain/" method="get"><div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
			<table>
				<tr>
					<td class="lbl"><div>Доменное имя:</div></td>
					<td class="inp"><div><input type="text" class="text" name="zone_name" value="{Zone[1]/@zone_name}"/></div></td>
				</tr>
				<tr>
					<td class="lbl">&#160;</td>
					<td class="sbm2"><div><input type="submit" class="sendquery" value="Проверить"/></div></td>
				</tr>
			</table>
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div></form>
			</xsl:otherwise>
		</xsl:choose>
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="UserDomainReg">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление клиентами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Домен зарегистрирован</h2>
		<xsl:for-each select="Zone[1]">
			<p>Домен успешно зарегистрирован. В <a href="{../@root_relative}/zone/">список зон</a> и в профиль пользователя <a href="{../@base_url}/{../Client[1]/@client_login}/"><xsl:value-of select="../Client[1]/@client_full_name"/></a> внесена новая зона, связанная с этим доменом.</p>
			<p>Перейти к <a href="{../@root_relative}/zone/{@zone_id}/">редактированию зоны</a>.</p> 
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="UserZone">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление клиентами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Добавление зоны</h2>
		<p>Клиент: <a href="{@base_url}/{Client[1]/@client_login}/"><xsl:value-of select="Client[1]/@client_full_name"/></a></p>
		<xsl:apply-templates select="Error"/>
		<form action="{@base_url}/{Client[1]/@client_login}/add_zone/" method="post"><div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
			<table>
				<tr>
					<td class="lbl"><div>Доменное имя:</div></td>
					<td class="inp"><div><input type="text" class="text" name="zone_name" value="{Zone[1]/@zone_name}"/></div></td>
				</tr>
				<tr>
					<td class="lbl">&#160;</td>
					<td class="sbm2"><div><input type="submit" class="sendquery" value="Проверить"/></div></td>
				</tr>
			</table>
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div></form>
			
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="UserZoneReg">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление клиентами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Зона добавлена</h2>
		<xsl:for-each select="Zone[1]">
			<p>Зона успешно добавлена. В <a href="{../@root_relative}/zone/">список зон</a> и в профиль пользователя <a href="{../@base_url}/{../Client[1]/@client_login}/"><xsl:value-of select="../Client[1]/@client_full_name"/></a> внесена новая зона.</p>
			<p>Перейти к <a href="{../@root_relative}/zone/{@zone_id}/">редактированию зоны</a>.</p> 
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="UserReverse">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление клиентами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Добавление обратной зоны</h2>
		<p>Клиент: <a href="{@base_url}/{Client[1]/@client_login}/"><xsl:value-of select="Client[1]/@client_full_name"/></a></p>
		<xsl:apply-templates select="Error"/>
		<form action="{@base_url}/{Client[1]/@client_login}/add_reverse/" method="post"><div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
			<table>
				<xsl:if test="count( IpBlock )">
				<tr>
					<td class="lbl"><div>Выберите имя обратной зоны:</div></td>
					<td class="sel"><div><select name="zone_block">
					<xsl:for-each select="IpBlock">
						<option value="{@name}"><xsl:value-of select="@name"/></option>
					</xsl:for-each>
					</select></div></td>
				</tr>
				<tr>
					<td class="lbl">&#160;</td>
					<td class="inp"><div>
						<span class="info">или введите нужное имя (<a href="{@root_relative}/help/#reversezone">как это правильно сделать?</a>):</span>
					</div></td>
				</tr>
				</xsl:if>
				<tr>
					<td class="lbl"><div>&#160;</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="zone_name" value="{Zone[1]/@zone_name}"/>
						<span class="info">к имени зоны будет добавлен суффикс <xsl:value-of select="@reverse_zone_suffix"/></span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl">&#160;</td>
					<td class="sbm2"><div><input value="Добавить" type="submit"/></div></td>
				</tr>
			</table>
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div></form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="UserReverseReg">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление клиентами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Обратная зона добавлена</h2>
		<xsl:for-each select="Zone[1]">
			<p>Обратная зона успешно добавлена. В <a href="{../@root_relative}/zone/">список зон</a> и в профиль пользователя <a href="{../@base_url}/{../Client[1]/@client_login}/"><xsl:value-of select="../Client[1]/@client_full_name"/></a> внесена новая зона.</p>
			<p>Перейти к <a href="{../@root_relative}/zone/{@zone_id}/">редактированию зоны</a>.</p> 
		</xsl:for-each>
		<!--xsl:if test="@show_msg">
		<p>Вам необходимо создать обратную зону для подсети класса С в которой прописать CNAME диапазона адресов, входящих в вашу подсеть, ссылающихся на создаваемую обратную зону.</p>
		</xsl:if-->
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="UserAdminList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление
				<xsl:choose>
					<xsl:when test="@user_rank &gt; 1">операторами</xsl:when>
					<xsl:otherwise>администраторами</xsl:otherwise>
				</xsl:choose>
				</h1>
			</div></td>
			<td class="ccol"><div class="content">

		<div class="add_admin"><a href="{@base_url}/+/">Добавить
			<xsl:choose>
				<xsl:when test="@user_rank &gt; 1">
					оператора
				</xsl:when>
				<xsl:otherwise>
					администратора
				</xsl:otherwise>
			</xsl:choose>
		</a></div>
		<form action="{@post_url}" method="post">
		<div class="list"><table>
			<tr>
				<th><div>Логин</div></th>
				<th><div>Ранг</div></th>
				<th class="col_reg"><div>Дата рег.</div></th>
				<th class="col_del"><div>Удалить</div></th>
			</tr>
			<xsl:for-each select="Admin">
			<tr>
				<td><div><a href="{../@base_url}/{@admin_login}/"><xsl:value-of select="@admin_login"/></a>&#160;</div></td>
				<td><div>
				<xsl:choose>
					<xsl:when test="@admin_rank = 2">
						оператор
					</xsl:when>
					<xsl:otherwise>
						администратор
					</xsl:otherwise>
				</xsl:choose>
				</div></td>
				<td class="col_reg"><div><xsl:value-of select="@admin_reg_date"/>&#160;</div></td>
				<td class="col_del"><div><input type="checkbox" name="del[{@admin_id}]" value="{@admin_id}"/></div></td>
			</tr>
			</xsl:for-each>
		</table></div>
		<div class="client_list_save"><table><tr>
			<td class="col1"><div class="clinfo">Учетные записи, помеченные флажком "Удалить", будут удалены.</div></td>
			<td class="col2"><div><input type="submit" class="sendquery" value="Удалить"/></div></td>
		</tr></table></div>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="UserAdminEdit">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление администраторами</h1>
			</div></td>
			<td class="ccol"><div class="content">
		
		<h2>
		<xsl:choose>
			<xsl:when test="@mode = 'add'">Добавление
				<xsl:choose>
					<xsl:when test="@user_rank &gt; 1">
						оператора
					</xsl:when>
					<xsl:otherwise>
						администратора
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>Редактирование 
			<xsl:choose>
				<xsl:when test="Admin[1]/@admin_rank = 1">
					администратора
				</xsl:when>
				<xsl:otherwise>
					оператора
				</xsl:otherwise>
			</xsl:choose>
			<xsl:choose>
				<xsl:when test="count( Admin ) = 2">
					<xsl:value-of select="Admin[not(@main)][1]/@admin_login"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="Admin[1]/@admin_login"/>
				</xsl:otherwise>
			</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
		</h2>
		<xsl:apply-templates select="Error"/>
		<xsl:for-each select="Admin[@main][1]">
<form action="{../@post_url}" method="post"><div class="x9"><table>
	<tr class="top"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
	<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
	<table>
		<tr>
			<td class="lbl"><div>Логин:</div></td>
			<td class="inp"><div><input type="text" class="text" name="admin_login" value="{@admin_login}" autocomplete="off"/></div></td>
		</tr>
		<tr>
			<td class="lbl"><div>Пароль:</div></td>
			<td class="inp"><div><input type="password" class="text" name="admin_password" value="" autocomplete="off"/></div></td>
		</tr>
		<xsl:if test="../@user_rank = 1">
		<tr>
			<td class="lbl"><div>Ранг:</div></td>
			<td class="rad"><div>
			
			<label for="op"><input type="radio" name="admin_rank" id="op" value="2">
			<xsl:if test="@admin_rank = 2">
				<xsl:attribute name="checked">checked</xsl:attribute>
			</xsl:if>
			</input> оператор</label>
			<label for="ad"><input type="radio" name="admin_rank" id="ad" value="1">
			<xsl:if test="@admin_rank = 1">
				<xsl:attribute name="checked">checked</xsl:attribute>
			</xsl:if>
			</input> администратор</label>
			
			</div></td>
		</tr>
		</xsl:if>
		<tr>
			<td class="lbl"><div>Дополнительная информация:</div></td>
			<td class="txt"><div><textarea name="admin_add_info"><xsl:value-of select="@admin_add_info"/></textarea></div></td>
		</tr>
		<tr>
			<td class="lbl"><div>&#160;</div></td>
			<td class="sbm2"><div><input type="submit" class="sendquery" value="Сохранить">
			<xsl:if test="../@mode = 'add'">
				<xsl:attribute name="value">Добавить</xsl:attribute>
			</xsl:if>
			</input></div></td>
		</tr>
	</table>

	</div></td><td class="r">&#160;</td></tr>
	<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
</table></div></form>
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<!-- Модуль файлов зон Zone ModZone -->
	<xsl:template match="ZoneList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
				<div class="vmenu">
					<a href="{@base_url}/conf/">Настройки</a>
					<a href="{@base_url}/generator/">Сгенерировать файлы зон</a>
				</div>
			</div></td>
			<td class="ccol"><div class="content">
		
		<div class="cont_top"><xsl:for-each select="ZoneFilter[1]">
		<form action="{../@base_url}/" method="get">
			<input type="text" class="zone_find" name="s" value="{@s}"/>
			<input type="submit" value="Найти"/>
		</form>
		</xsl:for-each>
		</div>
		<xsl:apply-templates select="Error"/>
		<form action="{@base_url}/" method="post">
		<div class="list"><table>
			<tr>
				<th class="col_name"><div>Имя</div></th>
				<th class="col_comment"><div>Комментарий</div></th>
				<!--th><div>SOA</div></th-->
				<th class="col_client"><div>Клиент</div></th>
				<th class="col_del"><div>Удалить</div></th>
				<th>&#160;</th>
			</tr>
			<xsl:for-each select="FileZoneListItem">
				<tr>
				<td class="col_name"><div><xsl:value-of select="@name"/>&#160;</div></td>
				<td class="col_comment"><div><xsl:value-of select="@comment"/>&#160;</div></td>
				<!--td><div><xsl:value-of select="@soa"/>&#160;</div></td-->
				<td class="col_client"><div><a href="{../@base_url_client}/{@client_login}/"><xsl:value-of select="@client_full_name"/></a></div></td>
				<td class="col_del"><div><input type="checkbox" name="del[{@id}]" value="{@id}"/></div></td>
				<td class="col_edit"><div>
					<a class="zone_edit" href="{../@base_url}/{@id}/"><span class="l1"><span class="l2">редактировать</span></span></a>
				</div></td>
				</tr>
			</xsl:for-each>
		</table></div>
		<div class="client_list_save"><table><tr>
			<td class="col1"><div class="clinfo">Зоны, помеченные флажком "Удалить", будут удалены.</div></td>
			<td class="col2"><div><input type="submit" class="sendquery" value="Удалить"/></div></td>
		</tr></table></div>
		</form>
		<xsl:apply-templates select="Pager"/>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneEdit">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
				<xsl:for-each select="Zone[1]">
				<div class="vmenu">
					<a href="{../@base_url}/{@zone_id}/old/">Сохраненные версии файла зоны</a>
					<a href="{../@base_url}/{@zone_id}/upload/">Загрузка файла зоны с вашего компьютера</a>
					<a href="{../@base_url}/{@zone_id}/export/">Экспорт зоны</a>
				</div>
				</xsl:for-each>
			</div></td>
			<td class="ccol"><div class="content">
			
		<xsl:for-each select="Zone[1]">
		<h2>
		<xsl:choose>
			<xsl:when test="../@locked = 0">
				Редактирование
			</xsl:when>
			<xsl:otherwise>
				Просмотр
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="@zone_type = 1">обратной</xsl:if> зоны <xsl:value-of select="@zone_name"/>
			<!--span><img src="{../@root_relative}/skin/bg_h1.gif"/>
			<a href="{../@base_url}/{@zone_name}/text/"><xsl:choose>
			<xsl:when test="../@locked = 0">Редактировать</xsl:when>
			<xsl:otherwise>Посмотреть</xsl:otherwise>
			</xsl:choose>
			в текстовом виде</a></span-->
		</h2>
		<xsl:if test="../@locked = 1">
			<div class="zone_locked"><div class="l1"><div class="l2">Зона заблокирована от редактирования</div></div></div>
		</xsl:if>
		<a class="zone_text_link" href="{../@base_url}/{@zone_id}/text/"><xsl:choose>
		<xsl:when test="../@locked = 0">Редактировать</xsl:when>
		<xsl:otherwise>Посмотреть</xsl:otherwise>
		</xsl:choose>
		в текстовом виде</a>
		<xsl:choose>
			<xsl:when test="../@locked = 0">
		<form action="{../@base_url}/{@zone_id}/" method="post">
		
		<div class="zone_controls"><table><tr>
			<td class="col_back"><a href="{../@base_url}/{@zone_id}/exit/">Вернуться к списку без сохранения</a></td>
			<td class="col_save"><div><input type="submit" name="save" value="Сохранить и загрузить файл на сервер"/></div></td>
			<td class="col_tmp_save"><input type="submit" value="Сохранить временный файл"/></td>
		</tr></table></div>
		
		<table>
			<tr>
				<td class="lbl"><div>Default TTL:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_default_ttl" value="{@zone_default_ttl}"/></div></td>
			</tr>
		</table>
		
		<h3>SOA</h3>
		<div class="zone_soa"><table>
		<xsl:for-each select="ResRec[@rr_type = 'SOA'][1]">
			<xsl:variable name="rr_id" select="@rr_id"/>
			<xsl:if test="count(../../ErrorById[@id = $rr_id])">
				<xsl:for-each select="../../ErrorById[@id = $rr_id]">
					<tr>
						<td class="lbl">&#160;</td>
						<td>
					<xsl:for-each select="ErrorByAttr">
						<xsl:value-of select="@text"/>
					</xsl:for-each>
						</td>
					</tr>
				</xsl:for-each>
			</xsl:if>
			<tr>
				<td class="lbl"><div>Name:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_rrs[{@rr_id}][rr_name]" value="{@rr_name}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>TTL:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_rrs[{@rr_id}][rr_ttl]" value="{@rr_ttl}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Origin:</div></td>
				<td class="sel"><div>
				<select name="zone_rrs[{@rr_id}][rr_origin]">
				<xsl:for-each select="../../Server">
					<option value="{@server_name}">
					<xsl:if test="../Zone[1]/ResRec[@rr_type = 'SOA']/@rr_origin = concat( @server_name, '.' )">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="@server_name"/></option>
				</xsl:for-each>
				</select>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Person:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_rrs[{@rr_id}][rr_person]" value="{@rr_person}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Serial:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_serial"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Refresh:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_rrs[{@rr_id}][rr_refresh]" value="{@rr_refresh}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Retry:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_rrs[{@rr_id}][rr_retry]" value="{@rr_retry}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Expire:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_rrs[{@rr_id}][rr_expire]" value="{@rr_expire}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Minimum TTL:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_rrs[{@rr_id}][rr_minimum_ttl]" value="{@rr_minimum_ttl}"/></div></td>
			</tr>
		</xsl:for-each>
		</table></div>
		<h3>Ресурсные записи</h3>
		
		<div class="rr_list"><table>
			<tr>
				<th class="col_ord"><div>№</div></th>
				<th class="col_name"><div>Name</div></th>
				<th class="col_ttl"><div>TTL</div></th>
				<th class="col_type"><div>Type</div></th>
				<th class="col_data"><div>Data</div></th>
				<th class="col_del"><div>Удалить</div></th>
			</tr>
			<xsl:for-each select="ResRec[@rr_type != 'SOA']">
			<xsl:variable name="rr_id" select="@rr_id"/>
			<xsl:if test="count(../../ErrorById[@id = $rr_id])">
				<tr>
					<td colspan="2">&#160;</td>
					<td colspan="4">
						<xsl:for-each select="../../ErrorById[@id = $rr_id]">
							<div class="info">
							<xsl:for-each select="ErrorByAttr">
								<xsl:value-of select="@text"/>
							</xsl:for-each>
							</div>
						</xsl:for-each>
					</td>
				</tr>
			</xsl:if>
			<xsl:choose>
				<xsl:when test="@rr_type = 'SRV'">
			<tr>
			<xsl:choose>
				<xsl:when test="position( ) = 1">
					<xsl:attribute name="class">linef</xsl:attribute>
				</xsl:when>
				<xsl:when test="position( ) = last( )">
					<xsl:attribute name="class">linel</xsl:attribute>
				</xsl:when>
			</xsl:choose>
				<td class="col_ord"><div><input type="text" name="zone_rrs[{@rr_id}][rr_order]" value="{@rr_order}"/></div></td>
				<td class="col_name"><div>
					Service:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_service]" value="{@rr_service}"/><br/>
					Proto:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_proto]" value="{@rr_proto}"/><br/>
					Name:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_name]" value="{@rr_name}"/>
				</div></td>
				<td class="col_ttl">
					<div><input type="text" name="zone_rrs[{@rr_id}][rr_ttl]" value="{@rr_ttl}"/></div>
				</td>
				<td class="col_type"><div><xsl:value-of select="@rr_type"/></div></td>
				<td class="col_data"><div>
					Priority:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_priority]" value="{@rr_priority}"/><br/>
					Weight:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_weight]" value="{@rr_weight}"/><br/>
					Port:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_port]" value="{@rr_port}"/><br/>
					Target:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_target]" value="{@rr_target}"/>
				</div></td>
				<td class="col_del"><div><input type="checkbox" name="del[]" value="{@rr_id}"/></div></td>
			</tr>
				</xsl:when>
				<xsl:when test="@rr_type = '_TTL'">
			<tr>
			<xsl:choose>
				<xsl:when test="position( ) = 1">
					<xsl:attribute name="class">linef</xsl:attribute>
				</xsl:when>
				<xsl:when test="position( ) = last( )">
					<xsl:attribute name="class">linel</xsl:attribute>
				</xsl:when>
			</xsl:choose>
				<td class="col_ord"><div><input type="text" name="zone_rrs[{@rr_id}][rr_order]" value="{@rr_order}"/></div></td>
				<td class="col_name"><div>
					$TTL:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_name]" value="{@rr_name}"/>
				</div></td>
				<td>&#160;</td>
				<td>&#160;</td>
				<td>&#160;</td>
				<td class="col_del"><div><input type="checkbox" name="del[]" value="{@rr_id}"/></div></td>
			</tr>
				</xsl:when>
				<xsl:when test="@rr_type = '_ORIGIN'">
			<tr>
			<xsl:choose>
				<xsl:when test="position( ) = 1">
					<xsl:attribute name="class">linef</xsl:attribute>
				</xsl:when>
				<xsl:when test="position( ) = last( )">
					<xsl:attribute name="class">linel</xsl:attribute>
				</xsl:when>
			</xsl:choose>
				<td class="col_ord"><div><input type="text" name="zone_rrs[{@rr_id}][rr_order]" value="{@rr_order}"/></div></td>
				<td class="col_name"><div>
					$ORIGIN:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_name]" value="{@rr_name}"/>
				</div></td>
				<td>&#160;</td>
				<td>&#160;</td>
				<td>&#160;</td>
				<td class="col_del"><div><input type="checkbox" name="del[]" value="{@rr_id}"/></div></td>
			</tr>
				</xsl:when>
				<xsl:when test="@rr_type = '_INCLUDE'">
			<tr>
			<xsl:choose>
				<xsl:when test="position( ) = 1">
					<xsl:attribute name="class">linef</xsl:attribute>
				</xsl:when>
				<xsl:when test="position( ) = last( )">
					<xsl:attribute name="class">linel</xsl:attribute>
				</xsl:when>
			</xsl:choose>
				<td class="col_ord"><div><input type="text" name="zone_rrs[{@rr_id}][rr_order]" value="{@rr_order}"/></div></td>
				<td class="col_name"><div>
					$INCLUDE:<br/><input type="text" name="zone_rrs[{@rr_id}][rr_name]" value="{@rr_name}"/>
				</div></td>
				<td>&#160;</td>
				<td>&#160;</td>
				<td>&#160;</td>
				<td class="col_del"><div><input type="checkbox" name="del[]" value="{@rr_id}"/></div></td>
			</tr>
				</xsl:when>
				<xsl:otherwise>
			<tr>
			<xsl:choose>
				<xsl:when test="position( ) = 1">
					<xsl:attribute name="class">linef</xsl:attribute>
				</xsl:when>
				<xsl:when test="position( ) = last( )">
					<xsl:attribute name="class">linel</xsl:attribute>
				</xsl:when>
			</xsl:choose>
				<td class="col_ord"><div><input type="text" name="zone_rrs[{@rr_id}][rr_order]" value="{@rr_order}"/></div></td>
				<td class="col_name"><div><input type="text" name="zone_rrs[{@rr_id}][rr_name]" value="{@rr_name}"/></div></td>
				<td class="col_ttl"><div><input type="text" name="zone_rrs[{@rr_id}][rr_ttl]" value="{@rr_ttl}"/></div></td>
				<td class="col_type"><div><xsl:value-of select="@rr_type"/></div></td>
				<td class="col_data"><div>
				<xsl:choose>
					<xsl:when test="@rr_type = 'NS'">
						<input type="text" name="zone_rrs[{@rr_id}][rr_server]" value="{@rr_server}"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'A'">
						<input type="text" name="zone_rrs[{@rr_id}][rr_address]" value="{@rr_address}"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'CNAME'">
						<input type="text" name="zone_rrs[{@rr_id}][rr_host]" value="{@rr_host}"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'MX'">
						Preference<br/><input type="text" name="zone_rrs[{@rr_id}][rr_preference]" value="{@rr_preference}"/><br/>
						Host<br/><input type="text" name="zone_rrs[{@rr_id}][rr_host]" value="{@rr_host}"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'PTR'">
						<input type="text" name="zone_rrs[{@rr_id}][rr_name_ptr]" value="{@rr_name_ptr}"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'AAAA'">
						<input type="text" name="zone_rrs[{@rr_id}][rr_address]" value="{@rr_address}"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'TXT'">
						<textarea name="zone_rrs[{@rr_id}][rr_text]"><xsl:value-of select="@rr_text"/></textarea>
					</xsl:when>
					<xsl:otherwise>
						<input type="text" name="zone_rrs[{@rr_id}][rr_data]" value="{@rr_data}"/>
					</xsl:otherwise>
				</xsl:choose>
				</div></td>
				<td class="col_del"><div><input type="checkbox" name="del[]" value="{@rr_id}"/></div></td>
			</tr>
				</xsl:otherwise>
			</xsl:choose>
			</xsl:for-each>
			<tr>
				<td colspan="2">&#160;</td><td colspan="4"><div><a href="{../@base_url}/{@zone_id}/add_rr/{../@last}/">добавить</a></div></td>
			</tr>
		</table></div>
		<div class="zone_comment"><textarea name="zone_comment"><xsl:value-of select="@zone_comment"/></textarea></div>
		
		<div class="zone_controls"><table><tr>
			<td class="col_back"><a href="{../@base_url}/{@zone_id}/exit/">Вернуться к списку без сохранения</a></td>
			<td class="col_save"><div><input type="submit" name="save" value="Сохранить и загрузить файл на сервер"/></div></td>
			<td class="col_tmp_save"><input type="submit" value="Сохранить временный файл"/></td>
		</tr></table></div>
		
		</form>
			</xsl:when>
			<xsl:otherwise>
			
		<div class="zone_soa2">
		<table>
			<tr>
				<td class="lbl"><div>Default TTL:</div></td>
				<td class="inp"><div><xsl:value-of select="@zone_default_ttl"/></div></td>
			</tr>
		</table>
		
		<xsl:for-each select="ResRec[@rr_type = 'SOA']">
			<h3>SOA</h3>
		<table>
			<tr>
				<td class="lbl"><div>Name:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_name"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>TTL:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_ttl"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Origin:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_origin"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Person:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_person"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Serial:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_serial"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Refresh:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_refresh"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Retry:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_retry"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Expire:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_expire"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Minimum TTL:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_minimum_ttl"/></div></td>
			</tr>
		</table>
		</xsl:for-each>
		</div>
		
		<h3>Ресурсные записи</h3>
		<div class="list"><table>
			<tr>
				<th class="col_name"><div>Name</div></th>
				<th class="col_ttl"><div>TTL</div></th>
				<th class="col_class"><div>Class</div></th>
				<th class="col_type"><div>Type</div></th>
				<th class="col_data"><div>Data</div></th>
			</tr>
		<xsl:for-each select="ResRec[@rr_type != 'SOA']">
			<xsl:choose>
				<xsl:when test="@rr_type = 'SRV'">
			<tr>
				<td class="col_name"><div>_<xsl:value-of select="@rr_service"/>._<xsl:value-of select="@rr_proto"/>.<xsl:value-of select="@rr_name"/></div></td>
				<td class="col_ttl"><div><xsl:value-of select="@rr_ttl"/>&#160;</div></td>
				<td class="col_class"><div><xsl:value-of select="@rr_class"/>&#160;</div></td>
				<td class="col_type"><div><xsl:value-of select="@rr_type"/>&#160;</div></td>
				<td class="col_data"><div>
					<xsl:value-of select="@rr_priority"/>&#160;
					<xsl:value-of select="@rr_weight"/>&#160;
					<xsl:value-of select="@rr_port"/>&#160;
					<xsl:value-of select="@rr_target"/>&#160;
				</div></td>
			</tr>
				</xsl:when>
				<xsl:when test="@rr_type = '_TTL'">
			<tr>
				<td class="col_name"><div>$TTL <xsl:value-of select="@rr_name"/></div></td>
				<td colspan="4">&#160;</td>
			</tr>
				</xsl:when>
				<xsl:when test="@rr_type = '_ORIGIN'">
			<tr>
				<td class="col_name"><div>$ORIGIN <xsl:value-of select="@rr_name"/></div></td>
				<td colspan="4">&#160;</td>
			</tr>
				</xsl:when>
				<xsl:when test="@rr_type = '_INCLUDE'">
			<tr>
				<td class="col_name"><div>$INCLUDE <xsl:value-of select="@rr_name"/></div></td>
				<td colspan="4">&#160;</td>
			</tr>
				</xsl:when>
				<xsl:otherwise>
			<tr>
				<td class="col_name"><div><xsl:value-of select="@rr_name"/>&#160;</div></td>
				<td class="col_ttl"><div><xsl:value-of select="@rr_ttl"/>&#160;</div></td>
				<td class="col_class"><div><xsl:value-of select="@rr_class"/>&#160;</div></td>
				<td class="col_type"><div><xsl:value-of select="@rr_type"/>&#160;</div></td>
				<td class="col_data"><div>
				<xsl:choose>
					<xsl:when test="@rr_type = 'NS'">
						<xsl:value-of select="@rr_server"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'A'">
						<xsl:value-of select="@rr_address"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'CNAME'">
						<xsl:value-of select="@rr_host"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'MX'">
						<xsl:value-of select="@rr_preference"/>&#160;
						<xsl:value-of select="@rr_host"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'PTR'">
						<xsl:value-of select="@rr_name_ptr"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'AAAA'">
						<xsl:value-of select="@rr_address"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'TXT'">
						<xsl:value-of select="@rr_text"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="@rr_data"/>
					</xsl:otherwise>
				</xsl:choose>
				&#160;
				</div></td>
			</tr>
				</xsl:otherwise>
			</xsl:choose>
			</xsl:for-each>
		</table></div>
		<xsl:if test="string-length( @zone_comment ) &gt; 0">
		<div class="zone_comment2">
			Комментарий к файлу:<br/>
			<xsl:value-of select="@zone_comment"/>
		</div>
		</xsl:if>
		
			</xsl:otherwise>
		</xsl:choose>
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneText">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<xsl:for-each select="Zone[1]">
		<h2>
		<xsl:choose>
			<xsl:when test="../@locked = 0">
				Редактирование файла
			</xsl:when>
			<xsl:otherwise>
				Просмотр файла
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="@zone_type = 1">обратной</xsl:if> зоны <xsl:value-of select="@zone_name"/></h2>
		<xsl:if test="../@locked = 1">
			<div class="zone_locked"><div class="l1"><div class="l2">Зона заблокирована от редактирования</div></div></div>
		</xsl:if>
		<p>Вернуться <a href="{../@base_url}/{@zone_id}/">в веб-редактор</a></p>
		<xsl:apply-templates select="../Error"/>
		<xsl:choose>
			<xsl:when test="../@locked = 0">
			
		<form action="{../@base_url}/{@zone_id}/text/" method="post"><table>
			<tr>
				<td class="lbl"><div>Текст</div></td>
				<td class="txt"><div><textarea class="zone_text" name="zone_text"><xsl:value-of select="text( )"/></textarea></div></td>
			</tr>
			<tr>
				<td class="lbl">&#160;</td>
				<td class="sbm2"><div><input type="submit" class="sendquery" value="Сохранить"/></div></td>
			</tr>
		</table></form>
		
			</xsl:when>
			<xsl:otherwise>
			
		<pre><xsl:value-of select="text( )"/></pre>
		
			</xsl:otherwise>
		</xsl:choose>
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneConf">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Настройки</h2>
		<xsl:apply-templates select="Error"/>
		
		<form action="{@base_url}/conf/" method="post">
		<div class="list zone_conf"><table>
			<tr><th>&#160;</th><th>&#160;</th></tr>
			<tr>
				<td class="lbl"><div>Default TTL:</div></td>
				<td class="inp"><div><input type="text" name="dttl_ttl" value="{DefaultTTL[1]/@dttl_ttl}"/></div></td>
			</tr>
		<xsl:for-each select="TplSoa[1]">
			<tr>
				<td class="lbl">&#160;</td>
				<td class="inp"><div><h3>Шаблон SOA</h3></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>TTL:</div></td>
				<td class="inp"><div><input type="text" class="text" name="tplsoa_ttl" value="{@tplsoa_ttl}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Origin:</div></td>
				<td class="sel"><div><select name="tplsoa_origin">
				<xsl:for-each select="../Server">
					<option value="{@server_name}">
					<xsl:if test="../TplSoa[1]/@tplsoa_origin = @server_name">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
						<xsl:value-of select="@server_name"/>
					</option>
				</xsl:for-each>
				</select></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Refresh:</div></td>
				<td class="inp"><div><input type="text" class="text" name="tplsoa_refresh" value="{@tplsoa_refresh}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Retry:</div></td>
				<td class="inp"><div><input type="text" class="text" name="tplsoa_retry" value="{@tplsoa_retry}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Expire:</div></td>
				<td class="inp"><div><input type="text" class="text" name="tplsoa_expire" value="{@tplsoa_expire}"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Minimum TTL:</div></td>
				<td class="inp"><div><input type="text" class="text" name="tplsoa_minimum_ttl" value="{@tplsoa_minimum_ttl}"/></div></td>
			</tr>
			<tr>
				<td class="lbl">&#160;</td>
				<td class="sbm2"><div><input type="submit" class="sendquery" value="Сохранить"/></div></td>
			</tr>
		</xsl:for-each>
		</table></div>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneAddRR">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<xsl:variable name="url1" select="concat( @base_url, '/', Zone[1]/@zone_id )"/>
		<h2>Добавление ресурсной записи</h2>
		<p>
		<a href="{$url1}/">назад к списку записей</a><br/>
		<xsl:if test="@mode = 'input'">
		<a href="{$url1}/add_rr/{@rr_pos}/">назад к выбору типа записи</a>
		</xsl:if>
		</p>
		<xsl:apply-templates select="Error"/>
		<form action="{$url1}/add_rr/{@rr_pos}/" method="post">
		<xsl:if test="@mode = 'select'">
			<xsl:attribute name="method">get</xsl:attribute>
		</xsl:if>
		<xsl:choose>
			<xsl:when test="@mode = 'select'">
				<h2>Тип</h2>
				<select name="rr_type">
					<option value="NS">NS</option>
					<option value="A">A</option>
					<option value="CNAME">CNAME</option>
					<option value="MX">MX</option>
					<option value="PTR">PTR</option>
					<option value="SRV">SRV</option>
					<option value="AAAA">AAAA</option>
					<option value="TXT">TXT</option>
					<option value="_TTL">$TTL</option>
					<option value="_ORIGIN">$ORIGIN</option>
					<option value="_INCLUDE">$INCLUDE</option>
				</select>
				<input type="submit" value="Продолжить"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="rr_type" select="ResRec[1]/@rr_type"/>
				<xsl:if test="$rr_type != ''">
					<input type="hidden" name="rr_type" value="{$rr_type}"/>
				</xsl:if>
				<table>
				<xsl:for-each select="ResRec[1]">
				<xsl:choose>
					<xsl:when test="$rr_type = 'SRV'">
					<tr>
						<td class="lbl"><div>Service:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_service" value="{@rr_service}"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Proto:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_proto" value="{@rr_proto}"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Name:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_name" value="{@rr_nam}"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>TTL:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_ttl" value="{@rr_ttl}"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Priority:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_priority" value="{@rr_priority}"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Weight:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_weight" value="{@rr_weight}"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Port:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_port" value="{@rr_port}"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Target:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_target" value="{@rr_target}"/></div></td>
					</tr>
					</xsl:when>
					<xsl:when test="$rr_type = '_TTL'">
					<tr>
						<td class="lbl"><div>TTL:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_name" value="{@rr_name}"/></div></td>
					</tr>
					</xsl:when>
					<xsl:when test="$rr_type = '_ORIGIN'">
					<tr>
						<td class="lbl"><div>ORIGIN:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_name" value="{@rr_name}"/></div></td>
					</tr>
					</xsl:when>
					<xsl:when test="$rr_type = '_INCLUDE'">
					<tr>
						<td class="lbl"><div>INCLUDE:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_name" value="{@rr_name}"/></div></td>
					</tr>
					</xsl:when>
					<xsl:otherwise>
					<tr>
						<td class="lbl"><div>Name:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_name" value="{@rr_name}"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>TTL:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rr_ttl" value="{@rr_ttl}"/></div></td>
					</tr>
						<xsl:if test="$rr_type = ''">
					<tr>
						<td class="lbl"><div>Type:</div></td>
						<td class="inp"><div><input type="text" name="rr_type" value="{$rr_type}"/></div></td>
					</tr>
						</xsl:if>
						<xsl:choose>
							<xsl:when test="$rr_type = 'NS'">
							<tr>
								<td class="lbl"><div>Server:</div></td>
								<td class="inp"><div><input type="text" class="text" name="rr_server" value="{@rr_server}"/></div></td>
							</tr>
							</xsl:when>
							<xsl:when test="$rr_type = 'A' or $rr_type = 'AAAA'">
							<tr>
								<td class="lbl"><div>Address:</div></td>
								<td class="inp"><div><input type="text" class="text" name="rr_address" value="{@rr_address}"/></div></td>
							</tr>
							</xsl:when>
							<xsl:when test="$rr_type = 'CNAME'">
							<tr>
								<td class="lbl"><div>Host:</div></td>
								<td class="inp"><div><input type="text" name="rr_host" value="{@rr_host}"/></div></td>
							</tr>
							</xsl:when>
							<xsl:when test="$rr_type = 'MX'">
							<tr>
								<td class="lbl"><div>Preference:</div></td>
								<td class="inp"><div><input type="text" class="text" name="rr_preference" value="{@rr_preference}"/></div></td>
							</tr>
							<tr>
								<td class="lbl"><div>Host:</div></td>
								<td class="inp"><div><input type="text" class="text" name="rr_host" value="{@rr_host}"/></div></td>
							</tr>
							</xsl:when>
							<xsl:when test="$rr_type = 'PTR'">
							<tr>
								<td class="lbl"><div>Host name:</div></td>
								<td class="inp"><div><input type="text" class="text" name="rr_name_ptr" value="{@rr_name_ptr}"/></div></td>
							</tr>
							</xsl:when>
							<xsl:when test="$rr_type = 'TXT'">
							<tr>
								<td class="lbl"><div>Text:</div></td>
								<td class="txt"><div><textarea name="rr_text"><xsl:value-of select="@rr_text"/></textarea></div></td>
							</tr>
							</xsl:when>
							<xsl:otherwise>
							<tr>
								<td class="lbl"><div>Data:</div></td>
								<td class="txt"><div><input type="text" class="text" name="rr_data" value="{@rr_data}"/></div></td>
							</tr>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
				</xsl:for-each>
				<tr>
					<td class="lbl">&#160;</td>
					<td class="sbm2"><div><input type="submit" class="sendquery" value="Добавить"/></div></td>
				</tr>
				</table>
			</xsl:otherwise>
		</xsl:choose>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneOldList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Сохраненные версии файла зоны <xsl:value-of select="Zone[1]/@zone_name"/></h2>
		<xsl:if test="@locked = 1">
			<div class="zone_locked"><div class="l1"><div class="l2">Зона заблокирована от редактирования</div></div></div>
		</xsl:if>
		<p><a href="{@base_url}/{Zone[1]/@zone_id}/">&amp;larr; Текущая версия</a> файла зоны</p>
		<form action="{@base_url}/{Zone[1]/@zone_id}/old/" method="post">
		<div class="list"><table>
			<tr>
				<th><div>№ версии</div></th>
				<th><div>IP-адрес</div></th>
				<th><div>Дата, время</div></th>
				<th><div>Комментарий к версии</div></th>
				<th class="col_del"><div>Удалить</div></th>
				<th>&#160;</th>
				<th>&#160;</th>
			</tr>
			<xsl:for-each select="OldZone">
			<tr>
				<td><div><xsl:value-of select="@old_zone_version"/>&#160;</div></td>
				<td><div><xsl:value-of select="@old_zone_ip_edited"/>&#160;</div></td>
				<td><div><xsl:value-of select="@old_zone_last_edit"/>&#160;</div></td>
				<td><div><xsl:value-of select="@old_zone_comment"/>&#160;</div></td>
				<td class="col_del"><div>
				<xsl:if test="../@locked = 0">
					<input type="checkbox" name="del[{@old_zone_id}]" value="{@old_zone_id}"/>
				</xsl:if>
				<xsl:if test="../@locked = 1">&#160;</xsl:if>
				</div></td>
				<td><div><a href="{../@base_url}/{../Zone[1]/@zone_id}/old/{@old_zone_id}/">Посмотреть</a></div></td>
				<td><div>
				<xsl:if test="../@locked = 0">
				<a href="{../@base_url}/{../Zone[1]/@zone_id}/old/{@old_zone_id}/load/">Загрузить</a>
				</xsl:if>
				&#160;
				</div></td>
			</tr>
			</xsl:for-each>
		</table></div>
		<xsl:if test="@locked = 0">
		<div class="client_list_save"><table><tr>
			<td class="col1"><div class="clinfo">Версии, помеченные флажком "Удалить", будут удалены.</div></td>
			<td class="col2"><div><input type="submit" class="sendquery" value="Удалить"/></div></td>
		</tr></table></div>
		</xsl:if>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneOldView">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Просмотр сохраненного файла зоны <xsl:value-of select="Zone[1]/@zone_name"/></h2>
		<xsl:if test="@locked = 1">
			<!--a href="/zone/57/" class="zone_edit"><span class="l1"><span class="l2">редактировать</span></span></a-->
			<div class="zone_locked"><span class="l1"><span class="l2">Зона заблокирована от редактирования</span></span></div>
		</xsl:if>
		<p>Назад <a href="{@base_url}/{Zone[1]/@zone_id}/old/">к списку версий</a></p>
		<xsl:for-each select="OldZone[1]">
		<xsl:if test="../@locked = 0">
		<p><a href="{../@base_url}/{../Zone[1]/@zone_id}/old/{@old_zone_id}/load/">Загрузить</a></p>
		</xsl:if>
		
		<div class="zone_old"><table>
			<tr>
				<td class="lbl"><div>№ версии:</div></td>
				<td class="inp"><div><xsl:value-of select="@old_zone_version"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>IP-адрес, с которого делались изменения:</div></td>
				<td class="inp"><div><xsl:value-of select="@old_zone_ip_edited"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Дата, время:</div></td>
				<td class="inp"><div><xsl:value-of select="@old_zone_last_edit"/></div></td>
			</tr>
		</table></div>
		
		<div class="zone_soa2">
		<table>
			<tr>
				<td class="lbl"><div>Default TTL:</div></td>
				<td class="inp"><div><xsl:value-of select="@old_zone_default_ttl"/></div></td>
			</tr>
		</table>
		
		<xsl:for-each select="ResRec[@rr_type = 'SOA']">
			<h3>SOA</h3>
		<table>
			<tr>
				<td class="lbl"><div>Name:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_name"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>TTL:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_ttl"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Origin:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_origin"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Person:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_person"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Serial:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_serial"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Refresh:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_refresh"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Retry:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_retry"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Expire:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_expire"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Minimum TTL:</div></td>
				<td class="inp"><div><xsl:value-of select="@rr_minimum_ttl"/></div></td>
			</tr>
		</table>
		</xsl:for-each>
		</div>
		
		<h3>Ресурсные записи</h3>
		<div class="list"><table>
			<tr>
				<th class="col_name"><div>Name</div></th>
				<th class="col_ttl"><div>TTL</div></th>
				<th class="col_class"><div>Class</div></th>
				<th class="col_type"><div>Type</div></th>
				<th class="col_data"><div>Data</div></th>
			</tr>
		<xsl:for-each select="ResRec[@rr_type != 'SOA']">
			<xsl:choose>
				<xsl:when test="@rr_type = 'SRV'">
			<tr>
				<td class="col_name"><div>_<xsl:value-of select="@rr_service"/>._<xsl:value-of select="@rr_proto"/>.<xsl:value-of select="@rr_name"/></div></td>
				<td class="col_ttl"><div><xsl:value-of select="@rr_ttl"/>&#160;</div></td>
				<td class="col_class"><div><xsl:value-of select="@rr_class"/>&#160;</div></td>
				<td class="col_type"><div><xsl:value-of select="@rr_type"/>&#160;</div></td>
				<td class="col_data"><div>
					<xsl:value-of select="@rr_priority"/>&#160;
					<xsl:value-of select="@rr_weight"/>&#160;
					<xsl:value-of select="@rr_port"/>&#160;
					<xsl:value-of select="@rr_target"/>&#160;
				</div></td>
			</tr>
				</xsl:when>
				<xsl:when test="@rr_type = '_TTL'">
			<tr>
				<td class="col_name"><div>$TTL <xsl:value-of select="@rr_name"/></div></td>
				<td colspan="4">&#160;</td>
			</tr>
				</xsl:when>
				<xsl:when test="@rr_type = '_ORIGIN'">
			<tr>
				<td class="col_name"><div>$ORIGIN <xsl:value-of select="@rr_name"/></div></td>
				<td colspan="4">&#160;</td>
			</tr>
				</xsl:when>
				<xsl:when test="@rr_type = '_INCLUDE'">
			<tr>
				<td class="col_name"><div>$INCLUDE <xsl:value-of select="@rr_name"/></div></td>
				<td colspan="4">&#160;</td>
			</tr>
				</xsl:when>
				<xsl:otherwise>
			<tr>
				<td class="col_name"><div><xsl:value-of select="@rr_name"/>&#160;</div></td>
				<td class="col_ttl"><div><xsl:value-of select="@rr_ttl"/>&#160;</div></td>
				<td class="col_class"><div><xsl:value-of select="@rr_class"/>&#160;</div></td>
				<td class="col_type"><div><xsl:value-of select="@rr_type"/>&#160;</div></td>
				<td class="col_data"><div>
				<xsl:choose>
					<xsl:when test="@rr_type = 'NS'">
						<xsl:value-of select="@rr_server"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'A'">
						<xsl:value-of select="@rr_address"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'CNAME'">
						<xsl:value-of select="@rr_host"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'MX'">
						<xsl:value-of select="@rr_preference"/>&#160;
						<xsl:value-of select="@rr_host"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'PTR'">
						<xsl:value-of select="@rr_name_ptr"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'AAAA'">
						<xsl:value-of select="@rr_address"/>
					</xsl:when>
					<xsl:when test="@rr_type = 'TXT'">
						<xsl:value-of select="@rr_text"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="@rr_data"/>
					</xsl:otherwise>
				</xsl:choose>
				&#160;
				</div></td>
			</tr>
				</xsl:otherwise>
			</xsl:choose>
			</xsl:for-each>
		</table></div>
		<xsl:if test="string-length( @zone_comment ) &gt; 0">
		<div class="zone_comment2">
			Комментарий к файлу:<br/>
			<xsl:value-of select="@zone_comment"/>
		</div>
		</xsl:if>
		
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneUpload">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Загрузка файла зоны с вашего компьютера</h2>
		<p><a href="{@base_url}/{Zone[1]/@zone_id}/">&amp;larr; Текущая версия</a> файла зоны</p>
		<xsl:if test="count(Error)">
		<p>Ошибки в файле зон, загрузка провалилась</p>
		</xsl:if>
		<form action="{@base_url}/{Zone[1]/@zone_id}/upload/" method="post" enctype="multipart/form-data">
		<table>
			<tr>
				<td class="lbl"><div>Файл зоны:</div></td>
				<td class="file"><div><input type="file" name="file_zone" value=""/></div></td>
			</tr>
			<tr>
				<td class="lbl">&#160;</td>
				<td class="sbm2"><div><input type="submit" name="load" value="Загрузить"/></div></td>
			</tr>
		</table>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneExport">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Экспорт файла зоны <xsl:value-of select="Zone[1]/@zone_name"/></h2>
		<p><a href="{@base_url}/{Zone[1]/@zone_id}/">&amp;larr; Текущая версия</a> файла зоны</p>
		<form action="{@base_url}/{Zone[1]/@zone_id}/export/" method="post">
			<p>BIND&#160;<input type="radio" name="type" checked="checked" value="0"/></p>
			<p>CSV&#160;<input type="radio" name="type" value="1"/></p>
			<p>NSD&#160;<input type="radio" name="type" value="2"/></p>
			<p><input type="submit" value="Export"/></p>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneGenerator">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Генерация файлов зон</h2>
		<iframe frameborder="0" width="600" height="600" hspace="0" vspace="0" src="{@base_url}/generator/?frm=1">&#160;</iframe>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ZoneDel">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление зонами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Удаление зон</h2>
		<xsl:if test="count(Zone)">
			<p>При удалении следующих зон будут удалены, связанные с ними зоны<br/><b>Все зоны из списка автоматически блокируются вами от редактирования</b></p>
			<xsl:for-each select="Zone">
				<p>Зона: <b><xsl:value-of select="@zone_name"/></b><br/>
				Дочерние зоны:<br/>
				<xsl:for-each select="*">
					&#160;&#160;&#160;&#160;<b><xsl:value-of select="@zone_name"/></b><xsl:if test="position( ) != last( )"><br/></xsl:if>
				</xsl:for-each>
				</p>
			</xsl:for-each>
			<form action="{@base_url}/del/?del={@orig_del}" method="post">
				<p>Продолжить?&#160;<input type="submit" name="y" value="Да"/>&#160;<input type="submit" name="n" value="Нет"/></p>
			</form>
		</xsl:if>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<!-- Модуль серверов Link ModLink -->
	<xsl:template match="LinkList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление серверами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<div class="add_admin"><a href="{@base_url}/+/">Добавить сервер</a></div>
		<xsl:apply-templates select="Error"/>
		
		<div class="cl_zone_list">
		<div class="clear">&#160;</div>
		<xsl:for-each select="Server">
			<div class="item1"><a href="{../@base_url}/{@server_name}/"><xsl:value-of select="@server_name"/></a>&#160;|&#160;</div>
			<div class="item2"><a class="del" href="{../@base_url}/{@server_id}/" onclick="return confirm('Удалить сервер?');"><span class="l1"><span class="l2">удалить</span></span></a></div>
			<!--a class="del_server" href="{../@base_url}/{@server_id}/" onclick="return confirm('Удалить сервер?');">удалить</a-->
			<xsl:if test="position( ) != last( )">
				<div class="clear">&#160;</div>
			</xsl:if>
		</xsl:for-each>
		<div class="clear">&#160;</div>
		</div>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="LinkAdd">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление серверами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Добавление нового сервера</h2>
		<xsl:apply-templates select="Error"/>
		<xsl:for-each select="Server[1]">
		<form action="{../@base_url}/+/" method="post"><div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c">
			
			<table>
				<tr>
					<td class="lbl"><div>Имя:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="server_name" value="{@server_name}"/>
						<span class="info">имя сервера, для отправки заявки регистратору</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Путь к конфигурационному файлу:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="server_config_file" value="{@server_config_file}"/>
						<span class="info">файл, в который будут писаться записи для подключения зоны</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Путь к папке файлов зон:</div></td>
					<td class="inp"><div>
						<input type="text" name="server_zone_folder" value="{@server_zone_folder}"/>
						<span class="info">папка, в которой будут храниться файлы зон</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>IP адрес:</div></td>
					<td class="inp"><div>
						<input type="text" name="server_ip" value="{@server_ip}"/>
						<span class="info">ip адрес для взаимодействия частей системы</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Тип:</div></td>
					<td class="sel"><div>
						<select name="server_type">
							<option value="0">
							<xsl:if test="@server_type = 0">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							master</option>
							<option value="1">
							<xsl:if test="@server_type = 1">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							slave</option>
						</select>
					</div></td>
				</tr>
				<tr>
					<td class="lbl">&#160;</td>
					<td class="sbm2"><div><input type="submit" class="sendquery" value="Добавить"/></div></td>
				</tr>
			</table>
			
			</td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div></form>
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="LinkEdit">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Управление серверами</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<xsl:choose>
			<xsl:when test="@mode and ( @mode = 'edit' )">
				<h2>Редактирование данных сервера <xsl:value-of select="Server[1]/@server_name"/></h2>
			</xsl:when>
			<xsl:otherwise>
				<h2>Добавление нового сервера</h2>
			</xsl:otherwise>
		</xsl:choose>
		
		<xsl:apply-templates select="Error"/>
		
		<xsl:for-each select="Server[1]">
		<form action="{../@base_url}/{@server_name}/" method="post">
		<xsl:if test="../@mode = 'add'">
			<xsl:attribute name="action"><xsl:value-of select="../@base_url"/>/+/</xsl:attribute>
		</xsl:if>
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c">
			
			<table>
				<xsl:if test="../@mode = 'add'">
				<tr>
					<td class="lbl"><div>Имя:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="server_name" value="{@server_name}"/>
						<span class="info">имя сервера, для отправки заявки регистратору</span>
					</div></td>
				</tr>
				</xsl:if>
				<tr>
					<td class="lbl"><div>Путь к конфигурационному файлу:</div></td>
					<td class="inp"><div>
						<input type="text" class="text" name="server_config_file" value="{@server_config_file}"/>
						<span class="info">файл, в который будут писаться записи для подключения зоны</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Путь к папке файлов зон:</div></td>
					<td class="inp"><div>
						<input type="text" name="server_zone_folder" value="{@server_zone_folder}"/>
						<span class="info">папка, в которой будут храниться файлы зон</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>IP адрес:</div></td>
					<td class="inp"><div>
						<input type="text" name="server_ip" value="{@server_ip}"/>
						<span class="info">ip адрес для взаимодействия частей системы</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Тип:</div></td>
					<td class="sel"><div>
						<select name="server_type">
							<option value="0">
							<xsl:if test="@server_type = 0">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							master</option>
							<option value="1">
							<xsl:if test="@server_type = 1">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							slave</option>
						</select>
					</div></td>
				</tr>
				<tr>
					<td class="lbl"><div>Префикс пути:</div></td>
					<td class="inp"><div>
						<input type="text" name="server_root_prefix" value="{@server_root_prefix}"/>
						<span class="info">если сервер работает через chroot, то данное поле стоит заполнить</span>
					</div></td>
				</tr>
				<tr>
					<td class="lbl">&#160;</td>
					<td class="sbm2"><div><input type="submit" class="sendquery" value="Сохранить"/></div></td>
				</tr>
			</table>
			
			</td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div></form>
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<!-- Модуль резервных копий Backup ModBackup -->
	<xsl:template match="BackupList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Резервные копии системы</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<div class="cont_top"><table><tr>
			<td class="filter flt_backup"><xsl:apply-templates select="BackupFilter"/></td>
			<td><div class="add_client"><a href="{@base_url}/+/">Создать резервную копию</a></div></td>
		</tr></table></div>
		
		<form action="{@base_url}/" method="post">
		<div class="list backup_list"><table>
			<tr>
				<th class="col_datetime"><div>Дата, время</div></th>
				<th class="col_components"><div>Компоненты</div></th>
				<th class="col_del"><div>Удалить</div></th>
				<th class="col_dl">&#160;</th>
				<th class="col_restore">&#160;</th>
			</tr>
			<xsl:for-each select="Record">
			<tr>
				<td class="col_datetime"><div><xsl:value-of select="@_cr_date"/>&#160;</div></td>
				<td class="col_components"><div><xsl:value-of select="@_components"/>&#160;</div></td>
				<td class="col_del"><div><input type="checkbox" name="del[{@id}]" value="{@id}"/></div></td>
				<td class="col_dl"><div><a href="{../@base_url}/export/{@id}/">скачать</a></div></td>
				<td class="col_restore"><div>
					<a class="zone_edit" href="{../@base_url}/restore/{@id}/"><span class="l1"><span class="l2">восстановить</span></span></a>
					<!--a class="restore" href="{../@base_url}/restore/{@id}/">восстановить</a-->
				</div></td>
			</tr>
			</xsl:for-each>
		</table></div>
		<div class="client_list_save"><table><tr>
			<td class="col1"><div class="clinfo">Резервные копии, помеченные флажком "Удалить", будут удалены.</div></td>
			<td class="col2"><div><input type="submit" class="sendquery" value="Удалить"/></div></td>
		</tr></table></div>
		<xsl:apply-templates select="Pager"/>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="BackupAdd">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Резервные копии системы</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Создание резервной копии</h2>
		<form action="{@base_url}/+/" method="post">
		<p>Отметьте компоненты, для которых необходимо создать резервную копию данных</p>
		<label for="d01"><input type="checkbox" name="backup[db]" checked="checked" id="d01"/> клиенты, зоны, сервера, администраторы, настройки системы</label><br/>
		<label for="d02"><input type="checkbox" name="backup[source]" checked="checked" id="d02"/> исходные коды</label><br/>
		<label for="d03"><input type="checkbox" name="backup[zone]" checked="checked" id="d03"/> файлы зон</label><br/>
		<p><input type="submit" value="Создать резервную копию"/></p>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="BackupRestore">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Резервные копии системы</h1>
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>Восстановление данных из резервной копии</h2>
		<p>Отметьте модули, данные которых требуется восстановить:</p>
		<form action="{@base_url}/restore/{Record[1]/@id}/" method="post">
		<xsl:for-each select="Record[1]">
				<xsl:if test="@_db">
				<label for="d01"><input type="checkbox" name="backup[db]" checked="checked" id="d01"/> клиенты, зоны, сервера, администраторы, настройки системы</label><br/>
				</xsl:if>
				<xsl:if test="@_source">
				<label for="d02"><input type="checkbox" name="backup[source]" checked="checked" id="d02"/> исходные коды</label><br/>
				</xsl:if>
				<xsl:if test="@_zone">
				<label for="d03"><input type="checkbox" name="backup[zone]" checked="checked" id="d03"/> файлы зон</label><br/>
				</xsl:if>
		</xsl:for-each>
		<p><input type="submit" value="Восстановить данные" style="font-size: 1.1em;"/></p>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<!-- Модуль логов системы Logger ModLogger -->
	<xsl:template match="LoggerList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Логи системы</h1>
			</div></td>
			<td class="ccol"><div class="content">
		
		<div class="cont_top"><xsl:apply-templates select="LogFilter"/></div>
		<xsl:apply-templates select="Error"/>
		<form action="{@base_url}/" method="post">
		<div class="list log_list"><table>
			<tr>
				<th><div>Дата, время</div></th>
				<th><div>Модуль</div></th>
				<th><div>Пользователь</div></th>
				<th><div>Действие</div></th>
				<!--th><div>Комментарий</div></th-->
				<th><div>IP-адрес</div></th>
				<th class="col_del"><div>Удалить</div></th>
				<th class="col_view">&#160;</th>
			</tr>
		<xsl:for-each select="Log">
			<tr>
				<td><div><xsl:value-of select="@log_cr_date"/>&#160;</div></td>
				<td><div><xsl:value-of select="@log_module"/>&#160;</div></td>
				<td><div><xsl:value-of select="@log_user"/>&#160;</div></td>
				<td><div><xsl:value-of select="@log_action"/>&#160;</div></td>
				<!--td><xsl:value-of select="@log_comment"/>&#160;</td-->
				<td><div><xsl:value-of select="@log_ip_address"/></div></td>
				<td class="col_del"><div><input type="checkbox" name="del[{@log_id}]" value="{@log_id}"/></div></td>
				<td class="col_view"><div>
					<a class="zone_edit" href="{../@base_url}/{@log_id}/"><span class="l1"><span class="l2">посмотреть</span></span></a>
					<!--a href="{../@base_url}/{@log_id}/">Посмотреть</a-->
				</div></td>
			</tr>
		</xsl:for-each>
		</table></div>
		<div class="client_list_save"><table><tr>
			<td class="col1"><div class="clinfo">Логи, помеченные флажком "Удалить", будут удалены.</div></td>
			<td class="col2"><div><input type="submit" class="sendquery" value="Удалить"/></div></td>
		</tr></table></div>
		<xsl:apply-templates select="Pager"/>
		</form>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="LoggerView">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Логи системы</h1>
			</div></td>
			<td class="ccol"><div class="content">
		
		<xsl:for-each select="Log[1]">
		<div class="log"><table>
			<tr>
				<td class="lbl"><div>Дата, время:</div></td>
				<td class="inp"><div><xsl:value-of select="@log_cr_date"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Модуль:</div></td>
				<td class="inp"><div><xsl:value-of select="@log_module"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Пользователь:</div></td>
				<td class="inp"><div><xsl:value-of select="@log_user"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>IP-адрес:</div></td>
				<td class="inp"><div><xsl:value-of select="@log_ip_address"/></div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Действие:</div></td>
				<td class="inp"><div><xsl:value-of select="@log_action"/></div></td>
			</tr>
		</table>
		<p>Дополнительная информаци о произведенных действиях</p>
		<p><xsl:value-of select="@log_comment"/></p>
		</div>
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<!-- Модуль отчетов -->
	<xsl:template match="ReportList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Отчеты</h1>
				<div class="vmenu">
					<span>Пользователи &amp;mdash; зоны</span>
					<a href="{@base_url}/servers/">Обращения к серверам</a>
				</div>
			</div></td>
			<td class="ccol"><div class="content">
			
			<h2>Пользователи &amp;mdash; зоны</h2>
			
			<div class="cont_top2"><xsl:apply-templates select="ReportFilter"/></div>
			
			<xsl:for-each select="Report[1]">
			<div class="list report_sys"><table>
				<tr>
					<td class="col1"><div><b>Всего клиентов:</b></div></td>
					<td class="col2"><div><xsl:value-of select="@report_cl_count"/>&#160;</div></td>
				</tr>
				<tr>
					<td class="col1"><div>&#160;&#160;&#160;&#160;Активных:</div></td>
					<td class="col2"><div><xsl:value-of select="@report_cl_active"/>&#160;</div></td>
				</tr>
				<tr>
					<td class="col1"><div>&#160;&#160;&#160;&#160;Неактивных:</div></td>
					<td class="col2"><div><xsl:value-of select="@report_cl_inactive"/>&#160;</div></td>
				</tr>
				<tr>
					<td class="col1"><div>&#160;&#160;&#160;&#160;Заблокировано:</div></td>
					<td class="col2"><div><xsl:value-of select="@report_cl_blocked"/>&#160;</div></td>
				</tr>
				<tr>
					<td class="col1"><div><b>Всего зон:</b></div></td>
					<td class="col2"><div><xsl:value-of select="@report_zones"/>&#160;</div></td>
				</tr>
			</table></div>
			</xsl:for-each>
			
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ReportServers">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Отчеты</h1>
				<div class="vmenu">
					<a href="{@base_url}/">Пользователи &amp;mdash; зоны</a>
					<span>Обращения к серверам</span>
					<div class="lvl">
						<a href="{@base_url}/domains/?{@url_for_ip}">Обращения к доменам</a>
					</div>
				</div>
			</div></td>
			<td class="ccol"><div class="content">
			
			<h2>Обращения к серверам</h2>
			<div class="cont_top2"><xsl:apply-templates select="QueriesFilter"/></div>
			
			<xsl:for-each select="Queries">
			
			<div class="query_info"><table><tr>
				<td class="col1">
			<div class="list"><table>
				<tr>
					<th class="col_qtime"><div>Время</div></th>
					<th class="col_queries"><div>Запросы</div></th>
				</tr>
				<xsl:for-each select="TimeQuery">
				<tr>
					<td class="col_qtime"><div><xsl:value-of select="@time"/></div></td>
					<td class="col_queries"><div><xsl:value-of select="@queries"/></div></td>
				</tr>
				</xsl:for-each>
			</table></div>
				</td>
				<td class="col2">
				
			<div id="flot" style="width: 526px; height: 300px;">&#160;</div>
			<div id="flot_min" style="width: 526px; height: 60px;">&#160;</div>
			
<script language="javascript" type="text/javascript">
var g_FlotData = [<xsl:for-each select="TimeQuery">
[<xsl:value-of select="@timestamp"/>,<xsl:value-of select="@queries"/>]<xsl:if test="position( ) != last( )">,</xsl:if>
</xsl:for-each>];
var g_QueriesTotal = <xsl:value-of select="@queries_maximum"/>;
</script>
			<script language="javascript" type="text/javascript" src="{../@root_relative}/custom2.js"></script>
			
				</td>
			</tr></table></div>
			</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ReportDomains">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Отчеты</h1>
				<div class="vmenu">
					<a href="{@base_url}/">Пользователи &amp;mdash; зоны</a>
					<a href="{@base_url}/servers/?d1={QueriesFilter2[1]/@d1}">Обращения к серверам</a>
					<div class="lvl">
						<span>Обращения к доменам</span>
					</div>
				</div>
			</div></td>
			<td class="ccol"><div class="content">
			
			<h2>Обращения к доменам</h2>
			<div class="cont_top2"><xsl:apply-templates select="QueriesFilter2"/></div>
			<div class="list query_domain"><table>
				<tr>
					<th class="col1"><div>Домен</div></th>
					<th class="col2"><div>Запросов</div></th>
				</tr>
				<xsl:for-each select="DomainCount">
				<tr>
					<td class="col1"><div><xsl:value-of select="@domain"/></div></td>
					<td class="col2"><div><a href="{../@base_url}/domains/{@id}/?{../@url_for_ip}"><xsl:value-of select="@count"/></a></div></td>
				</tr>
				</xsl:for-each>
			</table></div>
			
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ReportDomainView">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Отчеты</h1>
				<div class="vmenu">
					<a href="{@base_url}/">Пользователи &amp;mdash; зоны</a>
					<a href="{@base_url}/servers/?d1={QueriesFilter2[1]/@d1}">Обращения к серверам</a>
					<div class="lvl">
						<a class="cur" href="{@base_url}/domains/?d1={QueriesFilter2[1]/@d1}">Обращения к доменам</a>
					</div>
				</div>
			</div></td>
			<td class="ccol"><div class="content">
			
			<h2>Обращения к домену <xsl:value-of select="@selected_domain"/></h2>
			<div class="cont_top2"><xsl:apply-templates select="QueriesFilter2"/></div>
			<div class="list query_domain"><table>
				<tr>
					<th class="col1"><div>IP</div></th>
					<th class="col2"><div>Запросов</div></th>
				</tr>
				<xsl:for-each select="DomainCount">
				<tr>
					<td class="col1"><div><xsl:value-of select="@domain"/></div></td>
					<td class="col2"><div><xsl:value-of select="@count"/></div></td>
				</tr>
				</xsl:for-each>
			</table></div>
			
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<!-- Модуль входа в систему -->
	<xsl:template match="LoginForm">
		<div class="wrap">
			<div class="header">
				<div class="logo"><a href="{@logo_url}"><img src="{@logo_src}" alt="Rostelecom"/></a></div>
			</div>
			<xsl:apply-templates select="Error"/>
			<div class="login_form"><form action="{@post_url}" method="post"><div class="x9"><table>
				<tr class="top"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
				<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
					<table>
						<tr>
							<td class="lbl"><div>Логин:</div></td>
							<td class="inp"><div><input type="text" class="text" name="login" value=""/></div></td>
						</tr>
						<tr>
							<td class="lbl"><div>Пароль:</div></td>
							<td class="inp"><div><input type="password" class="text" name="password" value=""/></div></td>
						</tr>
						<tr>
							<td class="lbl">&#160;</td>
							<td class="sbm2"><div><input type="submit" class="sendquery" value="Войти"/></div></td>
						</tr>
					</table>
				</div></td><td class="r">&#160;</td></tr>
				<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
			</table></div>
			</form></div>
		</div>
	</xsl:template>
	
	<!-- Модуль инсталляции -->
	<xsl:template match="Install1">
		<h1>Системные настройки</h1>
		<xsl:apply-templates select="Error"/>
		<form action="{@post_url}" method="post"><div class="conf">
		
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td>
				<td class="c"><span>Database account</span></td>
			<td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
			<xsl:for-each select="CDbAccount[1]">
				<table>
					<tr>
						<td class="lbl"><div>Сервер:</div></td>
						<td class="inp"><div><input type="text" class="text" name="db[server]" value="{@server}"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Имя пользователя:</div></td>
						<td class="inp"><div><input type="text" class="text" name="db[username]" value="{@username}" autocomplete="off"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Пароль:</div></td>
						<td class="inp"><div><input type="text" class="text" name="db[password]" value="{@password}" autocomplete="off"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Имя базы данных:</div></td>
						<td class="inp"><div><input type="text" class="text" name="db[database]" value="{@database}"/></div></td>
					</tr>
				</table>
			</xsl:for-each>
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div>
		
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td>
				<td class="c"><span>rsync account</span></td>
			<td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
			<xsl:for-each select="CRsyncAccount[1]">
				<table>
					<tr>
						<td class="lbl"><div>Имя пользователя:</div></td>
						<td class="inp"><div><input type="text" class="text" name="rsync[username]" value="{@username}" autocomplete="off"/></div></td>
					</tr>
				</table>
			</xsl:for-each>
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div>
		
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td>
				<td class="c"><span>Superadmin account</span></td>
			<td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
			<xsl:for-each select="Admin[1]">
				<table>
					<tr>
						<td class="lbl"><div>Логин:</div></td>
						<td class="inp"><div><input type="text" class="text" name="superadmin[admin_login]" value="{@admin_login}" autocomplete="off"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Пароль:</div></td>
						<td class="inp"><div><input type="text" class="text" name="superadmin[admin_password]" value="" autocomplete="off"/></div></td>
					</tr>
				</table>
			</xsl:for-each>
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div>
		
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td>
				<td class="c"><span>reg.ru account</span></td>
			<td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
			<xsl:for-each select="Regru[1]">
				<table>
					<tr>
						<td class="lbl"><div>Логин:</div></td>
						<td class="inp"><div><input type="text" class="text" name="regru[regru_username]" value="{@regru_username}" autocomplete="off"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Пароль:</div></td>
						<td class="inp"><div><input type="text" class="text" name="regru[regru_password]" value="{@regru_password}" autocomplete="off"/></div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Шлюз:</div></td>
						<td class="inp"><div><input type="text" class="text" name="regru[regru_gateway]" value="{@regru_gateway}"/></div></td>
					</tr>
				</table>
			</xsl:for-each>
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div>
		
		<div class="x9"><table>
			<tr class="top"><td class="l">&#160;</td>
				<td class="c"><span>system config</span></td>
			<td class="r">&#160;</td></tr>
			<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
			<xsl:for-each select="CSystemConfig[1]">
				<table>
					<tr>
						<td class="lbl"><div>Бэкапы:</div></td>
						<td class="inp"><div>
							<input type="text" class="text" name="system[backup]" value="{@backup}"/>
							<span class="info">Путь к папке, где хранятся резервные копии (относительно корневой директории, в которой находится ситема). Если путь не указан, то по умолчанию  - backup</span>
						</div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Зоны:</div></td>
						<td class="inp"><div>
							<input type="text" class="text" name="system[zone]" value="{@zone}"/>
							<span class="info">Путь к папке, где хранятся файлы зон (относительно корневой директории, в которой находится ситема). Если путь не указан, то по умолчанию  - zone</span>
						</div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Конфиги:</div></td>
						<td class="inp"><div>
							<input type="text" class="text" name="system[config]" value="{@config}"/>
							<span class="info">Путь к папке, где хранятся конфигурационные файлы для master и slave серверов (относительно корневой директории, в которой находится ситема). Если путь не указан, то по умолчанию  - config</span>
						</div></td>
					</tr>
					<tr>
						<td class="lbl"><div>Файл сислога:</div></td>
						<td class="inp"><div>
							<input type="text" class="text" name="system[logfile]" value="{@logfile}"/>
							<span class="info">Путь к файлу, где хранятся записи сислога. Если путь не указан, то агрегация данных не происходит</span>
						</div></td>
					</tr>
				</table>
			</xsl:for-each>
			</div></td><td class="r">&#160;</td></tr>
			<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
		</table></div>
		<div class="client_end"><table>
			<tr>
				<td class="lbl">&#160;</td>
				<td class="sbm2"><div><input type="submit" class="sendquery" value="Ok"/></div></td>
			</tr>
		</table></div>
		
		</div></form>
	</xsl:template>
	
	
	<xsl:template match="HelpView">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Помощь</h1>
				<xsl:value-of select="HelpMenu[1]/text( )"/>
				
<ul>
<li><a href="#users">Пользователи</a>
<ul>
<li><a href="#admins">Учетные записи администраторов и операторов</a></li>
</ul>
</li>
<li><a href="#customers">Клиенты</a>
<ul>
<li><a href="#newfields">Добавление полей в анкету учетной записи</a></li>
<li><a href="#searchcustomers">Поиск учетных записей клиентов</a></li>
</ul>
</li>
<li><a href="#regdomain">Регистрация доменов</a></li>
<li><a href="#zone">Зоны</a>
<ul>
<li><a href="#addzone">Добавление зоны</a></li>
<li><a href="#editzone">Редактирование зон</a></li>
<li><a href="#importzone">Импорт и экспорт файлов зон</a></li>
<li><a href="#deletezone">Удаление зон</a></li>
<li><a href="#templatezone">Редактирование шаблона зон</a></li>
<li><a href="#createzone">Генерация файлов зон</a></li>
<li><a href="#reversezone">Обратные зоны</a></li>
</ul>
</li>
<li><a href="#server">Сервера</a></li>
<li><a href="#backups">Резервные копии</a></li>
<li><a href="#log">Логи</a></li>
</ul>

			</div></td>
			<td class="ccol"><div class="content">

			<!--xsl:for-each select="Help[1]">
				<xsl:value-of select="text( )"/>
				<xsl:apply-templates select="*"/>
			</xsl:for-each-->

<a name="users" id="user"></a>
<h2>Пользователи</h2>
<p>Все пользователи РС ДНС получают доступ к системе после авторизации. В РС ДНС существует четыре типа пользователей, различающихся уровнем доступа к функциям системы:</p>
<ol>
<li>Суперадминистратор — создается при создании системы; существует только одна учетная запись суперадминистратора; обладает неограниченным доступом ко всем функциям системы; может создавать учетные записи администраторов, операторов, клиентов.</li>
<li>Администратор — создается суперадминистратором; количество администраторов в системе не ограничено; может создавать учетные записи операторов и клиентов.</li>
<li>Оператор — создается суперадминистратором или администратором; количество операторов в системе не ограничено; обладает ограниченным доступом к функциям системы и управлению клиентами; может создавать учетные записи клиентов.</li>
<li>Клиент — создается суперадминистратором, администратором или оператором; количество клиентов в системе не ограничено; имеет доступ только к своей учетной записи и зонам.</li>
</ol>

<a name="admins" id="admins"></a>
<h3>Учетные записи администраторов и операторов</h3>
<p>Управление учетными записями администраторов и операторов осуществляется в разделе «<a href="{@root_relative}/admins/">Администраторы</a>» / «<a href="{@root_relative}/admins/">Операторы</a>».</p>

<p>Чтобы добавить новую учетную запись администратора, необходимо авторизоваться в системе под учетной записью суперадминистратора. Чтобы добавить новую учетную запись оператора, необходимо авторизоваться в системе под учетной записью суперадминистратора или администратора.</p>

<p>Перейдите по ссылке  «Добавить администратора» / «Добавить оператора» и заполните поля формы: логин, пароль, дополнительная информация (опциональное поле). Если добавление учетной записи происходит от имени суперадминистратора, выберите тип создаваемой учетной записи: администратор или оператор. Нажмите кнопку «Добавить».</p>

<p>Чтобы просмотреть и изменить данные какой-либо учетной записи, кликните по ее заголовку в списке учетных записей, внесите все необходимые изменения и нажмите кнопку «Сохранить».</p>

<p>Чтобы удалить учетные записи, поставьте галочки в колонке «Удалить» напротив тех учетных записей, которые больше не нужны, и нажмите кнопку «Удалить». ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при выборе удаляемых учетных записей.</p>

<a name="customers" id="customers"></a>
<h2>Клиенты</h2>
<p>Управление учетными записями клиентов осуществляется в разделе «<a href="{@root_relative}/user/">Клиенты</a>».</p>

<p>Чтобы добавить новую учетную запись клиента, перейдите по ссылке «Добавить клиента» и заполните все требуемые поля в соответствии с приведенными образцами. При заполнении регистрационных полей обязательно соблюдение формата данных, указанного в примерах, так как некорректно введенная информация может повлиять на принятие регистратором заявок на регистрацию доменов и может сделать невозможной регистрацию доменов для этого клиента.</p>

<p>При добавлении клиента можно сразу указать его статус: активирован, не активирован, заблокирован. После создания учетной записи клиента этот статус можно будет изменить в любое время.</p>

<p>Чтобы изменить статус учетной записи клиента, в строке напротив нужного клиента необходимо выбрать из выпадающего списка требуемый статус и нажать кнопку «Сохранить». Одновременно можно изменить статус нескольких клиентов. Обратите внимание, что изменения статуса сохраняются и вступают в силу только после нажатия на кнопку «Сохранить».</p>

<p>Чтобы изменить данные учетной записи клиента, кликните по ее названию, затем в форме на открывшейся странице внесите все необходимые изменения и нажмите кнопку «Сохранить».</p>

<p>Чтобы удалить учетные записи клиентов, поставьте в строке напротив удаляемой учетной записи галочку в столбце «Удалить» и нажмите кнопку «Сохранить». Одновременно можно удалить нескольких клиентов. ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при выборе удаляемых учетных записей.</p>

<a name="newfields" id="newfields"></a>
<h3>Добавление полей в анкету учетной записи</h3>
<p>В анкету учетной записи можно добавить произвольные поля для указания необходимой информации. Чтобы добавить такие поля, перейдите по ссылке «Добавление полей» в разделе «Клиенты».</p>

<p>В форме на открывшейся странице укажите:</p>
<ul>
<li>имя поля латиницей без пробелов — это имя будет использоваться в качестве переменной;</li>
<li>в выпадающем списке выберите тип добавляемого поля:
<ul>
<li>text — простое текстовое поле ввода в одну строчку,</li>
<li>textarea — текстовое поле для ввода сравнительно больших объемов информации,</li>
<li>select — выпадающий список;</li>
</ul>
</li>
<li>если выбран тип добавляемого поля select, в поле «Опции» через запятую введите значения списка в этом поле;</li>
<li>укажите заголовок поля, который будет отображаться в анкете учетной записи, объясняя, какую информацию следует вводить в данное поле.</li>
</ul>

<p>После ввода всей информации нажмите кнопку «Добавить».</p>

<p>На этой же странице можно изменить заголовки добавленных ранее полей, либо удалить добавленные ранее поля.</p>

<a name="searchcustomers" id="searchcustomers"></a>
<h3>Поиск учетных записей клиентов</h3>
<p>Для поиска учетных записей клиентов используется специальная форма, позволяющая фильтровать учетные записи клиентов по дате добавления и / или статусу и / или поисковому запросу. Форма поиска расположена на странице раздела «Клиенты» над списком учетных записей клиентов.</p>

<a name="regdomain" id="regdomain"></a>
<h2>Регистрация доменов</h2>
<p>РС ДНС позволяет отправлять заявки на регистрацию доменов регистратору Reg.ru. Для того, чтобы отправить заявку на регистрацию домена, необходимо выполнить следующие действия:</p>
<ol>
<li>Перейти в раздел «Клиенты».</li>
<li>Выбрать из списка учетную запись клиента, для которого нужно зарегистрировать домен, и перейти на страницу редактирования информации об этой учетной записи, кликнув по названию клиента. Для поиска учетных записей клиентов можно воспользоваться формой, расположенной над списком клиентов.</li>
<li>Перейти по ссылке «Зарегистрировать домен».</li>
<li>Ввести в текстовое поле формы доменное имя и нажать на кнопку «Проверить».</li>
<li>Если указанное доменное имя занято, будет выведено сообщение: «Доменное имя занято». После этого можно проверить другое доменное имя.</li>
<li>Если указанное доменное имя свободно, будет показана ссылка «Зарегистрировать». Для продолжения регистрации домена необходимо перейти по этой ссылке, а затем нажать на кнопку «Зарегистрировать».</li>
<li>После нажатия на кнопку «Зарегистрировать» введенные данные будут отправлены регистратору Reg.ru, по <a href="#templatezone">существующему шаблону</a> будет создана зона связанная с данным клиентом, отобразится сообщение об отправке заявки на регистрацию домена регистратору. Созданная зона будет видна в списке зон данного клиента и доступна для редактирования.</li>
<li>В случае возникновения ошибок, будет выведено сообщение о невозможности отправки заявки.</li>
</ol>

<a name="zone" id="zone"></a>
<h2>Зоны</h2>
<p>Управление зонами осуществляется в разделе «<a href="{@root_relative}/zone/">Зоны</a>». Кроме того, возможен просмотр зон, принадлежащих конкретному клиенту. Для этого необходимо перейти в учетную запись клиента (см. пункт «<a href="#user">Клиенты</a>» настоящего руководства).</p>

<p>На странице раздела «Зоны» предусмотрена возможность поиска зон по доменным именам или IP-адресам. Чтобы осуществить поиск, введите в форму над списком зон доменное имя или IP-адрес и нажмите кнопку «Найти.</p>

<a name="addzone" id="addzone"></a>
<h3>Добавление зоны</h3>
<p>Если есть необходимость создать зону без регистрации домена, например, домен уже зарегистрирован и нужно перенести его, вы может создать соответствующую зону без регистрации домена.</p>
<p>Для добавления зоны без регистрации домена, выполните следующие действия:</p>
<ol>
<li>Перейдите в раздел «Клиенты».</li>
<li>Нажмите на ссылку с названием клиента, для которого будет создаваться зона.</li>
<li>В меню слева нажмите на ссылку «Добавление зоны».</li>
<li>В текстовом поле укажите имя создаваемой зоны, например, google.ru.</li>
<li>Нажмите кнопку «Добавить».</li>
</ol>
<p>Если зона с таким именем уже есть в системе, будет выведено предупреждение о невозможности добавить зону. В противном случае в системе будет создана зона, которую при необходимости вы сможете отредактировать в разделе «Редактор файлов зон» (см. раздел <a href="#editzone">«Редактирование зон»</a>).</p>
<p>Проверка существования зоны где-либо, кроме РС ДНС не проводится.</p>

<a name="editzone" id="editzone"></a>
<h3>Редактирование зон</h3>
<p>Чтобы отредактировать зону, выберите ее из списка и перейдите по ссылке «Редактировать» в строке выбранной зоны, либо в учетной записи клиента кликните по названию зоны, которую нужно отредактировать.</p>

<p>В форме на открывшей странице внесите необходимые изменения в поля зоны и нажмите кнопку «Сохранить и загрузить файл на сервер», чтобы применить все сделанные изменения. Предыдущая версия зоны будет доступна в разделе «Сохраненные версии файла зоны».</p>

<p>Можно также после внесения изменений сохранить их во временный файл без применения, а затем продолжить редактирование параметров зоны. Обратите внимание, что в этом случае до нажатия кнопки «Сохранить и загрузить файл на сервер» внесенные изменения не будут применены и будут сохранены лишь во временном файле.</p>

<p>Чтобы выйти из редактирования зоны без сохранения сделанных изменений, перейдите по ссылке «Вернуться к списку без сохранения».</p>

<p>Файл зоны можно также отредактировать в текстовом виде. Для этого перейдите на страницу редактирования зоны и кликните по ссылке «Редактировать в текстовом виде». Внесите все необходимые изменения и нажмите кнопку «Сохранить». Если введенные изменения корректны, вы перейдете на страницу редактирования зоны, в ином случае будет выведено соответствующее предупреждение. После успешного сохранения, внесенные изменения будут применены, а предыдущая версия файла зоны будет доступна в разделе «Сохраненные версии файла зоны».</p>

<p>В РС ДНС предусмотрена возможность добавлять ресурсные записи зоны. Чтобы добавить ресурсные записи зоны перейдите на страницу редактирования зоны. В списке ресурсных записей кликните по ссылке «Добавить», затем из выпадающего списка выберите тип ресурсной записи: NS, A, CNAME, MX, PTR, SRV, AAAA, TXT и нажмите кнопку «Дальше». В форме на открывшейся странице будут отображены поля, соответствующие выбранному типу записи. Введите в поля соответствующие значения и нажмите на кнопку «Добавить». Если значения введены корректно, вы перейдете на страницу редактирования зоны, в ином случае будет выведено соответствующее предупреждение.</p>

<p>Чтобы удалить ненужные ресурсные записи зоны, перед сохранением поставьте галочки в столбце «Удалить» напротив удаляемых ресурсных записей. ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при выборе удаляемых ресурсных записей.</p>

<h4>Сохраненные версии зоны</h4>
<p>При внесении и применении любых изменений в файл зоны, предыдущие версии файла сохраняются в системе для того, чтобы затем их можно было просмотреть, при необходимости загрузить, либо удалить.</p>

<p>Чтобы просмотреть список версий файла конкретной зоны, перейдите на страницу редактирования нужной зоны, а затем кликните по ссылке «Сохраненные версии файла зоны». Вам будет представлена подробная информация о предыдущих версиях файла зоны.</p>

<p>При помощи управляющих ссылок на данной страницы можно просмотреть интересующие файлы зоны, либо загрузить их вместо текущей — при этом заменяемый файл зоны будет сохранен в списке предыдущих версий файлов зоны.</p>

<p>Чтобы удалить ненужные версии файлов зоны, поставьте галочки в столбце «Удалить» напротив удаляемых версий файлов зоны и нажмите кнопку «Удалить». ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при выборе удаляемых версий файлов зоны.</p>


<a name="importzone" id="importzone"></a>
<h3>Импорт и экспорт файлов зон</h3>
<p>В РС ДНС предусмотрена возможность импорта файла зоны с компьютера пользователя. Для того чтобы заменить текущий файл зоны файлом, хранящимся на компьютере пользователя, необходимо перейти на страницу редактирования зоны и кликнуть по ссылке «Загрузка файла зоны с вашего компьютера». При помощи формы на открывшей странице необходимо указать расположение файла зоны на вашем компьютере. Если указанный файл является корректным файлом зоны, вы перейдете на страницу редактирования зоны, в ином случае будет выведено соответствующее предупреждение.</p>

<p>Текущий файл зоны можно экспортировать в файл на вашем компьютере. Для этого перейдите на страницу редактирования зоны и кликните по ссылке «Экспорт зоны».  В списке выберите требуемый формат сохраняемого файла: bind, csv, nsd и нажмите кнопку «Export». Будет сформирован файл зоны в указанном формате и браузер отобразит интерфейс выбора места сохранения файла на компьютер (зависит от настроек вашего браузера).</p>

<a name="deletezone" id="deletezone"></a>
<h3>Удаление зон</h3>
<p>Чтобы удалить зону,  в списке зон раздела  «Зоны» в строке напротив удаляемой зоны поставьте галочку в столбце «Удалить» и нажмите кнопку «Удалить». Одновременно можно удалить несколько зон. ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при выборе удаляемых зон.</p>

<a name="templatezone" id="templatezone"></a>
<h3>Редактирование шаблона зон</h3>
<p>Чтобы отредактировать настройки шаблона зон, перейдите по ссылке «Настройки» в разделе «<a href="{@root_relative}/zone/">Зоны</a>». В форме на открывшейся странице внесите все необходимые изменения в нужные поля и нажмите кнопку «Сохранить». Новые значения по умолчания будут сохранены в РС ДНС и будут использоваться при создании новых зон.</p>

<a name="createzone" id="createzone"></a>
<h3>Генерация файлов зон</h3>
<p>В случае возникновения необходимости генерирования из первичных данных сразу всех файлов зон, можно воспользоваться функцией генерации файлов зон. Чтобы запустить генерацию файлов, перейдите в раздел «Зоны» и кликните по ссылке «Генерация файлов зон». Обратите внимание, что генерация файлов зон может занимать длительное время в зависимости от количества зон в системе. Когда процесс закончится, будет отображено сообщение «Все файлы зон сгенерированы».</p>

<a name="reversezone" id="reversezone"></a>
<h3>Обратные зоны</h3>
<p>Добавление обратной зоны происходит из учетной записи клиента. Чтобы добавить обратную зону, перейдите на страницу учетной записи клиента и кликните по ссылке «Добавить обратную зону».</p>
<p>Из выпадающего списка выберите нужное имя обратной зоны или введите имя в текстовое поле.</p>
<p>Имена обратных зон в выпадающем списке формируются на основании блоков IP-адресов, выделенных клиенту (список выделенных адресов находится в учетной записи клиента в поле «Блоки ip-адресов, выделенные клиенту»).</p>
<p>Соблюдайте формат задания имени зоны, например, 0/27.2.168.192, неверно: 0-27.2.168.192/0. (<a href="http://www.ietf.org/rfc/rfc2317.txt">RFC2317</a>)</p>
<p>Система проверяет выделенные клиентам диапазоны и стремится не допускать пересечений, при создании обратных зон. Если указанное вами имя обратной зоны пересекается с существующей сетью, зона не будет создана, система выведет предупреждение, например: «0/27.2.168.192 пересекается с существующей сетью 192.168.1.0/27».</p>

<a name="server" id="server"></a>
<h2>Сервера</h2>
<p>Управление серверами осуществляется в разделе «<a href="{@root_relative}/link/">Сервера</a>».</p>

<p>Чтобы добавить новый сервер, перейдите по ссылке «Добавить сервер». В форме на открывшейся странице укажите всю требуемую информацию:  имя сервера, путь к конфигурационному файлу, путь к папке файлов, IP-адрес и тип добавляемого сервера. После ввода всей информации нажмите кнопку «Добавить».</p>

<p>Чтобы отредактировать информацию об уже существующих серверах, кликните по их названию в списке серверов, внесите все необходимые изменения и нажмите кнопку «Сохранить».</p>

<p>Чтобы удалить ненужные сервера, кликните по ссылке «Удалить» рядом с выбранным сервером. ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при удалении серверов.</p>

<a name="backups" id="backups"></a>
<h2>Резервные копии</h2>
<p>В РС ДНС предусмотрена функция создания резервных копий всей информации, хранящейся в системе. Управление резервными копиями осуществляется в разделе «<a href="{@root_relative}/backup/">Резервные копии</a>».</p>

<p>Чтобы создать резервную копию системы, кликните по ссылке «Создать резервную копию». В форме на открывшейся странице выберите информацию, которую необходимо включить в резервную копию и нажмите кнопку «Создать резервную копию».</p>

<p>Созданные ранее резервные копии системы можно скачать на свой компьютер в виде архива файлов, либо восстановить систему из резервной копии вместо текущей. Чтобы скачать резервную копию системы, воспользуйтесь ссылкой «Скачать» напротив нужной версии. Чтобы восстановить систему из резервной копии, воспользуйтесь ссылкой «Восстановить» напротив нужной версии. ВНИМАНИЕ! Эту операцию невозможно отменить, система будет полностью переписана из резервной копии, поэтому будьте особенно внимательны при использовании этой функции.</p>

<p>Чтобы удалить ненужные резервные копии, поставьте галочки в столбце «Удалить» напротив удаляемых копий и нажмите кнопку «Удалить». ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при выборе удаляемых резервных копий системы.</p>

<a name="log" id="log"></a>
<h2>Логи</h2>
<p>РС ДНС записывает все действия пользователей в системе в логи, которые содержат максимально подробную доступную информацию о пользователях и их действиях. Работа с логами осуществляется в разделе «<a href="{@root_relative}/logger/">Логи</a>».</p>

<p>В разделе «Логи» предусмотрена функция поиска и фильтра списка логов по заданным параметрам. Чтобы применить фильтр, в полях над списком логов укажите даты записи логов (совершения действий пользователей в системе) и / или модуль, с которым совершались действия, и / или название пользователя и /или IP-адрес, с которого совершались действия. Указав все необходимые параметры фильтра, нажмите кнопку «Выбрать».</p>

<p>Чтобы просмотреть подробные данные о том или ином действии в системе, воспользуйтесь ссылкой «Посмотреть» напротив выбранного лога. Чтобы удалить ненужные логи, поставьте галочки в столбце «Удалить» нужных строк и нажмите кнопку «Удалить». ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при выборе удаляемых логов.</p>
			
			</div></td>
		</tr></table></div>
	</xsl:template>
	
</xsl:stylesheet>
