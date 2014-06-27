<?php
$results = $view->result;
$count = count($results);

$total_node_risk = 0;
$total_transation_risk = 0;

$total_risk_sum = 0;
$total_risk_count = 0;

foreach($results as $result)
{
    $entity = $result->_field_data['nid']['entity'];   
    $overall_cvss_score = $result->field_field_overall_cvss_score[0]['raw']['value'];
    if ($entity->type == 'supply_chain_node' || $entity->type == 'hq') {
        $supply_chain_nodes[] = $entity;
        $total_node_risk += $overall_cvss_score;
    }
    if ($entity->type == 'supply_chain_transaction') {   
        $supply_chain_transactions[] = $entity;
        $total_transation_risk += $overall_cvss_score;
        
        $transaction = $entity;
        $sid = $transaction->field_source['und'][0]['target_id'];
        $did = $transaction->field_destination['und'][0]['target_id'];

        $source_node = node_load($sid);
        $value = $source_node->field_cvss_calculator['und'][0]['value'];
        $source_cvss = entity_load('field_collection_item', array($value));
        $source_overall_cvss_score = $source_cvss[$value]->field_overall_cvss_score['und'][0]['value'];
        
        $dest_node = node_load($did);
        $value = $dest_node->field_cvss_calculator['und'][0]['value'];
        $dest_cvss = entity_load('field_collection_item', array($value));
        $dest_overall_cvss_score = $dest_cvss[$value]->field_overall_cvss_score['und'][0]['value'];
        
        $weight = 2;
        $weighted_average = ($source_overall_cvss_score + $dest_overall_cvss_score + ($overall_cvss_score * $weight)) / (2 + $weight);
        
        $total_risk_sum  += $weighted_average;
        $total_risk_count = $total_risk_count + 1;
        
        //echo $source_overall_cvss_score." <-> ".$overall_cvss_score." <-> ".$dest_overall_cvss_score." => ".$weighted_average."<br />";
    }
}
$node_total_possible = count($supply_chain_nodes)*10;
if ($total_node_risk > 0) {
    $actual_node_risk = $node_total_possible / $total_node_risk;
}
else {
    $actual_node_risk = "n/a";
}

$transaction_total_possible = count($supply_chain_transactions)*10;
if ($total_transation_risk > 0) {
    $actual_transaction_risk = $transaction_total_possible / $total_transation_risk;
}
else {
    $actual_transaction_risk = "n/a";
}
?>

<div style="
    display: inline;
    width: 50%;
    float: left;
">
<h3>Total # of Transactions: <?php print count($supply_chain_transactions) ?></h3>
Total Possible Risk: <?php print $transaction_total_possible ?><br />
Actual Risk Score: <?php print round($actual_transaction_risk,1) ?><br />
</div>
<div>
<h3>Total # of Nodes: <?php print count($supply_chain_nodes) ?></h3>
Total Possible Risk: <?php print $node_total_possible ?><br />
Actual Risk Score: <?php print round($actual_node_risk,1) ?><br />
</div>
<h3>Supply Chain Risk Score: <span id="supply-chain-risk-score"><?php print round($total_risk_sum/$total_risk_count,1) ?></span></h3>
<!-- risk footer -->
<?php print $footer; ?>
<br />
