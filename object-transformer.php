
<?php
function readJsonFile($jsonFilePath) {
    $jsonData = file_get_contents($jsonFilePath);
    return json_decode($jsonData, true);
}

function generateCCode($blocks) {
    $cCode = "";

    foreach ($blocks as $block) {
        switch ($block['type']) {
            case 'start':
                $cCode .= "int main() {\n";
                break;
            case 'set_variable':
                $cCode .= "int " . $block['variable_name'] . " = " . $block['value'] . ";\n";
                break;
            case 'loop':
                $cCode .= "for (int i = 0; i < " . $block['count'] . "; i++) {\n";
                $cCode .= generateCCode($block['blocks']);
                $cCode .= "}\n";
                break;
            case 'print':
                $cCode .= "printf(\"" . $block['text'] . "\\n\");\n";
                break;
            case 'change_variable':
                $cCode .= $block['variable_name'] . " += " . $block['change'] . ";\n";
                break;
            case 'condition':
                $cCode .= "if (" . $block['label'] . ") {\n";
                $cCode .= generateCCode($block['blocks']);
                $cCode .= "}\n";
                if ($block['else']) {
                    $cCode .= "else {\n";
                    $cCode .= generateCCode($block['else']);
                    $cCode .= "}\n";
                }
                break;
            case 'end':
                $cCode .= "return 0;\n}\n";
                break;
        }
    }

    return "<pre><code>" . htmlspecialchars($cCode) . "</code></pre>";
}



function compileCCode($cCode) {
    // Create a temporary C file
    $tempFile = tempnam(sys_get_temp_dir(), 'ccode');
    file_put_contents($tempFile, $cCode);

    // Compile the C code using a suitable compiler (e.g., GCC)
    $command = "gcc -o output $tempFile 2>&1";
    $output = [];
    $return_code = exec($command, $output);

    // Delete the temporary file
    unlink($tempFile);

    if ($return_code === 0) {
        echo "<pre><code>C code compiled successfully!</code></pre>";
    } else {
        echo "<pre><code>Compilation failed:\n" . implode("\n", $output) . "</code></pre>";
        echo "<pre><code>Temporary file: $tempFile</code></pre>"; // Add this line to debug
    }
}

// Example usage:
$jsonFilePath = "data.json";
$blocks = readJsonFile($jsonFilePath);
$cCode = generateCCode($blocks);
echo $cCode;

// Compile the C code (optional)
compileCCode($cCode);
?>