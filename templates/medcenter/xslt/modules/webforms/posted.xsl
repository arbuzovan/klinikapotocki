<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

    <xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:param name="template" />

	<xsl:template match="result[@module = 'webforms'][@method = 'posted']">
		<div>
			<xsl:apply-templates select="document(concat('udata://webforms/posted/', $template,'/'))/udata" />
		</div>
	</xsl:template>

	<xsl:template match="udata[@module = 'webforms'][@method = 'posted']">
		<xsl:value-of select="." disable-output-escaping="yes" />
	</xsl:template>

</xsl:stylesheet>