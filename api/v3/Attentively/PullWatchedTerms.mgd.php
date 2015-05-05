<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:Attentively.PullWatchedTerms',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Call Attentively.PullWatchedTerms API',
      'description' => 'Call Attentively.PullWatchedTerms API',
      'run_frequency' => 'Daily',
      'api_entity' => 'Attentively',
      'api_action' => 'Pullwatchedterms',
      'parameters' => '',
    ),
  ),
);