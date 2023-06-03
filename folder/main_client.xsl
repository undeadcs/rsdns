<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes"/>
	
	<xsl:template match="@*">
		<xsl:value-of select="name()"/>(<xsl:value-of select="."/>)
	</xsl:template>
	
	<xsl:template match="Error">
		<p><b><xsl:value-of select="@text"/></b></p>
	</xsl:template>
	
	<xsl:template match="CMenu">
		<div class="menu_wrap"><table><tr>
			<td class="menu_lcol"><div class="menu"><!--table><tr-->
			<div class="clear">&#160;</div>
			<xsl:for-each select="*[ position( ) &lt; 4 ]">
				<!--td class="c1"-->
				<xsl:choose>
					<xsl:when test="@flags = 2"><!-- текущий элемент выбран -->
						<span>
						<xsl:attribute name="id">cl_menu_item<xsl:value-of select="position( )"/></xsl:attribute>
						<xsl:value-of select="@title"/></span>
					</xsl:when>
					<xsl:when test="@flags = 3"><!-- выбран дочерний элемент текущего -->
						<a href="{@url}" class="cur">
						<xsl:attribute name="id">cl_menu_item<xsl:value-of select="position( )"/></xsl:attribute>
						<xsl:value-of select="@title"/></a>
					</xsl:when>
					<xsl:otherwise>
						<a href="{@url}">
						<xsl:attribute name="id">cl_menu_item<xsl:value-of select="position( )"/></xsl:attribute>
						<xsl:value-of select="@title"/></a>
					</xsl:otherwise>
				</xsl:choose>
				<!--/td-->
			</xsl:for-each>
			<div class="clear">&#160;</div>
			<!--/tr></table--></div></td>
			<td class="cl_menu_rcol">
			<xsl:for-each select="*[ position( ) &gt; 3 ]">
				<xsl:choose>
					<xsl:when test="@flags = 2"><!-- текущий элемент выбран -->
						<span>
						<xsl:attribute name="id">cl_menu_item<xsl:value-of select="position( ) + 5"/></xsl:attribute>
						<xsl:value-of select="@title"/></span>
					</xsl:when>
					<xsl:when test="@flags = 3"><!-- выбран дочерний элемент текущего -->
						<a href="{@url}" class="cur">
						<xsl:attribute name="id">cl_menu_item<xsl:value-of select="position( ) + 5"/></xsl:attribute>
						<xsl:value-of select="@title"/></a>
					</xsl:when>
					<xsl:otherwise>
						<a href="{@url}">
						<xsl:attribute name="id">cl_menu_item<xsl:value-of select="position( ) + 5"/></xsl:attribute>
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
	
	<xsl:template match="ClientZoneAdd">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Регистрация доменных имен</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
		<xsl:choose>
			<xsl:when test="@blocked">
		<p>Доступ к сервисам заблокирован администратором системы. Вы не можете регистрировать домены, редактировать зоны, редактировать свои персональные данные.</p>
			</xsl:when>
			<xsl:when test="@noadd">
		<p>Вы не можете добавлять зоны. Вы можете редактировать зоны, редактировать свои персональные данные.</p>
			</xsl:when>
			<xsl:otherwise>
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
		<p>Имя зоны: <b><xsl:value-of select="Zone[1]/@zone_name"/></b></p>
		<form action="{@base_url}/add_zone/" method="post">
			<p><input type="submit" class="sendquery" name="ok" value="Зарегистрировать"/></p>
			<input type="hidden" name="zone_name" value="{Zone[1]/@zone_name}"/>
		</form>
			</xsl:when>
			<xsl:otherwise>
		<form action="{@base_url}/add_zone/" method="post"><table>
			<tr>
				<td class="lbl"><div>Имя зоны:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_name" value="{Zone[1]/@zone_name}"/></div></td>
			</tr>
			<tr>
				<td class="lbl">&#160;</td>
				<td class="inp"><div><input value="Добавить" type="submit"/></div></td>
			</tr>
		</table></form>
			</xsl:otherwise>
		</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ClientZoneReg">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Регистрация доменных имен</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
			<h2>Зона добавлена</h2>
			<xsl:for-each select="Zone[1]">
				<p>Зона успешно добавлена. В <a href="{../@base_url}/zone/">список ваших зон</a> внесена новая зона.</p>
				<p>Перейти к <a href="{../@base_url}/zone/{@zone_id}/">редактированию зоны</a>.</p>
			</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ClientRegDomain">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Регистрация доменных имен</h1>
				<div class="vmenu">
					<a href="{@base_url}/add_zone/">Добавление зоны</a>
				</div>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
		<xsl:choose>
			<xsl:when test="@blocked">
		<p>Доступ к сервисам заблокирован администратором системы. Вы не можете регистрировать домены, редактировать зоны, редактировать свои персональные данные</p>
			</xsl:when>
			<xsl:when test="@noreg">
		<p>Вы не можете регистрировать домены. Вы можете редактировать зоны, редактировать свои персональные данные</p>
			</xsl:when>
			<xsl:otherwise>
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
		<p>Домен: <b><xsl:value-of select="Zone[1]/@zone_name"/></b>&#160;свободен.</p>
		<form action="{@base_url}/" method="post">
			<p><input type="submit" class="sendquery" name="ok" value="Зарегистрировать"/></p>
			<input type="hidden" name="zone_name" value="{Zone[1]/@zone_name}"/>
		</form>
			</xsl:when>
			<xsl:otherwise>
		<form action="{@base_url}/" method="get"><table>
			<tr>
				<td class="lbl"><div>Доменное имя:</div></td>
				<td class="inp"><div><input type="text" class="text" name="zone_name" value="{Zone[1]/@zone_name}"/></div></td>
			</tr>
			<tr>
				<td class="lbl">&#160;</td>
				<td class="inp"><div><input value="Проверить" type="submit"/></div></td>
			</tr>
		</table></form>
			</xsl:otherwise>
		</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ClientDomainReg">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Регистрация доменных имен</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
			<h2>Домен зарегистрирован</h2>
			<xsl:for-each select="Zone[1]">
				<p>Домен успешно зарегистрирован. В <a href="{../@base_url}/zone/">список ваших зон</a> внесена новая зона, связанная с этим доменом.</p>
				<p>Перейти к <a href="{../@base_url}/zone/{@zone_id}/">редактированию зоны</a>.</p>
			</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ClientZoneList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Зоны, доступные для редактирования</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
		<div class="cl_zone_list">
		<table>
		<xsl:for-each select="Zone">
			<tr>
				<td><div><xsl:value-of select="@zone_name"/></div></td>
				<td><div><a class="zone_edit" href="{../@base_url}/{@zone_id}/"><span class="l1"><span class="l2">редактировать</span></span></a></div></td>
			</tr>
		</xsl:for-each>
		</table>
		</div>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ClientAccount">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Персональные данные</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
		<xsl:apply-templates select="Error"/>
		<xsl:for-each select="Client[1]">
