<?php

App::import('Vendor', 'PHPExcel', array('file' => 'excel/PHPExcel.php'));
App::import('Vendor', 'PHPExcelWriter', array('file' => 'excel/PHPExcel/Writer/Excel5.php'));

class PwExcel {

    var $xls;
    var $sheet;
    var $data;
    var $blacklist = array();
    public $freezepane;

    function __construct() {
        set_time_limit(0);
        $this->xls = new PHPExcel();
        $this->sheet = $this->xls->getActiveSheet();
        $this->sheet->getDefaultStyle()->getFont()->setName('Calibri');
        $this->j = 1;
        $this->i = 0;
        $this->footer = array();
        $this->title = '';
        $this->header = array();
        $this->allign = 'h';
    }

    function generate(&$data, $title = '', $header = array(), $allign = 'h', $footer = array(), $headercolor = '#daf5fe') {
        $this->data = &$data;
        $this->footer = $footer;
        $this->title = $title;
        $this->header = $header;
        $this->allign = $allign;
        $this->generateHeader($header);
        $this->headercolor = "#daf5fe"; //$headercolor;
        $this->_title($title);
        if (strtoupper(trim($allign)) == 'V') {
            $this->_vheaders();
            $this->_vrows();
        } else {
            $this->_headers();
            $this->_rows();
        }
        return $this->_output();
        //return true; 
    }

