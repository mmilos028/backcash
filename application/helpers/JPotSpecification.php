<?php
class JPotSpecification{
	public $id;
	public $affiliate_id;
	public $impl_aff_id;
	public $pot_name_id;
	public $with_payback;
	public $tp_type;
	public $min_num_players;
	public $min_num_terminals;
	public $limit_level_bronze;
	public $percent_level_bronze;
	public $limit_level_silver;
	public $percent_level_silver;
	public $limit_level_gold;
	public $percent_level_gold;
	public $limit_level_platinum;
	public $percent_level_platinum;
	public $start_date;
	public $end_date;
	public $start_time;
	public $end_time;
	public function __construct($id, $affiliate_id, $impl_aff_id, $pot_name_id, $with_payback, $tp_type, $min_num_players,
	$min_num_terminals, $limit_level_bronze, $percent_level_bronze, $limit_level_silver,
	$percent_level_silver, $limit_level_gold, $percent_level_gold, $limit_level_platinum,
	$percent_level_platinum, $start_date, $end_date, $start_time, $end_time){
		$this->id = $id;
		$this->affiliate_id = $affiliate_id;
		$this->impl_aff_id = $impl_aff_id;
		$this->pot_name_id = $pot_name_id;
		$this->with_payback = $with_payback;
		$this->tp_type = $tp_type;
		$this->min_num_players = $min_num_players;
		$this->min_num_terminals = $min_num_terminals;
		$this->limit_level_bronze = $limit_level_bronze;
		$this->percent_level_bronze = $percent_level_bronze;
		$this->limit_level_silver = $limit_level_silver;
		$this->percent_level_silver = $percent_level_silver;
		$this->limit_level_gold = $limit_level_gold;
		$this->percent_level_gold = $percent_level_gold;
		$this->limit_level_platinum = $limit_level_platinum;
		$this->percent_level_platinum = $percent_level_platinum;
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		$this->start_time = $start_time;
		$this->end_time = $end_time;
	}
}
?>