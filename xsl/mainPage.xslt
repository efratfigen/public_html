<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" version="1.0" xmlns:spet="http://www.spetnik.com/2013/spetcms" xmlns:php="http://php.net/xsl" xmlns:spetml="http://www.spetnik.com/2013/spetml" xmlns:sl="http://example.com/solarlogistix" exclude-result-prefixes="spet spetml php sl xhtml">
	<spet:includes>
		<spet:include name="incHead" />
		<spet:include name="incHeader" />
		<spet:include name="incFooter" />
	</spet:includes>
<xsl:output method="html" doctype-system="about:legacy-compat" indent="yes" version="1.0" />
	<xsl:variable name="pageTitle" />

	<xsl:template match="@*|node()">
	  <xsl:copy>
		<xsl:apply-templates select="@*|node()"/>
	  </xsl:copy>
	</xsl:template>

	<xsl:template match="spetml:*" />
	<xsl:template match="sl:*" />

	<xsl:template match="/spetml:page">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
			<head>
				<xsl:call-template name="incHead" indent="yes" />
			</head>
			<body class="{/spetml:page/@name}">
				<div id="dvOuter">
					<xsl:call-template name="incHeader" />

					<xsl:apply-templates select="sl:topWidget" />

					<xsl:if test="/spetml:page/@name != /spetml:page/spetml:site/spetml:structure/spetml:page/@name">
						<div class="breadcrumbs">
							<xsl:for-each select="/spetml:page/spetml:site/spetml:structure//spetml:page[@name=/spetml:page/@name]/ancestor::spetml:page[ancestor::spetml:structure]">
								<a href="/{@name}" style="z-index:{3 - count(ancestor::spetml:page)}">
									<xsl:attribute name="href">
										<xsl:text>/</xsl:text>
										<xsl:if test="count(ancestor::spetml:page[ancestor::spetml:structure]) &gt; 0">
											<xsl:value-of select="@name" />
										</xsl:if>
									</xsl:attribute>
									<xsl:attribute name="class">
										<xsl:text>arrow-right</xsl:text>
										<xsl:if test="count(ancestor::spetml:page[ancestor::spetml:structure]) = 0"> dark home </xsl:if>
										z<xsl:value-of select="3 - count(ancestor::spetml:page)" />
									</xsl:attribute>
									<xsl:value-of select="@title" />
								</a>
							</xsl:for-each>
							<a class="arrow-right light">
								<xsl:if test="not(/spetml:page/sl:breadcrumb/@link='none')">
									<xsl:attribute name="href">
										<xsl:choose>
											<xsl:when test="/spetml:page/sl:breadcrumb/@link">
												<xsl:value-of select="/spetml:page/sl:breadcrumb/@link" />
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="@name" />
											</xsl:otherwise>
										</xsl:choose>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="/spetml:page/spetml:site/spetml:structure//spetml:page[@name=/spetml:page/@name]/@title" />
							</a>
						</div>
					</xsl:if>

					<div id="dvContent" class="main-content">
						<xsl:apply-templates select="sl:mainFrame" />
					</div>

					<xsl:if test="not(/spetml:page/@name='home')">
						<div class="right">
							<xsl:apply-templates select="/spetml:page/spetml:site/spetml:structure//spetml:page[@name=/spetml:page/@name]/@sideNav|/spetml:page/spetml:site/spetml:structure//spetml:page[.//spetml:page/@name=/spetml:page/@name and @sideNav]/@sideNav" />

							<xsl:if test="not(/spetml:page/@name='company-contact' or /spetml:page/@name='company-support')">
								<a href="/company-contact/quote">
									<div class="sideButton">
										<h4>Get Started Now</h4>
										<h5>
											Click here to contact us for a quote
										</h5>
									</div>
								</a>
							</xsl:if>
						</div>
					</xsl:if>

					<xsl:call-template name="incFooter" />
				</div>
			</body>
		</html>
  	</xsl:template>


	<xsl:template match="sl:widget[@name='testimonials']">
		<section class="testimonials left">
			<h4>Testimonials</h4>
			<ul class="testimonials">
				<xsl:for-each select="/spetml:page/spetml:site/sl:testimonials/sl:testimonial">
					<li>
						<p class="quote">
							&#8220;<xsl:value-of select="normalize-space(sl:quote)" />&#8221;
						</p>
						<p class="signature">
							<xsl:value-of select="sl:author" />
						</p>
						<p class="subsignature">
							<xsl:value-of select="sl:company" />
						</p>
					</li>
				</xsl:for-each>
			</ul>
		</section>
  	</xsl:template>

  	<xsl:template match="@sl-value[.='domain']">
  		<xsl:attribute name="href">
  			<xsl:text>http://</xsl:text>
  			<xsl:value-of select="/spetml:page/spetml:request/spetml:headers/spetml:header[@name='Host']" />
  		</xsl:attribute>
  	</xsl:template>

	<xsl:template match="sl:domainName">
		<xsl:value-of select="/spetml:page/spetml:request/spetml:headers/spetml:header[@name='Host']" />
	</xsl:template>

	<xsl:template match="sl:domainNameUrl">
		<a>
			<xsl:attribute name="href">
				<xsl:value-of select="/spetml:header[@name='Host']"/>
			</xsl:attribute>
			<xsl:text>Solar Logistix</xsl:text>
		</a>
	</xsl:template>
	
	<xsl:template match="spetml:page//sl:mainFrame">
		<xsl:apply-templates />
	</xsl:template>

  	<xsl:template match="sl:topWidget|sl:topWidget[@name='banner']">
  		<div id="dvBanner">
  			<h2>
  				<xsl:value-of select="/spetml:page/@title" />
  			</h2>
  		</div>
  	</xsl:template>

  	<xsl:template match="sl:topWidget[@name='homeSlides']">
  		<xsl:apply-templates />
  	</xsl:template>

  	<xsl:template match="/spetml:page/spetml:site/spetml:structure//spetml:page[@name=/spetml:page/@name]/@sideNav|/spetml:page/spetml:site/spetml:structure//spetml:page[.//spetml:page/@name=/spetml:page/@name and @sideNav]/@sideNav">
  		<ul id="ulSideNav">
  			<li>
  				<xsl:attribute name="class">
					<xsl:text>header</xsl:text>
					<xsl:if test="../@name=/spetml:page/@name">
						<xsl:text> selected</xsl:text>
					</xsl:if>
				</xsl:attribute>
  				<a href="/{../@name}">
  					<xsl:value-of select="../@title" />
  				</a>
  			</li>
  			<xsl:for-each select="../spetml:page">
  				<li>
  					<xsl:attribute name="class">
  						<xsl:text>item</xsl:text>
  						<xsl:if test="@name=/spetml:page/@name">
  							<xsl:text> selected</xsl:text>
  						</xsl:if>
  					</xsl:attribute>
  					<a href="/{@name}">
  						<xsl:value-of select="@title" />
  					</a>
  				</li>
  			</xsl:for-each>
  		</ul>
  	</xsl:template>

	


</xsl:stylesheet>