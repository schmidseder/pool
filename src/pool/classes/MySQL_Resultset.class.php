<?php
/**
 * # PHP Object Oriented Library (POOL) #
 *
 * Class MySQL_Resultset abgeleitet von der abstrakten Basisklasse Resultset.
 * Diese Klasse kuemmert sich um das Ergebnis eines ausgefuehrten SQL Statement.
 * Es fuehrt ein SQL Statement aus, speichert je nach Operation (insert, update,
 * select) die Ergebnismenge zwischen. Vererbte Iteratoren sorgen fuer die
 * Navigation in der Ergebnismenge (z.B. $this -> next).
 *
 * Ein "insert" liefert die "last_insert_id" zurueck. Sie kann ueber
 * $this -> getValue('last_insert_id') abgefragt werden. Falls es sich bei der
 * Tabelle um einen Primaerschluessel mit dem Attribut auto_increment handelt.
 *
 * Ein "update" liefert immer die Anzahl betroffener Zeilen: "affected_row"!
 *
 * Ein "select" natuerlich die Ergebnismenge/Entitaeten der Abfrage.
 *
 * @version $Id: MySQL_Resultset.class.php,v 1.13 2007/03/29 09:20:18 manhart Exp $
 * @version $Revision: 1.13 $
 *
 * @see Resultset.class.php
 * @see MySQL_db.class.php
 * @see MySQL_DAO.class.php
 *
 * @since 2003/07/10
 * @author Alexander Manhart <alexander@manhart-it.de>
 * @link https://alexander-manhart.de
 */

if(!defined('CLASS_MYSQL_RESULTSET')) {

    #### Prevent multiple loading
    define('CLASS_MYSQL_RESULTSET', 1);

    /**
     * MySQL_Resultset
     *
     * Siehe Datei fuer ausfuehrliche Beschreibung!
     *
     * @package pool
     * @author Alexander Manhart <alexander.manhart@gmx.de>
     * @version $Id: MySQL_Resultset.class.php,v 1.13 2007/03/29 09:20:18 manhart Exp $
     * @access public
     **/
    class MySQL_Resultset extends Resultset
    {
        /**
         * Datenbankschnittstellen-Objekt MySQL_Interface
         *
         * @var MySQL_Interface
         */
        var $db = null;

        /**
         * Konstruktor
         *
         * Erwartet Datenbank Layer als Parameter.
         * Der Datenbank Layer ist die Schnittstelle zur MySQL Datenbank.
         * Die MySQL_db Klasse uebt die eigentlichen datenbankspezfischen
         * Operationen (z.B. mysql_connect, mysql_query) aus.
         *
         * @access public
         * @param MySQL_Interface $db Datenbank Layer (Class MySQL_db)
         * @see MySQL_db
         **/
        function MySQL_Resultset(& $db)
        {
            $this->db = & $db;
        }

        /**
         * Die Funktion "execute" fuehrt das uebergebene SQL Statement aus
         * und speichert die Ergebnismenge zwischen. Ueber vererbte Iteratoren
         * kann durch das Ergebnis navigiert werden (z.B. $this -> prev()).
         *
         * Fehlermeldungen landen im $this -> errorStack und koennen ueber
         * $this -> getLastError() abgefragt werden.
         *
         * @access public
         * @param string $sql SQL Statement
         * @param string $dbname Datenbankname
         * @return boolean Erfolgsstatus (SQL Fehlermeldungen koennen ueber $this -> getLastError() abgefragt werden)
         * @see Resultset::getLastError()
         **/
        function execute($sql, $dbname='')
        {
            $bResult = false;
            $this->rowset = array();

            if (!is_a($this->db, 'DataInterface')) {
                $this->raiseError(__FILE__, __LINE__, 'Kein DataInterface vorhanden (@execute).');
            }
            else {
                if (defined('LOG_ENABLED') and LOG_ENABLED and defined('ACTIVATE_RESULTSET_SQL_LOG') and
                    ACTIVATE_RESULTSET_SQL_LOG == 1) {
                    // Zeitmessung starten
                    $Stopwatch = &Singleton('Stopwatch');
                    $Stopwatch->start('SQLQUERY');
                }
                $result = $this->db->query($sql, $dbname);
            }

            if (!$result) {
                $error_msg = $this->db->getErrormsg().' SQL Statement failed: '.$sql;
                $this -> raiseError(__FILE__, __LINE__, $error_msg);
                $error = $this -> db -> getError();
                $error['sql'] = $sql;
                $this -> errorStack[] = $error;
            }
            else {
                $cmd = $this->db->getLastSQLCommand();
                #echo $cmd.'<br>';
                if ($cmd == 'SELECT' or $cmd == 'SHOW'/* or substr($cmd, 0, 1) == '('*/) { // ( z.B. UNION
                    if ($this->db->numrows($result) > 0) {
                        $this->rowset = $this->db->fetchrowset($result);
                        $this->reset();
                    }
                    $this->db->freeresult($result);
                }
                elseif ($cmd == 'INSERT') {
                    $last_insert_id = $this -> db -> nextid();
                    $affected_rows = $this -> db -> affectedrows();
                    $this -> rowset = array(
                        0 => array(
                            0 => $last_insert_id,
                            'last_insert_id' => $last_insert_id,
                            'id' => $last_insert_id,
                            'affected_rows' => $affected_rows
                        )
                    );
                    $this -> reset();
                }
                elseif ($cmd == 'UPDATE' or $cmd == 'DELETE') {
                    $affected_rows = $this -> db -> affectedrows();
                    $this -> rowset = array(
                        0 => array(
                            0 => $affected_rows,
                            'affected_rows' => $affected_rows
                        )
                    );
                    $this -> reset();
                }
                $bResult = true;
            }

            // SQL Statement Logging:
            if (defined('LOG_ENABLED') and LOG_ENABLED and defined('ACTIVATE_RESULTSET_SQL_LOG') and
                ACTIVATE_RESULTSET_SQL_LOG == 1) {
                $Stopwatch->stop('SQLQUERY');
                $timespent = $Stopwatch->getDiff('SQLQUERY');

                $Log = &Singleton('Log');
                if($Log->isLogging()) {
                    $Log->addLine('SQL ON DB '.$dbname.': "'.$sql.'" in '.$timespent.' sec.');
                    if(!$bResult) $Log->addlIne('SQL-ERROR ON DB '.$dbname.': '.$this->db->getErrormsg());
                }
            }
            return $bResult;
        }

        /**
         * Gibt die komplette Ergebnismenge im als SQL Insert Anweisungen (String) zurueck.
         *
         * @access public
         * @param string $table
         * @return string
         **/
        function getSQLInserts($table = null)
        {
            $sql = '';

            if($this->count() && $table) {
                $line_break = chr(10);
                // Zuerst die Insert Anweisung und die Feldnamen
                foreach($this -> rowset as $row)
                {
                    $sql .= 'INSERT INTO '.$table.' (';
                    $sql .= implode(',', array_keys($this -> rowset[0]));
                    $sql .= ') VALUES (\''.implode('\',\'', array_values($row)).'\');'.$line_break;
                }
            }
            return $sql;
        }
    }
}