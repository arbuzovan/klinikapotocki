<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

    <xsl:output encoding="utf-8" method="html" indent="yes" />

	<xsl:template match="result[@module = 'content'][@method = 'content']">
		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
		</h1>
		<xsl:if test="document(concat('upage://', $pageId, '.header_pic'))/udata//value">
			<a href="{document(concat('upage://', $pageId, '.header_pic'))/udata//value}" class="fancybox">
				<img class="img-left" alt="{document(concat('upage://', $pageId, '.h1'))/udata//value}" src="{document(concat('udata://system/makeThumbnail/(',substring(document(concat('upage://',$pageId,'.header_pic'))//value,2),')/300/(auto)'))//src}"/>
			</a>
		</xsl:if>
		<div class="content" umi:field-name="content" umi:element-id="{$pageId}" umi:empty="&empty-page-content;">
			<xsl:value-of select=".//property[@name = 'content']/value" disable-output-escaping="yes"/>
		</div>
		<xsl:if test="$pageId = $main">
			<a href="{document(concat('udata://content/get_page_url/', $aboutId,'/'))/udata}" class="btn-more">Подробнее...</a>
		</xsl:if>

        <!-- GALLERY -->
        <xsl:for-each select="document(concat('udata://photoalbum/albums////',$pageId))/udata//items/item">
			<div class="h2" umi:element-id="{@id}" umi:field-name="h1">
				<xsl:value-of select="document(concat('upage://', @id, '.h1'))//udata//value"/>
			</div>

			<div class="mb30">
	        	<xsl:apply-templates select="document(concat('udata://photoalbum/album/', @id))/udata" />
			</div>
        </xsl:for-each>

		<!-- SCRIPTS -->
		<xsl:if test="//property[@name='scripts']">
			<div class="mt30">
				<xsl:value-of select="//property[@name='scripts']/value" disable-output-escaping="yes"/>
			</div>
		</xsl:if>
	</xsl:template>

	<!-- CONTACTS PAGE -->
	<xsl:template match="result[@module = 'content'][@method = 'content'][@request-uri = '/contacts/']">
		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
		</h1>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="content" umi:field-name="content" umi:element-id="{$pageId}" umi:empty="&empty-page-content;">
                                <xsl:value-of select=".//property[@name = 'content']/value" disable-output-escaping="yes"/>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="map">
                                    <xsl:value-of select="$conf//property[@name='map']/value" disable-output-escaping="yes"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="content" umi:field-name="second_adress" umi:element-id="454" umi:empty="&empty-page-content;">
                                <xsl:value-of select="$conf//property[@name = 'second_adress']/value" disable-output-escaping="yes"/>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="map">
                                    <xsl:value-of select="$conf//property[@name='second_map']/value" disable-output-escaping="yes"/>
                            </div>
                        </div>
                    </div>
                </div>


		<!--div class="content" itemscope="itemscope" itemtype="http://schema.org/Organization">
			<h3 itemprop="name">
				<xsl:value-of select="$conf//property[@name='company_name']/value"/>
			</h3>

			<p itemprop="address" itemscope="itemscope" itemtype="http://schema.org/PostalAddress">
				<span itemprop="postalCode" umi:field-name="postalcode" umi:object-id="{$conf//object/@id}">
					<xsl:value-of select="$conf//property[@name='postalcode']/value" />
				</span>,&nbsp;
				<span itemprop="addressLocality" umi:field-name="addresslocality" umi:object-id="{$conf//object/@id}">
					<xsl:value-of select="$conf//property[@name='addresslocality']/value" />
				</span>,&nbsp;
				<span itemprop="streetAddress" umi:field-name="streetaddress" umi:object-id="{$conf//object/@id}" >
					<xsl:value-of select="$conf//property[@name='streetaddress']/value" />
				</span>
			</p>
			<p>
				<strong>Телефон:</strong>&nbsp;
				<a target="_blank" href="tel:{$conf//property[@name='telephone']/value}" value="{$conf//property[@name='telephone']/value}" itemprop="telephone" umi:field-name="telephone" umi:object-id="{$conf//object/@id}">
					<xsl:value-of select="$conf//property[@name='telephone']/value" />
				</a>
			</p>
			<p>
				<xsl:if test="$conf//property[@name='faxnumber']/value">
					<strong>Факс:</strong>&nbsp;
					<a target="_blank" href="tel:{$conf//property[@name='faxnumber']/value}" value="{$conf//property[@name='faxnumber']/value}" itemprop="faxnumber" umi:field-name="faxnumber" umi:object-id="{$conf//object/@id}">
						<xsl:value-of select="$conf//property[@name='faxnumber']/value" />
					</a>
				</xsl:if>
			</p>
			<p>
				<strong>E-mail:</strong>&nbsp;
				<a href="mailto:{$conf//property[@name='email']/value}" itemprop="email" umi:field-name="email" umi:object-id="{$conf//object/@id}">
					<xsl:value-of select="$conf//property[@name='email']/value" />
				</a>
			</p>
		</div-->


	</xsl:template>

	<!-- PRICE PAGE -->
	<xsl:template match="result[@module = 'content'][@method = 'content'][@request-uri = '/price/']">
		<h1 umi:element-id="{$pageId}" umi:field-name="h1">
			<xsl:value-of select="document(concat('upage://', $pageId, '.h1'))/udata//value"/>
		</h1>

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
						        <table class="zebra">
						            <thead>
						                <tr>
						                    <th>Услуги направления</th>
						                    <th>Стоимость</th>
						                </tr>
						            </thead>
									<xsl:apply-templates select="document(concat('usel://getCatalogObjects/', @id))/udata//page" mode="price"/>
						        </table>
	                    	</div>
	                    </td>
	                </tr>
                </xsl:for-each>
            </tbody>
        </table>

        <script>
            $(".zebra").tablesorter();
        </script>
	</xsl:template>
</xsl:stylesheet>