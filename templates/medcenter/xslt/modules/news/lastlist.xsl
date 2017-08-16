<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">
	<xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="udata[@module = 'news'][@method = 'lastlist']">
		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
		</h1>
		<p umi:field-name="readme" umi:element-id="{$pageId}" umi:empty="&empty-page-content;">
			<xsl:value-of select="document(concat('upage://', $pageId, '.readme'))/udata//value"/>
		</p>
	</xsl:template>

	<xsl:template match="udata[@module = 'news'][@method = 'lastlist'][total]">
		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="$pageId = $articlesId">news</xsl:when>
					<xsl:when test="$pageId = $sharesId">shares</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
		</h1>
		<p umi:field-name="readme" umi:element-id="{$pageId}" umi:empty="&empty-page-content;">
			<xsl:value-of select="document(concat('upage://', $pageId, '.readme'))/udata//value"/>
		</p>
		<xsl:choose>
			<xsl:when test="$pageId = $doctorsId">
				<xsl:apply-templates select="items" mode="doctors"/>
			</xsl:when>
			<xsl:otherwise>

				<ul class="tile-3 xs-c" umi:element-id="{category_id}" umi:module="news" umi:method="lastlist" umi:sortable="sortable">
					<xsl:apply-templates select="items/item" mode="items"/>
				</ul>

				<xsl:apply-templates select="document(concat('udata://system/numpages/', total, '/', per_page, '/'))/udata">
					<xsl:with-param name="numpages" select="ceiling(total div per_page)" />
				</xsl:apply-templates>

			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="udata[@method = 'lastlist']/items/item" mode="items">
		<xsl:variable name="page" select="document(concat('upage://', @id))//udata" />
        <li>
            <div class="wrap">
            	<xsl:if test="$page//property[@name='anons_pic']/value">
	                <a rel="nofollow" href="{@link}" class="border" umi:field-name="anons_pic" umi:element-id="{@id}">
	                    <img class="full" alt="{$page//property[@name='h1']/value}" src="{document(concat('udata://system/makeThumbnailFull/(',substring($page//property[@name='anons_pic']/value,2),')/400/300'))//src}"/>
	                </a>
	            </xsl:if>
                <a class="h4" href="{@link}" umi:field-name="h1" umi:element-id="{@id}">
                	<xsl:value-of select="$page//property[@name='h1']/value" />
                </a>
				<div class="content" umi:element-id="{@id}" umi:field-name="anons" umi:empty="&empty-page-content;">
					<xsl:value-of select="$page//property[@name='anons']/value" disable-output-escaping="yes"/>
				</div>
                <a rel="nofollow" href="{@link}" class="btn-more">Подробнее...</a>
            </div>
        </li>
	</xsl:template>



	<!-- DOCTORS -->
	<xsl:template match="udata[@method = 'lastlist']/items" mode="doctors">
        <table class="accordion">
            <tbody>
                <xsl:for-each select="document(concat('usel://getCatalog/', $activitiesId))//udata//page">
                	<xsl:variable name="currentLink" select="./@link"/>

	                <tr>
	                    <td>
	                        <div class="caption">
								<xsl:value-of select="./name"/>
	                        </div>
	                        <div class="accordion_row">
		                        <ul class="tile-4 xs-c">
									<xsl:for-each select="document(concat('usel://getDoctors/', $doctorsId))//udata/page">
										<!-- <span><xsl:value-of select=".//property[@name='doctor_activities']//page/@link"/></span> -->
										<xsl:if test="$currentLink = .//property[@name='doctor_activities']//page/@link">
								            <li>
								                <div class="wrap">
								                    <a rel="nofollow" href="{@link}" class="portrait block">
								                        <img alt="{.//property[@name='h1']/value}" src="{document(concat('udata://system/makeThumbnailFull/(',substring(document(concat('upage://',@id,'.anons_pic'))//value,2),')/324/324'))//src}"/>
								                    </a>
								                    <a class="h5" href="{@link}" umi:field-name="h1" umi:element-id="{@id}">
														<xsl:value-of select=".//property[@name='h1']/value"/>
								                    </a>
								                    <div class="small" umi:field-name="doctor_specialization" umi:element-id="{@id}">
														<xsl:value-of select=".//property[@name='doctor_specialization']/value"/>
								                    </div>
								                </div>
								            </li>
										</xsl:if>
									</xsl:for-each>
								</ul>
							</div>
	                    </td>
	                </tr>
                </xsl:for-each>
            </tbody>
        </table>
	</xsl:template>

</xsl:stylesheet>