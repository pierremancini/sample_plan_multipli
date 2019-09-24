<?php
/**
 * PLUGIN NAME: Sample plan multipli
 * DESCRIPTION: Generate sample plan for multipli project
 * VERSION: v0.1
 * AUTHOR: Pierre Mancini
 */


// Call the REDCap Connect file in the main "redcap" directory
require_once "../../redcap_connect.php";


/* Récupération du repport */
if (isset($_GET['report_id']) && (is_numeric($_GET['report_id']))) {

    // Get report name
    $report_name = DataExport::getReportNames($_GET['report_id'], !$user_rights['reports']);

    // If report name is NULL, then user doesn't have Report Builder rights AND doesn't have access to this report

    if ($report_name === null) {
        $html .= RCView::div(array('class' => 'red'), $lang['global_01'] . $lang['colon'] . " " . $lang['data_export_tool_180']);
    } else {

        //Get report
        $report = DataExport::getReports($_GET['report_id']);

        //Get Data and report
        $record_data = Records::getData('array', array(), $report["fields"], $report["limiter_events"], array(), false, false, false, $report["limiter_logic"]);
        list ($report_table, $num_results_returned) = DataExport::doReport($_GET['report_id'], 'report', "html");
    }
}

// OPTIONAL: Display the project header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

// Your HTML page content goes here
?>

<h3 style="color:#800000;">
    Génération de sample plan
</h3>

<div class="chklist" style="padding:8px 15px 7px;margin:5px 0 20px;max-width:770px;">
    <table class="form_border" style="width:700px;">
        <!-- Header displaying record count -->
        <tbody>
            <tr>
                <td class="header" colspan="2" style="font-weight:normal;padding:10px 5px;color:#800000;font-size:12px;">
                    Total enregistrements : <b><?php print $num_results_returned; ?></b>
                </td>
            </tr>
            <tr>
                <td class="label">Choisir un rapport (date de RCP requise)</td>
                <td class="data">
                    <?php
                    $report_names = DataExport::getReportNames(null, !$user_rights['reports']);
                    print RCView::select(array(
                        'class' => "x-form-text x-form-field",
                        'onchange' => 'window.location.href="'.PAGE_FULL . "?pid=".$_GET['pid'].'&report_id='.'"+this.value'
                        ),
                        $report_names, $_GET['report_id']);
                    ?>
                </td>
            </tr>
        </tbody></table>
</div>

<div id="this_report_title" style="margin:40px 0 8px;padding:5px 3px;color:#800000;font-size:18px;font-weight:bold;">
    <?php print "Sample plan structure" ?>
</div>
<p>
    <style type="text/css">
        #export, #export:visited {
            text-decoration: none;
            color:#000;
            background-color:#ddd;
            border: 1px solid #ccc;
            padding:8px;
        }
    </style>
     <a href="download.php?pid=<?php echo $_GET['pid'];?>&report_id=<?php echo $_GET['report_id'];?>"  id="export"  > Transform to sample plan and export </a>
    <br>
</p>

<div>
    <?php
        print $report_table;
    ?>
</div>

<?php

// OPTIONAL: Display the project footer
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';