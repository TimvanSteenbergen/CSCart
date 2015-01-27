<?php

$array_to_csv = array(
	array('Naam 1','Naam 2','Naam 3','Straatnaam','Huisnummer','Huisnummer toevoeging','Postcode','Woonplaats','Landsdeel','Landcode','naampakbonbestand','opmerking','volgnummer'),
	array('AC Heijer','','2313600_37663','Burgemeester Keijzerlaan','185','','2262BG','Leidschendam','','Nederland','C:\UnderfashionDatabase\pakbonnen\2313600_37663PakbonEnPicklijst.pdf','','1'),
	array('Francoise Tai','','2313600_37736','Bergdreef','54','','4822TM','Breda','','Nederland','C:\UnderfashionDatabase\pakbonnen\2313600_37736PakbonEnPicklijst.pdf','','2'),
	array('mja jollie','','2313600_37735','Beelaertslaan','21','','6861AS','Oosterbeek','','Nederland','C:\UnderfashionDatabase\pakbonnen\2313600_37735PakbonEnPicklijst.pdf','','3'),
	array('Frederieke Linckens','','2313600_37560','Uitdamstraat','19','','3826CG','Amersfoort','','Nederland','C:\UnderfashionDatabase\pakbonnen\2313600_37560(3)PakbonEnPicklijst.pdf','+pakketzegel voor retourneren verkeerde bh op onze kosten','4')
);

function convert_to_csv($input_array, $output_file_name, $delimiter) {
    /** open raw memory as file, no need for temp files */
    $temp_memory = fopen('php://memory', 'w');
    /** loop through array  */
    foreach ($input_array as $line) {
        /** default php csv handler **/
        fputcsv($temp_memory, $line, $delimiter);
    }
    /** rewrind the "file" with the csv lines **/
    fseek($temp_memory, 0);
    /** modify header to be downloadable csv file **/
    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
    /** Send file to browser for download */
    fpassthru($temp_memory);
}

 
convert_to_csv($array_to_csv, 'report.csv', ',');
?>