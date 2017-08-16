<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi">

	<xsl:template match="udata[@module = 'catalog'][@method = 'getCategoryList']"/>

	<xsl:template match="udata[@module = 'catalog'][@method = 'getCategoryList'][total]">
	
			
		<ul class="tile-3 xs-c" umi:add-method="popup"
			umi:sortable="sortable"
			umi:method="menu"
			umi:module="content"
			umi:element-id="{$activitiesId}">
			<xsl:apply-templates select=".//items/item" mode="activities"/>
		</ul>

		<xsl:apply-templates select="document(concat('udata://system/numpages/', total, '/', per_page, '/'))/udata">
			<xsl:with-param name="numpages" select="ceiling(total div per_page)" />
		</xsl:apply-templates>
		
	</xsl:template>


<!-- 	<xsl:template match="udata[@module = 'catalog' and @method = 'getCategoryList']//item">
		<div>
			<a href="{@link}"><xsl:value-of select="." /></a>
		</div>
	</xsl:template> -->
</xsl:stylesheet>