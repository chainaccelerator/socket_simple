<?

Class Json_request_action_simple {

    public $type;
    public $resource_type;
    public $resource_id;
    public $name;

    public function init(string $type, string $resource_type, string $resource_id, string $name){

        $this->type = $type;
        $this->resource_type = $resource_type;
        $this->resource_id = $resource_id;
        $this->name = $name;

        return true;
    }
}
