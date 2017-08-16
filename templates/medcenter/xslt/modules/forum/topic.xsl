<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

	<xsl:template match="result[@module = 'forum'][@method = 'topic']">
		<xsl:apply-templates select="document('udata://forum/topic/')/udata" />
	</xsl:template>

	<xsl:template match="udata[@module = 'forum'][@method = 'topic']">
		<xsl:if test="total">
			<xsl:variable name="per_page" select="./per_page" />

			<h1 umi:field-name="h1" umi:element-id="{$pageId}">
				<xsl:value-of select="document(concat('upage://', $pageId))/udata//property[@name = 'h1']/value" />
				<a class="btn add_review pull-right" href="#review_header">
					Оставить отзыв
				</a>
			</h1>
			
			<div class="clear"></div>

			<ul class="anons">
				<xsl:apply-templates select="document(concat('usel://topic/', $p, '/', $per_page ))/udata/page" mode="message"/>
			</ul>
			
			<div class="center">
				<xsl:apply-templates select="total" />
			</div>
		</xsl:if>
	</xsl:template>

	<xsl:template match="udata[@module='usel' and @method='topic']/page" mode="message">
		<xsl:variable name="publish_time" select="document(concat('upage://',@id))//property[@name = 'publish_time']/value/@unix-timestamp" />
		<xsl:variable name="page" select="document(concat('upage://',@id))//udata" />
		<xsl:variable name="page_link" select="document(concat('udata://content/get_page_url/', @id, '/'))/udata"/>
        <li>
			<xsl:choose>
	        	<xsl:when test="document(concat('upage://',@id,'.photo'))//value">
					<a class="round120" href="{@link}" rel="nofollow" umi:field-name="anons_pic" umi:element-id="{@id}">
						<img alt="{document(concat('upage://', @id, '.h1'))/udata//value}" src="{document(concat('udata://system/makeThumbnailFull/(',substring(document(concat('upage://',@id,'.photo'))//value,2),')/120/120'))//src}"/>
					</a>
				</xsl:when>
				<xsl:otherwise>
					<a class="round120" href="{@link}" rel="nofollow" umi:field-name="anons_pic" umi:element-id="{@id}">
						<img alt="{document(concat('upage://', @id, '.h1'))/udata//value}" src="{$template-resources}images/nophoto.png"/>
					</a>
				</xsl:otherwise>
			</xsl:choose>
            <h4>
				<a href="{@link}" umi:field-name="h1" umi:delete="delete">
					<xsl:value-of select="document(concat('upage://', @id, '.h1'))/udata//value" />
				</a>
            </h4>
			<p umi:element-id="{@id}" umi:field-name="message" umi:empty="&empty-page-content;">
				<xsl:value-of select="document(concat('upage://', @id, '.message'))/udata//value" />
			</p>
           <span class="date">
				<xsl:call-template name="date">
					<xsl:with-param name="publish_time" select="document(concat('upage://', @id))//udata//property[@name='publish_time']/value/@unix-timestamp"/>
				</xsl:call-template>
           </span>
           <div class="clear"></div>
        </li>
	</xsl:template>

	<xsl:template match="result[@module = 'forum' and @method = 'message']">
		<xsl:variable name="publish_time" select="//property[@name = 'publish_time']/value/@unix-timestamp" />
		<h1 umi:field-name="h1" umi:element-id="{$pageId}">
			<xsl:value-of select="//property[@name = 'h1']/value" />
		</h1>

    	<xsl:if test="document(concat('upage://',$pageId,'.photo'))//value">
    		<a href="{document(concat('upage://',$pageId,'.photo'))//value}" class="round120 fancybox">
				<img alt="{document(concat('upage://', $pageId, '.h1'))/udata//value}" src="{document(concat('udata://system/makeThumbnail/(',substring(document(concat('upage://',$pageId,'.photo'))//value,2),')/120/120'))//src}"/>
			</a>
		</xsl:if>
		
		<p umi:element-id="{@id}" umi:field-name="message">
			<xsl:value-of select="//property[@name = 'message']/value" />
		</p>

		<p class="date">
			<xsl:apply-templates select="document(concat('udata://system/convertDate/',$publish_time,'/d.m.Y/'))/udata" />
		</p>

	</xsl:template>


</xsl:stylesheet>