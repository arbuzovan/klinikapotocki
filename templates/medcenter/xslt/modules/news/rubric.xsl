<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

		<xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="result[@module = 'news'][@method = 'rubric']">

		<xsl:apply-templates select="document(concat('udata://news/lastlents/', $pageId, '//999/1/'))/udata">
			<xsl:with-param name="page_id" select="$pageId" />
		</xsl:apply-templates>

		<xsl:apply-templates select="document(concat('udata://news/lastlist/', $pageId, '/'))/udata" />

	</xsl:template>

</xsl:stylesheet>