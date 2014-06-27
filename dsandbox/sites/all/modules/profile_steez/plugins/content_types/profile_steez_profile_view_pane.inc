<?php

$plugin = array(
  // the title in the admin
  'title' => t('Profile output'),
  // no one knows if "single" defaults to FALSE...
  'single' => TRUE,
  // oh joy, I get my own section of panel panes
  'category' => array(t('Profile'), -9),
  //'edit form' => 'file_steez_upload_pane_content_type_edit_form',
  'render callback' => 'profile_steez_profile_view_pane_content_type_render'
);

/** Produce a profile page. **/
function profile_steez_profile_view_pane_content_type_render($subtype, $conf, $context = NULL) {
  $block = new stdClass();
  //print print_r($context, 1);
  $uid = $context[0];
  module_load_include('inc', 'profile2_page');
  $block->content = profile2_page_view(profile2_by_uid_load($uid, arg(2)));
  
  return $block;
}