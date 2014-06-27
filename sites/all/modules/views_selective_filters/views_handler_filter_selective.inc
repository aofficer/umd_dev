<?php
/**
 * @file
 * Views Filter Selective Handler Overrides.
 */

/**
 * Views filter handler for selective values.
 */
class views_handler_filter_selective extends views_handler_filter_in_operator {
  protected static $results;

  /**
   * Override set_default_options.
   */
  public function set_default_options() {
    parent::set_default_options();
    // This filter should always be exposed!
    $this->options['exposed'] = TRUE;
    // Do not allow to reduce options, makes no sense.
    $this->options['expose']['reduce'] = FALSE;
  }

  /**
   * Override get_value_options: retrieve available values.
   */
  public function get_value_options() {
    $this->value_options = array();
    // If $this->view->selective_oids means that the handler is
    // being called inside the cloned view used to obtain
    // the selective values and thus this is to prevent infinite
    // recursive loop.
    if (empty($this->view->selective_oids) && !empty($this->view->inited)) {
      $oids = $this->get_oids();
      $this->value_options = $oids;
      // TODO: Omit null values in result: they are improperly handled.
      // When constructing the query.
      $this->value_options = array_diff_key($this->value_options, array("" => NULL));
      // Set a flag in the view so we know it is using selective filters.
      $this->view->using_selective = TRUE;
    }
    else {
      if (!empty($this->view->selective_oids)) {
        $this->value_options = array();
      }
      else {
        // This is a special case, if $this->value_options is not an array
        // then parent::value_form() will throw an exception, so,
        // in our custom override no form is generated when $this->value_options
        // is not an array. We only want this to happen in the administrative
        // interface.
        unset($this->value_options);
      }
    }
  }

  /**
   * Override value_form.
   */
  public function value_form(&$form, &$form_state) {
    $this->get_value_options();
    // If you call parent::value_form() and $this->value_options
    // is not an array, an exception is thrown.
    if (isset($this->value_options) && is_array($this->value_options)) {
      parent::value_form($form, $form_state);
    }
    // Avoid the 'illegal values' Form API error.
    $form['value']['#validated'] = TRUE;
    // Add behaviour for ajax block refresh.
    // Don't do this if the view is being executed
    // to obtain selective values.
    if (empty($this->view->selective_oids)) {
      $form['#attached']['js'][] = drupal_get_path('module', 'views_filters_selective') . '/js/attachBehaviours.js';
    }
  }

