<!doctype html>
<html class="no-js" lang="en" xmlns="http://www.w3.org/1999/html">
    <head>
        
        
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="description" content="PPI Skeleton Project">
        <meta name="viewport" content="width=device-width">
        
        <title>PPI Exception Has Occurred</title>
        
    </head>
    
    <style type="text/css">
        
body {
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
    font-size: 14px;
    line-height: 20px;
    color: #333;
    
}

.box {
	border: 1px solid #DEDEDE;
	border-radius: 3px;
	margin-top: 10px;
	margin-bottom: 10px;
	box-shadow: 0 0 10px #BDBDBD;
    
}

.box-header {
	border: none;
	position: relative;
	padding-top: 5px;
	border-bottom: 1px solid #DEDEDE;
	border-radius: 3px 3px 0 0;
	height: 12px;
	min-height: 12px;
	margin-bottom: 0;
	font-weight: bold;
	font-size: 16px;
	background: -moz-linear-gradient(top, rgba(255, 255, 255, 0) 0%, rgba(0, 0, 0, 0.1) 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255, 255, 255, 0)), color-stop(100%,rgba(0, 0, 0, 0.1)));
	background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 0%,rgba(0, 0, 0, 0.1) 100%);
	background: -o-linear-gradient(top, rgba(255, 255, 255, 0) 0%,rgba(0, 0, 0, 0.1) 100%);
	background: -ms-linear-gradient(top, rgba(255, 255, 255, 0) 0%,rgba(0, 0, 0, 0.1) 100%);
	background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%,rgba(0, 0, 0, 0.1) 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#00ffffff', endColorstr='#1a000000',GradientType=0 );
}

.box-content {
	padding: 10px;
}

.box-header h2 {
	font-size: 15px;
	padding-top: 0;
	margin-top: 0;
	margin-bottom: 0;
	width: auto;
	clear: none;
	float: left;
	line-height: 25px;
}
        
.well {
    padding: 4px 12px 18px 12px;
    -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
    -moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
}

table {
border-collapse: collapse;
border-spacing: 0;
}

.table th, .table td {
    padding: 8px;
    line-height: 20px;
    text-align: left;
    vertical-align: top;
    border-top: 1px solid #DDD;
}

.table thead th {
    vertical-align: bottom;
}

.table th {
    font-weight: bold;
}

.table caption + thead tr:first-child th, .table caption + thead tr:first-child td, .table colgroup + thead tr:first-child th, .table colgroup + thead tr:first-child td, .table thead:first-child tr:first-child th, .table thead:first-child tr:first-child td {
    border-top: 0;
}

.table-striped tbody tr:nth-child(odd) td, .table-striped tbody tr:nth-child(odd) th {
    background-color: #F9F9F9;
}

.table-hover tbody tr:hover td, .table-hover tbody tr:hover th {
    background-color: whiteSmoke;
}

.bs-docs-example .table, .bs-docs-example .progress, .bs-docs-example .well, .bs-docs-example .alert, .bs-docs-example .hero-unit, .bs-docs-example .pagination, .bs-docs-example .navbar, .bs-docs-example > .nav, .bs-docs-example blockquote {
    margin-bottom: 5px;
}
.table {
width: 100%;
margin-bottom: 20px;
}
table {
max-width: 100%;
background-color: transparent;
border-collapse: collapse;
border-spacing: 0;
}  
        
.exception-summary {
    position: relative;
}
.exception-summary .title {
    font-size: 1.3em;
    margin-bottom: 12px;
}
.exception-summary, .exception-handlers, .backtrace-container {
    border: 1px solid #D7D7D7;
    padding: 12px;
    font-size: 1.2em;
    margin-bottom: 12px;
}

.intro {
    font-size: 1.4em;
    padding: 0px;
    margin: 8px 8px 12px 8px; 
}

.ppi-logo {
    position: absolute;
    right: 0;
    top: 12px;
   
}


    </style>
