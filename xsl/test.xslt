<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" version="1.0" xmlns:spet="http://www.spetnik.com/2013/spetcms" xmlns:spetml="http://www.spetnik.com/2013/spetml" xmlns:php="http://php.net/xsl" xmlns:sl="http://example.com/solarlogistix" exclude-result-prefixes="spet spetml php sl xhtml">
	<xsl:output method="html" doctype-system="about:legacy-compat" indent="yes" version="1.0" />
<xsl:template match="@*|node()">
  <xsl:copy>
    <xsl:apply-templates select="@*|node()"/>
  </xsl:copy>
</xsl:template>
	
	
	<xsl:template match="/spetml:page">
		<html>
			<head><title>TEST</title></head>
			<body>
				<xsl:apply-templates select="sl:books" />
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="sl:books">
		<dl>
			<xsl:apply-templates />
		</dl>
	</xsl:template>

	<xsl:template match="sl:book">
		<xsl:apply-templates />
	</xsl:template>
	
	<xsl:template match="sl:title">
		<dt>
			<xsl:value-of select="." />
		</dt>
	</xsl:template>

	<xsl:template match="sl:purchase">
		<b>
			Buy now <xsl:value-of select="@type" />
		</b>
	</xsl:template>

	<xsl:template match="sl:author">
		<dd>
			<xsl:value-of select="." />
		</dd>
	</xsl:template>

	<xsl:template match="sl:blurb">
		<dd>
			<xsl:apply-templates />
		</dd>
	</xsl:template>

	<xsl:template match="xhtml:*">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()" />	
		</xsl:copy>
	</xsl:template>

	
</xsl:stylesheet>