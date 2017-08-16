<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">
		<xsl:output encoding="utf-8" method="html" indent="yes" />
		
		<xsl:template name="window">
			<div class="modal hide fade" id="call" tabindex="-1" role="dialog">
				<xsl:apply-templates select="document('udata://webforms/add/125/')/udata">
					<xsl:with-param name="formType" select="'call'"/>
				</xsl:apply-templates>
			</div>
		</xsl:template>
		

</xsl:stylesheet>