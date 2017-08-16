<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">

    <xsl:output encoding="utf-8" method="html" indent="yes" />

	<!-- TOP MENU -->
	<xsl:template match="udata[@module = 'content'][@method = 'menu']" mode="menu_top">
		<xsl:apply-templates select="items" mode="menu_top"/>
	</xsl:template>

	<xsl:template match="udata[@module = 'content' and @method = 'menu']//items" mode="menu_top">
		<ul class="navbar-nav" umi:add-method="popup"
			umi:sortable="sortable"
			umi:method="menu"
			umi:module="content"
			umi:element-id="0">
			<xsl:apply-templates select="item"  mode="menu_item"/>
		</ul>
	</xsl:template>

	<xsl:template match="udata[@module = 'content' and @method = 'menu']//item" mode="menu_item">
		<li umi:element-id="{@id}" umi:region="row" umi:field-name="name" umi:empty="&empty-section-name;" umi:delete="delete">
			<xsl:if test="@status = 'active'">
				<xsl:attribute name="class">active</xsl:attribute>
			</xsl:if>
			<a href="{@link}">
				<xsl:value-of select="@name" />
			</a>
			<!-- <xsl:if test="./items/item">
				<ul class="submenu">
					<xsl:apply-templates select="./items/item" mode="menu_subitem"/>
				</ul>
			</xsl:if> -->
		</li>
	</xsl:template>

	<xsl:template match="item" mode="menu_subitem">
		<li>
			<a href="{@link}">
				<xsl:value-of select="@name" />
			</a>
		</li>
	</xsl:template>

	<!-- ASIDE -->
	<xsl:template match="udata[@module = 'content'][@method = 'menu']" mode="aside">
		<ul  class="nav nav-sidebar hidden-xs" umi:add-method="popup"
			umi:sortable="sortable"
			umi:method="menu"
			umi:module="content"
			umi:element-id="{$activitiesId}">
			<xsl:apply-templates select="//items/item" mode="aside"/>
		</ul>
	</xsl:template>

	<xsl:template match="item" mode="aside">
		<li umi:element-id="{@id}" umi:region="row" umi:field-name="name" umi:empty="&empty-section-name;" umi:delete="delete">
			<xsl:if test="@status = 'active'">
				<xsl:attribute name="class">active</xsl:attribute>
			</xsl:if>
			<a href="{@link}">
				<xsl:value-of select="@name" />
			</a>
		</li>
	</xsl:template>

	<!-- FOOTER MENU -->
	<xsl:template match="udata[@module = 'content'][@method = 'menu']" mode="menu_footer">
		<ul class="footer-menu">
			<xsl:apply-templates select="items/item" mode="menu_footer"/>
		</ul>
	</xsl:template>

	<xsl:template match="item" mode="menu_footer">
		<li umi:element-id="{@id}" umi:region="row" umi:field-name="name" umi:empty="&empty-section-name;" umi:delete="delete">
			<xsl:if test="@status = 'active'">
				<xsl:attribute name="class">active</xsl:attribute>
			</xsl:if>
			<a href="{@link}">
				<xsl:value-of select="@name" />
			</a>
		</li>
	</xsl:template>

	<xsl:template match="udata[@module = 'content'][@method = 'menu']" mode="menu_activities">
		<xsl:variable name="all_items_count" select="count(items/item)" />

		<xsl:variable name="items_count">
			<xsl:choose>
				<xsl:when test="$all_items_count mod 2">
					<xsl:value-of select="$all_items_count + 1"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$all_items_count"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:variable name="col_height" select="$items_count div 2"/>
		<xsl:variable name="first_col_height" select="$items_count div 2 + 1" />

		<ul class="footer-menu footer-menu-half">
			<xsl:for-each select="items/item">
				<xsl:if test="position() &lt; $first_col_height">
					<xsl:apply-templates select="." mode="menu_footer"/>
				</xsl:if>
			</xsl:for-each>
		</ul>
		<ul class="footer-menu footer-menu-half">
			<xsl:for-each select="items/item">
				<xsl:if test="position() &gt; $col_height">
					<xsl:apply-templates select="." mode="menu_footer"/>
				</xsl:if>
			</xsl:for-each>
		</ul>
	</xsl:template>

</xsl:stylesheet>