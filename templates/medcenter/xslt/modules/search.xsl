<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi">

	<xsl:template match="udata[@method = 'insert_form']">
		 <form method="get" action="/search/search_do/">
			<div class="search">
				<input name="search_string" placeholder="Поиск" type="text"/>
				<input type="submit" value="" />
			</div>
		</form>
	</xsl:template>

	<xsl:template match="result[@module = 'search'][@method = 'search_do']">
		<xsl:variable name="search-results" select="document('udata://search/search_do/')/udata" />
		<xsl:apply-templates select="$search-results"/>
		<!-- <xsl:apply-templates select="document(concat('udata://system/numpages/', $search-results/total, '/', $search-results/per_page, '/notemplate/p/3'))" mode="paging.numbers" /> -->
		<xsl:if test=" ceiling(total div per_page) &gt; 1">
			<xsl:apply-templates select="document(concat('udata://system/numpages/', $search-results/total, '/', $search-results/per_page, '/'))/udata">
				<xsl:with-param name="numpages" select="ceiling(total div per_page)" />
			</xsl:apply-templates>
		</xsl:if>
	</xsl:template>

	<xsl:template match="udata[@module = 'search'][@method = 'search_do'][not(items/item)]">
		<h1>
		    <xsl:text>По запросу </xsl:text>
		    <span>&#171;<xsl:value-of select="$search_string" />&#187;</span>
		    <xsl:text> ничего не найдено.</xsl:text>
		</h1>
	</xsl:template>

	<xsl:template match="udata[@module = 'search'][@method = 'search_do'][items/item]">

		<xsl:variable name="per_page" select="per_page"/>
		<h1>Найдено страниц:
			<xsl:text> </xsl:text>
			<xsl:value-of select="total" />
		</h1>

		<ul class="list">
			<!-- <xsl:apply-templates select="items/item" mode="search.results"/> -->
			<xsl:for-each select="items/item">
				<li>
					<h3>
						<span class="black">
							<xsl:value-of select="$p * $per_page + position()" />
							<xsl:text>.  </xsl:text>
						</span>
						<a href="{@link}">
					    	<xsl:value-of select="@name"/>
					    </a>
					</h3>
			    	<xsl:value-of select="." disable-output-escaping="yes"/>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
</xsl:stylesheet>