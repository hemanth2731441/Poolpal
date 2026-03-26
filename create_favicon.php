<?php
// This script creates a basic favicon.ico file in the root directory

// PNG data of a simple blue drop icon (16x16 pixels)
$data = base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAB'.
    'VklEQVQ4y6WTsUoDQRCGv9nLmSuCgqCFWIiCYCGCINiIhZWFtVj5BPkTPoMvYGFlIVhILCwUbCNY'.
    'CAqCIGIhEhSN3mV3x2LvvOQwhYJTDTPz7bc7/yyIyDGwB1SVc/4Cu8BQRI4AvG63W1l+DGS+78+t'.
    'ra6tWGvzQAZGGV2M6Pf6SbVaXQFy4wDnHNbazDiTSSYmAq21DIdD6vX6FMAAiAiNRoNms8nh0QHW'.
    'WsIwJE1TQi9k89kmK8+l4u3LG+FEOPPpnAG0MYZzc9kOzjm63S6dbofmW5O3lzcalw06UYfaa4bW'.
    '1dL8FECSL9P+blN/rNO+bxNEAdWnar5TuChQWaw4gKlvUEqhlcZ4hs3TTZRW9KIex7fHaKVRSrm/'.
    'AGu9bMQp4sRXfLbCFEAgDyQA3sbefKlScs6NPMCADxQ9oCqHhpPT5bsKsBK/tZm9vHeRXZj4hfnC'.
    'lFII/gBGIkp64n/+rAAAAABJRU5ErkJggg=='
);

// Save the favicon
$file = fopen('favicon.ico', 'wb');
fwrite($file, $data);
fclose($file);

echo "Favicon created successfully.";
?> 