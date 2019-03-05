<?php

/**
 * Trait Socket_node_push_simple
 */
Trait Socket_node_push_simple {

    use Crypto_simple;

    private static $error_reporting = E_ALL;
    private static $address;
    private static $client_list = array();
    private static $port = 424242;
    private static $start_script_file = '../run/server_start.php';
    private $mutex_run_file = '../data/mutex/main_run.sock';
    private $mutex_stop_file = '../data/mutex/main_stop.sock';
    private $server_side_sock_file = '../data/socket/server.sock';
    private $client_side_sock = '../data/socket/client.sock';
    private $server_side_sock;
    private $sock;

    /**
     * @var Json_request_simple
     */
    private $global_json_request;
    /**
     * @var Json_request_simple
     */
    private $json_request;

    public function __construct()
    {
        $this->global_json_request = new Json_request_simple();
        $this->json_request = new Json_request_simple();
    }

    public function socket_init(Json_request_workflow_simple $workflow){

      // ... quelques controles ...
      error_reporting(self::$error_reporting);

      $this->sock = socket_create(AF_UNIX, SOCK_DGRAM, 0);

      if(is_file($this->mutex_run_file) === true) return false;

      self::$address = gethostbyname(gethostname());
      $this->global_json_request->workflow->init('client_start', __METHOD__, 'starting');
      $this->global_json_request->workflow = $workflow;
      $this->socket_state($this->sock, 'socket_create');

      $this->json_request = $this->global_json_request;

      /* For reading */
      ob_implicit_flush();

      return true;
    }

    public function socket_fail(string $function, bool $send = true){

      $err = socket_strerror( socket_last_error($this->sock) );
      $msg = $function.'() a échoué : raison : '.$err."\n";
      $this->json_request->status->init(false, $function, __CLASS__.__METHOD__.__FUNCTION__.__LINE__.$err, $msg);
      $this->json_request->response($send);

      return false;
    }

    public function socket_works(string $function, bool $send = true) {

      $this->json_request->status->init(true, $function, __CLASS__.__METHOD__.__FUNCTION__.__LINE__);
      return $this->json_request->response($send);
    }

    /**
     * @param bool|resource $state
     * @param string $function
     * @param bool $send
     * @return bool|false|string
     */
    public function socket_state($state, string $function, bool $send = true) {

        if($state === false)  $response = $this->socket_fail($function, $send);
        else                  $response = $this->socket_works($function, $send);

        return $response;
    }

    public function data_compact(array $workflow_transition_list, string $input){

      $this->json_request = $this->global_json_request;
      $this->json_request->workflow = $workflow_transition_list;
      $this->json_request->input = $input;
      $req = json_encode($input);

      $this->socket_state($req, 'json_encode');

      $req = self::crypt($req);

      $this->socket_state($req, 'crypt');

      return $req;
    }

    public function data_uncompact($buf){

        // request uncrypt
        $req = self::uncrypt($buf);

        $this->socket_state($req, 'uncrypt');

        // request json decode
        $req = json_decode($req);
        $json_request = $req;

        return $json_request;
    }

    public function start(Json_request_workflow_simple $workflow)
    {
        $this->socket_init($workflow);

        $this->sock = socket_create(AF_UNIX, SOCK_DGRAM, 0);

        self::socket_state($this->sock, 'socket_create');

        $res = socket_bind($this->sock, self::$address, self::$port);

        self::socket_state($res, 'socket_bind');

        $break = false;

        while (is_file($this->mutex_stop_file) === false && $break === false)
        {

          // receive query
          $set = socket_set_block($this->sock);

          self::socket_state($set, 'socket_set_block');

          if($set === false) return false;

          $buf = '';
          $size = socket_recvfrom($this->sock, $buf, 65536, 0, $ipaddress);

          self::socket_state($size, 'socket_recvfrom');

          $this->json_request->relationship->init($ipaddress, self::$address, 'socket', 'connexion');

          self::$client_list[$this->json_request->relationship->from] = $this->json_request->relationship->from;

          self::socket_state(true, 'buff');

          $req = self::data_uncompact($buf);

          self::socket_state($req, 'gzuncompress');

          // request json decode
          $req = json_decode($req);
          $json_request = $req;

          self::socket_state($req, 'json_decode');

          // request run
          if ($json_request->worflow->name === 'connexion' && $json_request->worflow->action === 'quit') break;
          if ($json_request->worflow->name === 'connexion' && $json_request->worflow->action === 'shutdown') {

              socket_close($this->sock);

              self::socket_state($this->sock, 'socket_close');

              unset(self::$client_list[$this->json_request->relationship->from]);

              $res = false;
              $break = true;

              continue;
          }
          // @TODO traiter
        }
        self::$client_list = array();

        if($res === false) {

            socket_close($this->sock);

            self::socket_state($this->sock, 'socket_close');
        }
        return $res;
    }

    public static function start_daemon()
    {
        exec(self::$start_script_file . ' > /dev/null &');

        return true;
    }
}
