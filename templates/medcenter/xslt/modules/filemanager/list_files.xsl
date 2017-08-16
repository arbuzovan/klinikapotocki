<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

	<xsl:template match="result[@module = 'filemanager'][@method = 'list_files']">

	</xsl:template>

	<xsl:template match="udata[@module = 'filemanager'][@method = 'list_files']">
		<ul class="properties-list files-list margin40" umi:add-method="popup" umi:sortable="sortable" umi:method="menu" umi:module="content" umi:element-id="{../@id}">
			<xsl:apply-templates select="items" mode="list_files" />
		</ul>
	</xsl:template>

	<xsl:template match="items" mode="list_files">
		<xsl:variable name="file_type" select="document(concat('upage://', @id,'.fs_file'))//value/@ext"/>
		<!-- <xsl:sort order="ascending" select="@name"/> -->

		<li>
			<a class="doc" href="{@link}" umi:element-id="{@id}" umi:region="row" umi:field-name="name" umi:empty="&empty-section-name;" umi:delete="delete">
				<xsl:attribute name="class">
					<xsl:choose>
						<xsl:when test="($file_type = 'doc') or ($file_type='docx')">
							doc
						</xsl:when>
						<xsl:when test="$file_type = 'pdf'">
							pdf
						</xsl:when>
						<xsl:when test="($file_type = 'xls') or ($file_type='xlsx')">
							xls
						</xsl:when>
						<xsl:when test="($file_type = 'rtf')">
							rtf
						</xsl:when>
						<xsl:when test="($file_type = 'ppt') or ($file_type = 'pptx')">
							ppt
						</xsl:when>
						<xsl:otherwise>
							file
						</xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
				<xsl:value-of select="@name"/>
			</a>
			<span class="doc_prop">
				<xsl:text>(</xsl:text>
				<span class="uppercase">
					<xsl:value-of select="$file_type"/>
				</span>
				<xsl:text>, </xsl:text>
				<xsl:value-of select="document(concat('udata://filemanager/shared_file//', @id))/udata//file_size"/>
				<xsl:text> кб.)</xsl:text>
			</span>
		</li>
	</xsl:template>

</xsl:stylesheet>