<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi">

	<xsl:template match="result[@module = 'content'][@method = 'notfound' or @method = 'sitemap']">
			<h1>Страница не найдена</h1>
			<h3>Запрашиваемая Вами страница не найдена.<br/>
			Проверьте корректность введённого адреса или воспользуйтесь картой сайта:</h3>
			<!-- <div class="content sitemap">
				<xsl:apply-templates select="document('udata://content/sitemap/')/udata/items" mode="sitemap"/>
			</div> -->
            <div class="content">
                <xsl:apply-templates select="document('udata://custom/sitemapnew/10/')/udata" mode="map"/>
            </div>
	</xsl:template>

	<!-- <xsl:template match="items" mode="sitemap">
		<ul >
			<xsl:apply-templates select="item" mode="sitemap" />
		</ul>
	</xsl:template>

	<xsl:template match="item" mode="sitemap">
		<li>
			<a href="{@link}">
				<xsl:value-of select="@name" />
			</a>
			<xsl:apply-templates select="items" mode="sitemap"/>
		</li>
	</xsl:template> -->

</xsl:stylesheet>