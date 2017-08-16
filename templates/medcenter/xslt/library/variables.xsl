<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

    <xsl:output encoding="utf-8" method="html" indent="yes" />
	<xsl:variable name="pageId" select="/result/@pageId" />
	<xsl:variable name="parentId" select="/result/parents/page/@id" />
	<xsl:variable name="lastParentId" select="/result/parents/page[last()]/@id" />
	<xsl:variable name="module" select="/result/@module" />
	<xsl:variable name="method" select="/result/@method" />
	<xsl:variable name="requestUri" select="/result/@request-uri" />
	<xsl:variable name="errors" select="document('udata://system/listErrorMessages')/udata" />
	<xsl:variable name="langPrefix" select="/result/@pre-lang" />
	<xsl:variable name="userId" select="/result/user/@id" />
	<xsl:variable name="userType" select="/result/user/@type" />
	<xsl:variable name="userInfo" select="document(concat('uobject://', $userId))/udata" />
	<xsl:variable name="siteInfoPage" select="document('upage://contacts')/udata/page" />
	<xsl:variable name="siteInfo" select="$siteInfoPage//group[@name = 'site_info']/property" />
	<xsl:variable name="domain" select="/result/@domain" />

	<xsl:variable name="isAdmin">
		<xsl:choose>
			<xsl:when test="$userType = 'admin' or $userType = 'sv'">1</xsl:when>
			<xsl:otherwise>0</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="mobVer" select="number(document('udata://custom/is_mobile/')//udata)"/>

	<xsl:variable name="isMob" >
		<xsl:choose>
			<xsl:when test="$mobVer = 1 or $mobVer = 2 or mobVer = 3 or $mobVer = 4">1</xsl:when>
			<xsl:when test="$mobVer = 5">5</xsl:when>
			<xsl:otherwise>0</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>


	<xsl:param name="p" select="'0'" />
	<xsl:param name="search_string" />
	<xsl:param name="param0" />
	<xsl:param name="formType" select="' '"/>
	<xsl:param name="publish_time" />
	<xsl:param name="price" />

	<xsl:variable name="main" select="document('upage:///')/udata/page/@id"/>
	<xsl:variable name="confid" select="document(concat('upage://', $main, '.conf'))/udata//value"/>
	<xsl:variable name="confObjectId" select="document(concat('usel://getList/', $confid, '/'))/udata//item/@id"/>
	<xsl:variable name="conf" select="document(concat('uobject://', $confObjectId))/udata"/>
	<xsl:variable name="sitemapId">
		<xsl:choose>
			<xsl:when test="document(concat('upage://', '/sitemap/'))/udata/page/@id">
				<xsl:value-of select="document(concat('upage://', '/sitemap/'))/udata/page/@id"/>
			</xsl:when>
			<xsl:otherwise>1</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="aboutId">
		<xsl:choose>
			<xsl:when test="document(concat('upage://', '/about/'))/udata/page/@id">
				<xsl:value-of select="document(concat('upage://', '/about/'))/udata/page/@id"/>
			</xsl:when>
			<xsl:otherwise>1</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="activitiesId">
		<xsl:choose>
			<xsl:when test="document(concat('upage://', '/activities/'))/udata/page/@id">
				<xsl:value-of select="document(concat('upage://', '/activities/'))/udata/page/@id"/>
			</xsl:when>
			<xsl:otherwise>1</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="documentsId">
		<xsl:choose>
			<xsl:when test="document(concat('upage://', '/documents/'))/udata/page/@id">
				<xsl:value-of select="document(concat('upage://', '/documents/'))/udata/page/@id"/>
			</xsl:when>
			<xsl:otherwise>1</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="sharesId">
		<xsl:choose>
			<xsl:when test="document(concat('upage://', '/shares/'))/udata/page/@id">
				<xsl:value-of select="document(concat('upage://', '/shares/'))/udata/page/@id"/>
			</xsl:when>
			<xsl:otherwise>1</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="doctorsId">
		<xsl:choose>
			<xsl:when test="document(concat('upage://', '/doctors/'))/udata/page/@id">
				<xsl:value-of select="document(concat('upage://', '/doctors/'))/udata/page/@id"/>
			</xsl:when>
			<xsl:otherwise>1</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="articlesId">
		<xsl:choose>
			<xsl:when test="document(concat('upage://', '/articles/'))/udata/page/@id">
				<xsl:value-of select="document(concat('upage://', '/articles/'))/udata/page/@id"/>
			</xsl:when>
			<xsl:otherwise>1</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="orderId" >
		<xsl:choose>
			<xsl:when test="document(concat('upage://', '/order/'))/udata/page/@id">
				<xsl:value-of select="document(concat('upage://', '/order/'))/udata/page/@id"/>
			</xsl:when>
			<xsl:otherwise>1</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

</xsl:stylesheet>