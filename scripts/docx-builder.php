<?php

declare(strict_types=1);

function xmlEscape(string $text): string
{
    return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function buildParagraph(string $style, string $text = ''): string
{
    $escaped = xmlEscape($text);

    return match ($style) {
        'title' => '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="200"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="36"/><w:color w:val="1B4332"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'subtitle' => '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="120"/></w:pPr><w:r><w:rPr><w:sz w:val="24"/><w:color w:val="40916C"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'meta' => '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="60"/></w:pPr><w:r><w:rPr><w:i/><w:sz w:val="20"/><w:color w:val="666666"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'heading1' => '<w:p><w:pPr><w:spacing w:before="360" w:after="120"/><w:pBdr><w:bottom w:val="single" w:sz="6" w:space="1" w:color="40916C"/></w:pBdr></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="28"/><w:color w:val="1B4332"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'heading2' => '<w:p><w:pPr><w:spacing w:before="240" w:after="80"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="24"/><w:color w:val="2D6A4F"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'body' => '<w:p><w:pPr><w:spacing w:after="120"/><w:jc w:val="both"/></w:pPr><w:r><w:rPr><w:sz w:val="22"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'note' => '<w:p><w:pPr><w:spacing w:after="120"/><w:shd w:val="clear" w:color="auto" w:fill="FFF3CD"/><w:jc w:val="both"/></w:pPr><w:r><w:rPr><w:sz w:val="20"/><w:color w:val="856404"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'status' => '<w:p><w:pPr><w:spacing w:after="120"/><w:shd w:val="clear" w:color="auto" w:fill="D8F3DC"/><w:jc w:val="both"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="1B4332"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'bullet' => '<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr><w:spacing w:after="60"/></w:pPr><w:r><w:rPr><w:sz w:val="22"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'numbered' => '<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="2"/></w:numPr><w:spacing w:after="80"/></w:pPr><w:r><w:rPr><w:sz w:val="22"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'table_header' => buildTableRow($text, true),
        'table_row' => buildTableRow($text, false),
        'spacer' => '<w:p><w:r><w:t></w:t></w:r></w:p>',
        default => '<w:p><w:r><w:t>'.$escaped.'</w:t></w:r></w:p>',
    };
}

function buildTableRow(string $text, bool $header): string
{
    $cells = explode("\t", $text);
    $row = '<w:tr>';
    foreach ($cells as $cell) {
        $row .= '<w:tc><w:tcPr><w:tcW w:w="3000" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr>';
        if ($header) {
            $row .= '<w:b/><w:color w:val="FFFFFF"/>';
        }
        $row .= '<w:sz w:val="20"/></w:rPr><w:t>'.xmlEscape($cell).'</w:t></w:r></w:p></w:tc>';
    }
    $row .= '</w:tr>';

    if ($header) {
        return '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="40916C"/><w:left w:val="single" w:sz="4" w:space="0" w:color="40916C"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="40916C"/><w:right w:val="single" w:sz="4" w:space="0" w:color="40916C"/><w:insideH w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/><w:insideV w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/></w:tblBorders></w:tblPr><w:tblGrid><w:gridCol w:w="3500"/><w:gridCol w:w="4500"/><w:gridCol w:w="2000"/></w:tblGrid>'.$row;
    }

    return $row;
}

/**
 * @param  list<array{style: string, text?: string}>  $paragraphs
 */
function writeDocx(string $outPath, array $paragraphs): void
{
    $bodyXml = '';
    $inTable = false;

    foreach ($paragraphs as $p) {
        $style = $p['style'];
        $text = $p['text'] ?? '';

        if ($style === 'table_header') {
            $bodyXml .= buildParagraph($style, $text);
            $inTable = true;

            continue;
        }

        if ($inTable && $style === 'table_row') {
            $bodyXml .= buildParagraph($style, $text);

            continue;
        }

        if ($inTable && $style !== 'table_row') {
            $bodyXml .= '</w:tbl>';
            $inTable = false;
        }

        $bodyXml .= buildParagraph($style, $text);
    }

    if ($inTable) {
        $bodyXml .= '</w:tbl>';
    }

    $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
        .'<w:body>'.$bodyXml
        .'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>'
        .'</w:body></w:document>';

    $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        .'<Default Extension="xml" ContentType="application/xml"/>'
        .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
        .'<Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>'
        .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
        .'</Types>';

    $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
        .'</Relationships>';

    $docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>'
        .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
        .'</Relationships>';

    $numbering = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
        .'<w:abstractNum w:abstractNumId="0"><w:multiLevelType w:val="hybridMultilevel"/>'
        .'<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="•"/><w:lvlJc w:val="left"/></w:lvl></w:abstractNum>'
        .'<w:abstractNum w:abstractNumId="1"><w:multiLevelType w:val="hybridMultilevel"/>'
        .'<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/><w:lvlJc w:val="left"/></w:lvl></w:abstractNum>'
        .'<w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>'
        .'<w:num w:numId="2"><w:abstractNumId w:val="1"/></w:num>'
        .'</w:numbering>';

    $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
        .'<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/></w:rPr></w:rPrDefault></w:docDefaults>'
        .'</w:styles>';

    $zip = new ZipArchive;
    if ($zip->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        fwrite(STDERR, "Cannot create docx at {$outPath}\n");
        exit(1);
    }

    $zip->addFromString('[Content_Types].xml', $contentTypes);
    $zip->addFromString('_rels/.rels', $rels);
    $zip->addFromString('word/document.xml', $documentXml);
    $zip->addFromString('word/_rels/document.xml.rels', $docRels);
    $zip->addFromString('word/numbering.xml', $numbering);
    $zip->addFromString('word/styles.xml', $styles);
    $zip->close();
}
