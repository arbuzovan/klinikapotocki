<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

	<xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:variable name="user-type" select="/result/user/@type" />
	<!-- <xsl:include href="window.xsl" /> -->
	<xsl:include href="library/__common.xsl" />
	<xsl:include href="modules/__common.xsl" />
	<xsl:include href="sitemapnew.xsl" />
</xsl:stylesheet>