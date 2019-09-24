<?php

/*
    Retoune true quand les valeur de l'instance doivent apparaitre 
    dans le sample plan.
*/
function filter_instances($value) {

    $falsy = array('1', '2');

    if ($value['out_of_date_constit'] || in_array($value['transferred_locally_constit'], $falsy) 
        || in_array($value['transferred_locally'], $falsy)
        || in_array($value['transferred_locally_rna'], $falsy) 
        ||  in_array($value['out_of_date'], $falsy)
        || in_array($value['out_of_date_rna'], $falsy)) {
        return false;
    } else {
        return true;
    }
}

/* 
    - Formate les données du record fournit pas l'appli redcap
    - Retourne une chaine de caractère destinée à être "affiché" dans
      la sortie du script, correspondant au contenut du fichier exportable 
    - Respecte l'ordre donné par $value_order 
*/
function transform_record_data($record_data, $value_order) {

    $output = '';
    $analysis_type = '';

    foreach($record_data as $patient_id => $array_patient) {
        $sub_array = $array_patient['repeat_instances'][49];
        foreach($sub_array as $instrument => $inst_array) {
            foreach ($inst_array as $instance_nb => $instance) {

                // On utilise la fonction filter_instances car Records::getData
                // nous donne également les champs vides.
                if(filter_instances($instance)) {
                    if($instrument == 'germline_dna_sequencing') {
                        $path_on_cng = $instance['path_on_cng_constit'];
                        $fastQ_file_cng = $instance['fastq_filename_cng_constit'];
                        $fastQ_file_local = $instance['fastq_filename_local_constit'];
                        $set = $instance['set_on_cng_constit'];
                        $analysis_type = 'CD';
                    } elseif ($instrument == 'tumor_dna_sequencing') {
                        $path_on_cng = $instance['path_on_cng'];
                        $fastQ_file_cng = $instance['fastq_filename_cng'];
                        $fastQ_file_local = $instance['fastq_filename_local'];
                        $set = $instance['set_on_cng'];
                        $analysis_type = 'MD';
                    } elseif ($instrument == 'rna_sequencing') {
                        $path_on_cng = $instance['path_on_cng_rna'];
                        $fastQ_file_cng = $instance['fastq_filename_cng_rna'];
                        $fastQ_file_local = $instance['fastq_filename_local_rna'];
                        $set = $instance['set_on_cng_rna'];
                        $analysis_type = 'MR';
                    } else {
                        $path_on_cng = 'Error';
                        $fastQ_file_cng = 'Error';
                        $fastQ_file_local = 'Error';
                        $set = 'Error';
                        $analysis_type = 'Error';
                    }

                    $case = "$patient_id-$set-$analysis_type";


                    foreach ($value_order as $key => $value) {

                        $line .= "$$value\t";
                        $line_md5 = "$$value\t";
                    }

                    $line = substr($line, 0, -1)."\n";
                    $line_md5 = substr($line_md5, 0, -1)."\n";


                    $output .= "$case\t$path_on_cng\t$fastQ_file_cng\t$fastQ_file_local\n";
                    // ligne des chemins vers les md5
                    $output .= "$case\t$path_on_cng\t$fastQ_file_cng.md5\t$fastQ_file_local.md5\n";              
                }
            } 
        }
    }

    return $output;

}