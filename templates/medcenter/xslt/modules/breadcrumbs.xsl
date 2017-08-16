<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

    <xsl:output encoding="utf-8" method="html" indent="yes" />

    <xsl:template match="udata[@module = 'core'][@method = 'navibar']">
        <xsl:if test="count(items/item) &gt; 1">
            <ul class="breadcrumbs">
                <xsl:apply-templates select="items/item" mode="navibar"/>
            </ul>
        </xsl:if>
    </xsl:template>

    <xsl:template match="item" mode="navibar">
        <li>
            <a href="{@link}"><xsl:value-of select="."/></a>
        </li>
    </xsl:template>

    <xsl:template match="item[position() = last()]" mode="navibar">
        <li>
            <xsl:value-of select="."/>
        </li>
    </xsl:template>
 
</xsl:stylesheet>

