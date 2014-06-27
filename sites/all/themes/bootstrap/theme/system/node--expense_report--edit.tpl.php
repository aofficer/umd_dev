<?php
/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * The doctype, html, head and body tags are not in this template. Instead they
 * can be found in the html.tpl.php template in this directory.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see bootstrap_preprocess_page()
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see bootstrap_process_page()
 * @see template_process()
 * @see html.tpl.php
 *
 * @ingroup themeable
 */
?>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>

<?php
    $mileage=.56;
	 $brft=8.00;
	 $lunch=10.00;
	 $dinner=24.00;
	
	
	
	function chk(&$x){$ans=""; if($x==1){$ans="checked";} return $ans;}
	
	function objectToArray($d) {
		if (is_object($d)) {$d = get_object_vars($d);}
		if (is_array($d)) {return array_map(__FUNCTION__, $d);}
		else {return $d;}
	}
?>

<form class="webform-client-form webform-client-form row" enctype="multipart/form-data" accept-charset="UTF-8"><div><fieldset class="webform-component-fieldset webform-component--contact-info panel panel-default form-wrapper">
            <legend class="panel-heading">
        <div class="panel-title fieldset-legend">
          Contact Info        </div>
      </legend>
          <div class="panel-body">
          
<?php

$nid = $node->nid;
$sql = 'SELECT nid,title FROM {node} node 
LEFT JOIN {field_data_field_report} field_data_field_report 
ON node.nid = field_data_field_report.entity_id
WHERE (field_data_field_report.field_report_target_id=:nid)AND(node.status = 1)';
$sql2 ='DELETE r FROM redirect r INNER JOIN url_alias u ON r.source = u.alias AND r.redirect = u.source AND r.language = u.language;';
$firstArr = db_query($sql2, array(':nid' => $nid))->fetchAll();
$secArr = db_query('SELECT entity_id,field_total_value FROM {field_data_field_total}', array(':nid' => $nid))->fetchAll();
$distArr = db_query('SELECT entity_id,field_distance_value FROM {field_data_field_distance}', array(':nid' => $nid))->fetchAll();
$chargeArr = db_query('SELECT entity_id,field_charge_value FROM {field_data_field_charge}', array(':nid' => $nid))->fetchAll();

objectToArray($firstArr);
objectToArray($secArr);
objectToArray($distArr);
objectToArray($chargeArr);

$i = 0;
foreach($chargeArr as $x){	
$chargeArr2 = objectToArray($x);
$endArr3[$chargeArr2['entity_id']]=$chargeArr2['field_charge_value'];
$i++;
}

//print_r($endArr3);
$secArr=array_merge($secArr,$distArr);
//print_r($secArr);

$i = 0;
foreach($secArr as $x){	
$arr2 = objectToArray($x);
if($arr2['field_distance_value']=='')
{$endArr2[$arr2['entity_id']]=$arr2['field_total_value'];}
else{$endArr2[$arr2['entity_id']]=intval($arr2['field_distance_value'])*$mileage;}
$i++;
}
//print_r($endArr2);

$j = count($firstArr);
for ($i = 0; $i<$j; $i++) {
	$arr1 = objectToArray($firstArr);
	$val = $endArr2[$arr1[$i]['nid']];
	$endArr1[$arr1[$i]['nid']]=$val;
	if($endArr3[$arr1[$i]['nid']] == 1){$rArr[$i]=$val;}
	if($endArr3[$arr1[$i]['nid']] == 0){$bArr[$i]=$val;}
}
$total= number_format(array_sum($endArr1),2);
$reim= number_format(array_sum($rArr),2);
$bill= number_format(array_sum($bArr),2);

 if($total)print "<h3>Total $".$total."</h3><br>";


?>
<div id="outputDiv"></div>	 
        
        

      
<div class="col-md-8">
  <label for="name">Name <span class="form-required" title="This field is required.">*</span></label>
 <input class="form-control form-text required" type="text" id="name" name="submitted[name]" value="
 <?php print $node->field_name['und'][0]['value']?>
 " size="30" maxlength="40" readonly>
</div>

<div class="col-md-4 ">
  <label for="edit-submitted-contact-info-ssn">SSN<span class="form-required" title="This field is required.">*</span></label>
 <input class="form-control form-text required" type="text" id="edit-submitted-contact-info-ssn" name="submitted[ssn]" value="
  <?php print $node->field_ssn['und'][0]['value']?>" size="60" maxlength="128" readonly>
</div>

<div class="col-md-8 ">
  <label for="edit-submitted-contact-info-addr">Home Address<span class="form-required" title="This field is required.">*</span></label>
 <input class="form-control form-text required" type="text" id="addr" name="submitted[addr]" value="
 <?php print $node->field_home_address['und'][0]['value']?>" size="60" maxlength="128" readonly>
</div>

