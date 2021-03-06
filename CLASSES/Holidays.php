<?php
require_once('../../CLASSES/ClassParent.php');
class Holidays extends ClassParent {

	var $pk = NULL;
	var $name = NULL;
    var $type = NULL;
	var $datex = NULL;
	var $archived = NULL;


	public function __construct(
                                    $pk,
    								$name,
                                    $type,
    								$datex,
    								$archived
                                ){
        
        $fields = get_defined_vars();
        
        if(empty($fields)){
            return(FALSE);
        }

        //sanitize
        foreach($fields as $k=>$v){
            $this->$k = pg_escape_string(trim(strip_tags($v)));
        }

        return(true);
    }

    public function get_holidays($data){
        foreach($data as $k=>$v){
            $data[$k] = pg_escape_string(trim(strip_tags($v)));
        }

        $str=$data['searchstring'];

        if ($str){
            $where = " AND (name ILIKE '$str%')";
        }
        
        $sql = <<<EOT
                select 
                    pk,
                    name,
                    type,
                    datex ::date as datex,
                    archived
                from holidays
                where archived = $this->archived
                $where
                order by pk
                ;
EOT;

        return ClassParent::get($sql);
    }

    public function get_active_holidays($extra){      
        foreach($extra as $k=>$v){
            $extra[$k] = pg_escape_string(trim(strip_tags($v)));
        }

        $date_from = $extra['date_from'];
        $date_to = $extra['date_to'];

        $sql = <<<EOT
                select 
                    pk,
                    name,
                    type,
                    datex::date as datex,
                    archived
                from holidays
                where archived = false
                    and datex::date between '$date_from' and '$date_to'
                order by datex
                ;
EOT;

        return ClassParent::get($sql);
    }

    public function save_holidays($data){
    	
    	$name= $data['holiday_name'];
        $type = $data['holiday_type'];
    	$date= $data['new_date'];
    	$approver = $data['creator_pk'];

        $sql = 'begin;';
		$sql .= <<<EOT
                insert into holidays
                (    
                    name,
                    type,
                    datex,
                    created_by
                )  
                values
                (
                    '$name',
                    '$type',
                    '$date',
                    $approver
                );
EOT;

        $sql .= <<<EOT
                insert into calendar
                (    
                   	location,
                    description,
                    time_from,
                    time_to,
                   	color,
                   	created_by
                )  
                values
                (
                    'N/A',
                    '$name',
                    '$date 00:00:00',
                    '$date 23:59:59',
                    '#fcfa90',
                    $approver
                );
EOT;
        $sql .= "commit;";
        return ClassParent::insert($sql);
    }


    public function update_holidays(){


        $sql = <<<EOT
                UPDATE holidays set
                (
                    name,
                    type,
                    datex
                )
                =
                (
                    '$this->name',
                    '$this->type',
                    '$this->datex'
                )
                WHERE pk = $this->pk
                ;
EOT;

        return ClassParent::update($sql);
    }


    public function deactivate(){

        $sql = <<<EOT
                update holidays set 
                archived = true
                where pk = $this->pk;
EOT;

          return ClassParent::update($sql);
    }

    public function reactivate(){

        $sql = <<<EOT
                update holidays set 
                archived = false
                where pk = $this->pk;
EOT;

          return ClassParent::update($sql);
    }
}
?>