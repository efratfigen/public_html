<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template name="incHeader" match="/spetml:page/includes/include[@name='incHeader']">
		<h1>
			<a href="/">
				<img alt="Solar Logistix" src="/files/images/logotop.png" />
			</a>
		</h1>
		<div id="dvLogoText">
			<div>Professional <span class="blue">Power</span> For The People</div>
			<div id="dvLogoSubText">&#8220;The Sun isn't just for light anymore&#8221;</div>
		</div>

		<div id="dvHamburgerBar">
			<button class="hamburger" />
		</div>
		<div id="dvNavContainer">
			<ul id="ulNav">
				<xsl:for-each select="/spetml:page/spetml:site/spetml:topNavigation/spetml:page[@navBar='true']">
					<li class="{@name} {name(/spetml:page[@name=current()/@name])}">
						<xsl:attribute name="class">
							<xsl:value-of select="@name" />
							<xsl:choose>
								<xsl:when test="@name=/spetml:page/@name">
									<xsl:text> selected</xsl:text>
								</xsl:when>
								<xsl:when test="//spetml:page[@name=/spetml:page/@name and ancestor::spetml:page[@name=current()/@name]] and not(@name = 'home') and not(../spetml:page[@name=/spetml:page/@name])">
									<xsl:text> subselected</xsl:text>
								</xsl:when>
							</xsl:choose>
						</xsl:attribute>
						<a href="/{current()[not(@name='home')]/@name}">
							<xsl:value-of select="/spetml:page/spetml:site/spetml:structure//spetml:page[@name=current()/@name]/@title" />
						</a>
					</li>
				</xsl:for-each>
			</ul>
		</div>


	</xsl:template>
</xsl:stylesheet>
