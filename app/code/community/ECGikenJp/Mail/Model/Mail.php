<?php
/**
 * ECGiken_Price
 *
 * Copyright (c) 2011, EC-GIKEN Inc.
 * All rights reserved.
 */

class ECGikenJp_Mail_Model_Mail extends Zend_Mail {

    public function __construct($charset = 'iso-8859-1') {
        parent::__construct('iso-2022-jp');
        $this->_headerEncoding = Zend_Mime::ENCODING_BASE64;
    }

    private function decode_mimeheader($txt) {
        if (preg_match('/^=\?utf-8\?B\?/ui', $txt)) {
            return base64_decode(substr($txt, 10)); // 10: '=?utf-8?B?'
        }
        return $txt;
    }

    private $ms_utf8_conv_table = array(
        '―' => '—',
        '～' => '〜',
        '∥' => '‖',
        '－' => '−',
        '￠' => '¢',
        '￡' => '£',
        '￢' => '¬',
    );
    private function ja_JP_text($txt) {
        $txt = preg_replace('/\r/u', '', $txt); // for old qmail server
        foreach ($this->ms_utf8_conv_table as $ms => $ibm) {
            $txt = preg_replace('/' . $ms . '/', $ibm, $txt);
        }
        $txt = iconv('utf-8', 'iso-2022-jp-3', $txt); // for hankaku kana etc.
        $txt = preg_replace('/\033\$\(O/', "\033\$B", $txt); // for maru number etc.
        return $txt;
    }

    public function setBodyText($txt, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE) {
        parent::setBodyText($this->ja_JP_text($txt), 'iso-2022-jp', Zend_Mime::ENCODING_7BIT);
    }

    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE) {
        $body = preg_replace('/utf-8/ui', 'iso-2022-jp', $html);
        parent::setBodyHtml($this->ja_JP_text($body), 'iso-2022-jp', Zend_Mime::ENCODING_7BIT);
    }

    protected function _encodeHeader($value) {
        if (Zend_Mime::isPrintable($value) === false || preg_match('/^iso-2022-jp/ui', $this->getCharset())) {
            if ($this->getHeaderEncoding() === Zend_Mime::ENCODING_QUOTEDPRINTABLE) {
                $value = Zend_Mime::encodeQuotedPrintableHeader($value, $this->getCharset(), Zend_Mime::LINELENGTH, '');
            } else {
                // this code is not correct, but works fine.
                $value = "=?ISO-2022-JP?B?" . base64_encode($this->ja_JP_text($this->decode_mimeheader($value))) . "?=";
            }
        }

        return $value;
    }

    protected function _filterName($name) {
        $rule = array(
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => "'"
        );

        $name = trim(strtr($name, $rule));
        $return_value = '';
        $mode = 0;
        foreach (str_split($name) as $c) {
            if ($mode == 0) {
                if ($c == chr(0x1b)) {
                    $mode = 1;
                } else {
                    $mode = 0;
                }
            } else if ($mode == 1) {
                if ($c == '$') {
                    $mode = 2;
                } else {
                    $mode = 0;
                }
            } else if ($mode == 2) {
                if ($c == 'B') {
                    $mode = 3;
                } else {
                    $mode = 0;
                }
            } else if ($mode == 3) {
                if ($c == chr(0x1b)) {
                    $mode = 4;
                } else {
                    $mode = 3;
                }
            } else if ($mode == 4) {
                if ($c == '(') {
                    $mode = 5;
                } else {
                    $mode = 3;
                }
            } else if ($mode == 5) {
                if ($c == 'B') {
                    $mode = 0;
                } else {
                    $mode = 3;
                }
            }

            if ($mode != 3) {
                if ($c == '<') {
                    $c = '[';
                } else if ($c == '>') {
                    $c = ']';
                }
            }
            $return_value .= $c;
        }
        return $return_value;
    }
}
