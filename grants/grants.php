<?php

$rawFolder = __DIR__ . '/raw';
$csvFile = __DIR__ . '/grants.csv';

$allData = [];
$header = ['year', 'name', 'content', 'area', 'budget_submitted', 'budget_approved', 'account', 'department', 'type', 'vendor'];

foreach (glob($rawFolder . '/*.ods') as $odsFile) {
    $filename = basename($odsFile);
    // Extract year from filename (e.g., "101年度", "臺南市政府102年度")
    if (preg_match('/(\d{2,3})年度/', $filename, $matches)) {
        $year = $matches[1];
    } else {
        continue;
    }

    $rows = parseOdsFile($odsFile);
    foreach ($rows as $row) {
        $row['year'] = $year;
        $allData[] = $row;
    }
}

// Sort by year
usort($allData, function ($a, $b) {
    return $a['year'] <=> $b['year'];
});

// Write CSV
$fp = fopen($csvFile, 'w');
fputcsv($fp, $header);
foreach ($allData as $row) {
    fputcsv($fp, [
        $row['year'],
        $row['name'],
        $row['content'],
        $row['area'],
        $row['budget_submitted'],
        $row['budget_approved'],
        $row['account'],
        $row['department'],
        $row['type'],
        $row['vendor'],
    ]);
}
fclose($fp);

echo "Generated {$csvFile} with " . count($allData) . " records.\n";

function parseOdsFile($odsFile)
{
    $rows = [];

    $zip = new ZipArchive();
    if ($zip->open($odsFile) !== true) {
        return $rows;
    }

    $content = $zip->getFromName('content.xml');
    $zip->close();

    if (empty($content)) {
        return $rows;
    }

    // Parse XML
    $xml = new DOMDocument();
    $xml->loadXML($content);

    $xpath = new DOMXPath($xml);
    $xpath->registerNamespace('table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0');
    $xpath->registerNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');
    $xpath->registerNamespace('office', 'urn:oasis:names:tc:opendocument:xmlns:office:1.0');

    $tableRows = $xpath->query('//table:table-row');

    // Collect all header text from first several rows
    $allHeaderText = '';
    $rowCount = 0;
    foreach ($tableRows as $tableRow) {
        if ($rowCount++ >= 10) break;
        $cells = $xpath->query('table:table-cell', $tableRow);
        foreach ($cells as $cell) {
            $textNodes = $xpath->query('.//text:p', $cell);
            foreach ($textNodes as $textNode) {
                $allHeaderText .= $textNode->textContent . ' ';
            }
        }
    }

    // Detect format: 'full' (with name column), 'no_name' (with budget), 'minimal' (no budget)
    $hasNameColumn = (mb_strpos($allHeaderText, '議員') !== false && mb_strpos($allHeaderText, '姓名') !== false);
    $hasBudgetColumn = (mb_strpos($allHeaderText, '建議') !== false && mb_strpos($allHeaderText, '金額') !== false);

    if ($hasNameColumn) {
        $format = 'full';
    } elseif ($hasBudgetColumn) {
        $format = 'no_name';
    } else {
        $format = 'minimal';
    }

    foreach ($tableRows as $tableRow) {
        $cells = $xpath->query('table:table-cell|table:covered-table-cell', $tableRow);
        $cellValues = [];

        foreach ($cells as $cell) {
            // Handle repeated columns
            $repeat = $cell->getAttribute('table:number-columns-repeated');
            $repeatCount = $repeat ? (int)$repeat : 1;

            // Skip covered cells (merged cells placeholders)
            if ($cell->nodeName === 'table:covered-table-cell') {
                for ($i = 0; $i < $repeatCount && count($cellValues) < 20; $i++) {
                    $cellValues[] = '';
                }
                continue;
            }

            // Get cell value - prefer office:value for numbers, otherwise get text content
            $value = '';
            if ($cell->hasAttribute('office:value')) {
                $value = $cell->getAttribute('office:value');
            } elseif ($cell->hasAttribute('office:string-value')) {
                $value = $cell->getAttribute('office:string-value');
            } else {
                // Get text from text:p elements
                $textNodes = $xpath->query('.//text:p', $cell);
                $textParts = [];
                foreach ($textNodes as $textNode) {
                    $textParts[] = $textNode->textContent;
                }
                $value = implode(' ', $textParts);
            }

            // Clean up value - replace newlines and multiple spaces
            $value = preg_replace('/[\r\n]+/', ' ', $value);
            $value = preg_replace('/\s+/', ' ', $value);
            $value = trim($value);

            for ($i = 0; $i < $repeatCount && count($cellValues) < 20; $i++) {
                $cellValues[] = $value;
            }
        }

        // Determine area index based on format
        switch ($format) {
            case 'full':
                $areaIndex = 2;
                $minCols = 9;
                break;
            case 'no_name':
                $areaIndex = 1;
                $minCols = 8;
                break;
            case 'minimal':
            default:
                $areaIndex = 1;
                $minCols = 6;
                break;
        }

        // Skip header rows and empty rows
        // Data rows should have area column containing "區"
        if (count($cellValues) >= $minCols && isset($cellValues[$areaIndex]) && mb_strpos($cellValues[$areaIndex], '區') !== false) {
            switch ($format) {
                case 'full':
                    $rows[] = [
                        'name' => $cellValues[0],
                        'content' => $cellValues[1],
                        'area' => $cellValues[2],
                        'budget_submitted' => $cellValues[3],
                        'budget_approved' => $cellValues[4],
                        'account' => $cellValues[5],
                        'department' => $cellValues[6],
                        'type' => $cellValues[7],
                        'vendor' => isset($cellValues[8]) ? $cellValues[8] : '',
                    ];
                    break;
                case 'no_name':
                    $rows[] = [
                        'name' => '',
                        'content' => $cellValues[0],
                        'area' => $cellValues[1],
                        'budget_submitted' => $cellValues[2],
                        'budget_approved' => $cellValues[3],
                        'account' => $cellValues[4],
                        'department' => $cellValues[5],
                        'type' => $cellValues[6],
                        'vendor' => isset($cellValues[7]) ? $cellValues[7] : '',
                    ];
                    break;
                case 'minimal':
                default:
                    // 6 columns: content, area, account, department, type, vendor
                    $rows[] = [
                        'name' => '',
                        'content' => $cellValues[0],
                        'area' => $cellValues[1],
                        'budget_submitted' => '',
                        'budget_approved' => '',
                        'account' => $cellValues[2],
                        'department' => $cellValues[3],
                        'type' => $cellValues[4],
                        'vendor' => isset($cellValues[5]) ? $cellValues[5] : '',
                    ];
                    break;
            }
        }
    }

    return $rows;
}
