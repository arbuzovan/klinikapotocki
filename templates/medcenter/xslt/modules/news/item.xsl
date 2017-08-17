<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

	<xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="result[@module = 'news'][@method = 'item']">
		<article>
			<h1 umi:element-id="{$pageId}" umi:field-name="h1">
				<xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
			</h1>
			<xsl:if test="//property[@name = 'publish_pic']/value">
				<a class="fancybox" href="{//property[@name = 'publish_pic']/value}">
					<img style="float: left;" alt="{.//property[@name = 'h1']/value}" src="{document(concat('udata://system/makeThumbnail/(',substring(//property[@name = 'publish_pic']/value,2),')/300/(auto)'))//src}"/>
				</a>
			</xsl:if>

			<div class="content" umi:element-id="{$pageId}" umi:field-name="content" umi:empty="введите содержание страницы">
				<xsl:value-of select=".//property[@name = 'content']/value" disable-output-escaping='yes'/>
			</div>

			<div class="clear"></div>
		</article>
	</xsl:template>

	<!-- DOCTOR PAGE -->
	<xsl:template match="result[@module = 'news'][@method = 'item'][substring(//parents/page/@link, 1, 8) = '/doctors']">
		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
		</h1>
		<div class="row">
			<div class="col-md-5 col-sm-5">
				<xsl:if test="//property[@name = 'publish_pic']/value">
					<a class="fancybox m-w80" href="{//property[@name = 'publish_pic']/value}">
						<img class="full portrait m-w80" alt="{.//property[@name = 'h1']/value}" src="{document(concat('udata://system/makeThumbnail/(',substring(//property[@name = 'publish_pic']/value,2),')/450/(auto)'))//src}"/>
					</a>
				</xsl:if>
			</div>
			<div class="col-md-7 col-sm-7">
				<!-- ORDER FORM -->
	            <div class="window-wrap">
	                <a id="order-window" class="btn stethoscope" umi:object-id="{$conf//object/@id}" umi:field-name="form_title">
	                	<xsl:value-of select="$conf//property[@name='form_title']/value" />
	            	</a>

	                <div class="btn-close"></div>

	                <div class="border form" style="border-color:{$conf//property[@name='border-color']/value}">
	                	<xsl:apply-templates select="document('udata://webforms/add/medcenter/')//udata" >
	                		<xsl:with-param name="formType" select="'window'"/>
	                	</xsl:apply-templates>
	                </div>
	            </div>

			    <div class="dictor-badge">
			        <div class="badge-img" umi:object-id="{$conf//object/@id}" umi:field-name="badge_img">
						<img alt="{$conf//property[@name='company_name']/value}" src="{document(concat('udata://system/makeThumbnail/(',substring($conf//property[@name='badge_img']/value, 2),')/74/(auto)'))//src}"/>
			        </div>

			        <div class="doctor-name">
			            <span umi:element-id="{$pageId}" umi:field-name="h1">
			            	<xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
			            </span>
			        </div>

			        <div class="doctor-specialization" umi:element-id="{$pageId}" umi:field-name="doctor_specialization">
						<xsl:value-of select="//property[@name='doctor_specialization']/value"/>
			        </div>

			        <div class="content" umi:element-id="{$pageId}" umi:field-name="anons">
			            <xsl:value-of select="//property[@name='anons']/value" disable-output-escaping='yes'/>
			        </div>
			    </div>
			</div>
		</div>

		<!-- DOCTOR INFO -->
		<xsl:for-each select="//group[@name='custom']/property[@type='wysiwyg']">
			<div class="caption gray-bg">
				<xsl:value-of select="./title"/>
			</div>
			<div class="content" umi:element-id="{$pageId}" umi:field-name="{./@name}">
				<xsl:value-of select="./value" disable-output-escaping='yes'/>
			</div>
		</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>