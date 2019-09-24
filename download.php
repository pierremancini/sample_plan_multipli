<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../redcap_connect.php";
// On met à part les fonctions testés par test unitaire
require_once "downloadUtil.php";

/* Récupération du repport */
if (isset($_GET['report_id']) && (is_numeric($_GET['report_id']))) {

    // Get report name
    $report_name = DataExport::getReportNames($_GET['report_id'], !$user_rights['reports']);

    // If report name is NULL, then user doesn't have Report Builder rights AND doesn't have access to this report

    //Get report
    $report = DataExport::getReports($_GET['report_id']);


    //Get Data and report
    $record_data = Records::getData('array', array(), $report["fields"], $report["limiter_events"], array(), false, false, false, $report["limiter_logic"]);
}

function print_r_reverse($in) {
    $lines = explode("\n", trim($in));
    if (trim($lines[0]) != 'Array') {
        // bottomed out to something that isn't an array
        return $in;
    } else {
        // this is an array, lets parse it
        if (preg_match("/(\s{5,})\(/", $lines[1], $match)) {
            // this is a tested array/recursive call to this function
            // take a set of spaces off the beginning
            $spaces = $match[1];
            $spaces_length = strlen($spaces);
            $lines_total = count($lines);
            for ($i = 0; $i < $lines_total; $i++) {
                if (substr($lines[$i], 0, $spaces_length) == $spaces) {
                    $lines[$i] = substr($lines[$i], $spaces_length);
                }
            }
        }
        array_shift($lines); // Array
        array_shift($lines); // (
        array_pop($lines); // )
        $in = implode("\n", $lines);
        // make sure we only match stuff with 4 preceding spaces (stuff for this array and not a nested one)
        preg_match_all("/^\s{4}\[(.+?)\] \=\> /m", $in, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        $pos = array();
        $previous_key = '';
        $in_length = strlen($in);
        // store the following in $pos:
        // array with key = key of the parsed array's item
        // value = array(start position in $in, $end position in $in)
        foreach ($matches as $match) {
            $key = $match[1][0];
            $start = $match[0][1] + strlen($match[0][0]);
            $pos[$key] = array($start, $in_length);
            if ($previous_key != '') $pos[$previous_key][1] = $match[0][1] - 1;
            $previous_key = $key;
        }
        $ret = array();
        foreach ($pos as $key => $where) {
            // recursively see if the parsed out value is an array too
            $ret[$key] = print_r_reverse(substr($in, $where[0], $where[1] - $where[0]));
        }
        return $ret;
    }
} 

// Navigateur met le ouput du code dans le fichier sample_plan_multipli.tsv
header("Content-Disposition: attachment; filename=\"sample_plan_multipli.tsv\"");
header("Content-Type: text/plain");

$header_order = ['CASE' => 'case',
                 'URL' => 'url',
                 'REMOTEFILE' => 'fastQ_file_cng',
                 'LOCALFILE' => 'fastQ_file_local'];


foreach ($header_order as $key => $value) {

    $header .= "$key\t";

    $value_order[] = $value;
}

$header = substr($header, 0, -1)."\n";

echo $header;

echo transform_record_data($record_data, $value_order);