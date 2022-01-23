<xsl:stylesheet cdata-section-elements="script" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0" xmlns:spetml="http://www.spetnik.com/2013/spetml" xmlns:sl="http://example.com/solarlogistix">

	<xsl:template name="incHead">
		<meta name="robots" content="noindex, nofollow" />
		<script type="text/javascript" src="/files/scripts/jquery/jquery-1.10.2.min.js" />

		<xsl:copy-of select="spetml:page/head/node()" disable-output-escaping="yes"/>

		<xsl:variable name="finalTitle">
			<xsl:if test="string-length($pageTitle)"><xsl:copy-of select="$pageTitle" /><xsl:text> | </xsl:text></xsl:if>
			<xsl:if test="/spetml:page/@title and /spetml:page/@name!='home'"><xsl:value-of select="/spetml:page/@title"/><xsl:text> | </xsl:text></xsl:if>
			<xsl:text>Solar Logistix Solar Power</xsl:text>
		</xsl:variable>
        <title>
        	<xsl:value-of select="$finalTitle" />
        </title>
		<xsl:if test="/spetml:page/sl:description">
			<meta name="description" content="{normalize-space(/spetml:page/sl:description)}"/>
		</xsl:if>
		<link rel="icon" type="image/png" href="/files/images/favicon.png" />
		<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" />
		<link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet" />
		<link rel="Stylesheet" type="text/css" href="/files/styles/main.css" media="screen and (min-width: 1017px)" />
		<link rel="Stylesheet" type="text/css" href="/files/styles/main1016.css" media="screen and (max-width: 1016px)" />
		<meta http-equiv="content-language" content="en-us" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

		<script type="text/javascript" src="/files/scripts/main.js" />

		<script type="text/javascript">
			var argarray = new Array();
			<xsl:for-each select="/spetml:page/args/*">
				argarray['<xsl:value-of select="local-name()"/>'] = '<xsl:value-of select="."/>';
			</xsl:for-each>
		</script>

	</xsl:template>
</xsl:stylesheet>
