<?php
/**
 * 
 * Locale file.  Returns the strings for the de_DE language.
 * 
 * @category Solar
 * 
 * @package Solar_Locale
 * 
 * @author Bahtiar `kalkin` Gadimov <bahtiar@gadimov.de>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: de_DE.php 4326 2010-02-02 02:33:00Z pmjones $
 * 
 */
return array(
    
    // formatting codes and information
    'FORMAT_LANGUAGE'            => 'Deutsch',
    'FORMAT_COUNTRY'             => 'Deutschland',
    'FORMAT_CURRENCY'            => '€%s', // printf()
    'FORMAT_DEC_POINT'           => ',', // number_format
    'FORMAT_THOUSANDS_SEP'       => '.', // number_format
    'FORMAT_DATE'                => '%e. %b. %Y', // strftime(): 19. Mär. 2005 (DIN 5008)
    'FORMAT_TIME'                => '%r', // strftime: 24-hour
    
    // action processes
    'PROCESS_ADD'                => 'Hinzufügen',
    'PROCESS_CANCEL'             => 'Abrechen',
    'PROCESS_CREATE'             => 'Erstellen',
    'PROCESS_DELETE'             => 'Entfernen',
    'PROCESS_DELETE_CONFIRM'     => 'Entfernen (Bestätigung)',
    'PROCESS_EDIT'               => 'Bearbeiten',
    'PROCESS_GO'                 => 'Los!',
    'PROCESS_LOGIN'              => 'Einloggen',
    'PROCESS_LOGOUT'             => 'Ausloggen',
    'PROCESS_NEXT'               => 'Weiter',
    'PROCESS_PREVIEW'            => 'Vorschau',
    'PROCESS_PREVIOUS'           => 'Zurück',
    'PROCESS_RESET'              => 'Zurücksetzen',
    'PROCESS_SAVE'               => 'Speichern',
    'PROCESS_SEARCH'             => 'Suche',
    
    // controller actions
    'ACTION_BROWSE'              => 'Durchsuchen',
    'ACTION_READ'                => 'Lesen',
    'ACTION_EDIT'                => 'Bearbeiten',
    'ACTION_ADD'                 => 'Hinzufügen',
    'ACTION_DELETE'              => 'Entfernen',
    'ACTION_SEARCH'              => 'Suchen',
    'ACTION_NOT_FOUND'           => 'Nicht gefunden',
    
    // exception error messages  
    'ERR_CONNECTION_FAILED'      => 'Verbindungsfehler.',
    'ERR_EXTENSION_NOT_LOADED'   => 'Erweiterung ist nicht geladen.',
    'ERR_FILE_NOT_FOUND'         => 'Datei nicht gefunden.',
    'ERR_FILE_NOT_READABLE'      => 'Datei nicht lesbar oder nicht vorhanden',
    'ERR_METHOD_NOT_CALLABLE'    => 'Die Funktion ist nicht aufrufbar.',
    'ERR_METHOD_NOT_IMPLEMENTED' => 'Die Funktion ist nicht implementiert.',
    
    // success feedback messages
    'SUCCESS_FORM'               => 'Gespeichert.',
    'SUCCESS_ADDED'              => 'Hinzugefügt.',
    'SUCCESS_DELETED'            => 'Entfernt.',
    
    // failure feedback messages  
    'FAILURE_FORM'               => 'Nicht gespeichert - bitte korrigieren Sie die angegebenen Fehler.',
    'FAILURE_INVALID'            => 'Ungültige Daten.',
    
    // pagers
    'PAGER_PREV'                 => 'Zurück',
    'PAGER_NEXT'                 => 'Weiter',
    
    // generic text      
    'TEXT_AUTH_USERNAME'         => 'Eingeloggt als',
    
    // generic form element labels  
    'LABEL_SUBMIT'               => 'Senden',
    'LABEL_HANDLE'               => 'Benutzername',
    'LABEL_PASSWD'               => 'Passwort',
    'LABEL_EMAIL'                => 'Email',
    'LABEL_MONIKER'              => 'Angezeigter Name',
    'LABEL_URI'                  => 'Webseite',
    'LABEL_CREATED'              => 'Erstellt',
    'LABEL_UPDATED'              => 'Aktualisiert',
);
