<?

Class Json_request_workflow_simple {

    public $name;
    public $step;
    public $state;

    public function init(string $name, string $step, string $state){

        $this->name = $name;
        $this->step = $step;
        $this->state = $state;

        return true;
    }
}
