<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Fooman_PdfCustomiser_Helper_Pdf extends Mage_Core_Helper_Abstract
{

    abstract public function getNumberText();

    abstract public function getPdfColumns();

    /**
     * initalise the helper with a storeId
     *
     * @param int $storeId
     */
    public function __construct($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $this->setStoreId($storeId);
    }

    /**
     * keep track if we have adjusted the pdf total sort order
     * @var bool
     */
    public $hasAdjustedTotalsSort = false;

    /**
     * storeId
     * @access protected
     */
    protected $_storeId;


    /**
     * get storeId
     * @return  int
     * @access public
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * set storeId
     *
     * @param $id int
     *
     * @return  void
     * @access public
     */
    public function setStoreId($id)
    {
        $this->_storeId = $id;
    }

    /**
     *
     * @var Mage_Sales_Model_Abstract
     */
    protected $_salesObject;

    /**
     * retrieve current sales object for which pdf
     * is being generated
     *
     * @return Mage_Sales_Model_Abstract
     */
    public function getSalesObject()
    {
        return $this->_salesObject;
    }

    /**
     * set current sales object for which pdf
     * is being generated
     *
     * @param Mage_Sales_Model_Abstract $salesObject
     */
    public function setSalesObject (Mage_Sales_Model_Abstract $salesObject)
    {
        $this->_salesObject = $salesObject;
    }

    /**
     * retrieve order object associated with current
     * sales object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $salesObject = $this->getSalesObject();
        if ($salesObject instanceof Mage_Sales_Model_Order) {
            return $salesObject;
        } else {
            return $salesObject->getOrder();
        }
    }

    /**
     * parameters
     * @access protected
     */
    protected $_parameters = array();

    /**
     * set array of parameters manually - can be used to override settings from DB
     *
     * @param array $parameters
     *
     * @return  void
     * @access public
     */
    public function setParameters(array $parameters = array())
    {
        $this->_parameters = $parameters;
    }

    /**
     * set a single parameter manually
     *
     * @param int    $storeId
     * @param string $parameterName
     * @param mixed  $parameterValue
     */
    public function setParameter($storeId, $parameterName, $parameterValue)
    {
        $this->_parameters[$storeId][$parameterName] = $parameterValue;
    }

    /**
     * store owner address
     *
     * @return  string | bool
     * @access public
     */
    public function getPdfOwnerAddresss()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allowneraddress'])) {
            $this->_parameters[$this->getStoreId()]['allowneraddress'] =
                Mage::getStoreConfig('sales_pdf/all/allowneraddress', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allowneraddress'];
    }

    /**
     * get store flag to display base and order currency
     * @return  bool
     * @access public
     */
    public function getDisplayBoth()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['displayboth'])) {
            $this->_parameters[$this->getStoreId()]['displayboth'] =
                Mage::getStoreConfig('sales_pdf/all/displayboth', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['displayboth'] && $this->getOrder()->isCurrencyDifferent();
    }

    /**
     * font for pdf - courier, times, helvetica
     * not embedded
     * @return  string
     * @access public
     */
    public function getPdfFont()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allfont'])) {
            $this->_parameters[$this->getStoreId()]['allfont'] =
                Mage::getStoreConfig('sales_pdf/all/allfont', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allfont'];
    }

    /**
     * @param string $size (otpional) normal | large | small
     * @return float
     * @access public
     */
    public function getPdfFontsize($size = 'normal')
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allfontsize'])) {
            $this->_parameters[$this->getStoreId()]['allfontsize'] =
                Mage::getStoreConfig('sales_pdf/all/allfontsize', $this->getStoreId());
        }
        $fontSize = $this->_parameters[$this->getStoreId()]['allfontsize'];
        switch ($size) {
            case 'normal':
                return $fontSize;
                break;
            case 'large':
                return $fontSize * 1.33;
                break;
            case 'small':
                return $fontSize * ($fontSize < 12 ? 1 : 0.8);
                break;
            default:
                return $fontSize;
        }
    }

    /**
     * font for pdf - courier, times, helvetica
     * not embedded
     * @return  string
     * @access public
     */
    public function getPdfQtyAsInt()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allqtyasint'])) {
            $this->_parameters[$this->getStoreId()]['allqtyasint'] =
                Mage::getStoreConfig('sales_pdf/all/allqtyasint', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allqtyasint'];
    }

    public function getNewWindow()
    {
        if (!isset($this->_parameters['allnewwindow'])) {
            $this->_parameters['allnewwindow'] = (bool)Mage::getStoreConfig('sales_pdf/all/allnewwindow') ? 'D' : 'I';
        }
        return $this->_parameters['allnewwindow'];
    }

    /**
     * get path for print logo
     * @return string path information for logo
     * @access public
     */
    public function getPdfLogo()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['alllogo'])) {
            if (Mage::getStoreConfig('sales_pdf/all/alllogo', $this->getStoreId())) {
                $pdfLogo = Mage::getBaseDir('media') . DS . 'pdf-printouts' . DS . Mage::getStoreConfig(
                    'sales_pdf/all/alllogo',
                    $this->getStoreId()
                );
            } else {
                $pdfLogo = false;
            }
            $this->_parameters[$this->getStoreId()]['alllogo'] = $pdfLogo;
        }
        return $this->_parameters[$this->getStoreId()]['alllogo'];
    }

    /**
     * get logo placement auto / manual
     * @return string
     * @access public
     */
    public function getPdfLogoPlacement()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['alllogoplacement'])) {
            $this->_parameters[$this->getStoreId()]['alllogoplacement'] =
                Mage::getStoreConfig('sales_pdf/all/alllogoplacement', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['alllogoplacement'];
    }

    /**
     * get logo placement coordinates
     * @return array
     * @access public
     */
    public function getPdfLogoCoords()
    {

        if (!isset($this->_parameters[$this->getStoreId()]['alllogocoords'])) {
            $returnArray = array();
            $returnArray['w'] = Mage::getStoreConfig('sales_pdf/all/alllogoheight', $this->getStoreId());
            $returnArray['h'] = Mage::getStoreConfig('sales_pdf/all/alllogoheight', $this->getStoreId());
            $returnArray['x'] = Mage::getStoreConfig('sales_pdf/all/alllogofromleft', $this->getStoreId());
            $returnArray['y'] = Mage::getStoreConfig('sales_pdf/all/alllogofromtop', $this->getStoreId());
            $this->_parameters[$this->getStoreId()]['alllogocoords'] = $returnArray;
        }
        return $this->_parameters[$this->getStoreId()]['alllogocoords'];
    }

    /**
     * get path for print background
     * @return string path information for logo
     * @access public
     */
    public function getPdfBgImage()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allbgimage'])) {
            if (Mage::getStoreConfig('sales_pdf/all/allbgimage', $this->getStoreId())) {
                $bgImage = Mage::getBaseDir('media') . DS . 'pdf-printouts' . DS . Mage::getStoreConfig(
                    'sales_pdf/all/allbgimage',
                    $this->getStoreId()
                );
            } else {
                $bgImage = false;
            }
            $this->_parameters[$this->getStoreId()]['allbgimage'] = $bgImage;
        }
        return $this->_parameters[$this->getStoreId()]['allbgimage'];
    }

    /**
     * get path for print logo
     * @return string path information for logo
     * @access public
     */
    public function getPdfBgOnlyFirst()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allbgimagefirstpageonly'])) {
            $this->_parameters[$this->getStoreId()]['allbgimagefirstpageonly'] =
                Mage::getStoreConfig('sales_pdf/all/allbgimagefirstpageonly', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allbgimagefirstpageonly'];
    }

    /**
     * get Logo Dimensions
     * @param string $which (optional) identify the dimension to return  all | w | h
     * @return bool|float|array
     * @access public
     */
    public function getPdfLogoDimensions($which = 'all')
    {
        if (!$this->getPdfLogo()) {
            return false;
        }

        if (!isset($this->_parameters[$this->getStoreId()]['logodimensions'])) {
            list($width, $height, $type, $attr) = getimagesize($this->getPdfLogo());
            $this->_parameters[$this->getStoreId()]['logodimensions']['width'] =
                $width / Fooman_PdfCustomiser_Model_Mypdf::FACTOR_PIXEL_PER_MM;
            $this->_parameters[$this->getStoreId()]['logodimensions']['height'] =
                $height / Fooman_PdfCustomiser_Model_Mypdf::FACTOR_PIXEL_PER_MM;
        }

        switch ($which) {
            case 'w':
                return $this->_parameters[$this->getStoreId()]['logodimensions']['width'];
                break;
            case 'h-scaled':
                //calculate if image will be scaled apply factor to height
                $maxWidth = ($this->getPageWidth() / 2) - $this->getPdfMargins('sides');
                if ($this->getPdfLogoDimensions('w') > $maxWidth) {
                    $scaleFactor = $maxWidth / $this->getPdfLogoDimensions('w');
                } else {
                    $scaleFactor = 1;
                }
                return $scaleFactor * $this->_parameters[$this->getStoreId()]['logodimensions']['height'];
                break;
            case 'h':
                return $this->_parameters[$this->getStoreId()]['logodimensions']['height'];
                break;
            case 'all':
            default:
                return $this->_parameters[$this->getStoreId()]['logodimensions'];
        }
    }

    /**
     * get Margins
     *
     * @param string $which (optional) identify the dimension to return  all | top | bottom | sides
     *
     * @return mixed
     * @access public
     */
    public function getPdfMargins($which = 'all')
    {
        if (!isset($this->_parameters[$this->getStoreId()]['pdfmargins'])) {
            $this->_parameters[$this->getStoreId()]['pdfmargins']['top'] =
                Mage::getStoreConfig('sales_pdf/all/allmargintop', $this->getStoreId());
            $this->_parameters[$this->getStoreId()]['pdfmargins']['bottom'] =
                Mage::getStoreConfig('sales_pdf/all/allmarginbottom', $this->getStoreId());
            $this->_parameters[$this->getStoreId()]['pdfmargins']['sides'] =
                Mage::getStoreConfig('sales_pdf/all/allmarginsides', $this->getStoreId());
        }

        switch ($which) {
            case 'top':
                return $this->_parameters[$this->getStoreId()]['pdfmargins']['top'];
                break;
            case 'bottom':
                return $this->_parameters[$this->getStoreId()]['pdfmargins']['bottom'];
                break;
            case 'sides':
                return $this->_parameters[$this->getStoreId()]['pdfmargins']['sides'];
                break;
            case 'all':
            default:
                return $this->_parameters[$this->getStoreId()]['pdfmargins'];
        }
    }

    /**
     * return page width in mm
     *
     * @param  void
     *
     * @return float
     * @access public
     */
    public function getPageWidth()
    {

        if (!isset($this->_parameters[$this->getStoreId()]['allpagesize'])) {
            $this->_parameters[$this->getStoreId()]['allpagesize'] =
                Mage::getStoreConfig('sales_pdf/all/allpagesize', $this->getStoreId());
        }

        $pageSize = $this->_parameters[$this->getStoreId()]['allpagesize'];

        switch ($pageSize) {
            case 'A4':
                return 21.000155556 * 10;
                break;
            case 'letter':
                return 21.59 * 10;
                break;
            default:
                return 21.000155556 * 10;
        }
    }

    /**
     * return if we want to print comments and statusses
     *
     * @param  void
     *
     * @return bool
     * @access public
     */
    public function getPrintComments()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allprintcomments'])) {
            $this->_parameters[$this->getStoreId()]['allprintcomments'] =
                Mage::getStoreConfig('sales_pdf/all/allprintcomments', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allprintcomments'];
    }

    /**
     * return data for all blocks set for the footers
     *
     * @return array    array[0] contains how many blocks we need to set up
     * @access public
     */
    public function getFooters()
    {

        if (!isset($this->_parameters[$this->getStoreId()]['footers'])) {
            $this->_parameters[$this->getStoreId()]['footers'][0] = 0;
            for ($i = 1; $i < 5; $i++) {
                $this->_parameters[$this->getStoreId()]['footers'][$i] =
                    nl2br(Mage::getStoreConfig('sales_pdf/all/allfooter' . $i, $this->getStoreId()));
                if (!empty($this->_parameters[$this->getStoreId()]['footers'][$i])) {
                    $this->_parameters[$this->getStoreId()]['footers'][0] = $i;
                }
            }
        }
        return $this->_parameters[$this->getStoreId()]['footers'];
    }

    /**
     * return data for all blocks set for the footers
     *
     * @return bool
     * @access public
     */
    public function hasFooter()
    {
        $footers = $this->getFooters();
        return (bool)$footers[0];
    }


    /**
     * return if weight should be displayed as part of the shipping information
     *
     * @return bool
     * @access public
     */
    public function displayWeight()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['alldisplayweight'])) {
            $this->_parameters[$this->getStoreId()]['alldisplayweight'] =
                Mage::getStoreConfig('sales_pdf/all/alldisplayweight', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['alldisplayweight'];
    }

    /**
     * return flag if detailed tax breakdown should be displayed
     *
     * @return bool
     * @access public
     */
    public function displayTaxSummary()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['alltaxsummary'])) {
            $this->_parameters[$this->getStoreId()]['alltaxsummary'] =
                Mage::getStoreConfig('sales_pdf/all/alltaxsummary', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['alltaxsummary'];
    }

    /**
     * should we display the gift message? default false
     * @return bool
     * @access public
     */
    public function displayGiftMessage()
    {
        return false;
    }

    /**
     * should we display totals? default true
     *
     * @return bool
     * @access public
     */
    public function displayTotals()
    {
        return true;
    }

    /**
     * print product images?
     * @return  bool
     * @access public
     */
    public function printProductImages()
    {
        return strpos($this->getPdfColumns(), 'image') !== false;
    }

    /**
     * should we display the order id? default false
     * @return bool
     * @access public
     */
    public function getPutOrderId()
    {
        return false;
    }

    /**
     * setter to update the image height used in @see Fooman_PdfCustomiser_Model_Mypdf::Header()
     * @param $imageHeight
     * @return Fooman_PdfCustomiser_Helper_Pdf
     * @access public
     */
    public function setImageHeight ($imageHeight)
    {
        $this->_parameters[$this->getStoreId()]['allimageheight'] = $imageHeight;
        return $this;
    }

    /**
     * retrieve image height of the last added logo @see Fooman_PdfCustomiser_Model_Mypdf::Header()
     * @return float
     * @access public
     */
    public function getImageHeight()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allimageheight'])) {
            $this->_parameters[$this->getStoreId()]['allimageheight'] = false;
        }
        return $this->_parameters[$this->getStoreId()]['allimageheight'];
    }

    /**
     * return the formatted date of the current sales object, store time
     * @return string
     * @access public
     */
    public function getDate ()
    {
        return Mage::helper('core')->formatDate($this->getSalesObject()->getCreatedAtStoreDate(), 'medium', false);
    }

    /**
     * return the formatted date of the current order, store time
     * @return string
     * @access public
     */
    public function getOrderDate ()
    {
        return Mage::helper('core')->formatDate($this->getOrder()->getCreatedAtStoreDate(), 'medium', false);
    }

    /**
     * return additional content to be added in the top section
     * @return bool
     * @access public
     */
    public function getTopAdditional()
    {
        return false;
    }

    /**
     * define defaults for column width and order
     * @return array
     * @access public
     */
    public function getDefaultColumnOrderAndWidth()
    {
        return json_decode(
            '{
            "position":8,
            "name":20,
            "name-space":25,
            "name-sku":25,
            "sku":18,
            "image":18,
            "custom":20,
            "custom2":20,
            "custom3":20,
            "custom4":20,
            "custom5":20,
            "barcode":18,
            "price":12,
            "qty_ordered":8,
            "qty":8,
            "tax":12,
            "taxrate":8,
            "rowtotal2":12,
            "discount":12,
            "subtotal":12
            }',
            true
        );
    }

    /**
     * return what column values should be used to reorder items
     *
     * @return bool
     * @access public
     */
    public function getColumnsSortOrder()
    {
        return false;
    }

    /**
     * construct columns based on default widths and user column choices
     * @return array|bool
     * @access public
     */
    public function getPdfColumnHeaders()
    {
        $columnsToPrint = explode(',', $this->getPdfColumns());
        $columnWidths = $this->getColumnOrderAndWidth();

        $columnTitles = array(
            'position' => htmlentities(Mage::helper('pdfcustomiser')->__('Pos'), ENT_QUOTES, 'UTF-8', false),
            'name' => htmlentities(Mage::helper('sales')->__('Product'), ENT_QUOTES, 'UTF-8', false),
            'name-sku' => htmlentities(Mage::helper('sales')->__('Product'), ENT_QUOTES, 'UTF-8', false),
            'sku' => htmlentities(Mage::helper('sales')->__('SKU'), ENT_QUOTES, 'UTF-8', false),
            'image' => '',
            'custom' => '', 'custom2' => '', 'custom3' => '', 'custom4' => '', 'custom5' => '',
            'price' => htmlentities(Mage::helper('sales')->__('Price'), ENT_QUOTES, 'UTF-8', false),
            'discount' => htmlentities(Mage::helper('sales')->__('Discount'), ENT_QUOTES, 'UTF-8', false),
            'qty' => htmlentities(Mage::helper('sales')->__('Qty'), ENT_QUOTES, 'UTF-8', false),
            'qty_ordered' => htmlentities(Mage::helper('sales')->__('Qty Ordered'), ENT_QUOTES, 'UTF-8', false),
            'tax' => htmlentities($this->getTranslatedString('Tax', 'sales'), ENT_QUOTES, 'UTF-8', false),
            'taxrate' => htmlentities(Mage::helper('tax')->__('Tax Rate'), ENT_QUOTES, 'UTF-8', false),
            'subtotal' => htmlentities(Mage::helper('sales')->__('Subtotal'), ENT_QUOTES, 'UTF-8', false),
            'rowtotal2' => htmlentities(Mage::helper('sales')->__('Row Total'), ENT_QUOTES, 'UTF-8', false),
            'barcode' => ''
        );

        $i=1;
        $attributeCodes = $this->getCustomColumnAttributes();
        foreach ($attributeCodes as $attributeCode) {
            $customName = $i == 1 ? 'custom' : 'custom' . $i;
            if ($attributeCode) {
                $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
                //$attribute->initLabels($this->getStoreId());
                if ($attribute->getStoreLabel()) {
                    $columnTitles[$customName] = htmlentities(
                        $attribute->getStoreLabel(), ENT_QUOTES, 'UTF-8', false
                    );
                } else {
                    $storeLabels = $attribute->getStoreLabels();
                    if (isset($storeLabels[$this->getStoreId()])) {
                        $columnTitles[$customName] = htmlentities(
                            $storeLabels[$this->getStoreId()], ENT_QUOTES, 'UTF-8', false
                        );
                    } else {
                        $columnTitles[$customName] = '';
                    }
                }
            }
            $i++;
        }

        $totalWidth = 0;
        foreach ($columnsToPrint as $columnToPrint) {
            if (isset($columnWidths[$columnToPrint])) {
                $totalWidth += $columnWidths[$columnToPrint];
            } else {
                //apply a default in case someone previously created a Columns Advanced override
                $totalWidth += 8;
            }
        }
        if ($totalWidth > 0) {
            $widthFactor = 100 / $totalWidth;
        } else {
            $widthFactor = 1;
        }

        $columnHeadings = array();
        $i = -1;
        if ($columnWidths) {
            foreach ($columnWidths as $key => $standardWidth) {
                if (in_array($key, $columnsToPrint)) {
                    $columnHeadings[] = array(
                        'width' => $standardWidth * $widthFactor,
                        'title' => $columnTitles[$key],
                        'key' => $key,
                        'align' => 'center',
                        'style_first' => 'border-top:1px solid black;',
                        'style_last' => 'border-bottom:1px solid black;'
                    );
                    $i++;
                }
            }
            if ($this->isRtl()) {
                $columnHeadings[0]['align'] = 'right';
                $columnHeadings[$i]['align'] = 'left';
            } else {
                $columnHeadings[0]['align'] = 'left';
                $columnHeadings[$i]['align'] = 'right';
            }
            //no columns
        } else {
            $columnHeadings = false;
        }
        return $columnHeadings;
    }

    /**
     * render the html for 1 sales object item line <tr>$trInner</tr>
     *
     * @param      $pdfItem
     * @param      $vertSpacing
     * @param bool $styleOverride
     * @param int  $position
     *
     * @return string html
     * @access public
     */
    public function getPdfItemRow($pdfItem, $vertSpacing, $styleOverride = false, $position = 1)
    {

        $trInner = '';
        $columns = $this->getPdfColumnHeaders();
        $maxColumns = sizeof($columns);
        if ($columns) {
            foreach ($columns as $column) {
                $isNotLast = (
                    $pdfItem['productDetails']['Options']
                        || $pdfItem['giftMessage']['message']
                        || $pdfItem['productDetails']['Subitems']
                );
                $style = $isNotLast
                    ? $column['style_first']
                    : $column['style_first'] . $column['style_last'];
                $horizSpace = 0;
                if ($styleOverride) {
                    $style = $isNotLast
                        ? $styleOverride['style_first']
                        : $styleOverride['style_first'] . $styleOverride['style_last'];
                    if (isset($styleOverride['horiz_whitespace'])) {
                        $horizSpace = $styleOverride['horiz_whitespace'];
                    }
                }
                switch ($column['key']) {
                    case 'position':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $position.'.' . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'name':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $pdfItem['productDetails']['Name']
                            . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'name-space':
                        $cellContent = $this->serializeParams(
                            array(
                                0, 0,
                                $pdfItem['productDetails']['Name'],
                                array(
                                    'mode' => 'int',
                                    'L'    => array('width' => $horizSpace, 'color' => array(255, 255, 255)),
                                    'R'    => array('width' => $horizSpace, 'color' => array(255, 255, 255))
                                ),
                                null, null, null, null, null, null, null, true
                            )
                        );
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . '<tcpdf method="MultiCell" params="'.$cellContent.'"/>'
                            . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'name-sku':
                        $trInner .= '<td style="' . $style . '" width="' . $column['width'] . '%">'
                            . $vertSpacing . $pdfItem['productDetails']['Name'] .  '<br/>' .
                            htmlentities(Mage::helper('sales')->__('SKU'), ENT_QUOTES, 'UTF-8', false) . ': '
                            . $pdfItem['productDetails']['Sku'] .($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'sku':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $pdfItem['productDetails']['Sku'] . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'custom':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $pdfItem['productDetails']['custom'] . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'custom2':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $pdfItem['productDetails']['custom2'] . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'custom3':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing .
                            $pdfItem['productDetails']['custom3'] . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'custom4':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            .$pdfItem['productDetails']['custom4'] . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'custom5':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $pdfItem['productDetails']['custom5']. ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'image':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . ($pdfItem['image']?'<img src="' . $pdfItem['image'] . '" height="180"/>':'')
                            . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'barcode':
                        $barcodeParams = $this->serializeParams(
                            array(
                                $pdfItem['productDetails']['Sku'],
                                $this->getBarcodeType(),
                                '', '', '35', '13')
                        );
                        $trInner .= '<td valign="top" style="' . $style . '" width="'
                            . $column['width'] . '%"><tcpdf method="write1DBarcode" params="' . $barcodeParams . '"/>'
                            . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'qty_ordered':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $pdfItem['qty_ordered'] . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'qty':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $pdfItem['qty'] . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'price':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $this->OutputPrice(
                                $pdfItem['price'],
                                $pdfItem['basePrice'],
                                $this->getDisplayBoth(),
                                $this->getSalesObject()
                            ) . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'discount':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $this->OutputPrice(
                                $pdfItem['discountAmount'],
                                $pdfItem['baseDiscountAmount'],
                                $this->getDisplayBoth(),
                                $this->getSalesObject()
                            ) . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'tax':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $this->OutputPrice(
                                $pdfItem['taxAmount'],
                                $pdfItem['baseTaxAmount'],
                                $this->getDisplayBoth(),
                                $this->getSalesObject()
                            ) . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'taxrate':
                        $trInner .= '<td style="' . $style . '" align="' . $column['align']
                            . '" width="' . $column['width'] . '%">' . $vertSpacing
                            . $pdfItem['taxPercent'] . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'subtotal':
                        $trInner .= '<td style="' . $style . '" align="'
                            . $column['align'] . '" width="' . $column['width'] . '%">' . $vertSpacing .
                            $this->OutputPrice(
                                $pdfItem['rowTotal'],
                                $pdfItem['baseRowTotal'],
                                $this->getDisplayBoth(),
                                $this->getSalesObject()
                            ) . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                    case 'rowtotal2':
                        $trInner .= '<td style="' . $style . '" align="'
                            . $column['align'] . '" width="' . $column['width'] . '%">' . $vertSpacing .
                            $this->OutputPrice(
                                $pdfItem['rowTotal2'],
                                $pdfItem['baseRowTotal2'],
                                $this->getDisplayBoth(),
                                $this->getSalesObject()
                            ) . ($isNotLast ? '' : $vertSpacing) . '</td>';
                        break;
                }
            }
            if (
                $pdfItem['productDetails']['Options']
                || $pdfItem['giftMessage']['message']
                || $pdfItem['productDetails']['Subitems']
            ) {
                $trInner .= '</tr>
                <tr nobr="true">
                <td style="' . ($styleOverride ? $styleOverride['style_last'] : $column['style_last'])
                    . '" colspan="' . $maxColumns . '" width="100%">'
                    . $pdfItem['productDetails']['Options'] . $pdfItem['productDetails']['Subitems']
                    . $this->OutputGiftMessageItem($pdfItem['giftMessage']) . $vertSpacing . '
                </td>';
            }
        }
        $transport = new Varien_Object();
        $transport->setHtml($trInner);
        Mage::dispatchEvent(
            'fooman_pdfcustomiser_pdf_item_row',
            array(
                'item'      => $pdfItem,
                'transport' => $transport
            )
        );
        return $transport->getHtml();
    }

    /**
     * render the html for 1 bundled sales object item line <tr>$trInner</tr>
     *
     * @param      $pdfItem
     * @param      $subItems
     * @param      $vertSpacing
     * @param bool $styleOverride
     * @param int  $position
     *
     * @return string
     * @access public
     */
    public function getPdfBundleItemRow($pdfItem, $subItems, $vertSpacing, $styleOverride = false, $position = 1)
    {
        $trInner = '';
        $columns = $this->getPdfColumnHeaders();
        $maxColumns = sizeof($columns);
        if ($columns) {
            //check if the subitems of the bundle have separate prices
            $subItemsSum = 0;
            foreach ($subItems as $bundleItem) {
                $subItemsSum += $bundleItem['price'];
            }
            //don't display bundle price if subitems have prices
            if ($subItemsSum > 0) {
                $nameColumnWidth = 18;
                $colcounter=0;
                foreach ($columns as $column) {
                    if ($column['key'] == 'name' || $column['key'] == 'name-sku') {
                        $trInner .= '<td style="'
                            . ($styleOverride ? $styleOverride['style_first'] : $column['style_first'])
                            . '" width="' . $column['width'] . '%">'
                            . $vertSpacing . $pdfItem['productDetails']['Name']
                            .($column['key'] == 'name-sku'?'<br/>'.$pdfItem['productDetails']['Sku']:'').'</td>';
                        $nameColumnWidth += $column['width'];
                        $colcounter++;
                    } elseif ($column['key'] == 'sku') {
                        $trInner .= '<td style="'
                            . ($styleOverride ? $styleOverride['style_first'] : $column['style_first'])
                            . '" width="' . (100 - $nameColumnWidth) . '%">'
                            . $vertSpacing . $pdfItem['productDetails']['Sku'] . '</td>';
                        $nameColumnWidth = 100;
                        $colcounter++;
                    }
                }
                if ($colcounter < $maxColumns) {
                    $trInner
                        .= '<td style="' . ($styleOverride ? $styleOverride['style_first'] : $column['style_first'])
                        . '" colspan="' . ($maxColumns - 1) . '" width="' . (100 - $nameColumnWidth) . '%"></td>';
                }
                if ($pdfItem['productDetails']['Options']) {
                    $trInner .= '</tr>';
                    $trInner .= '<tr nobr="true">';
                    $trInner .= '<td colspan="' . $maxColumns . '" width="100%">'
                        . $pdfItem['productDetails']['Options'] . '</td>';
                }

                //Display subitems
                $nrSubItems = sizeof($subItems);
                $i = 1;
                foreach ($subItems as $bundleItem) {
                    $isLast = $i == $nrSubItems;
                    $style = ($bundleItem['productDetails']['Options'])
                        ? ''
                        : ($isLast ? $column['style_last'] : '');
                    if ($styleOverride) {
                        $style = ($bundleItem['productDetails']['Options'])
                            ? ''
                            : ($isLast ? $styleOverride['style_last'] : '');
                    }
                    $trInner .= '</tr><tr nobr="true">';
                    foreach ($columns as $column) {
                        switch ($column['key']) {
                            case 'position':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $vertSpacing . $position.'.' . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'name':
                            case 'name-space':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">&nbsp;&nbsp;&nbsp;&nbsp;'
                                    . $bundleItem['productDetails']['Name'] . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'name-sku':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">&nbsp;&nbsp;&nbsp;&nbsp;'
                                    . $bundleItem['productDetails']['Name'].'<br/>&nbsp;&nbsp;&nbsp;&nbsp;'
                                    .$bundleItem['productDetails']['Sku'] . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'sku':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $bundleItem['productDetails']['Sku'] . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'custom':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $bundleItem['productDetails']['custom'] .($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'custom2':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $bundleItem['productDetails']['custom2'] .($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'custom3':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $bundleItem['productDetails']['custom3'] .($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'custom4':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $bundleItem['productDetails']['custom4'] .($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'custom5':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $bundleItem['productDetails']['custom5'] .($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'image':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    .($bundleItem['image']?'<img src="' . $bundleItem['image'] . '" height="180"/>':'')
                                    . ($isLast ? '' : $vertSpacing) . '</td>';
                                break;
                            case 'barcode':
                                $barcodeParams = $this->serializeParams(
                                    array(
                                        $bundleItem['productDetails']['Sku'],
                                        $this->getBarcodeType(),
                                        '', '', '35', '8'
                                    )
                                );
                                
                                $trInner .= '<td valign="top" style="' . $style . '" width="200px"><tcpdf method="write1DBarcode2" params="'
                                    . $barcodeParams . '"/>'
                                    . ($isLast ? $vertSpacing : '') . '</td>';
/*
                                $trInner .= '<td valign="top" style="' . $style . '" width="'
                                    . $column['width'] . '%"><tcpdf method="write1DBarcode2" params="'
                                    . $barcodeParams . '"/>'
                                    . ($isLast ? $vertSpacing : '') . '</td>';
*/
                                break;
                            case 'qty_ordered':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $bundleItem['qty_ordered'] . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'qty':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $bundleItem['qty'] . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'price':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $this->OutputPrice(
                                        $bundleItem['price'],
                                        $bundleItem['basePrice'],
                                        $this->getDisplayBoth(),
                                        $this->getSalesObject()
                                    ). ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'discount':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $this->OutputPrice(
                                        $bundleItem['discountAmount'],
                                        $bundleItem['baseDiscountAmount'],
                                        $this->getDisplayBoth(),
                                        $this->getSalesObject()
                                    ) . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'tax':
                                $trInner .= '<td style="' . $style . '" align="' .
                                    $column['align'] . '" width="' . $column['width'] . '%">' .
                                    $this->OutputPrice(
                                        $bundleItem['taxAmount'],
                                        $bundleItem['baseTaxAmount'],
                                        $this->getDisplayBoth(),
                                        $this->getSalesObject()
                                    ) . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'taxrate':
                                $trInner .= '<td style="' . $style . '" align="'
                                    . $column['align'] . '" width="' . $column['width'] . '%">'
                                    . $bundleItem['taxPercent'] . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'subtotal':
                                $trInner .= '<td style="' . $style . '" align="' .
                                    $column['align'] . '" width="' . $column['width'] . '%">' .
                                    $this->OutputPrice(
                                        $bundleItem['rowTotal'],
                                        $bundleItem['baseRowTotal'],
                                        $this->getDisplayBoth(),
                                        $this->getSalesObject()
                                    ) . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                            case 'rowtotal2':
                                $trInner .= '<td style="' . $style . '" align="' .
                                    $column['align'] . '" width="' . $column['width'] . '%">' .
                                    $this->OutputPrice(
                                        $bundleItem['rowTotal2'],
                                        $bundleItem['baseRowTotal2'],
                                        $this->getDisplayBoth(),
                                        $this->getSalesObject()
                                    ) . ($isLast ? $vertSpacing : '') . '</td>';
                                break;
                        }
                    }
                    if ($bundleItem['productDetails']['Options']) {
                        $trInner .= '</tr>';
                        $trInner .= '<tr nobr="true">';
                        $trInner .= '<td style="'
                            . ($styleOverride ? $styleOverride['style_last'] : $column['style_last'])
                            . '" colspan="' . $maxColumns . '" width="100%">'
                            . $bundleItem['productDetails']['Options']
                            . ($isLast ? $vertSpacing : '') . '</td>';
                    }
                    $i++;
                }
            } else {
                $pdfItem['productDetails']['Subitems'] = '';
                foreach ($subItems as $bundleItem) {
                    $pdfItem['productDetails']['Subitems'] .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;"
                        . $bundleItem['qty'] . " x " . $bundleItem['productDetails']['Name'];
                }
                return $this->getPdfItemRow($pdfItem, $vertSpacing);
            }
        }
        $transport = new Varien_Object();
        $transport->setHtml($trInner);
        Mage::dispatchEvent(
            'fooman_pdfcustomiser_pdf_item_row_bundle',
            array(
                'item'      => $pdfItem,
                'subitems'  => $subItems,
                'transport' => $transport
            )
        );
        return $transport->getHtml();
    }

    /**
     * get price output for items
     *
     * @param $price
     * @param $basePrice
     * @param bool    $displayBoth
     *
     * @internal param \Mage_Sales_Model_Order $salesObject
     * @return string html
     * @access   public
     */
    public function OutputPrice($price, $basePrice, $displayBoth)
    {
        $order = $this->getOrder();
        if ($this->isRtl()) {
            $price = sprintf("%F", $price);
            $basePrice = sprintf("%F", $basePrice);
            $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            $basePrice = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                ->toCurrency($basePrice, array('position' => Zend_Currency::LEFT));
            if ($displayBoth) {
                $html = htmlentities($basePrice, ENT_QUOTES, 'UTF-8', false) . '<br/>' .
                    htmlentities($price, ENT_QUOTES, 'UTF-8', false);
            } else {
                $html = htmlentities($price, ENT_QUOTES, 'UTF-8', false);
            }
        } else {
            if ($displayBoth) {
                $html = htmlentities(strip_tags($order->formatBasePrice($basePrice)), ENT_QUOTES, 'UTF-8', false).
                        '<br/>' .
                        htmlentities(strip_tags($order->formatPrice($price)), ENT_QUOTES, 'UTF-8', false);
            } else {
                $html = htmlentities($order->formatPriceTxt($price), ENT_QUOTES, 'UTF-8', false);
            }
        }

        return $html;
    }

    /**
     * prepare the order's gift message for display
     * @return array|bool
     * @access public
     */
    public function OutputGiftMessage()
    {
        if (!$this->displayGiftMessage()) {
            return false;
        }
        $order = $this->getOrder();

        if ($order->getGiftMessageId()) {
            $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage($order->getGiftMessageId());
            if ($giftMessage) {
                $message = array();
                $message['from'] = htmlspecialchars($giftMessage->getSender());
                $message['to'] = htmlspecialchars($giftMessage->getRecipient());
                $message['message'] = htmlspecialchars($giftMessage->getMessage());
                return $message;
            }
        }
        return false;
    }

    /**
     * prepare the item's gift message for display
     *
     * @param array $message
     *
     * @return array|bool
     * @access public
     */
    public function OutputGiftMessageItem($message)
    {
        $html = '';
        if ($message['message']) {
            $html = '<br/><br/>';
            $html .= "<b>" . Mage::helper('giftmessage')->__('From:') . "</b> " . $message['from'] . "<br/>";
            $html .= "<b>" . Mage::helper('giftmessage')->__('To:') . "</b> " . $message['to'] . "<br/>";
            $html .= "<b>" . Mage::helper('giftmessage')->__('Message:') . "</b> " . $message['message'] . "<br/>";
        }
        return $html;
    }

    /**
     * prepare the sales object's comment history for display
     * @return array|bool
     * @access public
     */
    public function OutputCommentHistory ()
    {
        if ($this->getPrintComments()) {
            $comments = array();
            $salesObject = $this->getSalesObject();
            if ($salesObject instanceof Mage_Sales_Model_Order) {
                foreach ($salesObject->getAllStatusHistory() as $history) {
                    $comments[] = array(
                        'date' => Mage::helper('core')->formatDate($history->getCreatedAtStoreDate(), 'medium'),
                        'label' => $history->getStatusLabel(),
                        'comment' => $history->getComment()
                    );
                }
            } else {
                if ($salesObject->getCommentsCollection()) {
                    foreach ($salesObject->getCommentsCollection() as $comment) {
                        $comments[] = array(
                            'date' => Mage::helper('core')->formatDate($comment->getCreatedAtStoreDate(), 'medium'),
                            'label' => '',
                            'comment' => $comment->getComment()
                        );
                    }
                }
            }
            if (!empty($comments)) {
                return $comments;
            }
        }
        return false;
    }

    /**
     * output customer order comments - requires additional extensions
     * @see    magento-community/Biebersdorf_CustomerOrderComment
     *
     * @return array|bool
     * @access public
     */
    public function OutputCustomerOrderComment()
    {
        $order = $this->getOrder();
        $orderComments = array();

        if ($order->getBiebersdorfCustomerordercomment()) {
            $orderComments[] = array(
                'title' => Mage::helper('biebersdorfcustomerordercomment')->__('Customer Order Comment'),
                'comment' => Mage::helper('biebersdorfcustomerordercomment')->escapeHtml(
                    $order->getBiebersdorfCustomerordercomment()
                )
            );
        }

        //hello extension
        if ($this->getOrder()->getHCheckoutcomment()) {
            $orderComments[] = array(
                'title' => Mage::helper('checkoutcomment')->__('This is a message from the customer'),
                'comment' => Mage::helper('checkoutcomment')->escapeHtml($this->getOrder()->getHCheckoutcomment())
            );
        }

        if ($order->getCustomerNote() || $order->getCustomerNotes()) {
            $orderComments[] = array(
                'title' => Mage::helper('pdfcustomiser')->__('Customer Order Comment'),
                'comment' => Mage::helper('pdfcustomiser')->escapeHtml(
                    $order->getCustomerNote()?$order->getCustomerNote():$order->getCustomerNotes()
                )
            );
        }

        if ($order->getShipNoteId()) {
            $shipNote = Mage::getModel('shipnote/note')->load($order->getShipNoteId());
            if ($shipNote->getId()) {
                $orderComments[] = array(
                    'title'   => Mage::helper('shipnote')->getFrontendLabel(),
                    'comment' => Mage::helper('pdfcustomiser')->escapeHtml($shipNote->getNote())
                );
            }
        }
        
        if ($order->getOnestepcheckoutCustomercomment()) {
            $orderComments[] = array(
                'title' => Mage::helper('onestepcheckout')->__('Customer Comments'),
                'comment' => Mage::helper('pdfcustomiser')->escapeHtml($order->getOnestepcheckoutCustomercomment())
            );
        }

        if ((string)Mage::getConfig()->getModuleConfig('Aitoc_Aitcheckoutfields')->active == 'true') {
            $aitCheckoutFields = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getInvoiceCustomData(
                $this->getSalesObject()->getId(), null, true
            );
            if ($aitCheckoutFields) {
                $collected = '';
                foreach ($aitCheckoutFields as $aitCheckoutField) {
                    $collected
                        .= $aitCheckoutField['label'] . ': ' . Mage::helper('pdfcustomiser')->escapeHtml(
                            htmlspecialchars_decode($aitCheckoutField['value'])
                        ) . '<br/>';
                }
                $orderComments[] = array(
                    'title' => Mage::getStoreConfig(
                        'aitcheckoutfields/common_settings/aitcheckoutfields_additionalblock_label'
                    ),
                    'comment' => $collected
                );
            }
        }

        if ((string)Mage::getConfig()->getModuleConfig('TM_CheckoutFields')->active == 'true') {
            $fields = Mage::helper('checkoutfields')->getFields();
            foreach ($fields as $field => $config) {
                $value = (string)$order->getData($field);
                if ($value) {
                    $orderComments[] = array(
                        'title'   => $config['label'],
                        'comment' => Mage::helper('pdfcustomiser')->escapeHtml($value)
                    );
                }
            }
        }

        if ($order->getFirecheckoutCustomerComment()) {
            $orderComments[] = array(
                'title' => Mage::helper('pdfcustomiser')->__('Order Comment'),
                'comment' => Mage::helper('pdfcustomiser')->escapeHtml($order->getFirecheckoutCustomerComment())
            );
        }

        if (!empty($orderComments)) {
            return $orderComments;
        }
        return false;
    }


    /**
     * prepare full tax summary for output
     *
     * @param array $taxTotal
     * @param array $taxAmount
     *
     * @return array
     * @access public
     */
    public function OutputTaxSummary(array $taxTotal, array $taxAmount)
    {
        $html = array();
        $filteredTaxrates = array();
        $zero = '0.0000';

        if (!$this->displayTaxSummary()) {
            return $html;
        }

        $order = $this->getOrder();

        foreach ($order->getFullTaxInfo() as $taxrate) {
            foreach ($taxrate['rates'] as $rate) {
                $taxId = $rate['code'];
                $filteredTaxrates[$taxId] = array(
                    'id' => $rate['code'],
                    'percent' => $rate['percent'],
                    'amount' => $taxrate['amount'],
                    'baseAmount' => $taxrate['base_amount']
                );
            }
        }

        if ($filteredTaxrates || isset($taxTotal[$zero])) {
            $html[] = array(
                Mage::helper('pdfcustomiser')->__('Tax Rate'),
                Mage::helper('pdfcustomiser')->__('Base Amount'),
                Mage::helper('pdfcustomiser')->__('Tax Amount'),
                Mage::helper('sales')->__('Subtotal')
            );
            if ($filteredTaxrates) {
                foreach ($filteredTaxrates as $filteredTaxrate) {
                    if (isset($taxTotal[sprintf("%01.4f", $filteredTaxrate['percent'])])) {
                        $taxBase = $taxTotal[sprintf("%01.4f", $filteredTaxrate['percent'])];
                    } else {
                        $taxBase = 0;
                    }
                    if (isset($taxAmount[sprintf("%01.4f", $filteredTaxrate['percent'])])) {
                        $taxBaseAmount = $taxAmount[sprintf("%01.4f", $filteredTaxrate['percent'])];
                    } else {
                        $taxBaseAmount = 0;
                    }
                    $html[] = array(
                        (float)$filteredTaxrate['percent'] . "%",
                        $order->formatBasePrice($taxBase),
                        $order->formatBasePrice($taxBaseAmount),
                        $order->formatBasePrice($taxBaseAmount + $taxBase)
                    );
                }
            }
            if (isset($taxTotal[$zero])) {
                $html[] = array(
                    (float)$zero . "%",
                    $order->formatBasePrice($taxTotal[$zero]),
                    $order->formatBasePrice($zero),
                    $order->formatBasePrice($taxTotal[$zero])
                );
            }
        }
        return $html;
    }

    /**
     * prepare parameters for use with tcpdfs fake html tag
     *
     * @param $array
     *
     * @return string
     * @access protected
     */
    public function serializeParams($array)
    {
        return urlencode(serialize($array));
    }

    /**
     * are we in right to left mode?
     * @return bool
     * @access public
     */
    public function isRtl()
    {
        return (bool)$this->_parameters[0]['rtl'];
    }

    /**
     * get attribute code for custom columns
     * @return array
     * @access public
     */
    public function getCustomColumnAttributes()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allcustomcolumn'])) {
            $this->_parameters[$this->getStoreId()]['allcustomcolumn'] =
                Mage::getStoreConfig('sales_pdf/all/allcustomcolumn', $this->getStoreId());
        }
        return explode(',', $this->_parameters[$this->getStoreId()]['allcustomcolumn']);
    }

    /**
     * get attribute code for custom columns
     * @return array
     * @access public
     */
    public function getBarcodeType()
    {
        if (!isset($this->_parameters[$this->getStoreId()]['allbarcode'])) {
            $this->_parameters[$this->getStoreId()]['allbarcode'] =
                Mage::getStoreConfig('sales_pdf/all/allbarcode', $this->getStoreId());
        }
        return $this->_parameters[$this->getStoreId()]['allbarcode'];
    }

    /**
     * Magento changed the translation strings without backwards compatibility
     * SOLD TO: to Sold to:
     * also used as a workaround since Mage::helper('sales')->__('Tax')
     * can sometimes return an empty string
     */
    public function getTranslatedString($string, $module = 'sales')
    {
        $translatedFromUppercase = Mage::helper($module)->__(strtoupper($string));
        if ($translatedFromUppercase != strtoupper($string) && !empty($translatedFromUppercase)) {
            return $translatedFromUppercase;
        }

        $translatedWithExtraColon = Mage::helper($module)->__($string . ':');
        if ($translatedWithExtraColon != $string . ':' && !empty($translatedWithExtraColon)) {
            return trim($translatedWithExtraColon, ':');
        }

        $translatedWithExtraColon = Mage::helper($module)->__($string . ' :');
        if ($translatedWithExtraColon != $string . ' :' && !empty($translatedWithExtraColon)) {
            return trim($translatedWithExtraColon, ' :');
        }

        return Mage::helper($module)->__($string);
    }
}