    function generateHeader($Header) {
        if (!is_array($Header) || count($Header) == 0)
            return;
        $i = $this->i;
        $j = $this->j + 1;
        foreach ($Header as $field => $value) {
            if (!in_array($field, $this->blacklist)) {
                $columnName = Inflector::humanize($field);
                $this->sheet->setCellValueByColumnAndRow($i, $j, $columnName);
                $this->sheet->getStyle('A' . $j)->getFont()->setBold(true);
                $this->sheet->getStyle('A' . $j)->getFont()->setSize(11);
                //$this->sheet->getRowDimension($j)->setRowHeight(23); 
                $this->sheet->mergeCells('A' . $j . ':' . 'D' . $j);
                $this->sheet->getStyle('A' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $this->sheet->setCellValueByColumnAndRow($i,  ++$j, $value);
                $this->sheet->getStyle('A' . $j)->getFont()->setBold(false);
                $this->sheet->getStyle('A' . $j)->getFont()->setSize(11);
                //$this->sheet->getRowDimension($j)->setRowHeight(20); 
                $this->sheet->mergeCells('A' . $j . ':' . 'D' . $j);
                $this->sheet->getStyle('A' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
            $j += 1;
        }
        $this->j = $j;
    }

    function _title($title) {
        if (is_array($title)) {
            foreach ($title as $val) {
                $j = $this->j + 1;
                $this->sheet->setCellValue('A' . $j, $val);
                $this->sheet->getStyle('A' . $j)->getFont()->setSize(11);
                //$this->sheet->getRowDimension('A'.$j)->setRowHeight(23); 
                $this->sheet->mergeCells('A' . $j . ':' . 'D' . $j);
                $this->sheet->getStyle('A' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $this->j = $j + 1;
            }
        } elseif (trim($title != "")) {
            $j = $this->j + 1;
            $this->sheet->setCellValue('A' . $j, $title);
            $this->sheet->getStyle('A' . $j)->getFont()->setSize(11);
            //$this->sheet->getRowDimension('A'.$j)->setRowHeight(23); 
            $this->sheet->mergeCells('A' . $j . ':' . 'D' . $j);
            $this->sheet->getStyle('A' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->j = $j + 1;
        }
    }

    function _headers() {
        $i = $this->i;
        $j = $this->j + 1;
        foreach ($this->data[0] as $field => $value) {
            if (isset($value) && is_array($value)) {
                if (!in_array($field, $this->blacklist)) {
                    $columnName = Inflector::humanize($field); //totoal
                    $this->sheet->mergeCellsByColumnAndRow($i, $j, ($i + count($value) - 1), $j);
                    $this->sheet->setCellValueByColumnAndRow($i, $j, $columnName);
                }
                foreach ($value as $fld => $value) {
                    if (!in_array($fld, $this->blacklist)) {
                        $columnName = Inflector::humanize($fld); //totoal
                        $this->sheet->setCellValueByColumnAndRow($i, $j + 1, $columnName);
                        $i+=1;
                    }
                }
                $this->sheet->getStyle('A' . ($j + 1))->getFont()->setBold(true);
                $this->sheet->getStyle('A' . ($j + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $this->sheet->getStyle('A' . ($j + 1))->getFill()->getStartColor()->setRGB('#8ce8fb');
                $this->sheet->duplicateStyle($this->sheet->getStyle('A' . ($j + 1)), 'B' . ($j + 1) . ':' . $this->sheet->getHighestColumn() . ($j + 1));
            } else if (!in_array($field, $this->blacklist)) {
                $columnName = Inflector::humanize($field);
                $this->sheet->setCellValueByColumnAndRow($i++, $j, $columnName);
            }
        }
        $this->j = $j + 1;
        $this->sheet->getStyle('A' . $j)->getFont()->setBold(true);
        $this->sheet->getStyle('A' . $j)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $this->sheet->getStyle('A' . $j)->getFill()->getStartColor()->setRGB($this->headercolor);
        $this->sheet->duplicateStyle($this->sheet->getStyle('A' . $j), 'B' . $j . ':' . $this->sheet->getHighestColumn() . $j);
        for ($j = $this->i; $j < $i; $j++) {
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($j))->setAutoSize(true);
        }

        if ($this->freezepane != '' && !is_null($this->freezepane))
            $this->sheet->freezePane($this->freezepane);
        //else
        //	$this->sheet->freezePane('A'.($this->j + 1));
    }

    function _rows() {
        $j = $this->j + 1;
        foreach ($this->data as $row) {
            $i = $this->i;
            unset($this->data[$j - ($this->j + 1)]);
            if ($j > 65535) {
                $this->sheet = $this->xls->createSheet();
                $this->sheet->getDefaultStyle()->getFont()->setName('Calibri');
                $this->data = array_values($this->data);
                $this->j = 1;
                $this->generateHeader($this->header);
                $this->headercolor = $this->headercolor;
                $this->_title($this->title);
                if (strtoupper(trim($this->allign)) == 'V') {
                    $this->_vheaders();
                    $this->_vrows();
                } else {
                    $this->_headers();
                    $this->_rows();
                }
                return;
            }
            foreach ($row as $field => $value) {
                if (isset($value) && is_array($value)) {
                    foreach ($value as $fld => $val) {
                        if (!in_array($fld, $this->blacklist)) {
                            $this->sheet->setCellValueByColumnAndRow($i++, $j, $val);
                        }
                    }
                } else if (!in_array($field, $this->blacklist)) {
                    $this->sheet->setCellValueByColumnAndRow($i++, $j, $value);
                }
            }
            $j++;
        }
        if (is_array($this->footer) && count($this->footer) > 0) {
            $i = 0;
            $j++;
            foreach ($this->footer as $field => $value) {
                if (isset($value) && is_array($value)) {
                    foreach ($value as $fld => $val) {
                        $this->sheet->setCellValueByColumnAndRow($i++, $j, $val);
                    }
                } else
                    $this->sheet->setCellValueByColumnAndRow($i++, $j, $value);
                $this->sheet->getStyle('A' . $j)->getFont()->setBold(true);
                $this->sheet->duplicateStyle($this->sheet->getStyle('A' . $j), 'B' . $j . ':' . $this->sheet->getHighestColumn() . $j);
            }
        }
        $this->j = $j;
    }

    function _vheaders() {
        $i = 0;
        $j = $this->j;
        foreach ($this->data[0] as $field => $value) {
            if (!in_array($field, $this->blacklist)) {
                $columnName = Inflector::humanize($field);
                $this->sheet->setCellValueByColumnAndRow($i, ++$j, $columnName);
            }
        }
        $this->sheet->getStyle('A' . ($this->j + 1))->getFont()->setBold(true);
        $this->sheet->getStyle('A' . ($this->j + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $this->sheet->getStyle('A' . ($this->j + 1))->getFill()->getStartColor()->setRGB($this->headercolor);
        $this->sheet->duplicateStyle($this->sheet->getStyle('A' . ($this->j + 1)), 'A' . ($this->j + 2) . ':' . 'A' . ($j));
    }

    function _vrows() {
        $i = 1;
        foreach ($this->data as $row) {
            $j = $this->j;
            foreach ($row as $field => $value) {
                if (!in_array($field, $this->blacklist)) {
                    $this->sheet->setCellValueByColumnAndRow($i,  ++$j, $value);
                    $this->sheet->getStyle('B' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                }
            }
            $i++;
        }
        for ($k = ($this->j); $k < $j; $k++)
            $this->sheet->duplicateStyle($this->sheet->getStyle('B' . ($k + 1)), 'B' . ($k + 1) . ':' . $this->sheet->getHighestColumn() . ($k + 1));
        if (is_array($this->footer) && count($this->footer) > 0) {
            $j = $this->j;
            $i++;
            foreach ($this->footer as $field => $value) {
                $this->sheet->setCellValueByColumnAndRow($i,  ++$j, $value);
                $this->sheet->getStyle($this->sheet->getHighestColumn() . $j)->getFont()->setBold(true);
                $this->sheet->getStyle($this->sheet->getHighestColumn() . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            }
            $i++;
        }
        for ($k = 0; $k < $i; $k++)
            $this->sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($k))->setAutoSize(true);
    }

    function _output() {
        $filepath = APP . '/tmp/';
        $filename = "excel" . date("Y_m_d_H_i_s") . '.xls';
        $objWriter = new PHPExcel_Writer_Excel5($this->xls);
        $objWriter->setTempDir(TMP);
        $objWriter->save($filepath . $filename);
        return $filename;
        /* header('Content-Description: File Transfer');
          header("Content-type: application/vnd.ms-excel");
          header('Content-Disposition: attachment;filename="'.$filename.'"',true);
          header('Cache-Control: max-age=0');
          readfile($filepath.$filename);
          //echo("http://220.226.204.104/mainlinkpos/irctcERS/".$filename);
          //header('Content-Length: '.filesize($filename));
          unlink($filepath.$filename); */
    }

}

?>