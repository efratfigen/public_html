<xsl:stylesheet cdata-section-elements="script" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0" >
	<xsl:template name="incFooter">
		<div id="dvFooter">
			<hr />

			<div class="third logo">
				<a href="/">
					<img alt="Solar Logistix" src="/files/images/logotop.png" />
				</a>
			</div>

			<div class="third contact">
				<div class="contact">
					<h5>Mailing Address:</h5>
					<p>
						Solar Logistics, Inc<br/>
						123 Any Street<br/>
						Suite 123<br/>
						Anytown, East Hampshire 12345
					</p>
					<p>
						Phone: <a href="tel:+13148029606">+1.212.555.0155</a>
						<br/>
						Fax: <a href="tel:+13148029607">+1.212.555.0156</a>
					</p>
				</div>
			</div>

			<div class="third social">
				<h4>Stay Connected</h4>
				<a class="facebook" href="http://facebook.com/">Facebook</a>
				<a class="twitter" href="http://twitter.com/">Twitter</a>
				<br />
				<a class="gplus" href="https://www.google.com/">Google+</a>
				<a class="rss" href="/rss">RSS</a>
			</div>

			<hr />
			<p class="center">
				Copyright &#169; Solar Logix, Inc <xsl:value-of select="php:function('formatStringDate', php:function('currentDate'), 'Y', 'UTC')" />
			</p>
		</div>
	</xsl:template>
</xsl:stylesheet>
