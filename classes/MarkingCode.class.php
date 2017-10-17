<?php

class MarkingCode {
    var $MarkingID;
    var $Name;
    public function __construct($markingid=false){
                if($markingid){
                        $this->MarkingID=$markingid;
                }
                return $this;
        }

    function MakeSafe(){
            $this->MarkingID=intval($this->MarkingID);
            $this->Name=sanitize($this->Name);
    }

    static function RowToObject($row){
            $ds=new MarkingCode();
            $ds->MarkingID=$row["MarkingID"];
            $ds->Name=$row["Name"];

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

    function createName() {
            global $dbh;

            $this->MakeSafe();

            $sql="INSERT INTO fac_MarkingCodes SET Name=\"$this->Name\"";

            if($this->exec($sql)){
                    $this->MarkingID=$dbh->lastInsertId();
            }else{
                    $info=$dbh->errorInfo();

                    error_log("PDO Error::createType {$info[2]}");
                    return false;
            }

            return $this->MarkingID;
    }

    function getName() {
            $this->MakeSafe();

            $sql="SELECT * FROM fac_MarkingCodes WHERE MarkingID=$this->MarkingID;";

            if($row=$this->query($sql)->fetch()){
                foreach(MarkingCode::RowToObject($row) as $prop=>$value){
                    $this->$prop=$value;
                }

                return true;
            }else{
                // Kick back a blank record if the TypeId was not found
                foreach($this as $prop=>$value){
                    if($prop!='MarkingID'){
                        $this->$prop = '';
                    }
                }

                return false;
            }
    }
    static function getMarkingName($markingid){
        global $dbh;
		
        if($markingid == 0)
            return "";
		$sql="SELECT Name FROM fac_MarkingCodes WHERE MarkingID=$markingid;";
		
		$markingName="";
		foreach($dbh->query($sql) as $row){
                    $markingName = $row["Name"];
		}		
		return sanitize($markingName);
     }
    static function getNameList($Indexed = false ) {
            global $dbh;

            $st = $dbh->prepare( "SELECT * FROM fac_MarkingCodes ORDER BY Name ASC;" );
            $args = array();

            $st->setFetchMode( PDO::FETCH_CLASS, "MarkingCode" );
            $st->execute( $args );

            $sList = array();

            while ( $row = $st->fetch() ) {
                    if ( $Indexed ) {
                            $sList[$row->MarkingID]=$row;
                    } else {
                            $sList[] = $row;
                    }
            }	

            return $sList;	
    }

    static function getMarkingNames() {
            global $dbh;

            $st = $dbh->prepare( "select * from fac_MarkingCodes order by Name ASC" );

            $st->execute( array() );
            $sList = array();

            while ( $row = $st->fetch() ) {
                    $sList[] = $row["Name"];
            }

            return $sList;
    }

    function updateName() {
            $this->MakeSafe();

            $oldstatus=new MarkingCode($this->MarkingID);
            $oldstatus->getName();

            $sql="UPDATE fac_MarkingCodes SET Name=\"$this->Name\" WHERE MarkingID=\"$this->MarkingID\";";

            if($this->MarkingID==0){
                    return false;
            }else{
                    (class_exists('LogActions'))?LogActions::LogThis($this,$oldstatus):'';
                    $this->query($sql);

                    return true;
            } 
    }

    function removeName() {
            // Type = Reserved
            // Type = Disposed
            // Both of which are reserved, so they can't be removed unless you go to the db directly, in which case, you deserve a broken system

            // Also, don't go trying to remove a marking that doesn't exist
            $ds=new MarkingCode($this->MarkingID);
            if(!$ds->getName()) {
                    return false;
            }

            // Need to search for any devices that have been assigned the given status - if so, don't allow the delete
            $srchDev=new DevicePorts();
            $srchDev->Marking=$ds->Name;
            $dList=$srchDev->Search();

            if(count($dList)==0){
                    $st=$this->prepare( "delete from fac_DeviceTypes where MarkingID=:MarkingID" );
                    return $st->execute( array( ":MarkingID"=>$this->MarkingID ));
            }

            return false;
    }
}
?>
