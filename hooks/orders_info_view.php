<?php
	/* Assuming this custom file is placed inside 'hooks' */
	define('PREPEND_PATH', '../');
	$hooks_dir = dirname(__FILE__);
	include("{$hooks_dir}/../defaultLang.php");
	include("{$hooks_dir}/../language.php");
	include("{$hooks_dir}/../lib.php");
	
	include_once("{$hooks_dir}/../header.php");
	
/*
	TODO:
	the dv form
	TVP
	DVP - multiple
	correct shipper field value
	no need for specifying templates
	check filters
	check quick search
*/
	
	/* check access */
	$mi = getMemberInfo();
	if(!in_array($mi['group'], array('Admins', 'Data entry'))){
		echo error_message("Access denied");
		include_once("{$hooks_dir}/../footer.php");
		exit;
	}
	
	/* create view SQL */
	$view_name = 'orders_info';
	$view_sql = "CREATE OR REPLACE VIEW `{$view_name}` AS
		SELECT
			o.OrderID, o.OrderDate, o.RequiredDate, o.ShippedDate,
			c.CompanyName AS 'Customer',
			c.City,
			c.Country,
			CONCAT_WS(' ', e.FirstName, e.LastName) AS 'Employee', 
			o.ShipVia AS 'Shipper', 
			SUM(d.UnitPrice * d.Quantity) AS 'Subtotal',
			o.Freight,
			SUM(d.UnitPrice * d.Quantity) + o.Freight  AS 'Total'
		FROM
			orders o LEFT JOIN
			order_details d ON o.OrderID=d.OrderID LEFT JOIN
			customers c ON c.CustomerID=o.CustomerID LEFT JOIN
			employees e ON e.EmployeeID=o.EmployeeID
		GROUP BY o.OrderID
	";
	
	sql($view_sql, $eo);
	
	$x = new DataList;
	$x->TableName = $view_name;

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = array(   
		'OrderID' => 'OrderID',
		'OrderDate' => 'OrderDate',
		'RequiredDate' => 'RequiredDate',
		'ShippedDate' => 'ShippedDate',
		'Customer' => 'Customer',
		'City' => 'City',
		'Country' => 'Country',
		'Employee' => 'Employee',
		'Shipper' => 'Shipper',
		'Subtotal' => 'Subtotal',
		'Freight' => 'Freight',
		'Total' => 'Total'
	);
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = array(   
		1 => '`OrderID`',
		2 => '`OrderDate`',
		3 => '`RequiredDate`',
		4 => '`shippedDate`',
		5 => 5,
		6 => 6,
		7 => 7,
		8 => 8,
		9 => 9,
		10 => '`Subtotal`',
		11 => '`Freight`',
		12 => '`Total`'
	);

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = $x->QueryFieldsTV;

	// Fields that can be filtered
	$x->QueryFieldsFilters = $x->QueryFieldsTV;

	// Fields that can be quick searched
	$x->QueryFieldsQS = $x->QueryFieldsTV;

	// Lookup fields that can be used as filterers
	$x->filterers = array();

	$x->QueryFrom = "`{$view_name}`";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = 0;
	$x->AllowDelete = 0;
	$x->AllowMassDelete = false;
	$x->AllowInsert = 0;
	$x->AllowUpdate = 0;
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = 1;
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowCSV = 1;
	$x->RecordsPerPage = 10;
	$x->QuickSearch = 1;
	$x->QuickSearchText = $Translation["quick search"];
	$x->ScriptFileName = "orders_info_view.php";
	$x->RedirectAfterInsert = $x->ScriptFileName;
	$x->TableTitle = "Orders Info";
	$x->TableIcon = PREPEND_PATH . "resources/table_icons/cash_register.png";
	$x->PrimaryKey = "`OrderID`";
	$x->DefaultSortField = '1';
	$x->DefaultSortDirection = 'desc';

	$x->ColWidth   = array();
	$x->ColCaption = array_values($x->QueryFieldsTV);
	$x->ColFieldName = array_keys($x->QueryFieldsTV);
	$x->ColNumber  = range(1, count($x->QueryFieldsTV));

	// template paths below are based on the app main directory
	$x->Template = 'templates/orders_templateTV.html';
	$x->SelectedTemplate = 'templates/orders_templateTVS.html';
	$x->TemplateDV = 'templates/orders_templateDV.html';
	$x->TemplateDVP = 'templates/orders_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->ShowRecordSlots = 0;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HighlightColor = '#FFF0C2';

	$x->Render();

	echo $x->HTML;
	
	include_once("{$hooks_dir}/../footer.php");
	
	function orders_info_form($id = '', $upd = 1, $ins = 1, $del = 1, $cncl = 0, $tmplt_dv = '', $tmplt_dvp = ''){
		return $id;
	}