<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi">

	<!-- <xsl:template match="udata[@module = 'catalog'][@method = 'getObjectsList'][total]"/> -->

	<xsl:template match="udata[@module = 'catalog'][@method = 'getSmartCatalog']">
		<xsl:variable name="page" select="document(concat('upage://', $pageId))/udata"/>

		<xsl:if test="$page//property[@name = 'header_pic']/value">
			<a class="fancybox" href="{$page//property[@name = 'header_pic']/value}">
				<img class="img-left" alt="{$page//property[@name = 'h1']/value}" src="{document(concat('udata://system/makeThumbnail/(',substring($page//property[@name = 'header_pic']/value,2),')/300/(auto)'))//src}"/>
			</a>
		</xsl:if>

        <div class="content" umi:element-id="{$pageId}" umi:field-name="descr">
            <xsl:value-of select="$page//property[@name='descr']/value" disable-output-escaping="yes"/>
        </div>
        <div class="clear"></div>

        <!-- GALLERY -->
        <xsl:for-each select="document(concat('udata://photoalbum/albums////',$pageId))/udata//items/item">
			<div class="h2" umi:element-id="{@id}" umi:field-name="h1">
				<xsl:value-of select="document(concat('upage://', @id, '.h1'))//udata//value"/>
			</div>

			<div class="mb30">
	        	<xsl:apply-templates select="document(concat('udata://photoalbum/album/', @id))/udata" />
			</div>
        </xsl:for-each>

		<!-- PRICE -->
		<xsl:if test="document(concat('usel://getCatalogObjects/', $pageId))/udata//page">
	        <table class="zebra">
	            <thead>
	                <tr>
	                    <th>Услуги направления</th>
	                    <th>Стоимость</th>
	                </tr>
	            </thead>
				<xsl:apply-templates select="document(concat('usel://getCatalogObjects/', $pageId))/udata//page" mode="price"/>
	        </table>
	        <script>
	            $(".zebra").tablesorter();
	        </script>
		</xsl:if>

		<!-- DOCTORS -->
		<xsl:if test="not($pageId = $activitiesId) and $page//property[@name='show_doctors']">
	        <div class="h2">
				<span umi:field-name="activities_prefix" umi:object-id="{$conf//object/@id}">
					<xsl:value-of select="$conf//property[@name = 'activities_prefix']/value"/>
				</span>
				<xsl:text>&nbsp;</xsl:text>
				<span class="lowercase">
					<xsl:value-of select="$page//property[@name = 'h1']/value"/>
				</span>
	        </div>
	        <ul class="tile-4 xs-c">
	        	<xsl:for-each select="document(concat('usel://getDoctors/', $doctorsId))//udata/page">
					<xsl:if test=".//property[@name='doctor_activities']/value/page/@link = document(concat('upage://', $pageId))/udata/page/@link">
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
		</xsl:if>
	</xsl:template>

	<xsl:template match="item | page" mode="price">
		<xsl:variable name="page" select="document(concat('upage://', @id))/udata"/>
	    <tr>
            <td>
            	<a href="{@link}" umi:field-name="h1" umi:element-id="{@id}">
            		<xsl:value-of select="$page//property[@name='h1']/value" />
            	</a>
            </td>
            <td>
                <xsl:if test="$page//property[@name='from_label']/value" >
                    <span class="prince_from_label">от</span>&nbsp;
                </xsl:if>
                <span umi:field-name="price" umi:element-id="{@id}">
                    <xsl:call-template name="price">
                    	<xsl:with-param name="price" select="$page//property[@name='price']/value"/>
                    </xsl:call-template>
                </span><xsl:text>&nbsp;zł.</xsl:text>

         	</td>
        </tr>
	</xsl:template>

</xsl:stylesheet>