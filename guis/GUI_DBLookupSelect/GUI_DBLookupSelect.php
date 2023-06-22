<?php
/**
 * -= PHP Object Oriented Library (POOL) =-
 *
 * gui_dblookupselect.class.php
 *
 * Das GUI_DBLookupSelect ist ein Datenbank Steuerelement. Es steuert ein ComboBox (DropDown) sowie eine Multiselect Box.
 *
 * @version $Id: gui_dblookupselect.class.php 38772 2019-09-30 09:31:12Z manhart $
 * @version $revision 1.0$
 * @version
 *
 * @since 2004-02-12
 * @author Alexander Manhart <alexander@manhart.bayern>
 * @link https://alexander-manhart.de
 */

use pool\classes\Core\Input;
use pool\classes\Database\DAO;

/**
 * DBSelect steuert eine Dropdown- und Multiselect Box.
 *
 * @package pool
 * @author manhart
 * @version $Id: gui_dblookupselect.class.php 38772 2019-09-30 09:31:12Z manhart $
 **/
class GUI_DBLookupSelect extends GUI_Select
{
    /**
     * Initialisiert Standardwerte:
     *
     * tabledefine 		= ''	Tabellendefinition (siehe database.inc.php)
     * id				= 0		IDs (bei zusammengesetzten Primaerschluessel werden die IDs mit ; getrennt)
     * key				= ''	Keys (bei zusammengesetzten Primaerschluessel werden die Keys mit ; getrennt)
     * autoload_fields 	= 1		1 laedt automatisch alle Felder, 0 nicht
     * pk				= ''	Primaerschluessel (mehrere Spaltennamen werden mit ; getrennt)
     * columns			= ''	Auszulesende Spalten (Spaltennamen werden mit ; getrennt)
     *
     * @access public
     **/
    function init(?int $superglobals= Input::EMPTY)
    {
        $this->Defaults->addVar('tabledefine', '');
        $this->Defaults->addVar('keyValue', false); 	// separated by ;
        $this->Defaults->addVar('keyField', ''); 	// separated by ;
        $this->Defaults->addVar('keyOperator', 'equal');
        $this->Defaults->getVar('filter', array());
        $this->Defaults->addVar('autoload_fields', 1);
        $this->Defaults->addVar('pk', ''); 		// separated by ;
        $this->Defaults->addVar('columns', ''); // separated by ;
        $this->Defaults->addVar('listfieldSeparator', ' ');
        $this->Defaults->addVar('listfield', '');
        $this->Defaults->addVar('datafield', '');
        $this->Defaults->addVar('sortfield', '');
        $this->Defaults->addVar('shorten', 0);
        $this->Defaults->addVar('utf8', 0);

        parent::init($superglobals);
    }

    /**
     * @return void
     */
    public function prepare ()
    {
        $Input = & $this -> Input;

        $utf8 = $Input->getVar('utf8');

        $DAO = DAO::createDAO($Input->getVar('tabledefine'));

        # filter
        $filter = $Input->getVar('filter');

        if(!is_array($filter)) $filter=array();
        $keyField = $Input -> getVar('keyField');
        $keyValue = $Input -> getVar('keyValue');
        $keyOperator = $Input -> getVar('keyOperator');
        $shorten = $Input -> getVar('shorten');

        if($keyField and $keyValue !== false) {
            if(str_contains($keyField, ';') or is_array($keyField)) {
                if(is_string($keyField)) $keyFields = explode(';', $keyField);
                if(is_string($keyValue)) $keyValues = explode(';', $keyValue);
                if(is_string($keyOperator)) $keyOperators = explode(';', $keyOperator);
                for($i=0; $i<sizeof($keyFields); $i++) {
                    $keyField = $keyFields[$i];
                    $keyValue = $keyValues[$i];
                    $keyOperator = $keyOperators[$i] ?? 'equal';
                    $filter[] = array($keyField, $keyOperator, $keyValue);
                }
            }
            else {
                $filter[] = array($keyField, $keyOperator, $keyValue);
            }
        }
        #echo pray($filter);
        $sorting = null;
        if ($sortfield = $Input -> getVar('sortfield')) {
            $sorting = array($sortfield => 'ASC');
        }
        $listfield = $Input->getVar('listfield');
        $datafield = $Input->getVar('datafield');
        if(empty($listfield)) $listfield = $datafield;
        if(empty($datafield)) $datafield = $listfield;

        if($listfield != $datafield) {
            $DAO->setColumnsAsString($listfield . ';' . $datafield);
        }
        else {
            $DAO->setColumnsAsString($listfield);
        }

        $Resultset = $DAO->getMultiple(null, null, $filter, $sorting);
        #echo pray($Resultset);
        $rowset = $Resultset -> getRowset();

        $listfields = explode(',', $listfield);
        $listfieldSeparator = $this->Input->getVar('listfieldSeparator');

        $options = array();
        $values = array();
        if (count($rowset)) {
            foreach($rowset as $record) {
                $option = '';
                foreach ($listfields as $field) {
                    if($option != '') $option .= $listfieldSeparator;
                    $option .= $record[$field];
                }

                if($shorten > 0) {
                    $option = shorten($option, $shorten, 1, false);
                }
                $value = $record[$datafield];
                $options[] = ($utf8) ? UConverter::transcode($option, 'UTF8', 'ISO-8859-1') : $option;
                $values[] = ($utf8) ? UConverter::transcode($value, 'UTF8', 'ISO-8859-1') : $value;
            }
        }
        $defaultOptions = $Input -> getVar('options');
        if(!is_array($defaultOptions)) $defaultOptions = explode(';', $defaultOptions);
        $defaultValues = $Input -> getVar('values');
        if(!is_array($defaultValues)) $defaultValues = explode(';', $defaultValues);

        $options = array_merge($defaultOptions, $options);
        $values = array_merge($defaultValues, $values);

        $Input->setVar('options', $options);
        $Input->setVar('values', $values);

        parent :: prepare();
    }
}