<xsl:choose>
	<xsl:when test="../@blocked">
<!-- просмотр персональных данных -->
	<div class="x9"><table>
		<tr class="top"><td class="l">&#160;</td><td class="c">
			<span>Имя контактного лица</span>
		</td><td class="r">&#160;</td></tr>
		<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
		
		<table>
			<tr>
				<td class="lbl"><div>Имя:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_first_name"/>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Фамилия:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_last_name"/>
				</div></td>
			</tr>
		</table>
		
		</div></td><td class="r">&#160;</td></tr>
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
					<xsl:value-of select="@client_full_name"/>
					<span class="info">Полное наименование организации-администратора домена на русском языке в соответствии с учредительными документами. Для нерезидентов РФ допускается написание на национальном языке (либо на английском языке). Например, Закрытое Акционерное Общество "Новое время"</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Организация (латиницей):</div></td>
				<td class="txt"><div>
					<xsl:value-of select="@client_full_name_en"/>
					<span class="info">Например, New Time Co Ltd.</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>ИНН:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_inn"/>
					<span class="info">Например, 7701107259</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>КПП:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_kpp"/>
					<span class="info">Например, 632946014</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Юридический адрес:</div></td>
				<td class="txt"><div>
					<xsl:value-of select="@client_addr"/>
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
					<xsl:value-of select="@client_country"/>
					<span class="info">Укажите двубуквенный код страны латинским буквами в соответствии со стандартом <a href="http://ru.wikipedia.org/wiki/ISO_3166-1">ISO 3166-1 alpha 1</a></span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>E-mail:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_email"/>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Телефон:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_phone"/>
					<span class="info">Например, +7 495 8102233</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Факс:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_fax"/>
					<span class="info">Например, +7 3432 811221</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Индекс:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_postcode"/>
					<span class="info">Например, 101000</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Область:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_region"/>
					<span class="info">Например, Московская обл.</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Город, населенный пункт:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_city"/>
					<span class="info">Например, Москва</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Улица, дом, офис:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_street"/>
					<span class="info">Например, ул. Ленина, д. 13а, оф. 222</span>
				</div></td>
			</tr>
			<tr>
				<td class="lbl"><div>Получатель:</div></td>
				<td class="inp"><div>
					<xsl:value-of select="@client_person"/>
					<span class="info">Например, Сергеев Виталий Павлович</span>
				</div></td>
			</tr>
		</table></div></td><td class="r">&#160;</td></tr>
		<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
	</table></div>

