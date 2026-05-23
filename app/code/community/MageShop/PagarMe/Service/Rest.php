<?php

class MageShop_PagarMe_Service_Rest
{
    /**
     * Inicio do request
     *
     * @var resource|false|CurlHandle
     */
    private $_curl;
    /**
     * Url para consultar
     *
     * @var string
     */
    private $_url;
    /**
     * Push
     *
     * @var string|array
     */
    private $data = null;
    /**
     * Cabeçalho
     *
     * @var array
     */
    private $headers = array('Content-Type: application/json');
    /**
     * Tipo da requisição
     *
     * @var string
     */
    private $method_header = "GET";
    private $options;
    private $_res;
    /**
     * Instancia
     *
     * @param string $url
     * @return object|MageShop_PagarMe_Service_Rest
     */
    public function __construct()
    {
        $this->_curl = curl_init();
    }
    /**
     * Set url
     *
     * @param string $url
     * @return object
     */
    public function url($url)
    {
        $this->_url = $url;
        return $this;
    }
    /**
     * Define o cobeçalho da req
     *
     * @param array $header
     * @return object|MageShop_PagarMe_Service_Rest
     */
    public function _header($header)
    {
        $this->headers = $header;
        return $this;
    }
    /**
     * Copo da req
     *
     * @param string|array $data
     * @return object|MageShop_PagarMe_Service_Rest 
     */
    public function _body($data)
    {
        $this->data = $data;
        return $this;
    }
    /**
     * Tipo de req
     * 
     * @param string $method
     * @return object|MageShop_PagarMe_Service_Rest
     */
    public function _method($method)
    {
        $this->method_header = strtoupper((string) $method);
        return $this;
    }
    /**
     * Executa o curl
     *
     * @return object|MageShop_PagarMe_Service_Rest
     */
    private function _inicialize()
    {
        $this->options = [
            CURLOPT_URL => $this->_url,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_CUSTOMREQUEST => $this->method_header,
        ];

        // Se houver dados a serem enviados no body, adicione-os (POST/PUT/DELETE/PATCH)
        if ($this->data !== null) {
            $this->options[CURLOPT_POSTFIELDS] = $this->data;
        }
        return $this;
    }

    public function setCurl($field, $value)
    {
        $this->options[$field] = $value;
        return $this;
    }
    /**
     * Returno da api
     *
     * @return string|array
     */
    public function getResponse()
    {
        return isset($this->_res['response']) ? $this->_res['response'] : null;
    }
    /**
     * Status de retorno
     *
     * @return int|string
     */
    public function getStatusCode()
    {
        return isset($this->_res['status_code']) ? $this->_res['status_code'] : null;
    }
    /**
     * Executa uma requisição na api
     *
     * @return object|MageShop_PagarMe_Service_Rest
     */
    public function exec()
    {
        if (!$this->_curl) {
            $this->_curl = curl_init();
        }
        $this->_inicialize();
        curl_setopt_array($this->_curl, $this->options);
        $response = curl_exec($this->_curl);
        if (curl_errno($this->_curl)) {
            $errorMessage = curl_error($this->_curl);
            $this->_res = array(
                'status_code' => 0,
                'response' => null,
                'curl_error' => $errorMessage,
            );
            curl_close($this->_curl);
            $this->_curl = null;
            throw new Exception("MageShop_PagarMe_Service_Rest error: " . $errorMessage);
        }
        $status_code = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        curl_close($this->_curl);
        $this->_curl = null;
        $this->_res = array(
            'status_code' => $status_code,
            'response' => $response
        );
        return $this;
    }

    public function success()
    {
        $code = (int) $this->getStatusCode();
        return $code >= 200 && $code <= 299;
    }

    /**
     * @return array|null
     */
    public function error()
    {
        if ($this->success()) {
            return null;
        }
        $body = $this->getResponse();
        if ($body === null || $body === '') {
            return array(
                'message' => 'HTTP ' . $this->getStatusCode() . ' sem corpo de resposta.',
            );
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return array(
                'message' => 'HTTP ' . $this->getStatusCode() . ': ' . substr((string) $body, 0, 500),
            );
        }
        if (isset($decoded['errors']) && is_array($decoded['errors'])) {
            $details = array();
            foreach ($decoded['errors'] as $field => $msgs) {
                $msgs = is_array($msgs) ? implode(', ', $msgs) : (string) $msgs;
                $details[] = (is_string($field) ? $field . ': ' : '') . $msgs;
            }
            if (!empty($details)) {
                $base = isset($decoded['message']) ? $decoded['message'] . ' - ' : '';
                $decoded['message'] = $base . implode(' | ', $details);
            }
        }
        if (!isset($decoded['message'])) {
            $decoded['message'] = 'HTTP ' . $this->getStatusCode();
        }
        return $decoded;
    }
    public function __destruct()
    {
        if ($this->_curl) {
            @curl_close($this->_curl);
        }
        $this->_curl = null;
        unset($this->_curl);
    }
}