  /**
   * Override options_form.
   */
  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
    // Filter should always be exposed, show warning.
    array_unshift($form['expose_button'], array(
      'warning' => array(
        '#type' => 'markup',
        '#markup' => '<div class="messages warning">' . t('This filter is always exposed to users.') . '</div>',
      )));
    // Remove option to unexpose filter. Tried to disable, but did not work.
    $form['expose_button']['checkbox']['checkbox']['#type'] = 'hidden';
    unset($form['expose_button']['button']);
    unset($form['expose_button']['markup']);
    // Do not allow to check "all values".
    $form['value']['#attributes']['disabled'] = 'disabled';
    // Cannot group without values.
    unset($form['group_button']);
  }

  /**
   * Override expose_form.
   */
  public function expose_form(&$form, &$form_state) {
    parent::expose_form($form, $form_state);
    // Remove reduce resultset interface.
    unset($form['expose']['reduce']);
    // TODO: Populated somewhere through AJAX, I could not find it....
    // Provide default value for filter name.
    if (empty($form['expose']['identifier']['#default_value'])) {
      $form['expose']['identifier']['#default_value'] = $this->options['field'];
    }
    if (empty($form['expose']['label']['#default_value'])) {
      $form['expose']['label']['#default_value'] = $this->definition['title'];
    }
    if (empty($form['ui_name']['#default_value'])) {
      $form['ui_name']['#default_value'] = $this->definition['title'];
    }
  }

  /**
   * Override query.
   */
  public function query() {
    // If this view was constructed to obtain the selective values for this
    // handler, the handler should not add any constraints itself.
    if (isset($this->view->selective_handler_signature) && $this->GetSignature() == $this->view->selective_handler_signature) {
      return;
    }
    // Decode the values to restore special chars.
    $this->value = array_map('urldecode', $this->value);
    parent::query();
  }

  /**
   * Get a signature for current filter handler.
   */
  protected function GetSignature() {
    $signature = md5(serialize(array(
      'name' => $this->view->name,
      'args' => $this->view->args,
      'input' => $this->view->exposed_input,
      'base_field' => $this->definition['field_base'],
      'real_field' => $this->real_field,
      'field' => $this->field,
      'table' => $this->table,
      'ui_name' => $this->options['ui_name'],
    )));
    return $signature;
  }

  /**
   * Get list of options for current view, only at runtime.
   */
  protected function get_oids() {
    // Parameters that we will be using during the process.
    $base_field = $this->definition['field_base'];
    $ui_name = $this->options['ui_name'];
    $signature = $this->GetSignature();

    // Prevent same filters from being recalculated.
    if (empty(self::$results[$signature])) {
      // We don't want a badly configured selective filter
      // to return thousands of possible values.
      $max_items = 100;

      // Clone the view (so it works while editting) and get all results.
      $view_copy = $this->view->clone_view();
      if (!$view_copy) {
        return NULL;
      }
      // Store a flag so that we can know from other places
      // that this view is being used to obtain selective data.
      $view_copy->selective_oids = TRUE;
      // Store information about what filter is this view being used for.
      $view_copy->selective_handler_signature = $signature;
      // Transfer contextual information to cloned view.
      $view_copy->set_exposed_input($this->view->exposed_input);
      $view_copy->set_arguments($this->view->args);

      // Mess up with the field used for distinct have thousands of elements.
      // Limit result set to 100: anything above is not user friendly at all.
      $view_copy->set_items_per_page($max_items);

      // Remove paging, and page number from context.
      if (isset($_GET['items_per_page'])) {
        $items_per_page = $_GET['items_per_page'];
        unset($_GET['items_per_page']);
      }
      if (isset($_GET['page'])) {
        $exposed_page = $_GET['page'];
        unset($_GET['page']);
      }

      // Manipulate display + default: don't know if fields are overriden.
      $display = $view_copy->display[$this->view->current_display];
      $display_default = $view_copy->display['default'];

      // Remove any exposed form configuration. This showed up with BEF module!
      unset($display->display_options['exposed_form']);
      unset($display_default->display_options['exposed_form']);

      // If we are using fields from default or current display.
      if (isset($display->display_options['fields'])) {
        $display_options_fields = &$display->display_options['fields'];
      }
      else {
        $display_options_fields = &$display_default->display_options['fields'];
      }

      // Remove all fields but the aggregate one.
      foreach ($display_options_fields as $key => $value) {
        if ((isset($value['ui_name']) && ($value['ui_name'] != $ui_name)) || ($value['field'] != $base_field)) {
          unset($display_options_fields[$key]);
        }
      }

      // Check to see if the user remembered to add the field.
      if (empty($display_options_fields)) {
        drupal_set_message(t('Selective query filter must have corresponding field added to view with Administrative Name set to "@name" and Base Type "@type"',
          array(
            '@name' => $ui_name,
            '@type' => $base_field)),
            'error');
        return array();
      }

      // Get ID of field that will be used for rendering.
      $display_field = reset($display_options_fields);

      // Get field Id.
      $display_field_id = $display_field['id'];

      // Check that relationships are coherent between Field and Filter.
      $no_display_field_relationship = empty($display_field['relationship']) || $display_field['relationship'] === 'none';
      $no_filter_relationship = empty($this->options['relationship']) || $this->options['relationship'] === 'none';
      $equal
        = (($no_display_field_relationship === TRUE) && ($no_filter_relationship === TRUE)) ||
        ($display_field['relationship'] === $this->options['relationship']);

      if (!$equal) {
        drupal_set_message(t('Selective filter "@name": relationship of field and filter must match.',
          array(
            '@name' => $ui_name,
            '@type' => $base_field)),
            'error');
        return array();
      }

      // If main field is excluded from presentation, bring it back.
      // Set group type for handler to populate database relationships in query.
      $display_field['exclude'] = 0;
      $display_field['group_type'] = 'group';

      // Remove all sorting: sorts must be added to aggregate fields.
      unset($display->display_options['sorts']);
      unset($display_default->display_options['sorts']);

      // Turn this into an aggregate query.
      $display->display_options['group_by'] = 1;
      $display->handler->options['group_by'] = 1;

      $display_default->display_options['group_by'] = 1;
      $display_default->handler->options['group_by'] = 1;

      // Aggregate is incompatible with distinct and pure distinct.
      // At least it does not make sense as it is implemented now.
      unset($display_default->display_options['query']['options']['distinct']);
      unset($display_default->display_options['query']['options']['pure_distinct']);

      unset($display->display_options['query']['options']['distinct']);
      unset($display->display_options['query']['options']['pure_distinct']);

      // Run View.
      $view_copy->execute($this->view->current_display);

      // Restore context parameters for real View.
      if (isset($items_per_page)) {
        $_GET['items_per_page'] = $items_per_page;
      }
      if (isset($exposed_page)) {
        $_GET['page'] = $exposed_page;
      }

      // Get Handler after execution.
      $display_field_handler = $view_copy->field[$display_field_id];

      // Create array of objects for selector.
      $oids = array();
      $field_alias = isset($display_field_handler->aliases[$display_field_handler->real_field]) ? $display_field_handler->aliases[$display_field_handler->real_field] : $display_field_handler->table_alias . '_' . $display_field_handler->real_field;
      foreach ($view_copy->result as $index => $row) {
        // $key = $display_field_handler->get_value($row) should be more robust.
        // But values are sometimes nested arrays, and we need single values.
        // For the filters.
        $key = $display_field_handler->get_value($row);
        if (is_array($key)) {
          $key = $row->{$field_alias};
        }
        $value = $view_copy->render_field($display_field_id, $index);
        $oids[$key] = empty($value) ? t('Empty (@key)', array('@key' => empty($key) ? json_encode($key) : $key)) : $value;
      }

      // Sort values alphabetically.
      asort($oids);

      // If limit exceeded this field is not good for being "selective".
      if (count($oids) == $max_items) {
        drupal_set_message(t('Selective filter "@field" has limited the amount of total results. Please, review you query configuration.', array('@field' => $ui_name)), 'warning');
      }

      self::$results[$signature] = $oids;
      $view_copy->destroy();
    }
    return self::$results[$signature];
  }
}
