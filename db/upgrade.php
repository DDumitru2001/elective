<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_elective_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads database manager.

    if ($oldversion < 2024060207) {
        $table = new xmldb_table('elective_question');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('questiontype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseids', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('questiontext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('maxanswers', XMLDB_TYPE_INTEGER, '10', null, null, null, '1');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2024060207, 'elective');
    }

    if ($oldversion < 2024060216) {
        $table = new xmldb_table('elective_answer');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('coursefullname', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2024060216, 'elective');
    }
//
//    if ($oldversion < 2024060213) {
//        // Define field sortorder to be added to elective_answer.
//        $table = new xmldb_table('elective_answer');
////        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'coursefullname');
//        $field = new xmldb_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'coursefullname');
//        // Conditionally launch add field sortorder.
//        if (!$dbman->field_exists($table, $field)) {
//            $dbman->add_field($table, $field);
//        }
//
//        upgrade_mod_savepoint(true, 2024060211, 'elective');
//    }
//    if ($oldversion < 2024060215) {
//        $table = new xmldb_table('elective_answer');
//        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
//        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
//        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
//        $table->add_field('coursefullname', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
//        $field = new xmldb_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'coursefullname');
//        // Conditionally launch add field sortorder.
//        if (!$dbman->field_exists($table, $field)) {
//            $dbman->add_field($table, $field);
//        }
//
//        // elective savepoint reached.
//        upgrade_mod_savepoint(true, 2024060215, 'elective');
//    }

    if ($oldversion < 2024060218) { // Actualizează această versiune la o valoare unică, de exemplu, data curentă.

        // Definirea tabelei și a noului câmp.
        $table = new xmldb_table('elective_answer');
        $field = new xmldb_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adaugă câmpul questionid dacă nu există deja.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Definirea cheii externe care leagă questionid de elective_question(id).
        $key = new xmldb_key('fk_questionid', XMLDB_KEY_FOREIGN, ['questionid'], 'elective_question', ['id']);

        // Actualizare finalizată, setăm punctul de salvare.
        upgrade_mod_savepoint(true, 2024060218, 'elective');
    }


    return true;
}
