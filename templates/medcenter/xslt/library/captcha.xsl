<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

    <xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="udata[@module = 'system' and @method = 'captcha']" />
	<xsl:template match="udata[@module = 'system' and @method = 'captcha' and count(url)]">
        <xsl:param name="formType" select="$formType"/>

        <div class="col-md-3">
            <xsl:if test="$formType = 'window'">
                <xsl:attribute name="class">col-md-6</xsl:attribute>
            </xsl:if>
            <input required="required" type="text" name="captcha" placeholder="Введите код:"/>
        </div>

        <div class="col-md-5 xs-c sm-c">
            <xsl:if test="$formType = 'window'">
                <xsl:attribute name="class">col-md-6 xs-c sm-c</xsl:attribute>
            </xsl:if>
            <img class="captcha-img" src="{url}{url/@random-string}" />
            <a href="#" class="reload"></a>
        </div>

    </xsl:template>


</xsl:stylesheet>