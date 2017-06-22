<?php

/**
 * Description of PwAgentInvoice
 *
  Sugal &amp; Damani Utility Services Pvt. Ltd.
  <br>Reg Off: 6/35, W.E.A Karol bagh,
  <br>New Delhi 110005
  <br>Web site: www.payworldindia.com
  <br>Email: contact@payworldindia.com
  <br>PAN Card No :AAICS2274B
  <br>Service Tax No :AAICS2274BST001

 * @author vinay
 */
require("pdffolder/html2fpdf_a4.php");

class PwAgentInvoice extends HTML2FPDF {

    public function GeneratePDF($result, $headinfo) {
        $this->SetFont('Arial', '', 8);
        $this->SetFont('Arial', '', 7);
        $AgtRow = isset($result['AgtRow']) ? $result['AgtRow'] : array();
        unset($result['AgtRow']);
        foreach ($result as $saledate => $dayresult) {
            ksort($dayresult);
            foreach ($dayresult as $invoice => $row) {
                $this->AddPage();
                $html = '';
                list($invdate, $invtime) = explode(" ", $row['date']);
                $html .= '<table style="text-align: left;white-space: nowrap;" width="100%">
    <tbody>
        <tr>
            <td  style="text-align:left;vertical-align: top;" width="70%" ></td>
            <td style="text-align:left; font-size: 10px;" ><font color="#606" >' . $headinfo . '</font></td>

        <tr ><td colspan="2"  height=40 align="center" style="font-size:20px" ><font size=26 color: #15AFE1;><b>INVOICE SUMMARY/ DEBIT NOTE/ CREDIT NOTE</b></font></td>
    
       </tr>
        <tr>
            <td  width="70%"><b>Invoice No. :</b>' . strtoupper(date("Ymd", strtotime($saledate)) . "/" . $AgtRow['agentcode'] . "/" . str_pad($invoice, 3, '0', STR_PAD_LEFT)) . '</td>
            <td style="text-align: left;"><b>Invoice Date : </b>' . date("d-m-Y", strtotime($invdate)) . '</td>
        </tr>
        <tr >
            <td><b>' . $AgtRow['agentname'] . '</b></td>
            <td style="text-align: left;"><b>Invoice time  : </b>' . $invtime . '</td>
        </tr>
        <tr>
            <td colspan=2 > ' . $AgtRow['add1'] . '<br>' . $AgtRow['city'] . ',<br>' . $AgtRow['state'] . ' - ' . $AgtRow['zip'] . '</td>
        </tr>
        <tr>
            <td colspan=2 ><b>PAN Card No :</b>' . $AgtRow['pan_no'] . '</td>
        </tr>
        <tr color="red">
            <td colspan=2 height=40 style="color: #606;  font-size: 10px;  margin: 5px 0px;"  ><font color="#606" ><b>We are debiting your A/c on ' . date("d/m/Y", strtotime($invdate)) . '</b></font></td>
        </tr>
    </tbody>
</table>';
                $totalrow = array();
                $mainflag = true;
                $html .= '<table  width="100%" style="text-align:left;font-family: Calibri;font-style:normal;border-collapse: collapse;" border="2">';
                foreach ($row['data'] as $type => $Arrr) {
                    $header = $Arrr[0];
                    $subtotal = array();
                    if ($mainflag) {
                        $html .= '<tr  bgcolor="#66F">';
                    }
                    foreach ($header as $key => $val) {
                        $align = 'left';
                        if (!in_array($key, array('S.No.', 'CELL OPERATOR', 'Services'))) {
                            $align = 'right';
                            $subtotal[$key] = 0;
                            if ($mainflag && in_array($key, array('Net', 'MRP AMT(Rs.)', 'PUR AMT(Rs.)'))) {
                                $totalrow[$key] = 0;
                            }
                        }
                        if ($mainflag) {
                            $html .= '<th align="' . $align . '"><font color="white">' . $key . '</font></th>';
                        }
                    }
                    if ($mainflag) {
                        $html .= '</tr>';
                    }
                    $html .= '<tr  bgcolor="#15AFE1;"><th colspan=' . count($header) . ' align="left" style="text-align:left;background-color: rgb(21, 175, 225);"><font color="white">' . $type . '</font></th></tr>';
                    $mainflag = false;
                    foreach ($Arrr as $value) {
                        $html .= '<tr>';
                        foreach ($header as $key => $val) {
                            $align = 'left';
                            if (!in_array($key, array('S.No.', 'CELL OPERATOR', 'Services'))) {
                                $align = 'right';
                                $subtotal[$key] += $value[$key];
                            }
                            if (in_array($key, array('Net', 'MRP AMT(Rs.)', 'PUR AMT(Rs.)'))) {
                                $totalrow[$key] += $value[$key];
                            }
                            $html .= '<td align="' . $align . '">' . $value[$key] . '</td>';
                        }
                        $html .= '</tr>';
                    }
                    $html .= '</tr><tr bgcolor="#66F"><font color="white">';
                    foreach ($header as $key => $val) {
                        if ($key == "S.No.") {
                            $html .= '<th align="right">Total</th>';
                        } else {
                            $html .= '<th align="right">' . ((isset($subtotal[$key]) && $key !== "No. of Collections") ? sprintf("%.2f", $subtotal[$key]) : '' ) . '</th>';
                        }
                    }
                    $html .= '</font></tr>';
                }
                $html .= '<tr bgcolor="#66F"><font color="white">';
                foreach ($header as $key => $val) {
                    if ($key == "S.No.") {
                        $html .= '<th align="left" colspan="' . (count($header) - count($totalrow)) . '">Grand Total</th>';
                    } else if (isset($totalrow[$key])) {
                        $html .= '<th align="right">' . sprintf("%.2f", $totalrow[$key]) . '</th>';
                    }
                }
                $html .= '</font></tr></table>';
                $html .= '<p style="text-align: left;padding-left: 10px;">Amount In Words : <b>' . $this->Convert_AmountInWord(isset($totalrow['Net']) ? strtoupper($totalrow['Net']) : strtoupper($totalrow['PUR AMT(Rs.)'])) . '</b></p>';
                $html .= '<p style="color: red;text-align: left;padding-left: 10px;">* This is Computer generated invoice summary / Debit note / credit note does not require any Signature</p>';
                $this->Ln(0);
                $this->lineheight = 3.5;
                $this->WriteHTML($html);
                $this->Image('img/logo_blue.jpg', 5, 11, 80, 16, 'jpg', '', true);
            }
        }
    }

