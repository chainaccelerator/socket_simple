<?

/**
 * Trait Socket_client_push_simple
 */
Trait Socket_client_push_broadcast_simple {

    use Socket_node_push_simple;

    private static $socket_client_push_broadcast_address_algo = 'sha256';

    public function socket_client_push_broadcast_send(string $request_address_api_uri, array $workflow_transition_list)
    {
        $res = socket_bind($this->sock, $this->client_side_sock);

        self::socket_state($res, 'socket_bind');

        $req = self::socket_data_compact($workflow_transition_list, $request_address_api_uri);

        $push_address = hash(self::$socket_client_push_broadcast_address_algo, microtime().$req.json_encode($res).$request_address_api_uri);

        $req = $push_address.$request_address_api_uri.'/'.$req;

        self::socket_state($req, 'crypt_msg');

        $len = strlen($req);
        $bytes_sent = socket_sendto($this->sock, $req, $len, 0, $this->server_side_sock);
        $res = true;

        if($bytes_sent === -1 || $bytes_sent === false) $res = false;

        self::socket_state($res, 'len');

        self::$client_list = array();
        socket_close($this->sock);

        self::socket_state($this->sock, 'socket_close');

        return $push_address;
    }

    public function socket_client_push_broadcast_request_count_get(string $request_address_api_uri){

        // @TODO get


        return 3;
    }
}
