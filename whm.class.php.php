<?php

/**
 * Cpanel/WHM XML API class 
 * Auther: DAVSON SANTOS
 * License: GNU GPL
 * Date: Outubro 2017
 */

class Whm{

	var $controller = true;
	var $host = "seudominio.com.br";//null;
	var $user = "username"; //null;
	var $accessHash = "JFSKLDFOIWEFUWEIOFOIFOWIWIYCU548";//null;
	var $errors = array();
	var $fp = null;

	function __construct(){
		$accessHash = str_replace(array("\r", "\n"),"",$this->accessHash);
		$this->accessHash = $accessHash;
		
	}
	/*
	 * CONECTA A API VIA XML
	 * RETORNO: TRUE, FALSE OU FAIL
	 */
	function connect($api_path){
		$this->fp = fsockopen("ssl://" . $this->host, 2087, $errno, $errstr, 30);
		if ($errno == 0 && $this->fp == false):
			$this->errors[]="Socket Error: Não foi possível inicializar o soquete.";
			return $this->errors;
		elseif ($this->fp == false):
			$this->errors[]="Socket Error #" . $errno . ": " . $errstr;
			return $this->errors;
		endif;		
				 
		$header = "";
		$header .= "GET " . $api_path . " HTTP/1.0\r\n";
		$header .= "Host: " . $this->host . "\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Authorization: WHM " . $this->user . ":" . $this->accessHash . "\r\n";
		$header .= "\r\n";

		if(!@fputs($this->fp, $header)):
			$this->errors[]='Não é possível enviar cabeçalho.';
			return $this->errors;
		endif;
	}

	
	/*
	 * Esta função lista a versão do cPanel e do WHM instalados no servidor.
	 * Retono: string
	 */
	function version(){
		$this->connect('/xml-api/version');
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();
		$xml = new SimpleXMLElement($xmlstr);
		return $xml->version;
	}


	/*
	 * Esta função lista o nome do host do servidor.
	 * Retorno: string
	 */
	function gethostname(){
		$this->connect('/xml-api/gethostname');
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();
		$xml = new SimpleXMLElement($xmlstr);
		return $xml->hostname;
	}

	/*
	 * Listar contas atualmente ativas
	 * Retorno: ARRAY com os usuários, false ou fail
	 */
	function list_account(){
		$this->connect('/xml-api/listaccts');
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();

		$xml = new DOMDocument();
		$xml->loadXML($xmlstr);
		$list = $xml->getElementsByTagName('user');
		$i=0;
		foreach ($list AS $element):
			foreach ($element->childNodes AS $item):
				$result[$i]['user']=$item->nodeValue;
				$i++;
			endforeach;
		endforeach;

		$list = $xml->getElementsByTagName('domain');
		$i=0;
		foreach ($list AS $element):
			foreach ($element->childNodes AS $item):
				$result[$i]['domain']=$item->nodeValue;
				$i++;
			endforeach;
		endforeach;

		$list = $xml->getElementsByTagName('plan');
		$i=0;
		foreach ($list AS $element):
			foreach ($element->childNodes AS $item):
				$result[$i]['package']=$item->nodeValue;
				$i++;
			endforeach;
		endforeach;

		$list = $xml->getElementsByTagName('unix_startdate');
		$i=0;
		foreach ($list AS $element):
			foreach ($element->childNodes AS $item):
				$result[$i]['start_date']=$item->nodeValue;
				$i++;
			endforeach;
		endforeach;

		return $result;
	}


	/*
	 * Lisa de Pacotes
	 * Output: ARRAY com a lista de pacotes, false on fail
	 */
	function list_packages(){
		$this->connect('/xml-api/listpkgs');
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();

		$xml = new DOMDocument();
		$xml->loadXML($xmlstr);

		$list = $xml->getElementsByTagName('name');
		$i=0;
		foreach ($list AS $element):
			foreach ($element->childNodes AS $item):
				$result[$i]['package_name']=$item->nodeValue;
				$i++;
			endforeach;
		endforeach;

		$list = $xml->getElementsByTagName('QUOTA');
		$i=0;
		foreach ($list AS $element):
			foreach ($element->childNodes AS $item):
				$result[$i]['package_quota']=$item->nodeValue;
				$i++;
			endforeach;
		endforeach;

		$list = $xml->getElementsByTagName('BWLIMIT');
		$i=0;
		foreach ($list AS $element):
			foreach ($element->childNodes AS $item):
				$result[$i]['package_bw_limit']=$item->nodeValue;
				$i++;
			endforeach;
		endforeach;

		return $result;
	}

