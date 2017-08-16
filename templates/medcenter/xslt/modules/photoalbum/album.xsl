<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

	<xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="result[@module = 'photoalbum'][@method = 'album']">
		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
		</h1>
		<xsl:apply-templates select="document(concat('udata://photoalbum/album/',$pageId))/udata" />
	</xsl:template>

	<xsl:template match="udata[@module = 'photoalbum'][@method = 'album']" />
<!-- 		<xsl:for-each select="document('usel://gallery/31/')/udata/page">
			<h2>
				<a href="{@link}">
					<xsl:value-of select="./name" />
				</a>
			</h2>
			<div class="row list">
				<xsl:apply-templates select="document(concat('udata://photoalbum/album/',@id, '//4/1/'))/udata/items/item" mode="photo_list"/>
			</div>

			<xsl:if test="count(document(concat('udata://photoalbum/album/',@id))/udata/items/item) &gt; 4">
				<div class="btn_wrap r">
					<a rel="nofollow" href="{@link}" class="btn">Показать все</a>
				</div>
			</xsl:if>
		</xsl:for-each>
	</xsl:template> -->

	<xsl:template match="udata[@module = 'photoalbum'][@method = 'album'][total]">
		<div class="content" umi:element-id="{id}" umi:field-name="descr">
			<xsl:value-of select="document(concat('upage://',id,'.descr'))/udata/property/value" disable-output-escaping="yes" />
		</div>
		<ul class="tile-3 certificates mob-c">
			<xsl:apply-templates select="items/item" mode="photo_list" />
		</ul>
		<xsl:apply-templates select="total" />
	</xsl:template>

	<xsl:template match="item" mode="photo_list">
		<xsl:variable name="page" select="document(concat('upage://',@id))//udata"/>
		<li>
			<div class="wrap text-center">
				<a title="{$page//property[@name='h1']/value}" rel="fancybox" href="{$page//property[@name='photo']/value}" class="fancybox">
					<xsl:choose>
						<xsl:when test="$pageId = $documentsId">
							<xsl:call-template name="makeThumbnail">
								<xsl:with-param name="element_id" select="@id" />
								<xsl:with-param name="field_name">photo</xsl:with-param>
								<xsl:with-param name="width">180</xsl:with-param>
								<xsl:with-param name="height">250</xsl:with-param>
								<xsl:with-param name="alt" select="document(concat('upage://',@id,'.h1'))//value" />
								<xsl:with-param name="class" select="full-max" />
							</xsl:call-template>

						</xsl:when>
						<xsl:otherwise>

							<img class="full-max" alt="{$page//property[@name='h1']/value}" src="{document(concat('udata://system/makeThumbnailFull/(',substring($page//property[@name='photo']/value,2),')/370/250'))//src}"/>
						</xsl:otherwise>
					</xsl:choose>
				</a>
				<div class="h4">
					<a href="{@link}">
						<xsl:value-of select="."/>
					</a>
				</div>
			</div>
		</li>
	</xsl:template>

</xsl:stylesheet>