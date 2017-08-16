<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi">

	<xsl:template match="result[@module = 'catalog'][@method = 'category']">
		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:value-of select="document(concat('upage://', $pageId))/udata//property[@name = 'h1']/value"/>
		</h1>

		<xsl:apply-templates select="document(concat('udata://catalog/getCategoryList//', @id))/udata" />
		

		<xsl:apply-templates select="document(concat('udata://catalog/getSmartCatalog//', $activitiesId,'/9/'))/udata" />
	</xsl:template>

</xsl:stylesheet>