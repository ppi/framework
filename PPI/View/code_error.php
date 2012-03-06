<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   PPI
 */

  foreach(array('file', 'code', 'message', 'backtrace', 'line') as $field) {
	if(!array_key_exists($field, $p_aError)) {
		$p_aError[$field] = '';
	}
  }
  $accordionIconPath = $baseUrl . 'images/ppi/error-accordion-icon.gif';
  $errorIconPath = $baseUrl . 'images/ppi/error-icon.png';

$header = <<<OUTPUT
<html>
<head>
	<style type="text/css">
	body {
		font-family: monospace;
		background-color: #F6FFE9;
	}
	#code_error td.title {
		font-weight: bold;
	}
	.accordion h3 {
		background:url("{$accordionIconPath}") no-repeat scroll right -51px #E9E7E7;
		border-color:#C4C4C4 #C4C4C4 -moz-use-text-color;
		border-style:solid solid none;
		border-width:1px 1px medium;
		cursor:pointer;
		font:bold 120%/100% Arial,Helvetica,sans-serif;
		margin:0;
		padding:7px 15px;
	}
	.accordion h3.active {
		background-position:right 5px;
	}
	.accordion div {
		background:none repeat scroll 0 0 #F7F7F7;
		border-left:1px solid #C4C4C4;
		border-right:1px solid #C4C4C4;
		margin:0;
		display: none;
		padding:10px 15px 20px;
	}
	.accordion {
		font-family: Arial;
		max-width: 100%;
	}
	</style>
	<link rel="stylesheet" href="{$baseUrl}app/css/errors.css" />
	<link rel="stylesheet" href="{$baseUrl}app/css/styles.css" />
	<script type="text/javascript" src="{$baseUrl}scripts/jquery1.4.2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.accordion h3').live('click', function() {
				$(this).toggleClass('active').next().slideToggle();
			});
		});
	</script>
	<title>PHP code error</title>
	<body>
	<table style="height: 100%; width: 100%;">
	<tr><td valign="middle" style="width: 100%;">
		<div id="content" style="border: 1px solid #C4C4C4; padding: 15px; background-color: white; position: relative;">
		<h1 style="margin-top: 0;">{$heading}</h1>
OUTPUT;

$html = <<<OUTPUT
        <table id="code_error">
          <tr>
            <td class="title">File: </td>
             <td>{$p_aError['file']}</td>
          </tr>
<!--
          <tr>
            <td class="title">errno: </td>
             <td>{$p_aError['code']}</td>
          </tr>
-->
          <tr>
            <td class="title">line: </td>
             <td>{$p_aError['line']}</td>
          </tr>
          <tr>
				<td class="title">message: </td>
				<td>{$p_aError['message']}</td>
          </tr>
          <tr>
          </tr>
        </table>
		<h3>Backtrace</h3>
OUTPUT;
$html .= nl2br($p_aError['backtrace']) . '<br><br>';
if(isset($p_aError['sql']) && !empty($p_aError['sql'])) {
	$html .= "<h3>MySQL Backtrace</h3>";
	$i = 1;
	foreach($p_aError['sql'] as $query) {
		$html .= "<b>#$i</b> - $query<bR><br>";
		$i++;
	}

}

foreach(array('POST' => $_POST, 'COOKIE' => $_COOKIE, 'SESSION' => $_SESSION, 'SERVER' => $_SERVER) as $title => $block) {
	if(empty($block)) {
		continue;
	}
	$block = nl2br(str_replace('  ', '&nbsp;&nbsp;', htmlspecialchars(print_r($block, true), ENT_QUOTES, 'UTF-8')));
	$class = $title == 'SERVER' ? 'server_info' : '';
$html .= <<<OUTPUT
			<div class="accordion">
				<h3>{$title}</h3>
				<div class="{$class}">{$block}</div>
			</div>
OUTPUT;
}

$footer = <<<OUTPUT
		</div>
	</td></tr></table> <!-- end of wrapper table  -->
</body>
</html>
OUTPUT;
?>