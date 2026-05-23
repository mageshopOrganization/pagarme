<?php

class MageShop_PagarMe_Helper_Validation extends MageShop_PagarMe_Helper_Data
{

  const MS_PAGARME_CUSTOMER_CPF_ATTR = "payment/mageshop_yapay_custompayment/customer_cpf_attribute";

  public function cnpj($cnpj)
  {

    $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

    // Valida tamanho
    if (strlen($cnpj) != 14)
      return false;

    // Verifica se todos os digitos são iguais
    if (preg_match('/(\d)\1{13}/', $cnpj))
      return false;

    // Valida primeiro dígito verificador
    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
      $soma += $cnpj[$i] * $j;
      $j = ($j == 2) ? 9 : $j - 1;
    }

    $resto = $soma % 11;

    if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
      return false;

    // Valida segundo dígito verificador
    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
      $soma += $cnpj[$i] * $j;
      $j = ($j == 2) ? 9 : $j - 1;
    }

    $resto = $soma % 11;

    return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
  }

  public function cpf($cpf)
  {
    // Extrair somente os números
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);
    // Verifica se foi informado todos os digitos corretamente
    if (strlen($cpf) != 11) {
      return false;
    }

    // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) {
      return false;
    }

    // Faz o calculo para validar o CPF
    for ($t = 9; $t < 11; $t++) {
      for ($d = 0, $c = 0; $c < $t; $c++) {
        $d += $cpf[$c] * (($t + 1) - $c);
      }
      $d = ((10 * $d) % 11) % 10;
      if ($cpf[$c] != $d) {
        return false;
      }
    }

    return true;
  }

  public function cnpj_cpf($str)
  {
    $doc = preg_replace("/[^0-9]/", "", $str);
    $qtd = strlen($doc);

    if ($qtd >= 11) {
      if ($qtd === 11) {
        $docFormatado = substr($doc, 0, 3) . '.' .
          substr($doc, 3, 3) . '.' .
          substr($doc, 6, 3) . '.' .
          substr($doc, 9, 2);
        return $this->cpf($docFormatado);
      } else {
        $docFormatado = substr($doc, 0, 2) . '.' .
          substr($doc, 2, 3) . '.' .
          substr($doc, 5, 3) . '/' .
          substr($doc, 8, 4) . '-' .
          substr($doc, -2);
        return $this->cnpj($docFormatado);
      }
    }
  }

  public function getCpfAttr()
  {
    return Mage::getStoreConfig(self::MS_PAGARME_CUSTOMER_CPF_ATTR);
  }

  public function getAttrCpfArray()
  {
    $attr = $this->getCpfAttr();
    if (!empty($attr) && strlen($attr) > 0) {
      $arr_select = explode("|", $attr);
    }
    return isset($arr_select) ? $arr_select : null;
  }

  public function extractDDDAndNumber($phoneNumber)
  {
    // Use uma expressão regular para extrair DDD e número
    $pattern = '/^(\d{2})(\d{4,5}\d{4})/';

    // Tenta extrair DDD e número
    preg_match($pattern, $phoneNumber, $matches);

    // Verifica se houve correspondência e retorna DDD e número
    if (!empty($matches)) {
      $ddd = isset($matches[1]) ? $matches[1] : null;
      $number = $matches[2];
      return compact('ddd', 'number');
    } else {
      // Se não houver correspondência, você pode retornar um valor padrão ou lançar uma exceção, dependendo do seu caso de uso.
      return null;
    }
  }


  /**
   * Function to get region code
   * @param string $state
   * @return string
   */
  public function getRegionCode($state): string
  {
    $response = $this->getStates();

    foreach ($response as $states) {
      if ($this->removeAccents(strtolower($states['nome'])) == $this->removeAccents(strtolower($state))) {
        return $states['sigla'];
      } else if ($this->removeAccents(strtolower($states['sigla'])) == $this->removeAccents(strtolower($state))) {
        return $states['sigla'];
      }
    }

    Mage::throwException('Região Inaválida. Verifique por favor.');
  }

  /**
   * Function to execute request
   * @return array
   */
  public function getStates()
  {
    $array = array(
      0 =>
      array(
        'id' => 11,
        'sigla' => 'RO',
        'nome' => 'Rondônia',
        'regiao' =>
        array(
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      1 =>
      array(
        'id' => 12,
        'sigla' => 'AC',
        'nome' => 'Acre',
        'regiao' =>
        array(
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      2 =>
      array(
        'id' => 13,
        'sigla' => 'AM',
        'nome' => 'Amazonas',
        'regiao' =>
        array(
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      3 =>
      array(
        'id' => 14,
        'sigla' => 'RR',
        'nome' => 'Roraima',
        'regiao' =>
        array(
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      4 =>
      array(
        'id' => 15,
        'sigla' => 'PA',
        'nome' => 'Pará',
        'regiao' =>
        array(
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      5 =>
      array(
        'id' => 16,
        'sigla' => 'AP',
        'nome' => 'Amapá',
        'regiao' =>
        array(
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      6 =>
      array(
        'id' => 17,
        'sigla' => 'TO',
        'nome' => 'Tocantins',
        'regiao' =>
        array(
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      7 =>
      array(
        'id' => 21,
        'sigla' => 'MA',
        'nome' => 'Maranhão',
        'regiao' =>
        array(
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      8 =>
      array(
        'id' => 22,
        'sigla' => 'PI',
        'nome' => 'Piauí',
        'regiao' =>
        array(
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      9 =>
      array(
        'id' => 23,
        'sigla' => 'CE',
        'nome' => 'Ceará',
        'regiao' =>
        array(
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      10 =>
      array(
        'id' => 24,
        'sigla' => 'RN',
        'nome' => 'Rio Grande do Norte',
        'regiao' =>
        array(
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      11 =>
      array(
        'id' => 25,
        'sigla' => 'PB',
        'nome' => 'Paraíba',
        'regiao' =>
        array(
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      12 =>
      array(
        'id' => 26,
        'sigla' => 'PE',
        'nome' => 'Pernambuco',
        'regiao' =>
        array(
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      13 =>
      array(
        'id' => 27,
        'sigla' => 'AL',
        'nome' => 'Alagoas',
        'regiao' =>
        array(
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      14 =>
      array(
        'id' => 28,
        'sigla' => 'SE',
        'nome' => 'Sergipe',
        'regiao' =>
        array(
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      15 =>
      array(
        'id' => 29,
        'sigla' => 'BA',
        'nome' => 'Bahia',
        'regiao' =>
        array(
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      16 =>
      array(
        'id' => 31,
        'sigla' => 'MG',
        'nome' => 'Minas Gerais',
        'regiao' =>
        array(
          'id' => 3,
          'sigla' => 'SE',
          'nome' => 'Sudeste',
        ),
      ),
      17 =>
      array(
        'id' => 32,
        'sigla' => 'ES',
        'nome' => 'Espírito Santo',
        'regiao' =>
        array(
          'id' => 3,
          'sigla' => 'SE',
          'nome' => 'Sudeste',
        ),
      ),
      18 =>
      array(
        'id' => 33,
        'sigla' => 'RJ',
        'nome' => 'Rio de Janeiro',
        'regiao' =>
        array(
          'id' => 3,
          'sigla' => 'SE',
          'nome' => 'Sudeste',
        ),
      ),
      19 =>
      array(
        'id' => 35,
        'sigla' => 'SP',
        'nome' => 'São Paulo',
        'regiao' =>
        array(
          'id' => 3,
          'sigla' => 'SE',
          'nome' => 'Sudeste',
        ),
      ),
      20 =>
      array(
        'id' => 41,
        'sigla' => 'PR',
        'nome' => 'Paraná',
        'regiao' =>
        array(
          'id' => 4,
          'sigla' => 'S',
          'nome' => 'Sul',
        ),
      ),
      21 =>
      array(
        'id' => 42,
        'sigla' => 'SC',
        'nome' => 'Santa Catarina',
        'regiao' =>
        array(
          'id' => 4,
          'sigla' => 'S',
          'nome' => 'Sul',
        ),
      ),
      22 =>
      array(
        'id' => 43,
        'sigla' => 'RS',
        'nome' => 'Rio Grande do Sul',
        'regiao' =>
        array(
          'id' => 4,
          'sigla' => 'S',
          'nome' => 'Sul',
        ),
      ),
      23 =>
      array(
        'id' => 50,
        'sigla' => 'MS',
        'nome' => 'Mato Grosso do Sul',
        'regiao' =>
        array(
          'id' => 5,
          'sigla' => 'CO',
          'nome' => 'Centro-Oeste',
        ),
      ),
      24 =>
      array(
        'id' => 51,
        'sigla' => 'MT',
        'nome' => 'Mato Grosso',
        'regiao' =>
        array(
          'id' => 5,
          'sigla' => 'CO',
          'nome' => 'Centro-Oeste',
        ),
      ),
      25 =>
      array(
        'id' => 52,
        'sigla' => 'GO',
        'nome' => 'Goiás',
        'regiao' =>
        array(
          'id' => 5,
          'sigla' => 'CO',
          'nome' => 'Centro-Oeste',
        ),
      ),
      26 =>
      array(
        'id' => 53,
        'sigla' => 'DF',
        'nome' => 'Distrito Federal',
        'regiao' =>
        array(
          'id' => 5,
          'sigla' => 'CO',
          'nome' => 'Centro-Oeste',
        ),
      ),
    );
    return $array;
  }

  /**
   * Function to remove accents from strings
   * @param string $name
   * @return string
   */
  public function removeAccents($name)
  {
    return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $name);
  }
}
