<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="result[@module = 'users'][@method = 'restore']">
		<div id="page_auth"><xsl:apply-templates select="document(concat('udata://users/restore/',$param0,'/'))/udata" /></div>
	</xsl:template>

	<xsl:template match="udata[@module = 'users'][@method = 'restore'][@status = 'success']">
		<p>
			<xsl:text>&forget-message;</xsl:text>
		</p>
		<!--div>
			<p><xsl:text>&login;: </xsl:text>	<xsl:value-of select="login" /></p>
			<p><xsl:text>&password;: </xsl:text> <xsl:value-of select="password" /></p>
		</div-->
	</xsl:template>

	<xsl:template match="udata[@module = 'users'][@method = 'restore'][@status = 'fail']">
		<xsl:text>&activation-error;</xsl:text>
	</xsl:template>

</xsl:stylesheet>