<xsl:if test="string-length(@client_add_inf) &gt; 0">
<div class="client_end">
	<table>
		<tr>
			<td class="lbl"><div>Дополнительная информация:</div></td>
			<td class="txt"><div><xsl:value-of select="@client_add_info"/></div></td>
		</tr>
	</table>
</div>
</xsl:if>
	</xsl:when>
	<xsl:otherwise>
<!-- редактирование персональных данных -->
<form action="{../@base_url}/" method="post">
	<div class="x9"><table>
		<tr class="top"><td class="l">&#160;</td><td class="c">
			<span>Настройки доступа</span>
		</td><td class="r">&#160;</td></tr>
		<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
		
		<table>
			<tr>
				<td class="lbl"><div>Пароль:</div></td>
				<td class="inp"><div><input type="password" class="text" name="client_password" value="" autocomplete="off"/></div></td>
			</tr>
		</table>
		
		</div></td><td class="r">&#160;</td></tr>
		<tr class="bot"><td class="l">&#160;</td><td class="c">&#160;</td><td class="r">&#160;</td></tr>
	</table></div>
	
	<div class="x9"><table>
		<tr class="top"><td class="l">&#160;</td><td class="c">
			<span>Имя контактного лица</span>
		</td><td class="r">&#160;</td></tr>
		<tr class="mid"><td class="l">&#160;</td><td class="c"><div class="x9_cont">
		
		<table>
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
		</table>
		
		</div></td><td class="r">&#160;</td></tr>
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
					<span class="info">Например, Закрытое Акционерное Общество "Новое время"</span>
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
			<td class="lbl"><div>&#160;</div></td>
			<td class="sbm"><div><input type="submit" class="sendquery" value="Сохранить"/></div></td>
		</tr>
	</table>
	</div>
</form>
	</xsl:otherwise>
</xsl:choose>
		</xsl:for-each>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ClientZoneEdit">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Редактор файлов зон</h1>
			
			<div class="vmenu">
				<a href="{@base_url}/{Zone[1]/@zone_id}/old/">Сохраненные версии файла зоны</a>
				<a href="{@base_url}/{Zone[1]/@zone_id}/upload/">Загрузка файла зоны с вашего компьютера</a>
				<a href="{@base_url}/{Zone[1]/@zone_id}/export/">Экспорт зоны</a>
			</div>
			
			</div></td>
			<td class="ccol"><div class="content">
			
		<h2>
		<xsl:choose>
			<xsl:when test="@locked = 0">
				Редактирование
			</xsl:when>
			<xsl:otherwise>
				Просмотр
			</xsl:otherwise>
		</xsl:choose>
		зоны <xsl:value-of select="Zone[1]/@zone_name"/></h2>
		<xsl:for-each select="Zone[1]">
		
		<xsl:if test="../@locked = 1">
			<p>Зона заблокирована от редактирования</p>
		</xsl:if>
		
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
			<!-- -->
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
	
	<xsl:template match="ClientZoneAddRR">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Редактор файлов зон</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
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
				<input type="submit" value="Дальше"/>
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
	
	<xsl:template match="ClientZoneOldList">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Редактор файлов зон</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
		<h2>Сохраненные версии файла зоны <xsl:value-of select="Zone[1]/@zone_name"/></h2>
		<p><a href="{@base_url}/{Zone[1]/@zone_id}/">&amp;larr; Текущая версия</a> файла зоны</p>
		<div class="block4-5">
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
		</div>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
	<xsl:template match="ClientZoneOldView">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Редактор файлов зон</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
		<h2>Просмотр сохраненного файла зоны <xsl:value-of select="Zone[1]/@zone_name"/></h2>
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
	
	<xsl:template match="ClientZoneUpload">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Редактор файлов зон</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
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
	
	<xsl:template match="ClientZoneExport">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Редактор файлов зон</h1>
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
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
	
	<xsl:template match="ClientHelp">
		<div class="container"><table><tr>
			<td class="lcol"><div class="lcont">
				<h1>Помощь</h1>
				
