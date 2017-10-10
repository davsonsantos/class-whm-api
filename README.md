# Classe PHP Inegração WHM
Classe PHP para integração com o Servidor WHM

# Configuração (Altere para seus dados)
var $host = "seudominio.com.br"; //Seu dominio de cadastro
var $user = "usuariowhm"; //Seu usuario de acesso
var $accessHash = "lkfvmerit905890348ytvnuerogue"; //Token publico

# Modo de uso
require_once('Whm.class.php');
$whm = new Whm();

# Versão do servidor
$Result = $whm->version();

# Nome do Host
$Result = $whm->gethostname();

# Nome do Host
$Result = $whm->gethostname();

# Lista de Contas
$Result = $whm->list_account();

# Lista de Pacotes
$Result = $whm->list_packages();

# Cria uma nova conta
$Dados = [
	'domain' => 'seudominio.com.br',
	'username' => 'user',
	'password' => 'pass',
	'package' => 'package',
	'email' => 'email@seudominio.com.br'
];
$Result = $whm->create_account($Dados);

# Informações da conta (Espaço em disco, email, banda utilizada ...)
$Result = $whm->account_info('user');

# Altera sua senha da conta do WHM
$Result = $whm->passwd('seuusuario');

# Bloqueia uma conta
$Result = $whm->block_account('seuusuario','motivo');

# Bloqueia uma conta
$Result = $whm->unblock_account('seuusuario');

# Exclui uma conta
$Result = $whm->terminate_account('seuusuario');

# Atualiza uma conta
$Result = $whm->update_package('seuusuario','package');

