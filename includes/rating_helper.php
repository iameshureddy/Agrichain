<?php
function renderStars($rating) {
    $s = "";
    for ($i=1;$i<=5;$i++) {
        $s .= $i <= $rating ? "⭐" : "☆";
    }
    return $s;
}
