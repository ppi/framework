<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   PPI
 */
/*
  foreach(array('file', 'code', 'message', 'backtrace', 'line') as $field) {
	if(!array_key_exists($field, $p_aError)) {
		$p_aError[$field] = '';
	}
  }
*/
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
	</style>
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

$footer = <<<OUTPUT
		</div>
	</td></tr></table> <!-- end of wrapper table  -->
</body>
</html>
OUTPUT;
?>