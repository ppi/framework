<?php
/**
 * 
 * Locale file.  Returns the strings for a specific language.
 * 
 * @category Solar
 * 
 * @package Solar_Locale
 * 
 * @author Jean-Eric Laurent <jel@jelaurent.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: fr_FR.php 2899 2007-10-19 23:44:30Z pmjones $
 * 
 */
return array(
    
    // formatting codes and information
    'FORMAT_LANGUAGE'    => 'Français',
    'FORMAT_COUNTRY'     => 'France',
    'FORMAT_CURRENCY'    => 'EUR €%s', // printf()
    'FORMAT_DATE'        => '%j %m %Y', // strftime(): 19 Mar 2005
    'FORMAT_TIME'        => '%r', // strftime: 24-hour 
    
    // operation actions
    'PROCESS_SAVE'       => 'Sauvegarder',
    'PROCESS_PREVIEW'    => 'Previsualisation',
    'PROCESS_CANCEL'     => 'Annuler',
    'PROCESS_DELETE'     => 'Effacer',
    'PROCESS_RESET'      => 'Réinitialiser',
    'PROCESS_NEXT'       => 'Prochain',
    'PROCESS_PREVIOUS'   => 'Précédent',
    'PROCESS_SEARCH'     => 'Chercher',
    'PROCESS_GO'         => 'Action!',
    'PROCESS_LOGIN'      => 'Sign In',
    'PROCESS_LOGOUT'     => 'Sign Out',
    
    // error messages
    'ERR_FILE_NOT_FOUND'       => 'Impossible de trouver le fichier.',
    'ERR_FILE_NOT_READABLE'    => 'Impossible de lire le fichier.',
    'ERR_EXTENSION_NOT_LOADED' => 'Extension non chargée.',
    'ERR_CONNECTION_FAILED'    => 'Connection invalide.',
    'ERR_INVALID'              => 'Donnée invalide.',
    
    // success/failure messages
    'SUCCESS_SAVED'           => 'Sauvegardé.',
    'FAILURE_FORM'                 => 'Merci de corriger les erreurs affichées.',
    
    // generic text
    'TEXT_AUTH_USERNAME' => 'Identifié comme',
    
    // generic labels
    'LABEL_HANDLE'     => 'Identifiant',
    'LABEL_PASSWD'     => 'Mot de passe',
);
