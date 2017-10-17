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
		$this->TypeID=intval($this->TypeID);
		$this->Type=sanitize($this->Type);
	}

	static function RowToObject($row){
		$ds=new DeviceType();
		$ds->TypeID=$row["TypeID"];
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

		$sql="INSERT INTO fac_DeviceTypes SET Type=\"$this->Type\"";
	
		if($this->exec($sql)){
			$this->TypeID=$dbh->lastInsertId();
		}else{
			$info=$dbh->errorInfo();

			error_log("PDO Error::createType {$info[2]}");
			return false;
		}
		
		return $this->TypeID;
	}

	function getType() {
		$this->MakeSafe();

		$sql="SELECT * FROM fac_DeviceTypes WHERE TypeID=$this->TypeID;";

        if($row=$this->query($sql)->fetch()){
            foreach(DeviceType::RowToObject($row) as $prop=>$value){
                $this->$prop=$value;
            }

            return true;
        }else{
            // Kick back a blank record if the TypeID was not found
            foreach($this as $prop=>$value){
                if($prop!='TypeID'){
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

		$oldstatus=new DeviceType($this->TypeID);
		$oldstatus->getType();

		$sql="UPDATE fac_DeviceTypes SET Type=\"$this->Type\" WHERE TypeID=\"$this->TypeID\";";

		if($this->TypeID==0){
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
		$ds=new DeviceType($this->TypeID);
		if(!$ds->getType() ) {
			return false;
		}

		// Need to search for any devices that have been assigned the given status - if so, don't allow the delete
		$srchDev=new Device();
		$srchDev->Type=$ds->Type;
		$dList=$srchDev->Search();

		if(count($dList)==0){
			$st=$this->prepare( "delete from fac_DeviceTypes where TypeID=:TypeID" );
			return $st->execute( array( ":TypeID"=>$this->TypeID ));
		}

		return false;
	}
}
?>
