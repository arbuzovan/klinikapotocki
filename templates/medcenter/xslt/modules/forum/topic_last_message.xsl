<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi">

	<xsl:template match="udata[@module = 'forum'][@method = 'topic_last_message']">
		<a href="{@link}" umi:empty="Название сообщения" umi:field-name="name" umi:element-id="{@id}"><xsl:value-of select="@name" /></a>
		<xsl:apply-templates select="document(concat('upage://',@id))/udata" mode="last_message" />
	</xsl:template>

</xsl:stylesheet>