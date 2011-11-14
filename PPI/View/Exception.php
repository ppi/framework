<?php $trace = $e->getTrace(); ?>
<!DOCTYPE html>
<html>
<head>
	<style>
		body {
			width:800px;
			margin:0 auto;
			font-family:Arial, sans-serif;
		}
		h1, .border {
			margin-bottom:10px;
			padding-bottom:10px;
			border-bottom:1px solid;
		}
		h2 {
			margin:10px 0;
		}
		table {
			width:800px;
			font-size:12px;
		}
		table th {
			text-align:left;
			background:#404040;
			color:#fff;
		}
		table th, table td {
			padding:6px;
		}
		table td {
			background:#efefef;
		}
		table tr.alt td {
			background:#ddd;
		}
		table tr:hover td {
			background:#bbb;
		}
	</style>
</head>
<body>
	<h1>PPI Exception (<?= get_class($e); ?>)</h1>
	<div><strong>File:</strong> <?= $e->getFile() ?></div>
	<div><strong>Line:</strong> <?= $e->getLine() ?></div>
	<div><strong>Message:</strong> <?= $e->getMessage() ?></div>
	<?php if($e->getCode() !== 0): ?>
	<div class="border"><strong>Code:</strong> <?= $e->getCode() ?></div>
	<?php endif; ?>
	<?php if(!empty($this->_listenerStatus)):?>
		<h2>Listeners</h2>
		<table>
		<tr>
		<th>Listener Object</th>
		<th>Status</th>
		<th>Message</th>
		</tr>
		<?php foreach($this->_listenerStatus as $listener): ?>
		<tr>
			<td><?= $listener['object'] ?></td>
			<td><?= ($listener['response']['status']) ? 'Success' : 'Fail' ?></td>
			<td><?= $listener['response']['message']?></td>
		</tr>
		<?php endforeach; ?>
		</table>
	<?php endif; ?>
	<?php if(isset($trace) && !empty($trace)): ?>
		<h2>Stack Trace</h2>
		<table>
		<tr>
			<th>#</th>
			<th>File</th>
			<th>Line</th>
			<th>Class</th>
			<th>Function</th>
		</tr>
		<?php foreach($trace as $k => $t): ?>
		<tr class="<?= ($k % 2 == 0) ? 'alt' : '' ?>">
			<td><?= $k ?></td>
			<td><?= $t['file'] ?></td>
			<td><?= $t['line'] ?></td>
			<td><?= $t['class'] ?></td>
			<td><?= $t['function'] ?></td>
		</tr>
		<?php endforeach; ?>
		</table>
	<?php endif; ?>
</body>
</html>