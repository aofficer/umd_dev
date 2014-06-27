<table class="community-framework">
  <tr>
    <td></td>
    <td></td>
    <td></td>
    <td>Your points earned</td>
    <td>Total possible points</td>
    <td>% of extent of coverage of attributes</td>
  </tr>
  <?php //if ($tier1['possible']):?> 
  <tr>
    <td>TIER I</td>
    <td>Risk Governance</td>
    <td><p>A1 Executive Risk Governance Group</p>
  <p>A2 Extended Enterprise Risk Assessment</p>
  <p>A3 Extended Enterprise Risk Mitigation Strategy</p>
  <p>A4 Extended Enterprise Risk Monitoring</p></td>
    <td><?php print $tier1['total']; ?></td>
    <td><?php print $tier1['possible']; ?></td>
    <td><?php if ($tier1['possible']): ?>
          <?php print round(($tier1['total']/$tier1['possible'])*100, 1); ?>%
        <?php else: ?>
          n/a
        <?php endif; ?>
    </td>
  </tr>
  <?php //endif; ?>
  <?php //if ($tier2['possible']):?> 
  <tr>
    <td>TIER II</td>
    <td>Systems Integration</td>
    <td><p>B1 System Lifecycle Integration/Design</p>
  <p>B2 System Risk Assessment/Sourcing</p>
  <p>B3 Acquisition Risk Assessment/Sourcing</p>
  <p>B4 Mapping</p>
  <p>B5 Tracking and Visibility of Supply Chain</p>
  <p>B6 Program/Project/Process Auditing</p></td>
    <td><?php print $tier2['total']; ?></td>
    <td><?php print $tier2['possible']; ?></td>
    <td><?php if ($tier2['possible']): ?>
          <?php print round(($tier2['total']/$tier2['possible'])*100, 1); ?>%
        <?php else: ?>
          n/a
        <?php endif; ?></td>
  </tr>
  <?php //endif; ?>
  <?php //if ($tier3['possible']):?> 
  <tr>
    <td>TIER III</td>
    <td>Operations</td>
    <td><p>C1 Plan</p>
  <p>C2 Design</p>
  <p>C3 Make</p>
  <p>C4 Source</p>
  <p>C5 Deliver</p>
  <p>C6 Return</p>
  <p>C7 Process Risk Auditing</p></td>
    <td><?php print $tier3['total']; ?></td>
    <td><?php print $tier3['possible']; ?></td>
    <td><?php if ($tier3['possible']): ?>
          <?php print round(($tier3['total']/$tier3['possible'])*100, 1); ?>%
        <?php else: ?>
          n/a
        <?php endif; ?></td>
  </tr>
  <?php //endif; ?>
</table>