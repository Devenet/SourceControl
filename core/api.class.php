<?php

class Api {

    const _400 = 'HTTP/1.1 400 Bad Request'; // The server cannot or will not process the request due to something that is perceived to be a client error
    const _401 = 'HTTP/1.1 401 Unauthorized';
    const _403 = 'HTTP/1.1 403 Forbidden';
    const _404 = 'HTTP/1.1 404 Not Found';
    const _405 = 'HTTP/1.1 405 Method Not Allowed';
    const _422 = 'HTTP/1.1 422 Unprocessable entity'; // The request was well-formed but was unable to be followed due to semantic errors.
    const _501 = 'HTTP/1.1 501 Not Implemented';
    const _503 = 'HTTP/1.1 503 Service Unavailable';

    const E_EMPTY_DATA = 'Required fields are missing';

    protected $data = array();

    public function data($key, $value)
    {
    	$this->data[$key] = $value;
    }

    public function send()
    {
        header('Content-type: application/json');
        exit(json_encode($this->data));
    }

    public function error($code = 400, $message = NULL)
    {
        $this->data['error'] = $code;
        $this->data['message'] = is_null($message) ? trim(preg_replace('#HTTP\/1.1 \d{3}#', '', constant('self::_'.$code))) : $message;
        header(constant('self::_'.$code), true, $code);
        $this->send();
    }
	public function error_empty() { $this->error(400, self::E_EMPTY_DATA); }

}
