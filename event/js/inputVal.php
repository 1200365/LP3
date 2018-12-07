<?php
header("Content-type: application/x-javascript");

echo "window.onload = function() {";

echo "var gradeMax['all'] = ${cnt}";

foreach ($grade as $index) {
	echo "var gradeMax['${index}'] = ${cntDiv[$index]}";
}

echo "}";