<ul>
<li><a href="#regdomain">Регистрация доменов</a></li>
<li><a href="#addzone">Добавление зоны</a></li>
<li><a href="#regdomain">Редактирование файлов зон</a>
<ul>
<li><a href="#textzone">Редактирование зоны в текстовом виде</a></li>
<li><a href="#editsource">Редактирование ресурсных записей</a></li>
<li><a href="#historyzone">Сохраненные версии зоны</a></li>
<li><a href="#importzone">Импорт и экспорт файлов зон</a></li>
</ul>
</li>
<li><a href="#user">Персональные данные</a></li>
</ul>
				
			</div></td>
			<td class="ccol"><div class="content cl_content">
			
<a name="regdomain" id="regdomain"></a>
<h2>Регистрация доменов</h2>
<p>Для того, чтобы отправить заявку на регистрацию домена, необходимо выполнить следующие действия:</p>
<ol>
<li>Перейти в раздел «<a href="{@root_relative}/">Регистрация доменов</a>».</li>
<li>Ввести в текстовое поле формы доменное имя и нажать на кнопку «Проверить».</li>
<li>Если указанное доменное имя занято, будет выведено сообщение: «Доменное имя занято». После этого можно проверить другое доменное имя.</li>
<li>Если указанное доменное имя свободно, будет показана ссылка «Зарегистрировать». Для продолжения регистрации домена необходимо перейти по этой ссылке, а затем нажать на кнопку «Зарегистрировать».</li>
<li>После нажатия на кнопку «Зарегистрировать» введенные данные будут отправлены регистратору, по существующему шаблону будет создана зона с указанным доменным именем, отобразится сообщение об отправке заявки на регистрацию домена регистратору. Доступ к редактированию созданной зоны будет заблокирован до подтверждения со стороны регистратора (делегирования домена).</li>
<li>В случае возникновения ошибок, будет выведено сообщение о невозможности отправки заявки и предложено начать регистрацию заново.</li>
</ol>

<a name="addzone" id="addzone"></a>
<h2>Добавление зоны</h2>
<p>Если у вас уже зарегистрирован домен и вы хотите перенести его, вы может создать зону без регистрации домена.</p>
<p>Для добавления зоны без регистрации  
домена, выполните следующие действия:</p>
<ol>
<li>Перейдите в раздел “Регистрация  
доменов”.</li>
<li>В меню слева нажмите на ссылку  
“Добавление зоны”.</li>
<li>В текстовом поле укажите домен,  
который хотите перенести.</li>
<li>Нажмите кнопку “Добавить”.</li>
</ol>

<p>В системе будет создана зона, которую  
при необходимости вы сможете  
отредактировать в разделе “Редактор  
файлов зон”.</p>

<a name="zone" id="zone"></a>
<h2>Редактирование файлов зон</h2>
<p>Управление зонами осуществляется в разделе «<a href="{@root_relative}/zone/">Редактор файлов зон</a>».</p>

<a name="editzone" id="editzone"></a>
<h3>Редактирование зон</h3>
<p>Чтобы отредактировать зону, выберите ее из списка и перейдите по ссылке «Редактировать» в строке выбранной зоны. В форме на открывшейся странице внесите необходимые изменения в поля зоны и нажмите кнопку «Сохранить и загрузить файл на сервер», чтобы применить все сделанные изменения. Предыдущая версия зоны будет доступна в разделе «Сохраненные версии файла зоны». </p>

<p>Можно также после внесения изменений сохранить их во временный файл без применения, а затем продолжить редактирование параметров зоны. Обратите внимание, что в этом случае до нажатия кнопки «Сохранить и загрузить файл на сервер» внесенные изменения не будут применены и будут сохранены лишь во временном файле.</p>

<p>Чтобы выйти из редактирования зоны без сохранения сделанных изменений, перейдите по ссылке «Вернуться к списку без сохранения».</p>