    public static function Convert_num2str($no_send) {
        $units[0] = "Zero";
        $units[1] = "";
        $units[2] = "Ten";
        $units[3] = "Hundred";
        $units[4] = "Thousand";
        $units[5] = "Thousand";
        $units[6] = "Lakh";
        $units[7] = "Lakh";
        $units[8] = "Crore";
        $units[9] = "Crore";
        $nos[0] = "Zero";
        $nos[1] = "One";
        $nos[2] = "Two";
        $nos[3] = "Three";
        $nos[4] = "Four";
        $nos[5] = "Five";
        $nos[6] = "Six";
        $nos[7] = "Seven";
        $nos[8] = "Eight";
        $nos[9] = "Nine";
        $nos[10] = "Ten";
        $nos[11] = "Eleven";
        $nos[12] = "Twelve";
        $nos[13] = "Thirteen";
        $nos[14] = "Fourteen";
        $nos[15] = "Fifteen";
        $nos[16] = "Sixteen";
        $nos[17] = "Seventeen";
        $nos[18] = "Eighteen";
        $nos[19] = "Ninteen";
        $nos[20] = "Twenty";
        $nos_sec[2] = "Twenty";
        $nos_sec[3] = "Thirty";
        $nos_sec[4] = "Forty";
        $nos_sec[5] = "Fifty";
        $nos_sec[6] = "Sixty";
        $nos_sec[7] = "Seventy";
        $nos_sec[8] = "Eighty";
        $nos_sec[9] = "Ninty";
        $no = $no_send;
        $no_of_digits = strlen($no);
        $tStr = "";
        for ($i = $no_of_digits; $i >= 1; $i--) {
            $X = substr($no, $no_of_digits - $i, 1);
            if ($X > 0) {
                if ($i < 3) {
                    $no1 = substr($no, $no_of_digits - $i);
                    if ($no1 > 20)
                        $tStr = $tStr . " " . $nos_sec[$X];
                    else {
                        if (!($i == 1 && $no1 == 0)) {
                            $tStr = $tStr . " " . $nos[$no1];
                            break;
                        }
                    }
                } else {
                    if ($i == 3)
                        $tStr = $tStr . " " . $nos[$X] . " " . $units[$i];
                    elseif ((($i - 4) % 2) == 0)
                        $tStr = $tStr . " " . $nos[$X] . " " . $units[$i];
                    else {
                        $no1 = substr($no, $no_of_digits - $i, 2);
                        if ($no1 > 20) {
                            if (($no1 % 10) == 0) {
                                $tStr = $tStr . " " . $nos_sec[$X] . " " . $units[$i];
                                $i = $i - 1;
                            } else {
                                $tStr = $tStr . " " . $nos_sec[$X];
                            }
                        } else {
                            $tStr = $tStr . " " . $nos[$no1] . " " . $units[$i];
                            $i = $i - 1;
                        }
                    }
                }
            }
        }
        return $tStr;
    }

    public function Convert_AmountInWord($amount) {
        $wordamt = "";
        $lstr = $amount;
        $rstr = "";
        $leng = strpos($amount, ".");
        if ($leng) {
            $lstr = substr($amount, 0, $leng);
            $rstr = str_pad(substr($amount, $leng + 1), 2, '0', STR_PAD_RIGHT);
        }
        $rs_str = " Rupees";
        if (strlen($lstr) == 1) {
            $rs_str = " Rupee";
        }
        if (($lstr == "" || intval($lstr) == 0) && ($rstr == "" || intval($rstr) == 0)) {
            $wordamt = "";
        } elseif ($rstr == "" || intval($rstr) == 0) {
            $wordamt = self::Convert_num2str($lstr) . $rs_str . " Only";
        } elseif ($lstr == "" || intval($lstr) == 0) {
            $wordamt = self::Convert_num2str($rstr) . " Paise Only";
        } else {
            $wordamt = self::Convert_num2str($lstr) . $rs_str . " And " . self::Convert_num2str($rstr) . " Paise Only";
        }
        return $wordamt;
    }

}
