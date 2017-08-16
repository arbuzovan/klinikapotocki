<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

<xsl:stylesheet	version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi">

	<xsl:template match="result[@module = 'catalog'][@method = 'object']">
		<xsl:variable name="page" select="document(concat('upage://', $pageId))/udata"/>

		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:value-of select="//property[@name = 'h1']/value"/>
		</h1>


		<xsl:if test="//property[@name = 'header_pic']/value">
	        <div class="img-left text-center">
	            <a href="{//property[@name = 'header_pic']/value}" class="fancybox">
					<xsl:call-template name="makeThumbnail">
						<xsl:with-param name="element_id" select="$pageId" />
						<xsl:with-param name="field_name">header_pic</xsl:with-param>
						<xsl:with-param name="width">300</xsl:with-param>
						<xsl:with-param name="height">300</xsl:with-param>
						<xsl:with-param name="alt" select="//property[@name = 'h1']/value" />
						<xsl:with-param name="class">full-max portrait</xsl:with-param>
					</xsl:call-template>
	            </a>
	        </div>
		</xsl:if>

        <div class="mob-c">
            <span class="price">
                <span>Стоимость обследования:</span>&nbsp;
                <span class="big-text" umi:field-name="price" umi:element-id="{$pageId}">
                    <xsl:call-template name="price">
                    	<xsl:with-param name="price" select="//property[@name='price']/value"/>
                    </xsl:call-template>
                </span><xsl:text> руб.</xsl:text>
            </span>

            <div class="window-wrap">
                <a id="order-window" class="btn stethoscope" umi:object-id="{$conf//object/@id}" umi:field-name="form_title">
					<xsl:value-of select="$conf//property[@name='form_title']/value" />
                </a>

                <div class="btn-close"></div>

                <div class="border form">
                	<xsl:apply-templates select="document('udata://webforms/add/medcenter/')//udata" >
                		<xsl:with-param name="formType" select="'window'"/>
                	</xsl:apply-templates>
                </div>
            </div>
        </div>

        <div class="content" umi:element-id="{$pageId}" umi:field-name="content">
            <xsl:value-of select="//property[@name='content']/value" disable-output-escaping="yes"/>
        </div>
	    <div class="clear"></div>


		<!-- DOCTORS -->
        <xsl:if test="document(concat('upage://', $lastParentId))//udata//property[@name='show_doctors']">
            <div class="h2">
    			<span umi:field-name="activities_prefix" umi:object-id="{$conf//object/@id}">
    				<xsl:value-of select="$conf//property[@name = 'activities_prefix']/value"/>
    			</span>
    			<xsl:text>&nbsp;</xsl:text>
    			<span class="lowercase">
    				<xsl:value-of select="document(concat('upage://', $lastParentId))/udata//property[@name = 'h1']/value"/>
    			</span>
            </div>
            <ul class="tile-4 xs-c">
            	<xsl:for-each select="document(concat('usel://getDoctors/', $doctorsId))//udata/page">
    				<xsl:if test=".//property[@name='doctor_activities']/value/page/@link = document(concat('upage://', $lastParentId))/udata/page/@link">
    					<xsl:apply-templates select="." mode="doctors_portrait"/>
    				</xsl:if>
               </xsl:for-each>
            </ul>
        </xsl:if>

		<!-- PRICE -->
        <table class="zebra mt30">
            <thead>
                <tr>
                    <th>Услуги направления</th>
                    <th>Стоимость</th>
                </tr>
            </thead>
			<!-- <xsl:apply-templates select="document(concat('udata://catalog/getObjectsList//', $lastParentId, '/1000/1/'))/udata//lines/item" mode="price"/> -->
            <xsl:apply-templates select="document(concat('usel://getCatalogObjects/', $lastParentId))/udata//page" mode="price"/>
        </table>
        <script>
            $(".zebra").tablesorter();
        </script>
	</xsl:template>

	<xsl:template name="price">
		<xsl:param name="price" select="$price"/>

		<xsl:variable name="spaceStart" select="string-length($price) - 3"/>
		<xsl:variable name="spaceEnd" select="string-length($price) - 2"/>

		<xsl:value-of select="substring($price, 1, $spaceStart)" />
		<xsl:text> </xsl:text>
		<xsl:value-of select="substring($price, $spaceEnd)"/>
	</xsl:template>


	<xsl:template match="page" mode="doctors_portrait">
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
	</xsl:template>
</xsl:stylesheet>