<div class="col-md-4">
  <label for="edit-submitted-contact-info-phone-number">Office Phone<span class="form-required" title="This field is required.">*</span></label>
 <input class="form-control form-text required" type="text" id="edit-submitted-contact-info-phone-number" name="submitted[phone_number]" value="
  <?php print $node->field_office_phone['und'][0]['value']?>
 " size="60" maxlength="128" readonly>
</div>
  </div>
  </fieldset>
  
  
<fieldset class="webform-component-fieldset webform-component--event-info panel panel-default form-wrapper">
            <legend class="panel-heading">
        <div class="panel-title fieldset-legend">
          Event Info        </div>
      </legend>
          <div class="panel-body">
    
<div class="col-md-8">
  <label for="edit-submitted-contact-info-travelfrom">Travel From<span class="form-required" title="This field is required.">*</span></label>
 <input class="form-control form-text required" type="text" id="edit-submitted-contact-info-travelfrom" name="submitted[travelfrom]" value="
  <?php print $node->field_travel_from['und'][0]['value']?>
 " size="60" maxlength="128" readonly>
</div>

<div class="col-md-4 ">
  <label for="edit-submitted-contact-info-departdate">Departure Date<span class="form-required" title="This field is required.">*</span></label>
 <input class="form-control form-text required" type="text" id="departdate" name="submitted[departdate]" value="
 <?php print $node->field_departure_date['und'][0]['value']?>" size="60" maxlength="10" readonly>
</div>

<div class="col-md-8">
  <label for="edit-submitted-contact-info-travelto">Travel To<span class="form-required" title="This field is required.">*</span></label>
 <input class="form-control form-text required" type="text" id="edit-submitted-contact-info-travelto" name="submitted[travelto]" value="
  <?php print $node->field_travel_to['und'][0]['value']?>
 " size="60" maxlength="128" readonly>
</div>

<div class="col-md-4 ">
  <label for="edit-submitted-contact-info-returndate">Return Date<span class="form-required" title="This field is required.">*</span></label>
 <input class="form-control form-text required" type="text" id="returndate" name="submitted[returndate]" value="
 <?php print $node->field_return_date['und'][0]['value']?>" size="60" maxlength="10" readonly>
</div>
    

<div class="col-md-12">
  <label for="edit-submitted-contact-info-purpose">Purpose Name & Location <span class="form-required" title="This field is required.">*</span></label>
 <input class="form-control form-text required" type="text" id="edit-submitted-contact-info-purpose" name="submitted[purpose]" value="
  <?php print $node->field_name_location['und'][0]['value'] ?>
 " size="60" maxlength="128" readonly>
</div>
   
    
</div>
</fieldset>

<fieldset class="webform-component-fieldset webform-component--event-info panel panel-default form-wrapper">
            <legend class="panel-heading">
        <div class="panel-title fieldset-legend">
          Report Items       <button type="button"><a href="../node/add/expense-item">Add Expense Item</a></button> </div>
      </legend>
          <div class="panel-body">

<?php
$viewName = 'expense';
$displayName1 = 'block_3';
$displayName2 = 'block_4';
$nid = $node->nid;
print views_embed_view($viewName,$displayName1,$nid); 
if($reim) print"<p style='color:red'>Reimbursable: $".$reim."</p>";
print views_embed_view($viewName,$displayName2,$nid);
if($bill) print"<p style='color:red'>Paid by Tcard/Pcard: $".$bill."</p>";
?>


</div>
</fieldset>


</div></form>

<br>
   <?php //print_r($node); ?>
<script>

var origin = document.getElementById("edit-submitted-contact-info-travelfrom").value;
var destination = document.getElementById("edit-submitted-contact-info-travelto").value;


function initialize() {
	calculateDistances();
}

function calculateDistances() {
  var service = new google.maps.DistanceMatrixService();
  service.getDistanceMatrix(
    {
      origins: [origin],
      destinations: [destination],
      travelMode: google.maps.TravelMode.DRIVING,
      unitSystem: google.maps.UnitSystem.IMPERIAL,
      avoidHighways: false,
      avoidTolls: false
    }, callback);
}

function callback(response, status) {
  if (status != google.maps.DistanceMatrixStatus.OK) {
    alert('Error was: ' + status);
  } else {
    var origins = response.originAddresses;
    var destinations = response.destinationAddresses;
    var outputDiv = document.getElementById('outputDiv');
    outputDiv.innerHTML = '';

    //for (var i = 0; i < origins.length; i++) {
      var results = response.rows[0].elements;

      //for (var j = 0; j < results.length; j++) {

        outputDiv.innerHTML += origins[0] + ' to ' + destinations[0]
            + ': ' + results[0].distance.text + ' in '
            + results[0].duration.text + '<br>';
      //}
    //}
  }
}

google.maps.event.addDomListener(window, 'load', initialize);

</script>

