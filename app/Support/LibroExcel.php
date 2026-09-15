<?php

namespace App\Support;

use ZipArchive;

/**
 * Escribe un .xlsx de verdad (no un CSV renombrado) sin depender de ninguna
 * librería externa: un xlsx es un zip con unos cuantos XML dentro.
 *
 * Alcance a propósito corto, que es lo que necesitan las exportaciones de la
 * bitácora: varias hojas, una fila de encabezado con estilo, texto y números,
 * y anchos de columna. Nada de fórmulas ni formatos condicionales.
 *
 * Uso:
 *   $libro = new LibroExcel();
 *   $libro->hoja('Sesiones', ['Usuario', 'Duración'], [['Alan', 3600]], [28, 14]);
 *   return $libro->descargar('bitacora.xlsx');
 */
class LibroExcel
{
    /** @var list<array{nombre:string,encabezados:list<string>,filas:list<list<mixed>>,anchos:list<int>}> */
    private array $hojas = [];

    /**
     * @param  list<string>  $encabezados
     * @param  list<list<mixed>>  $filas
     * @param  list<int>  $anchos  ancho de cada columna en caracteres
     */
    public function hoja(string $nombre, array $encabezados, array $filas, array $anchos = []): self
    {
        $this->hojas[] = [
            'nombre' => $this->nombreValido($nombre),
            'encabezados' => $encabezados,
            'filas' => $filas,
            'anchos' => $anchos,
        ];

        return $this;
    }

    /** Devuelve el contenido binario del libro. */
    public function contenido(): string
    {
        $archivo = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive;
        $zip->open($archivo, ZipArchive::OVERWRITE | ZipArchive::CREATE);

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->relacionesRaiz());
        $zip->addFromString('xl/workbook.xml', $this->libro());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->relacionesLibro());
        $zip->addFromString('xl/styles.xml', $this->estilos());

        foreach ($this->hojas as $indice => $hoja) {
            $zip->addFromString('xl/worksheets/sheet'.($indice + 1).'.xml', $this->hojaXml($hoja));
        }

        $zip->close();
        $contenido = (string) file_get_contents($archivo);
        @unlink($archivo);

        return $contenido;
    }

    /**
     * @return array<string, string> cabeceras HTTP para la descarga
     */
    public static function cabeceras(string $nombreArchivo): array
    {
        return [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$nombreArchivo.'"',
            'Cache-Control' => 'no-store, no-cache',
        ];
    }

    /**
     * @param  array{nombre:string,encabezados:list<string>,filas:list<list<mixed>>,anchos:list<int>}  $hoja
     */
    private function hojaXml(array $hoja): string
    {
        $cols = '';

        foreach ($hoja['anchos'] as $i => $ancho) {
            $n = $i + 1;
            $cols .= '<col min="'.$n.'" max="'.$n.'" width="'.$ancho.'" customWidth="1"/>';
        }

        $filas = '<row r="1">';

        foreach ($hoja['encabezados'] as $i => $texto) {
            $filas .= $this->celda($this->columna($i).'1', $texto, 1);
        }

        $filas .= '</row>';
        $numero = 2;

        foreach ($hoja['filas'] as $fila) {
            $filas .= '<row r="'.$numero.'">';

            foreach (array_values($fila) as $i => $valor) {
                $filas .= $this->celda($this->columna($i).$numero, $valor);
            }

            $filas .= '</row>';
            $numero++;
        }

        $ultima = $this->columna(max(count($hoja['encabezados']) - 1, 0)).max($numero - 1, 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .($cols !== '' ? '<cols>'.$cols.'</cols>' : '')
            .'<sheetData>'.$filas.'</sheetData>'
            .'<autoFilter ref="A1:'.$ultima.'"/>'
            .'</worksheet>';
    }

    private function celda(string $referencia, mixed $valor, int $estilo = 0): string
    {
        $atributoEstilo = $estilo > 0 ? ' s="'.$estilo.'"' : '';

        if (is_int($valor) || is_float($valor)) {
            return '<c r="'.$referencia.'"'.$atributoEstilo.'><v>'.$valor.'</v></c>';
        }

        $texto = htmlspecialchars((string) $valor, ENT_QUOTES | ENT_XML1, 'UTF-8');
        // Los caracteres de control rompen el XML de Excel
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $texto) ?? '';

        return '<c r="'.$referencia.'" t="inlineStr"'.$atributoEstilo.'><is><t xml:space="preserve">'.$texto.'</t></is></c>';
    }

    private function columna(int $indice): string
    {
        $letras = '';

        for ($n = $indice + 1; $n > 0; $n = intdiv($n - 1, 26)) {
            $letras = chr(65 + ($n - 1) % 26).$letras;
        }

        return $letras;
    }

    private function nombreValido(string $nombre): string
    {
        return mb_substr(str_replace(['\\', '/', '?', '*', '[', ']', ':'], '-', $nombre), 0, 31);
    }

    private function contentTypes(): string
    {
        $hojas = '';

        foreach ($this->hojas as $indice => $hoja) {
            $hojas .= '<Override PartName="/xl/worksheets/sheet'.($indice + 1).'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .$hojas
            .'</Types>';
    }

    private function relacionesRaiz(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function libro(): string
    {
        $hojas = '';

        foreach ($this->hojas as $indice => $hoja) {
            $hojas .= '<sheet name="'.htmlspecialchars($hoja['nombre'], ENT_QUOTES | ENT_XML1, 'UTF-8').'" sheetId="'.($indice + 1).'" r:id="rId'.($indice + 1).'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'.$hojas.'</sheets></workbook>';
    }

    private function relacionesLibro(): string
    {
        $relaciones = '';

        foreach ($this->hojas as $indice => $hoja) {
            $relaciones .= '<Relationship Id="rId'.($indice + 1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.($indice + 1).'.xml"/>';
        }

        $estilos = 'rId'.(count($this->hojas) + 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$relaciones
            .'<Relationship Id="'.$estilos.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    /** Dos estilos: normal (0) y encabezado en azul de marca (1). */
    private function estilos(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="3">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF063381"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            .'</cellXfs>'
            .'</styleSheet>';
    }
}
