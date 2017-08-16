<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

	<xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="result[@module = 'photoalbum'][@method = 'photo']">
		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:value-of select="//property[@name='h1']/value"/>
		</h1>

		<a href="{//property[@name='photo']/value}" class="fancybox gallery_photo">
			<img class="full-max" alt="{document(concat('upage://', @id, '.h1'))/udata//value}" src="{document(concat('udata://system/makeThumbnail/(',substring(//property[@name='photo']/value,2),')/400/(auto)'))//src}"/>
		</a>

		<!-- <xsl:apply-templates select="document(concat('udata://photoalbum/album/',page/@parentId,'//1000'))/udata/items/item[@id = $pageId]" mode="slider" /> -->


		<div class="content">
			<xsl:value-of select="//property[@name = 'descr']/value" disable-output-escaping="yes" />
		</div>

		<br/>

		<a class="btn blue" href="{parents/page[last()]/@link}">
			<xsl:choose>
				<xsl:when test="$pageId = $documentsId">Все сертификаты</xsl:when>
				<xsl:otherwise>Показать все фотографии</xsl:otherwise>
			</xsl:choose>
		</a>
	</xsl:template>

</xsl:stylesheet>