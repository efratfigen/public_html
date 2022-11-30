<?php
	$db['server'] = 'localhost';
	$db['port'] = '';
	$db['database'] = '';
	$db['user'] = '';
	$db['pass'] = '';
	$db['flags'] = 0;

	$site['defpage'] = 'home';
	$site['xmldir'] = "/xml";
	$site['xsldir'] = "/xsl";
	$site['moddir'] = "/php/modules";
	$site['structfile'] = '_sitestructure.xml';
	$site['deferrorpage'] = 'error';
	$site['blockprefix'] = '_'; //prefix for inaccessible pages
	$site['phpxsltfunctions'] = array("formatStringDate", "compareDates", "currentDate", "futureDate"); //PHP functions to be mapped to XSLT

?>
