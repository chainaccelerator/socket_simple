<?

Class Json_request_status_simple {

    private static $version = 'v0';
    public $global_status;
    public $status;
    public $code;
    public $message;

  public function init(bool $global_status, string $state, string $code, string $message = ''){

    $this->global_status = $global_status;
    $this->status = $state;
    $this->code = hash('sha512', $code.self::$version);
    $this->message = $message;

    return true;
  }
}
