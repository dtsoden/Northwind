<?php

// Data functions (insert, update, delete, form) for table products

// This script and data application were generated by AppGini 5.72
// Download AppGini for free from https://bigprof.com/appgini/download/

function products_insert(){
	global $Translation;

	// mm: can member insert record?
	$arrPerm=getTablePermissions('products');
	if(!$arrPerm[1]){
		return false;
	}

	$data['ProductName'] = makeSafe($_REQUEST['ProductName']);
		if($data['ProductName'] == empty_lookup_value){ $data['ProductName'] = ''; }
	$data['SupplierID'] = makeSafe($_REQUEST['SupplierID']);
		if($data['SupplierID'] == empty_lookup_value){ $data['SupplierID'] = ''; }
	$data['CategoryID'] = makeSafe($_REQUEST['CategoryID']);
		if($data['CategoryID'] == empty_lookup_value){ $data['CategoryID'] = ''; }
	$data['QuantityPerUnit'] = makeSafe($_REQUEST['QuantityPerUnit']);
		if($data['QuantityPerUnit'] == empty_lookup_value){ $data['QuantityPerUnit'] = ''; }
	$data['UnitPrice'] = makeSafe($_REQUEST['UnitPrice']);
		if($data['UnitPrice'] == empty_lookup_value){ $data['UnitPrice'] = ''; }
	$data['UnitsInStock'] = makeSafe($_REQUEST['UnitsInStock']);
		if($data['UnitsInStock'] == empty_lookup_value){ $data['UnitsInStock'] = ''; }
	$data['UnitsOnOrder'] = makeSafe($_REQUEST['UnitsOnOrder']);
		if($data['UnitsOnOrder'] == empty_lookup_value){ $data['UnitsOnOrder'] = ''; }
	$data['ReorderLevel'] = makeSafe($_REQUEST['ReorderLevel']);
		if($data['ReorderLevel'] == empty_lookup_value){ $data['ReorderLevel'] = ''; }
	$data['Discontinued'] = makeSafe($_REQUEST['Discontinued']);
		if($data['Discontinued'] == empty_lookup_value){ $data['Discontinued'] = ''; }
	if($data['UnitPrice'] == '') $data['UnitPrice'] = "0";
	if($data['UnitsInStock'] == '') $data['UnitsInStock'] = "0";
	if($data['UnitsOnOrder'] == '') $data['UnitsOnOrder'] = "0";
	if($data['ReorderLevel'] == '') $data['ReorderLevel'] = "0";

	// hook: products_before_insert
	if(function_exists('products_before_insert')){
		$args=array();
		if(!products_before_insert($data, getMemberInfo(), $args)){ return false; }
	}

	$o = array('silentErrors' => true);
	sql('insert into `products` set       `ProductName`=' . (($data['ProductName'] !== '' && $data['ProductName'] !== NULL) ? "'{$data['ProductName']}'" : 'NULL') . ', `SupplierID`=' . (($data['SupplierID'] !== '' && $data['SupplierID'] !== NULL) ? "'{$data['SupplierID']}'" : 'NULL') . ', `CategoryID`=' . (($data['CategoryID'] !== '' && $data['CategoryID'] !== NULL) ? "'{$data['CategoryID']}'" : 'NULL') . ', `QuantityPerUnit`=' . (($data['QuantityPerUnit'] !== '' && $data['QuantityPerUnit'] !== NULL) ? "'{$data['QuantityPerUnit']}'" : 'NULL') . ', `UnitPrice`=' . (($data['UnitPrice'] !== '' && $data['UnitPrice'] !== NULL) ? "'{$data['UnitPrice']}'" : 'NULL') . ', `UnitsInStock`=' . (($data['UnitsInStock'] !== '' && $data['UnitsInStock'] !== NULL) ? "'{$data['UnitsInStock']}'" : 'NULL') . ', `UnitsOnOrder`=' . (($data['UnitsOnOrder'] !== '' && $data['UnitsOnOrder'] !== NULL) ? "'{$data['UnitsOnOrder']}'" : 'NULL') . ', `ReorderLevel`=' . (($data['ReorderLevel'] !== '' && $data['ReorderLevel'] !== NULL) ? "'{$data['ReorderLevel']}'" : 'NULL') . ', `Discontinued`=' . (($data['Discontinued'] !== '' && $data['Discontinued'] !== NULL) ? "'{$data['Discontinued']}'" : 'NULL'), $o);
	if($o['error']!=''){
		echo $o['error'];
		echo "<a href=\"products_view.php?addNew_x=1\">{$Translation['< back']}</a>";
		exit;
	}

	$recID = db_insert_id(db_link());

	// hook: products_after_insert
	if(function_exists('products_after_insert')){
		$res = sql("select * from `products` where `ProductID`='" . makeSafe($recID, false) . "' limit 1", $eo);
		if($row = db_fetch_assoc($res)){
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = makeSafe($recID, false);
		$args=array();
		if(!products_after_insert($data, getMemberInfo(), $args)){ return $recID; }
	}

	// mm: save ownership data
	set_record_owner('products', $recID, getLoggedMemberID());

	return $recID;
}

function products_delete($selected_id, $AllowDeleteOfParents=false, $skipChecks=false){
	// insure referential integrity ...
	global $Translation;
	$selected_id=makeSafe($selected_id);

	// mm: can member delete record?
	$arrPerm=getTablePermissions('products');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='products' and pkValue='$selected_id'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='products' and pkValue='$selected_id'");
	if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3){ // allow delete?
		// delete allowed, so continue ...
	}else{
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: products_before_delete
	if(function_exists('products_before_delete')){
		$args=array();
		if(!products_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'];
	}

	// child table: order_details
	$res = sql("select `ProductID` from `products` where `ProductID`='$selected_id'", $eo);
	$ProductID = db_fetch_row($res);
	$rires = sql("select count(1) from `order_details` where `ProductID`='".addslashes($ProductID[0])."'", $eo);
	$rirow = db_fetch_row($rires);
	if($rirow[0] && !$AllowDeleteOfParents && !$skipChecks){
		$RetMsg = $Translation["couldn't delete"];
		$RetMsg = str_replace("<RelatedRecords>", $rirow[0], $RetMsg);
		$RetMsg = str_replace("<TableName>", "order_details", $RetMsg);
		return $RetMsg;
	}elseif($rirow[0] && $AllowDeleteOfParents && !$skipChecks){
		$RetMsg = $Translation["confirm delete"];
		$RetMsg = str_replace("<RelatedRecords>", $rirow[0], $RetMsg);
		$RetMsg = str_replace("<TableName>", "order_details", $RetMsg);
		$RetMsg = str_replace("<Delete>", "<input type=\"button\" class=\"button\" value=\"".$Translation['yes']."\" onClick=\"window.location='products_view.php?SelectedID=".urlencode($selected_id)."&delete_x=1&confirmed=1';\">", $RetMsg);
		$RetMsg = str_replace("<Cancel>", "<input type=\"button\" class=\"button\" value=\"".$Translation['no']."\" onClick=\"window.location='products_view.php?SelectedID=".urlencode($selected_id)."';\">", $RetMsg);
		return $RetMsg;
	}

	sql("delete from `products` where `ProductID`='$selected_id'", $eo);

	// hook: products_after_delete
	if(function_exists('products_after_delete')){
		$args=array();
		products_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("delete from membership_userrecords where tableName='products' and pkValue='$selected_id'", $eo);
}

function products_update($selected_id){
	global $Translation;

	// mm: can member edit record?
	$arrPerm=getTablePermissions('products');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='products' and pkValue='".makeSafe($selected_id)."'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='products' and pkValue='".makeSafe($selected_id)."'");
	if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3){ // allow update?
		// update allowed, so continue ...
	}else{
		return false;
	}

	$data['ProductName'] = makeSafe($_REQUEST['ProductName']);
		if($data['ProductName'] == empty_lookup_value){ $data['ProductName'] = ''; }
	$data['SupplierID'] = makeSafe($_REQUEST['SupplierID']);
		if($data['SupplierID'] == empty_lookup_value){ $data['SupplierID'] = ''; }
	$data['CategoryID'] = makeSafe($_REQUEST['CategoryID']);
		if($data['CategoryID'] == empty_lookup_value){ $data['CategoryID'] = ''; }
	$data['QuantityPerUnit'] = makeSafe($_REQUEST['QuantityPerUnit']);
		if($data['QuantityPerUnit'] == empty_lookup_value){ $data['QuantityPerUnit'] = ''; }
	$data['UnitPrice'] = makeSafe($_REQUEST['UnitPrice']);
		if($data['UnitPrice'] == empty_lookup_value){ $data['UnitPrice'] = ''; }
	$data['UnitsInStock'] = makeSafe($_REQUEST['UnitsInStock']);
		if($data['UnitsInStock'] == empty_lookup_value){ $data['UnitsInStock'] = ''; }
	$data['UnitsOnOrder'] = makeSafe($_REQUEST['UnitsOnOrder']);
		if($data['UnitsOnOrder'] == empty_lookup_value){ $data['UnitsOnOrder'] = ''; }
	$data['ReorderLevel'] = makeSafe($_REQUEST['ReorderLevel']);
		if($data['ReorderLevel'] == empty_lookup_value){ $data['ReorderLevel'] = ''; }
	$data['Discontinued'] = makeSafe($_REQUEST['Discontinued']);
		if($data['Discontinued'] == empty_lookup_value){ $data['Discontinued'] = ''; }
	$data['selectedID']=makeSafe($selected_id);

	// hook: products_before_update
	if(function_exists('products_before_update')){
		$args=array();
		if(!products_before_update($data, getMemberInfo(), $args)){ return false; }
	}

	$o=array('silentErrors' => true);
	sql('update `products` set       `ProductName`=' . (($data['ProductName'] !== '' && $data['ProductName'] !== NULL) ? "'{$data['ProductName']}'" : 'NULL') . ', `SupplierID`=' . (($data['SupplierID'] !== '' && $data['SupplierID'] !== NULL) ? "'{$data['SupplierID']}'" : 'NULL') . ', `CategoryID`=' . (($data['CategoryID'] !== '' && $data['CategoryID'] !== NULL) ? "'{$data['CategoryID']}'" : 'NULL') . ', `QuantityPerUnit`=' . (($data['QuantityPerUnit'] !== '' && $data['QuantityPerUnit'] !== NULL) ? "'{$data['QuantityPerUnit']}'" : 'NULL') . ', `UnitPrice`=' . (($data['UnitPrice'] !== '' && $data['UnitPrice'] !== NULL) ? "'{$data['UnitPrice']}'" : 'NULL') . ', `UnitsInStock`=' . (($data['UnitsInStock'] !== '' && $data['UnitsInStock'] !== NULL) ? "'{$data['UnitsInStock']}'" : 'NULL') . ', `UnitsOnOrder`=' . (($data['UnitsOnOrder'] !== '' && $data['UnitsOnOrder'] !== NULL) ? "'{$data['UnitsOnOrder']}'" : 'NULL') . ', `ReorderLevel`=' . (($data['ReorderLevel'] !== '' && $data['ReorderLevel'] !== NULL) ? "'{$data['ReorderLevel']}'" : 'NULL') . ', `Discontinued`=' . (($data['Discontinued'] !== '' && $data['Discontinued'] !== NULL) ? "'{$data['Discontinued']}'" : 'NULL') . " where `ProductID`='".makeSafe($selected_id)."'", $o);
	if($o['error']!=''){
		echo $o['error'];
		echo '<a href="products_view.php?SelectedID='.urlencode($selected_id)."\">{$Translation['< back']}</a>";
		exit;
	}


	// hook: products_after_update
	if(function_exists('products_after_update')){
		$res = sql("SELECT * FROM `products` WHERE `ProductID`='{$data['selectedID']}' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)){
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = $data['ProductID'];
		$args = array();
		if(!products_after_update($data, getMemberInfo(), $args)){ return; }
	}

	// mm: update ownership data
	sql("update membership_userrecords set dateUpdated='".time()."' where tableName='products' and pkValue='".makeSafe($selected_id)."'", $eo);

}

function products_form($selected_id = '', $AllowUpdate = 1, $AllowInsert = 1, $AllowDelete = 1, $ShowCancel = 0, $TemplateDV = '', $TemplateDVP = ''){
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selected_id. If $selected_id
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;

	// mm: get table permissions
	$arrPerm=getTablePermissions('products');
	if(!$arrPerm[1] && $selected_id==''){ return ''; }
	$AllowInsert = ($arrPerm[1] ? true : false);
	// print preview?
	$dvprint = false;
	if($selected_id && $_REQUEST['dvprint_x'] != ''){
		$dvprint = true;
	}

	$filterer_SupplierID = thisOr(undo_magic_quotes($_REQUEST['filterer_SupplierID']), '');
	$filterer_CategoryID = thisOr(undo_magic_quotes($_REQUEST['filterer_CategoryID']), '');

	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: SupplierID
	$combo_SupplierID = new DataCombo;
	// combobox: CategoryID
	$combo_CategoryID = new DataCombo;

	if($selected_id){
		// mm: check member permissions
		if(!$arrPerm[2]){
			return "";
		}
		// mm: who is the owner?
		$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='products' and pkValue='".makeSafe($selected_id)."'");
		$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='products' and pkValue='".makeSafe($selected_id)."'");
		if($arrPerm[2]==1 && getLoggedMemberID()!=$ownerMemberID){
			return "";
		}
		if($arrPerm[2]==2 && getLoggedGroupID()!=$ownerGroupID){
			return "";
		}

		// can edit?
		if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3){
			$AllowUpdate=1;
		}else{
			$AllowUpdate=0;
		}

		$res = sql("select * from `products` where `ProductID`='".makeSafe($selected_id)."'", $eo);
		if(!($row = db_fetch_array($res))){
			return error_message($Translation['No records found'], 'products_view.php', false);
		}
		$urow = $row; /* unsanitized data */
		$hc = new CI_Input();
		$row = $hc->xss_clean($row); /* sanitize data */
		$combo_SupplierID->SelectedData = $row['SupplierID'];
		$combo_CategoryID->SelectedData = $row['CategoryID'];
	}else{
		$combo_SupplierID->SelectedData = $filterer_SupplierID;
		$combo_CategoryID->SelectedData = $filterer_CategoryID;
	}
	$combo_SupplierID->HTML = '<span id="SupplierID-container' . $rnd1 . '"></span><input type="hidden" name="SupplierID" id="SupplierID' . $rnd1 . '" value="' . html_attr($combo_SupplierID->SelectedData) . '">';
	$combo_SupplierID->MatchText = '<span id="SupplierID-container-readonly' . $rnd1 . '"></span><input type="hidden" name="SupplierID" id="SupplierID' . $rnd1 . '" value="' . html_attr($combo_SupplierID->SelectedData) . '">';
	$combo_CategoryID->HTML = '<span id="CategoryID-container' . $rnd1 . '"></span><input type="hidden" name="CategoryID" id="CategoryID' . $rnd1 . '" value="' . html_attr($combo_CategoryID->SelectedData) . '">';
	$combo_CategoryID->MatchText = '<span id="CategoryID-container-readonly' . $rnd1 . '"></span><input type="hidden" name="CategoryID" id="CategoryID' . $rnd1 . '" value="' . html_attr($combo_CategoryID->SelectedData) . '">';

	ob_start();
	?>

	<script>
		// initial lookup values
		AppGini.current_SupplierID__RAND__ = { text: "", value: "<?php echo addslashes($selected_id ? $urow['SupplierID'] : $filterer_SupplierID); ?>"};
		AppGini.current_CategoryID__RAND__ = { text: "", value: "<?php echo addslashes($selected_id ? $urow['CategoryID'] : $filterer_CategoryID); ?>"};

		jQuery(function() {
			setTimeout(function(){
				if(typeof(SupplierID_reload__RAND__) == 'function') SupplierID_reload__RAND__();
				if(typeof(CategoryID_reload__RAND__) == 'function') CategoryID_reload__RAND__();
			}, 10); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
		});
		function SupplierID_reload__RAND__(){
		<?php if(($AllowUpdate || $AllowInsert) && !$dvprint){ ?>

			$j("#SupplierID-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c){
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_SupplierID__RAND__.value, t: 'products', f: 'SupplierID' },
						success: function(resp){
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="SupplierID"]').val(resp.results[0].id);
							$j('[id=SupplierID-container-readonly__RAND__]').html('<span id="SupplierID-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=suppliers_view_parent]').hide(); }else{ $j('.btn[id=suppliers_view_parent]').show(); }


							if(typeof(SupplierID_update_autofills__RAND__) == 'function') SupplierID_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term){ /* */ return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 10,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page){ /* */ return { s: term, p: page, t: 'products', f: 'SupplierID' }; },
					results: function(resp, page){ /* */ return resp; }
				},
				escapeMarkup: function(str){ /* */ return str; }
			}).on('change', function(e){
				AppGini.current_SupplierID__RAND__.value = e.added.id;
				AppGini.current_SupplierID__RAND__.text = e.added.text;
				$j('[name="SupplierID"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=suppliers_view_parent]').hide(); }else{ $j('.btn[id=suppliers_view_parent]').show(); }


				if(typeof(SupplierID_update_autofills__RAND__) == 'function') SupplierID_update_autofills__RAND__();
			});

			if(!$j("#SupplierID-container__RAND__").length){
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_SupplierID__RAND__.value, t: 'products', f: 'SupplierID' },
					success: function(resp){
						$j('[name="SupplierID"]').val(resp.results[0].id);
						$j('[id=SupplierID-container-readonly__RAND__]').html('<span id="SupplierID-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=suppliers_view_parent]').hide(); }else{ $j('.btn[id=suppliers_view_parent]').show(); }

						if(typeof(SupplierID_update_autofills__RAND__) == 'function') SupplierID_update_autofills__RAND__();
					}
				});
			}

		<?php }else{ ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_SupplierID__RAND__.value, t: 'products', f: 'SupplierID' },
				success: function(resp){
					$j('[id=SupplierID-container__RAND__], [id=SupplierID-container-readonly__RAND__]').html('<span id="SupplierID-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=suppliers_view_parent]').hide(); }else{ $j('.btn[id=suppliers_view_parent]').show(); }

					if(typeof(SupplierID_update_autofills__RAND__) == 'function') SupplierID_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
		function CategoryID_reload__RAND__(){
		<?php if(($AllowUpdate || $AllowInsert) && !$dvprint){ ?>

			$j("#CategoryID-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c){
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_CategoryID__RAND__.value, t: 'products', f: 'CategoryID' },
						success: function(resp){
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="CategoryID"]').val(resp.results[0].id);
							$j('[id=CategoryID-container-readonly__RAND__]').html('<span id="CategoryID-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=categories_view_parent]').hide(); }else{ $j('.btn[id=categories_view_parent]').show(); }


							if(typeof(CategoryID_update_autofills__RAND__) == 'function') CategoryID_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term){ /* */ return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 10,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page){ /* */ return { s: term, p: page, t: 'products', f: 'CategoryID' }; },
					results: function(resp, page){ /* */ return resp; }
				},
				escapeMarkup: function(str){ /* */ return str; }
			}).on('change', function(e){
				AppGini.current_CategoryID__RAND__.value = e.added.id;
				AppGini.current_CategoryID__RAND__.text = e.added.text;
				$j('[name="CategoryID"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=categories_view_parent]').hide(); }else{ $j('.btn[id=categories_view_parent]').show(); }


				if(typeof(CategoryID_update_autofills__RAND__) == 'function') CategoryID_update_autofills__RAND__();
			});

			if(!$j("#CategoryID-container__RAND__").length){
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_CategoryID__RAND__.value, t: 'products', f: 'CategoryID' },
					success: function(resp){
						$j('[name="CategoryID"]').val(resp.results[0].id);
						$j('[id=CategoryID-container-readonly__RAND__]').html('<span id="CategoryID-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=categories_view_parent]').hide(); }else{ $j('.btn[id=categories_view_parent]').show(); }

						if(typeof(CategoryID_update_autofills__RAND__) == 'function') CategoryID_update_autofills__RAND__();
					}
				});
			}

		<?php }else{ ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_CategoryID__RAND__.value, t: 'products', f: 'CategoryID' },
				success: function(resp){
					$j('[id=CategoryID-container__RAND__], [id=CategoryID-container-readonly__RAND__]').html('<span id="CategoryID-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=categories_view_parent]').hide(); }else{ $j('.btn[id=categories_view_parent]').show(); }

					if(typeof(CategoryID_update_autofills__RAND__) == 'function') CategoryID_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
	</script>
	<?php

	$lookups = str_replace('__RAND__', $rnd1, ob_get_contents());
	ob_end_clean();


	// code for template based detail view forms

	// open the detail view template
	if($dvprint){
		$template_file = is_file("./{$TemplateDVP}") ? "./{$TemplateDVP}" : './templates/products_templateDVP.html';
		$templateCode = @file_get_contents($template_file);
	}else{
		$template_file = is_file("./{$TemplateDV}") ? "./{$TemplateDV}" : './templates/products_templateDV.html';
		$templateCode = @file_get_contents($template_file);
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Detail View', $templateCode);
	$templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
	$templateCode = str_replace('<%%EMBEDDED%%>', ($_REQUEST['Embedded'] ? 'Embedded=1' : ''), $templateCode);
	// process buttons
	if($arrPerm[1] && !$selected_id){ // allow insert and no record selected?
		if(!$selected_id) $templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1" onclick="return products_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1" onclick="return products_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	}else{
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}

	// 'Back' button action
	if($_REQUEST['Embedded']){
		$backAction = 'AppGini.closeParentModal(); return false;';
	}else{
		$backAction = '$j(\'form\').eq(0).attr(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;';
	}

	if($selected_id){
		if(!$_REQUEST['Embedded']) $templateCode = str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" onclick="$$(\'form\')[0].writeAttribute(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;" title="' . html_attr($Translation['Print Preview']) . '"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($AllowUpdate){
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" onclick="return products_validateData();" title="' . html_attr($Translation['Save Changes']) . '"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		}else{
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		}
		if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3){ // allow delete?
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" onclick="return confirm(\'' . $Translation['are you sure?'] . '\');" title="' . html_attr($Translation['Delete']) . '"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
		}else{
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		}
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
	}else{
		$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', ($ShowCancel ? '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>' : ''), $templateCode);
	}

	// set records to read only if user can't insert new records and can't edit current record
	if(($selected_id && !$AllowUpdate) || (!$selected_id && !$AllowInsert)){
		$jsReadOnly .= "\tjQuery('#ProductName').replaceWith('<div class=\"form-control-static\" id=\"ProductName\">' + (jQuery('#ProductName').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#SupplierID').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('#SupplierID_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
		$jsReadOnly .= "\tjQuery('#CategoryID').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('#CategoryID_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
		$jsReadOnly .= "\tjQuery('#QuantityPerUnit').replaceWith('<div class=\"form-control-static\" id=\"QuantityPerUnit\">' + (jQuery('#QuantityPerUnit').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#UnitPrice').replaceWith('<div class=\"form-control-static\" id=\"UnitPrice\">' + (jQuery('#UnitPrice').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#UnitsInStock').replaceWith('<div class=\"form-control-static\" id=\"UnitsInStock\">' + (jQuery('#UnitsInStock').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#UnitsOnOrder').replaceWith('<div class=\"form-control-static\" id=\"UnitsOnOrder\">' + (jQuery('#UnitsOnOrder').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#ReorderLevel').replaceWith('<div class=\"form-control-static\" id=\"ReorderLevel\">' + (jQuery('#ReorderLevel').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#Discontinued').prop('disabled', true);\n";
		$jsReadOnly .= "\tjQuery('.select2-container').hide();\n";

		$noUploads = true;
	}elseif(($AllowInsert && !$selected_id) || ($AllowUpdate && $selected_id)){
		$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', true);"; // temporarily disable form change handler
			$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode = str_replace('<%%COMBO(SupplierID)%%>', $combo_SupplierID->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(SupplierID)%%>', $combo_SupplierID->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(SupplierID)%%>', urlencode($combo_SupplierID->MatchText), $templateCode);
	$templateCode = str_replace('<%%COMBO(CategoryID)%%>', $combo_CategoryID->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(CategoryID)%%>', $combo_CategoryID->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(CategoryID)%%>', urlencode($combo_CategoryID->MatchText), $templateCode);

	/* lookup fields array: 'lookup field name' => array('parent table name', 'lookup field caption') */
	$lookup_fields = array(  'SupplierID' => array('suppliers', 'Supplier'), 'CategoryID' => array('categories', 'Category'));
	foreach($lookup_fields as $luf => $ptfc){
		$pt_perm = getTablePermissions($ptfc[0]);

		// process foreign key links
		if($pt_perm['view'] || $pt_perm['edit']){
			$templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent hspacer-md" id="' . $ptfc[0] . '_view_parent" title="' . html_attr($Translation['View'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
		}

		// if user has insert permission to parent table of a lookup field, put an add new button
		if($pt_perm['insert'] && !$_REQUEST['Embedded']){
			$templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-success add_new_parent hspacer-md" id="' . $ptfc[0] . '_add_new" title="' . html_attr($Translation['Add New'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-plus-sign"></i></button>', $templateCode);
		}
	}

	// process images
	$templateCode = str_replace('<%%UPLOADFILE(ProductID)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(ProductName)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(SupplierID)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(CategoryID)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(QuantityPerUnit)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(UnitPrice)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(UnitsInStock)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(UnitsOnOrder)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(ReorderLevel)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(Discontinued)%%>', '', $templateCode);

	// process values
	if($selected_id){
		if( $dvprint) $templateCode = str_replace('<%%VALUE(ProductID)%%>', safe_html($urow['ProductID']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(ProductID)%%>', html_attr($row['ProductID']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(ProductID)%%>', urlencode($urow['ProductID']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(ProductName)%%>', safe_html($urow['ProductName']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(ProductName)%%>', html_attr($row['ProductName']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(ProductName)%%>', urlencode($urow['ProductName']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(SupplierID)%%>', safe_html($urow['SupplierID']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(SupplierID)%%>', html_attr($row['SupplierID']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(SupplierID)%%>', urlencode($urow['SupplierID']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(CategoryID)%%>', safe_html($urow['CategoryID']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(CategoryID)%%>', html_attr($row['CategoryID']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(CategoryID)%%>', urlencode($urow['CategoryID']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(QuantityPerUnit)%%>', safe_html($urow['QuantityPerUnit']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(QuantityPerUnit)%%>', html_attr($row['QuantityPerUnit']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(QuantityPerUnit)%%>', urlencode($urow['QuantityPerUnit']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(UnitPrice)%%>', safe_html($urow['UnitPrice']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(UnitPrice)%%>', html_attr($row['UnitPrice']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(UnitPrice)%%>', urlencode($urow['UnitPrice']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(UnitsInStock)%%>', safe_html($urow['UnitsInStock']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(UnitsInStock)%%>', html_attr($row['UnitsInStock']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(UnitsInStock)%%>', urlencode($urow['UnitsInStock']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(UnitsOnOrder)%%>', safe_html($urow['UnitsOnOrder']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(UnitsOnOrder)%%>', html_attr($row['UnitsOnOrder']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(UnitsOnOrder)%%>', urlencode($urow['UnitsOnOrder']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(ReorderLevel)%%>', safe_html($urow['ReorderLevel']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(ReorderLevel)%%>', html_attr($row['ReorderLevel']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(ReorderLevel)%%>', urlencode($urow['ReorderLevel']), $templateCode);
		$templateCode = str_replace('<%%CHECKED(Discontinued)%%>', ($row['Discontinued'] ? "checked" : ""), $templateCode);
	}else{
		$templateCode = str_replace('<%%VALUE(ProductID)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(ProductID)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(ProductName)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(ProductName)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(SupplierID)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(SupplierID)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(CategoryID)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(CategoryID)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(QuantityPerUnit)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(QuantityPerUnit)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(UnitPrice)%%>', '0', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(UnitPrice)%%>', urlencode('0'), $templateCode);
		$templateCode = str_replace('<%%VALUE(UnitsInStock)%%>', '0', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(UnitsInStock)%%>', urlencode('0'), $templateCode);
		$templateCode = str_replace('<%%VALUE(UnitsOnOrder)%%>', '0', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(UnitsOnOrder)%%>', urlencode('0'), $templateCode);
		$templateCode = str_replace('<%%VALUE(ReorderLevel)%%>', '0', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(ReorderLevel)%%>', urlencode('0'), $templateCode);
		$templateCode = str_replace('<%%CHECKED(Discontinued)%%>', '', $templateCode);
	}

	// process translations
	foreach($Translation as $symbol=>$trans){
		$templateCode = str_replace("<%%TRANSLATION($symbol)%%>", $trans, $templateCode);
	}

	// clear scrap
	$templateCode = str_replace('<%%', '<!-- ', $templateCode);
	$templateCode = str_replace('%%>', ' -->', $templateCode);

	// hide links to inaccessible tables
	if($_REQUEST['dvprint_x'] == ''){
		$templateCode .= "\n\n<script>\$j(function(){\n";
		$arrTables = getTableList();
		foreach($arrTables as $name => $caption){
			$templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
			$templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
		}

		$templateCode .= $jsReadOnly;
		$templateCode .= $jsEditable;

		if(!$selected_id){
		}

		$templateCode.="\n});</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode .= '<script>';
	$templateCode .= '$j(function() {';


	$templateCode.="});";
	$templateCode.="</script>";
	$templateCode .= $lookups;

	// handle enforced parent values for read-only lookup fields

	// don't include blank images in lightbox gallery
	$templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	/* default field values */
	$rdata = $jdata = get_defaults('products');
	if($selected_id){
		$jdata = get_joined_record('products', $selected_id);
		if($jdata === false) $jdata = get_defaults('products');
		$rdata = $row;
	}
	$templateCode .= loadView('products-ajax-cache', array('rdata' => $rdata, 'jdata' => $jdata));

	// hook: products_dv
	if(function_exists('products_dv')){
		$args=array();
		products_dv(($selected_id ? $selected_id : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}
?>