	/*
	 * Cria uma nova conta no WHM
	 * Retorna: ARRAY, false on fail
	 */
	function create_account($Data){
		$this->connect("/xml-api/createacct?username=".$Data['username']."&password=".$Data['password']."&plan=".$Data['package']."&domain=".$Data['domain']."&contactemail=".$Data['email']."&ip=n&cgi=y&frontpage=y&cpmod=x3&useregns=0&reseller=0");

		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1):
			$result['status']=$xml->result->status;
			$result['statusmsg']=$xml->result->statusmsg;
			$result['ip']=$xml->result->options->ip;
			$result['nameserver']=$xml->result->options->nameserver;
			$result['nameserver2']=$xml->result->options->nameserver2;
			$result['nameserver3']=$xml->result->options->nameserver3;
			$result['nameserver4']=$xml->result->options->nameserver4;
			$result['package']=$xml->result->options->package;
			$result['rawout']=$xml->result->rawout;
			return $result;
		else:
			$this->errors[]=$xml->result->statusmsg;
			return $this->errors[0];
		endif;
	}


	/*
	 * Esta função exibe informações de uma conta específica.
	 * Retorno: ARRAY on success , false on fail
	 */
	function account_info($accUser){
		//connect using prpoer xml api address
		$this->connect("/xml-api/accountsummary?user=$accUser");
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();

		$xml = new SimpleXMLElement($xmlstr);

		if($xml->status==1):
			$result['disklimit']=$xml->acct->disklimit;
			$result['diskused']=$xml->acct->diskused;
			$result['diskused']=$xml->acct->diskused;
			$result['domain']=$xml->acct->domain;
			$result['email']=$xml->acct->email;
			$result['ip']=$xml->acct->ip;
			$result['owner']=$xml->acct->owner;
			$result['partition']=$xml->acct->partition;
			$result['plan']=$xml->acct->plan;
			$result['startdate']=$xml->acct->startdate;
			$result['theme']=$xml->acct->theme;
			$result['unix_startdate']=$xml->acct->unix_startdate;
			$result['user']=$xml->acct->user;
			return $result;
		else:
			$this->errors[]=$xml->statusmsg;
			return $this->errors;
		endif;
	}

	/*
	 * Esta função altera a senha de uma conta do proprietário do domínio (cPanel) ou do revendedor (WHM).
	 * Output: ARRAY on success , false on fail
	 */
	function passwd($accUser,$pass){
		$this->connect("/xml-api/passwd?user=$accUser&pass=$pass");
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();

		$xml = new SimpleXMLElement($xmlstr);

		if($xml->passwd->status==1):
			$result['statusmsg']=$xml->passwd->statusmsg;
			$result['frontpage']=$xml->passwd->frontpage;
			$result['ftp']=$xml->passwd->ftp;
			$result['mail']=$xml->passwd->mail;
			$result['mysql']=$xml->passwd->mysql;
			$result['system']=$xml->passwd->system;
			$result['rawout']=$xml->passwd->rawout;
			return $result;
		else:
			$this->errors[]=$xml->passwd->statusmsg;
			return $this->errors;
		endif;
	}

	/*
	 * Suspenda uma conta cpanel
	 * Retorno: STRING (statusmsg) on success, false on fail
	 */
	function block_account($acctUser,$reason){
		$this->connect("/xml-api/suspendacct?user=$acctUser&reason=$reason");
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();

		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1):
			return $xml->result->statusmsg;
		else:
			$this->errors[] = $xml->result->statusmsg;
			return $this->errors;
		endif;
	}

	/*
	 * Habilita uma conta suspensa
	 * Retorno: string (statusmsg) on success, false on fail
	 */
	function unblock_account($acctUser){
		$this->connect("/xml-api/unsuspendacct?user=$acctUser");
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1):
			return $xml->result->statusmsg;
		else:
			$this->errors[]=$xml->result->statusmsg;
			return $this->errors;
		endif;
	}


	/*
	 * Exclui uma conta de dominio
	 * Retorno: string (statusmsg) on success, false on fail
	 */
	function terminate_account($acctUser,$keepDns=0){
		$this->connect("/xml-api/removeacct?user=$acctUser&keepdns=$keepDns");
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1):
			return $xml->result->statusmsg;
		else:
			$this->errors[]=$xml->result->statusmsg;
			return $this->errors;
		endif;
	}


	/*
	 * Upgrade/Downgrade and Account (Change Package)
	 * Retorno: array on success, false on fail
	 */
	function update_package($accUser,$pkg){
		$this->connect("/xml-api/changepackage?user=$accUser&pkg=$pkg");
		$xmlstr=$this->getOutput();
		if($xmlstr==''):
			$this->errors[]='Erro indefinido. Entre em contato com o Administrador do sistema';
			return $this->errors;
		endif;
		$this->disconnect();
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1):
			$result['statusmsg']=$xml->result->statusmsg;
			$result['rawout']=$xml->result->rawout;
			return $result;
		else:
			$this->errors[]=$xml->result->statusmsg;
			return false;
		endif;
	}


	/*
	 * FECHA A CONEXÃO
	 */
	private function disconnect(){
		fclose($this->fp);
	}

	/*
	 * OBTEM A SAIDA DO SERVIDOR
	 * Output: string
	 */
	private function getOutput(){
		$rawResult = "";
		while (!feof($this->fp)):
			$rawResult .= @fgets($this->fp, 128); // Suppress errors with @
		endwhile;
		$rawResultParts = explode("\r\n\r\n",$rawResult);
		$result = $rawResultParts[1];
		return $result;
	}

}
?>
