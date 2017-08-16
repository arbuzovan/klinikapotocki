<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

    <xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="udata[@module = 'webforms'][@method = 'add']">
		<xsl:param name="formType" select="$formType"/>
		<xsl:variable name="formId" select="./@form_id"/>

		<form method="post" action="/webforms/send/" id="medcenter">
			<div class="row">
				<xsl:apply-templates select="items" mode="address" />
				<xsl:apply-templates select="groups/group/field" mode="webforms" >
					<xsl:with-param name="formType" select="$formType"/>
				</xsl:apply-templates>
				<input type="hidden" name="system_form_id" value="{/udata/@form_id}" />
				<input type="hidden" name="ref_onsuccess" value="/webforms/posted/{/udata/@form_id}" />

				<xsl:apply-templates select="document('udata://system/captcha/')/udata" >
					<xsl:with-param name="formType" select="$formType"/>
				</xsl:apply-templates>
				
				<div class="col-md-12">
				<div class="checkbox">
					<lable umi:object-id='{$conf//object/@id}' umi:field-name='agreement'>
				       <input type='checkbox' required="required" checked="checked"/><xsl:value-of select="$conf//property[@name='agreement']/value" disable-output-escaping="yes"/>
			        </lable>
			    </div>
			    </div>		 

	            <div class="col-md-4 text-right xs-c sm-c">
	            	<xsl:if test="$formType = 'window'">
	            		<xsl:attribute name="class">col-md-12 text-center xs-c sm-c</xsl:attribute>
	            	</xsl:if>
		       		<input type="reset" class="hidden"/>
	                <input class="btn ajax-send" type="submit" value="Отправить заявку"/>
	            </div>
			</div>

			<noindex>
				<div class="alert alert-success success-msg">
					<div class="h3">
						<xsl:apply-templates select="document(concat('udata://webforms/posted/', $formId,'/'))/udata" />
					</div>
				</div>

				<div class="alert alert-danger captcha-msg">
					<div class="h3">Введен неверный код с картинки!</div>
				</div>

				<div class="alert alert-danger date-msg">
					<div class="h3">Выбранная Вами дата уже прошла.</div>
				</div>

				<div class="alert alert-danger date-format-msg">
					<div class="h3">Формат даты, введенной Вами некорректен.<br/>Правильный формат: ДД.ММ.ГГГГ</div>
				</div>
			</noindex>
		</form>
	</xsl:template>

	<xsl:template match="field" mode="webforms">
		<xsl:param name="formType" select="$formType"/>
		<div class="col-md-6">
			<xsl:if test="@name = 'order_text' or $formType = 'window'">
				<xsl:attribute name="class">col-md-12</xsl:attribute>
			</xsl:if>
			<xsl:if test="@name = 'order_current_date'">
				<xsl:attribute name="class">hidden current-date</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="." mode="webforms_input_type" />
		</div>
	</xsl:template>


	<xsl:template match="field" mode="webforms_input_type">
		<input type="text" name="{@input_name}" placeholder="{@title}">
			<xsl:if test="@required = 'required'">
				<xsl:attribute name="required">required</xsl:attribute>
			</xsl:if>
			<xsl:choose>
				<xsl:when test="@name = 'order_name'">
					<xsl:attribute name="pattern">^[а-яёА-ЯЁa-zA-Z\s]{2,20}$</xsl:attribute>
				</xsl:when>
				<xsl:when test="@name = 'order_phone'">
					<xsl:attribute name="pattern">^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$</xsl:attribute>
					<xsl:attribute name="id">
						<xsl:value-of select="./@name"/>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@name = 'order_date'">
					<xsl:attribute name="class">hasdatepicker</xsl:attribute>
				</xsl:when>
				<xsl:when test="@name = 'order_current_date'">
					<xsl:attribute name="value">
						<xsl:value-of select="document('udata://system/convertDate/now/(m.d.Y)')/udata" />
					</xsl:attribute>
				</xsl:when>
				<xsl:otherwise></xsl:otherwise>
			</xsl:choose>
		</input>
	</xsl:template>


	<xsl:template match="field[@type = 'text']" mode="webforms_input_type">
		<textarea name="{@input_name}">
			<xsl:if test="./@name = 'order_text'">
				<xsl:attribute name="placeholder">Причина обращения</xsl:attribute>
			</xsl:if>
		</textarea>

	</xsl:template>

	<xsl:template match="field[@name = 'order_activities']" mode="webforms_input_type">
		<select name="{@input_name}" class="chosen">
			<option value="">Выберите направление</option>
			<xsl:apply-templates select="document(concat('usel://getCatalog/', $activitiesId))//udata//page" mode="option" />

		</select>
	</xsl:template>

	 <xsl:template match="page" mode="option">
		<option value="{./name}">
			<xsl:if test="(@id = $pageId) or (@id = $parentId)">
				<xsl:attribute name="selected"> </xsl:attribute>
			</xsl:if>
			<xsl:value-of select="./name"/>
		</option>
	</xsl:template>

	<xsl:template match="field" mode="webforms_required" />

	<xsl:template match="field[@required = 'required']" mode="webforms_required">
		<xsl:attribute name="class">
			<xsl:text>required</xsl:text>
		</xsl:attribute>
	</xsl:template>

	<xsl:template match="items" mode="address">
		<xsl:apply-templates select="item" mode="address" />
	</xsl:template>

	<xsl:template match="item" mode="address">
		<input type="hidden" name="system_email_to" value="{@id}" />
	</xsl:template>

	<xsl:template match="items[count(item) &gt; 1]" mode="address">
		<xsl:choose>
			<xsl:when test="count(item[@selected='selected']) != 1">
				<div class="form_element">
					<label class="required">
						<span><xsl:text>Кому отправить:</xsl:text></span>
						<select name="system_email_to">
							<option value=""></option>
							<xsl:apply-templates select="item" mode="address_select" />
						</select>
					</label>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="item[@selected='selected']" mode="address" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="item" mode="address_select">
		<option value="{@id}"><xsl:apply-templates /></option>
	</xsl:template>

</xsl:stylesheet>