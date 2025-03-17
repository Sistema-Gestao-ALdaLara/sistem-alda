<?php
namespace App\Utils;

use Carbon\Carbon;

class Auxiliar{
    public static function cleanCaracter($var)
	{
		$var =  str_replace('(', '', $var);
		$var =  str_replace(')', '', $var);
		$var =  str_replace('-', '', $var);
		$var =  str_replace('.', '', $var);
		$var =  str_replace('/', '', $var);
		$var =  str_replace('.', '', $var);
		$var =  str_replace(',', '', $var);
		$var =  str_replace(' ', '', $var);
		return $var;
	}
	
	public static function formatData($data)
	{
		$data = strtr($data, '/', '-');
		$ret_data = date('Y-m-d', strtotime($data));
		return $ret_data;
	}

	public static function notaPorExtenso($nota){
		switch ($nota) {
			case 1:
				return "Um";
				break;
			case 2:
				return "Dois";
				break;
			case 3:
				return "Três";
				break;
			case 4:
				return "Quatro";
				break;
			case 5:
				return "Cinco";
				break;
			case 6:
				return "Seis";
				break;
			case 7:
				return "Sete";
				break;
			case 8:
				return "Oito";
				break;
			case 9:
				return "Nove";
				break;
			case 10:
				return "Dez";
				break;
			case 11:
				return "Onze";
				break;
			case 12:
				return "Doze";
				break;
			case 13:
				return "Treze";
				break;
			case 14:
				return "Quatorze";
				break;
			case 15:
				return "Quinze";
				break;
			case 16:
				return "Dezasseis";
				break;
			case 17:
				return "Dezassete";
				break;
			case 18:
				return "Dezoito";
				break;
			case 19:
				return "Dezanove";
				break;
			case 20:
				return "Vinte";
				break;
			default:
				return "";
				break;
		}
	}

    public static function diffYear($data)
    {
        $date_atual = new \DateTime(date('Y-m-d'));
        $date_user = new \DateTime($data);

        /** A classe Datetime tem um metodo 'diff' que retorna um DateInterval */
        $diff = $date_atual->diff($date_user);

        return $diff->y+1;
    }

	public static function retornaAnoNascimento($data)
    {
		
        $ano = date_format(date_create($data), 'Y');

        return $ano;
    }

	public static function retornaDataValidadeBI($data_emissao){
		$data = date_create($data_emissao);
		date_add($data,date_interval_create_from_date_string("5 years"));
		return date_format($data,"d-m-Y");
	}

    public static function formatDateExtension($data)
    {
        $date = Carbon::parse($data)->locale('pt_PT');

        return $date->translatedFormat('d \d\e F \d\e Y');
    }
    
	public static function formataDataHora($data)
	{
		$data = strtr($data, '/', '-');
		$ret_data = date('Y-m-d H:i:s', strtotime($data));
		return $ret_data;
	}
	
	public static function getDataHora($data)
	{
		$datahora = date('d/m/Y H:i:s', strtotime($data));
		return $datahora;
	}
	
	public static function getData($data)
	{
		$data = date('d/m/Y', strtotime($data));
		return $data;
	}
	
	public static function isEmpty($_str)
	{
		if( empty($_str) )
		{
			if($_str == 0){ return false; } else { return true; }
		}
		return false;
	}

	public static function uploadImagem($image, $local)
    {
        if (is_file($image)) {
            $path = $local;
            $filename = date('YmdHis').rand(1, 9999).".".$image->getClientOriginalExtension();
            $image->move($path, $filename);

            return $filename;
        } else {
            return 'default.png';
        }
    }

	public static function uploadFicheiro($ficheiro, $local){
		
		$path = $local;
		$filename = $ficheiro->getClientOriginalName();
		$ficheiro->move($path, $filename);

		return $filename;
	}

	public static function retornaNomeGenero($genero)
    {
        if ($genero == 'M' or $genero == 'm') {
            return 'Masculino';
        }

        return 'Feminino';
    }

	public static function gerarSenha($nome_completo, $data_nascimento){
		$nome = explode(' ', $nome_completo)[0];
		$data = explode('-', $data_nascimento)[0];
		$senha = strtolower($nome).$data;
		return $senha;

	}
}