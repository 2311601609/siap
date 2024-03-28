<?php namespace App\Libraries;

/*
 * Copyright(c)2011 Miguel Angel Nubla Ruiz (miguelangel.nubla@gmail.com). All rights reserved
 */

class My_oracle
  {
    
    /**
    * Copies an instance of CI
    */
    public function __construct()
    {
      
    }
	
	public function insert($table,$data,$set)
	{
		$columns=array_keys($data);
		$values=array_values($data);
		
		if($set!=0){
			foreach($values as $value){
				if(!in_array($value,$set)){
					$value="'".$value."'";
				}
				$new_values[]=$value;
			}
		}
		else{
			$new_values=$values;
		}
		
		return "insert into ".$table."(".implode(",",$columns).") values(".implode(",",$new_values).")";
		
	}
	
	public function update($table,$data,$set,$where)
	{
		$columns=array_keys($data);
		$values=array_values($data);
		$where_cols=array_keys($where);
		$where_vals=array_values($where);
		
		if($set!=0){
			foreach($values as $value){
				if(!in_array($value,$set)){
					$value="'".$value."'";
				}
				$new_values[]=$value;
			}
			foreach($where_vals as $where_val){
				if(!in_array($where_val,$set)){
					$where_val="'".$where_val."'";
				}
				$new_where_vals[]=$where_val;
			}
		}
		else{
			$new_values=$values;
		}
		
		for($i=0;$i<count($new_values);$i++){
			$new_data[]=$columns[$i]."=".$new_values[$i];
		}
		
		for($x=0;$x<count($new_where_vals);$x++){
			$new_where[$x]=$where_cols[$x]."=".$new_where_vals[$x];
		}
		
		return "update ".$table." set ".implode(",",$new_data)." where ".implode(" AND ",$new_where);
		
	}
	
}