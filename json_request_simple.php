<?

Class Json_request_simple {

    public static $header = 'Content-Type: application/json; charset=utf-8';
    public $input;
    public $output;
    public $ref_list = array();

    /**
     * @var Json_request_status_simple
     */
    public $status;
    /**
     * @var Json_request_workflow_simple
     */
    public $workflow;
    /**
     * @var Json_request_relationship_simple
     */
    public $relationship;
    /**
     * @var Json_request_action_simple
     */
    public $action;

    public function __construct()
    {
        $this->status = new Json_request_status_simple();
        $this->workflow = new Json_request_workflow_simple();
        $this->relationship = new Json_request_relationship_simple();
        $this->action = new Json_request_action_simple();
    }

    public function response($send = true) {

      header(self::$header);

      $response_json = json_encode($this);

      if($send === true) {

        echo $response_json;
      }
      return $response_json;
    }
}