</head>
    <body>
        <div class="container">
            
            <div class="box">
                
                <div class="box-header well">
                    <h2>An Exception Has Occured</h2>
                </div>
                
                <div class="box-content">
                
                <div class="exception-summary">
                    <div class="title">Exception Details</div>
                    <p><strong>File:</strong> <?= $e->getFile() ?></p>
                    <p><strong>Line:</strong> <?= $e->getLine() ?></p>
                    <p><strong>Message:</strong> <?= $e->getMessage() ?></p>
                    
                    <img class="ppi-logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFkAAAA4CAYAAACWo1RQAAAD8GlDQ1BJQ0MgUHJvZmlsZQAAKJGNVd1v21QUP4lvXKQWP6Cxjg4Vi69VU1u5GxqtxgZJk6XpQhq5zdgqpMl1bhpT1za2021Vn/YCbwz4A4CyBx6QeEIaDMT2su0BtElTQRXVJKQ9dNpAaJP2gqpwrq9Tu13GuJGvfznndz7v0TVAx1ea45hJGWDe8l01n5GPn5iWO1YhCc9BJ/RAp6Z7TrpcLgIuxoVH1sNfIcHeNwfa6/9zdVappwMknkJsVz19HvFpgJSpO64PIN5G+fAp30Hc8TziHS4miFhheJbjLMMzHB8POFPqKGKWi6TXtSriJcT9MzH5bAzzHIK1I08t6hq6zHpRdu2aYdJYuk9Q/881bzZa8Xrx6fLmJo/iu4/VXnfH1BB/rmu5ScQvI77m+BkmfxXxvcZcJY14L0DymZp7pML5yTcW61PvIN6JuGr4halQvmjNlCa4bXJ5zj6qhpxrujeKPYMXEd+q00KR5yNAlWZzrF+Ie+uNsdC/MO4tTOZafhbroyXuR3Df08bLiHsQf+ja6gTPWVimZl7l/oUrjl8OcxDWLbNU5D6JRL2gxkDu16fGuC054OMhclsyXTOOFEL+kmMGs4i5kfNuQ62EnBuam8tzP+Q+tSqhz9SuqpZlvR1EfBiOJTSgYMMM7jpYsAEyqJCHDL4dcFFTAwNMlFDUUpQYiadhDmXteeWAw3HEmA2s15k1RmnP4RHuhBybdBOF7MfnICmSQ2SYjIBM3iRvkcMki9IRcnDTthyLz2Ld2fTzPjTQK+Mdg8y5nkZfFO+se9LQr3/09xZr+5GcaSufeAfAww60mAPx+q8u/bAr8rFCLrx7s+vqEkw8qb+p26n11Aruq6m1iJH6PbWGv1VIY25mkNE8PkaQhxfLIF7DZXx80HD/A3l2jLclYs061xNpWCfoB6WHJTjbH0mV35Q/lRXlC+W8cndbl9t2SfhU+Fb4UfhO+F74GWThknBZ+Em4InwjXIyd1ePnY/Psg3pb1TJNu15TMKWMtFt6ScpKL0ivSMXIn9QtDUlj0h7U7N48t3i8eC0GnMC91dX2sTivgloDTgUVeEGHLTizbf5Da9JLhkhh29QOs1luMcScmBXTIIt7xRFxSBxnuJWfuAd1I7jntkyd/pgKaIwVr3MgmDo2q8x6IdB5QH162mcX7ajtnHGN2bov71OU1+U0fqqoXLD0wX5ZM005UHmySz3qLtDqILDvIL+iH6jB9y2x83ok898GOPQX3lk3Itl0A+BrD6D7tUjWh3fis58BXDigN9yF8M5PJH4B8Gr79/F/XRm8m241mw/wvur4BGDj42bzn+Vmc+NL9L8GcMn8F1kAcXjEKMJAAAAACXBIWXMAAAsTAAALEwEAmpwYAAABcWlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNC40LjAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iPgogICAgICAgICA8eG1wOkNyZWF0b3JUb29sPkFkb2JlIFBob3Rvc2hvcCBDUzUuMSBXaW5kb3dzPC94bXA6Q3JlYXRvclRvb2w+CiAgICAgIDwvcmRmOkRlc2NyaXB0aW9uPgogICA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgp2/56LAAABT0lEQVR4nO3awRHCMAxE0TVDnUkpQCtp1JwYjpFsZ2Ur+wuw0JtcnFBqrVD/tm0zgRzHUaxnPtp/jrImZEJCJiRkQkImJGRCQiYkZEJCJvQccci+7+9a62vEWb8sN6rIuZ4zu5/kKxbNNrcLeaVFI+c2I6+2aOTcJuQVF42c60ZeddHIuS7klReNnGtGXn3RyLkm5AyLRs4tZ5+fohYtpXxmnuv5/HR647Muah1q/YY2+1xPendBSMiEhExIyISETEjIhIRMSMiEhExoyDc+wH6jGl3UXE9TPsme9wIrzJ0OORswMBlyRmBgIuSswMAkyJmBgQmQswMDwch3AAYCke8CDAQh3wkYGHjjywJ3xR5DnuQswFfVjSzg87qQBWyrGVnA9pqQBezLjSxgfy5kAbdlRhZweyZkAfd1+tdZ1V/4q847JGRCQiYkZEJCJvQFzbQHSI16MrIAAAAASUVORK5CYII=" />
                    
                </div>
                
            <div class="exception-handlers">
                <p class="intro">The following exception handlers were also executed.</p>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr><th>Class Name</th><th>Status</th><th>Message from handler</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach($this->_handlerStatus as $handler): ?>
                    <tr>
                        <td><?= $handler['object'] ?></td>
                        <td><?= ($handler['response']['status']) ? 'Successful' : 'Fail' ?></td>
                        <td><?= $handler['response']['message']?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>   
                    
            <div class="backtrace-container">
                
                <?php if(isset($trace) && !empty($trace)): ?>
                <p class="intro">Stack Trace.</p>
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>File</th>
                                <th>Line</th>
                                <th>Class</th>
                                <th>Function</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php foreach($trace as $k => $t): ?>
                                <tr class="<?= ($k % 2 == 0) ? 'alt' : '' ?>">
                                        <td><?= $k ?></td>
                                        <td><?= $t['file'] ?></td>
                                        <td><?= $t['line'] ?></td>
                                        <td><?= $t['class'] ?></td>
                                        <td><?= $t['function'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                <?php endif; ?>
                
            </div>
                    
        </div>

    </div>

</div>
    
</body>
</html>