<a name="textzone" id="textzone"></a>
<h4>Редактирование зоны в текстовом виде</h4>
<p>Файл зоны можно также отредактировать в текстовом виде. Для этого перейдите на страницу редактирования зоны и кликните по ссылке «Редактировать в текстовом виде». Внесите все необходимые изменения и нажмите кнопку «Сохранить». Если введенные изменения корректны, вы перейдете на страницу редактирования зоны, в ином случае будет выведено соответствующее предупреждение. После успешного сохранения, внесенные изменения будут применены, а предыдущая версия файла зоны будет доступна в разделе «Сохраненные версии файла зоны».</p>

<a name="editsource" id="editsource"></a>
<h4>Редактирование ресурсных записей</h4>
<p>В системе предусмотрена возможность добавлять ресурсные записи зоны. Чтобы добавить ресурсные записи зоны перейдите на страницу редактирования зоны. В списке ресурсных записей кликните по ссылке «Добавить», затем из выпадающего списка выберите тип ресурсной записи: NS, A, CNAME, MX, PTR, SRV, AAAA, TXT и нажмите кнопку «Дальше».</p>

<p>В форме на открывшейся странице будут отображены поля, соответствующие выбранному типу записи. Введите в поля соответствующие значения и нажмите на кнопку «Добавить». Если значения введены корректно, вы перейдете на страницу редактирования зоны, в ином случае будет выведено соответствующее предупреждение.</p>

<p>Чтобы удалить ненужные ресурсные записи зоны, перед сохранением поставьте галочки в столбце «Удалить» напротив удаляемых ресурсных записей. ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при выборе удаляемых ресурсных записей.</p>

<a name="historyzone" id="historyzone"></a>
<h4>Сохраненные версии зоны</h4>
<p>При внесении и применении любых изменений в файл зоны, предыдущие версии файла сохраняются в системе для того, чтобы затем их можно было просмотреть, при необходимости загрузить, либо удалить.</p>

<p>Чтобы просмотреть список версий файла конкретной зоны, перейдите на страницу редактирования нужной зоны, а затем кликните по ссылке «Сохраненные версии файла зоны». Вам будет представлена подробная информация о предыдущих версиях файла зоны.</p>

<p>При помощи управляющих ссылок на данной страницы можно просмотреть интересующие файлы зоны, либо загрузить их вместо текущей — при этом заменяемый файл зоны будет сохранен в списке предыдущих версий файлов зоны.</p>

<p>Чтобы удалить ненужные версии файлов зоны, поставьте галочки в столбце «Удалить» напротив удаляемых версий файлов зоны и нажмите кнопку «Удалить». ВНИМАНИЕ! Эту операцию невозможно отменить, поэтому будьте особенно внимательны при выборе удаляемых версий файлов зоны.</p>

<a name="importzone" id="importzone"></a>
<h3>Импорт и экспорт файлов зон</h3>
<p>В РС ДНС предусмотрена возможность импорта файла зоны с компьютера пользователя. Для того чтобы заменить текущий файл зоны файлом, хранящимся на компьютере пользователя, необходимо перейти на страницу редактирования зоны и кликнуть по ссылке «Загрузка файла зоны с вашего компьютера».</p>

<p>При помощи формы на открывшей странице необходимо указать расположение файла зоны на вашем компьютере. Если указанный файл является корректным файлом зоны, вы перейдете на страницу редактирования зоны, в ином случае будет выведено соответствующее предупреждение.</p>

<p>Текущий файл зоны можно экспортировать в файл на вашем компьютере. Для этого перейдите на страницу редактирования зоны и кликните по ссылке «Экспорт зоны».  В списке выберите требуемый формат сохраняемого файла: bind, csv, nsd и нажмите кнопку «Export». Будет сформирован файл зоны в указанном формате и браузер отобразит интерфейс выбора места сохранения файла на компьютер (зависит от настроек вашего браузера).</p>

<a name="user" id="user"></a>
<h2>Персональные данные</h2>
<p>Чтобы изменить персональные данные вашей учетной записи, перейдите в раздел «<a href="{@root_relative}/account/">Персональные данные</a>». В форме на этой странице можно внести требуемые изменения в вашу регистрационную информацию. После завершения редактирования информации, нажмите кнопку «Сохранить». Если все внесенные изменения корректны, они будут сохранены. При возникновении ошибок будут выведены сообщения об обнаруженных ошибках и будет предложено их исправить.</p>
		
			</div></td>
		</tr></table></div>
	</xsl:template>
	
</xsl:stylesheet>