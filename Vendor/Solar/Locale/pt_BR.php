<?php
/**
 * 
 * Locale file.  Returns the strings for a specific language.
 * 
 * @category Solar
 * 
 * @package Solar_Locale
 * 
 * @author Marcelo Santos Araujo <marcelosaraujo@gmail.com>
 * 
 * @author Rodrigo Moraes <http://tipos.org>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: pt_BR.php 2899 2007-10-19 23:44:30Z pmjones $
 * 
 */
return array(
    
    // formatting codes and information
    'FORMAT_LANGUAGE'            => 'Português',
    'FORMAT_COUNTRY'             => 'Brasil',
    'FORMAT_CURRENCY'            => 'R$%s', // printf()
    'FORMAT_DATE'                => '%d/%m/%Y', // strftime(): Mar 19, 2005
    'FORMAT_TIME'                => '%R', // strftime: 12-hour am/pm
    
    // page submissions
    'PROCESS_ADD'                => 'Adicionar',    
    'PROCESS_CANCEL'             => 'Cancelar',
    'PROCESS_CREATE'             => 'Criar',
    'PROCESS_DELETE'             => 'Remover',
    'PROCESS_EDIT'               => 'Editar',
    'PROCESS_GO'                 => 'Ir!',
    'PROCESS_LOGIN'              => 'Efetuar login',
    'PROCESS_LOGOUT'             => 'Sair',
    'PROCESS_NEXT'               => 'Próximo',
    'PROCESS_PREVIEW'            => 'Prévia',
    'PROCESS_PREVIOUS'           => 'Anterior',
    'PROCESS_RESET'              => 'Reinicializar',
    'PROCESS_SAVE'               => 'Salvar',
    'PROCESS_SEARCH'             => 'Buscar',
    
    // controller actions
    'ACTION_BROWSE'              => 'Lista',
    'ACTION_READ'                => 'Ler',
    'ACTION_EDIT'                => 'Editar',
    'ACTION_ADD'                 => 'Adicionar',
    'ACTION_DELETE'              => 'Removar',
    
    // exception error messages  
    'ERR_CONNECTION_FAILED'      => 'Falha na conexão.',
    'ERR_EXTENSION_NOT_LOADED'   => 'Extensão não foi carregada.',
    'ERR_FILE_NOT_FOUND'         => 'Arquivo não encontrado.',
    'ERR_FILE_NOT_READABLE'      => 'Arquivo não pode ser lido ou não existe.',
    'ERR_METHOD_NOT_CALLABLE'    => 'Método não pode ser invocado.',
    'ERR_METHOD_NOT_IMPLEMENTED' => 'Método não implementado.',
    
    // success feedback messages
    'SUCCESS_FORM'               => 'Salvo.',
    
    // failure feedback messages  
    'FAILURE_FORM'               => 'Não-salvo; por favor corrija os erros apresentados.',
    'FAILURE_INVALID'            => 'Dado(s) inválidos.',
    
    // generic text      
    'TEXT_AUTH_USERNAME'         => 'Logado como',
    
    // generic form element labels  
    'LABEL_SUBMIT'               => 'Ação',
    'LABEL_HANDLE'               => 'Usuário',
    'LABEL_PASSWD'               => 'Senha',
    'LABEL_EMAIL'                => 'E-mail',
    'LABEL_MONIKER'              => 'Nome',
    'LABEL_URI'                  => 'Site',
);
