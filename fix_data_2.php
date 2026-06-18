<?php
$file = 'C:\Users\Aspire 7\Documents\Project TEFA WebWeavers\DENSO-LMS\includes\DX_LMS_M_StudentData.php';
$content = file_get_contents($file);

$pattern = "/\],,\s*\[\s*'id'\s*=>\s*'FRM02',\s*'title'\s*=>\s*'Pertanyaan Seputar Tugas Akhir',\s*'replies'\s*=>\s*2,\s*'last_active'\s*=>\s*'Kemarin'\s*\]\s*\],/s";
$content = preg_replace($pattern, "],", $content);

file_put_contents($file, $content);
echo "Fixed syntax error!";
