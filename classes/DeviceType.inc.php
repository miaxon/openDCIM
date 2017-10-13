<?php

class DeviceType {
        var $TypeID;
        var $Type;
        public function __construct($typeid=false){
                    if($typeid){
                            $this->TypeID=$typeid;
                    }
                    return $this;
            }

	function MakeSafe(){
		$this->TypeId=intval($this->TypeId);
		$this->Type=sanitize($this->Type);
	}

	static function RowToObject($row){
		$ds=new DeviceType();
		$ds->TypeId=$row["TypeId"];
		$ds->Type=$row["Type"];

		return $ds;
	}
	function exec($sql){
		global $dbh;
		return $dbh->exec($sql);
	}

	function query($sql){
		global $dbh;
		return $dbh->query($sql);
	}
	
	function prepare($sql){
		global $dbh;
		return $dbh->prepare($sql);
	}
	
	function lastID() {
		global $dbh;
		return $dbh->lastInsertID();
	}

	function createType() {
		global $dbh;

		$this->MakeSafe();

		$sql="INSERT INTO fac_DeviceTypes SET Type=\"$this->Type\", 
			ColorCode=\"$this->ColorCode\"";
	
		if($this->exec($sql)){
			$this->TypeId=$dbh->lastInsertId();
		}else{
			$info=$dbh->errorInfo();

			error_log("PDO Error::createType {$info[2]}");
			return false;
		}
		
		return $this->TypeId;
	}

	function getType() {
		$this->MakeSafe();

		$sql="SELECT * FROM fac_DeviceTypes WHERE TypeId=$this->TypeId;";

        if($row=$this->query($sql)->fetch()){
            foreach(DeviceType::RowToObject($row) as $prop=>$value){
                $this->$prop=$value;
            }

            return true;
        }else{
            // Kick back a blank record if the TypeId was not found
            foreach($this as $prop=>$value){
                if($prop!='TypeId'){
                    $this->$prop = '';
                }
            }

            return false;
        }
	}

	static function getTypeList($Indexed = false ) {
		global $dbh;

		$st = $dbh->prepare( "SELECT * FROM fac_DeviceTypes ORDER BY Type ASC;" );
		$args = array();

		$st->setFetchMode( PDO::FETCH_CLASS, "DeviceTypes" );
		$st->execute( $args );

		$sList = array();

		while ( $row = $st->fetch() ) {
			if ( $Indexed ) {
				$sList[$row->TypeId]=$row;
			} else {
				$sList[] = $row;
			}
		}	

		return $sList;	
	}

	static function getTypeNames() {
		global $dbh;

		$st = $dbh->prepare( "select * from fac_DeviceTypes order by Type ASC" );

		$st->execute( array() );
		$sList = array();

		while ( $row = $st->fetch() ) {
			$sList[] = $row["Type"];
		}

		return $sList;
	}

	function updateType() {
		$this->MakeSafe();

		$oldstatus=new DeviceType($this->TypeId);
		$oldstatus->getType();

		$sql="UPDATE fac_DeviceTypes SET Type=\"$this->Type\", 
			ColorCode=\"$this->ColorCode\" WHERE TypeId=\"$this->TypeId\";";

		if($this->TypeId==0){
			return false;
		}else{
			(class_exists('LogActions'))?LogActions::LogThis($this,$oldstatus):'';
			$this->query($sql);

			return true;
		} 
	}

	function removeType() {
		// Type = Reserved
		// Type = Disposed
		// Both of which are reserved, so they can't be removed unless you go to the db directly, in which case, you deserve a broken system

		// Also, don't go trying to remove a status that doesn't exist
		$ds=new DeviceType($this->TypeId);
		if(!$ds->getType() || $ds->Type == "Reserved" || $ds->Type == "Disposed" ) {
			return false;
		}

		// Need to search for any devices that have been assigned the given status - if so, don't allow the delete
		$srchDev=new Device();
		$srchDev->Type=$ds->Type;
		$dList=$srchDev->Search();

		if(count($dList)==0){
			$st=$this->prepare( "delete from fac_DeviceTypes where TypeId=:TypeId" );
			return $st->execute( array( ":TypeId"=>$this->TypeId ));
		}

		return false;
	}
}
?>
