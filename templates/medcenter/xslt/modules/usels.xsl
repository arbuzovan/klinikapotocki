<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

	<xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="udata[@module = 'usel']" mode="index_anons">
		<ul class="anons hidden-xs"
			umi:add-method="popup"
			umi:sortable="sortable"
			umi:method="menu"
			umi:module="content"
			umi:element-id="$articlesId">
			<xsl:apply-templates select="//page" mode="index_anons"/>
		</ul>
	</xsl:template>

	<xsl:template match="page" mode="index_anons">
		<xsl:variable name="page" select="document(concat('upage://', @id))//udata" />
        <li umi:element-id="{@id}" umi:region="row" umi:field-name="name" umi:empty="&empty-section-name;" umi:delete="delete">
            <div>
                <a href="{@link}" umi:element-id="{@id}" umi:field-name="h1">
					<xsl:value-of select="$page//property[@name='h1']/value" />
                </a>
            </div>
            <xsl:if test="$page//property[@name='anons_pic']/value">
                <a href="{@link}" rel="nofollow">
					<img alt="{$page//property[@name='h1']/value}" src="{document(concat('udata://system/makeThumbnailFull/(',substring($page//property[@name='anons_pic']/value,2),')/140/80'))//src}"/>
                </a>
			</xsl:if>
            <div class="content small" umi:element-id="{@id}" umi:field-name="anons">
                <xsl:value-of select="$page//property[@name='anons']/value" disable-output-escaping="yes"/>
            </div>
        </li>
	</xsl:template>

	<!-- BANNER -->
	<xsl:template match="udata[@module = 'usel']" mode="slider">
		<div class="carousel-inner" role="listbox">
        <ol class="carousel-indicators">
			<xsl:for-each select=".//page">
	            <li data-target="#banner" data-slide-to="{position()-1}">
		        	<xsl:if test="position() = 1">
		        		<xsl:attribute name="class">active</xsl:attribute>
		        	</xsl:if>
	            </li>
			</xsl:for-each>
        </ol>
		<xsl:for-each select=".//page">
			<xsl:if test=".//property[@name='publish_pic']/value">
				<xsl:variable name="slideImg" select="document(concat('udata://system/makeThumbnailFull/(',substring(.//property[@name='publish_pic']/value,2),')/1920/540/default/0/1/5/0/50'))//src" />

		        <div class="item" style="background-image: url('{$template-resources}images/grid.png'), url('{$slideImg}');">
		        	<xsl:if test="position() = 1">
		        		<xsl:attribute name="class">item active</xsl:attribute>
		        	</xsl:if>
		            <div class="container">
		                <div class="banner-content">
		                    <a href="{@link}" class="banner-caption" umi:field-name="h1" umi:element-id="{@id}">
								<xsl:value-of select=".//property[@name='h1']/value"/>
		                    </a>
		                    <div class="content" umi:element-id="{@id}" umi:field-name="anons">
		                        <xsl:value-of select=".//property[@name='anons']/value" disable-output-escaping="yes"/>
		                    </div>
		                    <a class="btn" href="{@link}">Подробнее...</a>
		                </div>
		            </div>
		        </div>
	        </xsl:if>
		</xsl:for-each>
		</div>
	</xsl:template>

	<xsl:template match="udata[@module = 'usel']" mode="activities">
		<ul class="tile-3 xs-c">
			<xsl:apply-templates select=".//page" mode="activities"/>
		</ul>
	</xsl:template>

	<xsl:template match="page | item" mode="activities">
        <li umi:element-id="{@id}" umi:region="row" umi:field-name="name" umi:empty="&empty-section-name;" umi:delete="delete">
            <figure>
            	<xsl:variable name="page" select="document(concat('upage://', @id))//udata"/>
                <a href="{@link}" rel="nofollow" class="border">
                    <img class="full" alt="{$page//property[@name='h1']/value}" src="{document(concat('udata://system/makeThumbnailFull/(',substring($page//property[@name='header_pic']/value,2),')/400/300'))//src}"/>
                </a>
                <figcaption>
                    <a href="{@link}" umi:element-id="{@id}" umi:field-name="h1">
						<xsl:value-of select="$page//property[@name='h1']/value"/>
                    </a>
                </figcaption>
            </figure>
        </li>
	</xsl:template>

</xsl:stylesheet>