<?

Class Json_request_relationship_simple {

    public $from;
    public $to;
    public $type;
    public $name;
    public $attribut_list = array();

    public function init(string $from, string $to, string $type, string $name, array $attribut_list = array()){

      $this->from = $from;
      $this->to = $to;
      $this->type = $type;
      $this->name = $name;
      $this->attribut_list = $attribut_list;

      return true;
    }
}
