<!doctype html>
<?php

require 'bootstrap.php';

if (function_exists('xhprof_enable')) {
  xhprof_enable();
} elseif (function_exists('tideways_enable')) {
  tideways_enable(TIDEWAYS_FLAGS_MEMORY + TIDEWAYS_FLAGS_CPU);
} else {
  throw new Exception("No profiler installed");
}

$n = filter_input(INPUT_GET, 'n', FILTER_VALIDATE_INT);
if ($n < 1) {
  $n = 1000;
}

for ($i=0; $i < $n; $i++) {
    include 'template.php';
}

if (function_exists('xhprof_disable')) {
  $xhprof = xhprof_enable();
} elseif (function_exists('tideways_disable')) {
  $xhprof = tideways_disable();
}

$html = $emmet
->html
  ->head
    ->meta['[charset="utf-8"]_']
    ->title['{XHProf}_']
    ->stylesheet("//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css")
    ->stylesheet("//cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.13.3/css/theme.bootstrap.css")
    ->script("//code.jquery.com/jquery-1.10.2.min.js")
    ->script("//cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.13.3/jquery.tablesorter.min.js")
    ->script("//cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.13.3/jquery.tablesorter.widgets.min.js")
    ->_
  ->body['.container']
    ->h1->t("XHProf ($n)")->_
    ->table['.tablesorter']
      ->thead
        ->tr
          ->th->t('Context')->_
          ->th->t('Function')->_
          ->th->t('Wall time')->_
          ->th->t('Internal time')->_
          ->_
        ->_
      ->tbody->each($xhprof, function ($key, $row) {
        $parts = explode('==>', $key);

        $this->tr
          ->td->t($parts[0])->_
          ->td->t(isset($parts[1]) ? $parts[1] : '')->_
          ->td->t(number_format($row['wt']))->_
          ->td->t(number_format($row['ct']))->_
          ;
      })
      ->_
    ->_
  ->script->setRef($script)
->__;

$script->h(<<<JAVASCRIPT

  $('.tablesorter').tablesorter({
    theme: 'bootstrap',
    widthFixed: true,
    headerTemplate : '{content} {icon}',
    widgets : [ "uitheme", "filter" ],
    sortList: [[3, 1]]
  });

JAVASCRIPT
);

echo $html;
