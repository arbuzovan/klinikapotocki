<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

        <xsl:output encoding="utf-8" method="html" indent="yes" />

        <xsl:template match="result[@module = 'content' and @method = 'content'][@request-uri = '/sitemap/']" >
            <h1 umi:element-id="{$pageId}" umi:field-name="h1">
                <xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
            </h1>
            <div class="content">
                <xsl:apply-templates select="document('udata://custom/sitemapnew/10/')/udata" mode="map"/>
            </div>
        </xsl:template>

        <xsl:template match="udata" mode="map">
            <xsl:apply-templates select="./items" mode="map"/>
        </xsl:template>

        <xsl:template match="items" mode="map">
            <ul>
                <xsl:apply-templates select="./item" mode="map"/>
            </ul>
        </xsl:template>

        <xsl:template match="item" mode="map">
            <li>
                <a href="{@link}">
                    <xsl:value-of select="@name"/>
                </a>
                <xsl:apply-templates select="./items" mode="map"/>
            </li>
        </xsl:template>
    </xsl:stylesheet>