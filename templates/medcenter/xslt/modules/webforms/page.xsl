<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

    <xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="result[@module = 'webforms'][@method = 'page']">
		<xsl:apply-templates select="document(concat('udata://webforms/add/', //property[@name = 'form_id']/value))/udata" />
	</xsl:template>

</xsl:stylesheet>