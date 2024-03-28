<?php

	function koneksiRef() {
		$dbhost="10.0.10.20";
		$dbuser="root";
		$dbpass="170845"; 
		$dbname="ebmn_ref";
		$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $dbh;
	}


	$query = "SELECT *
			FROM tref_kd_kppn
			WHERE kdkanwil=:kdkanwil";
	
	try {
    	$db = koneksiRef();

		//exe query insert status
		$stmt = $db->prepare($query);
		$stmt->bindParam('kdkanwil', $_GET['kdkanwil']);
		$stmt->execute();
		$data=array();
		$i=0;
		while($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$data[$i]=$rows;			
			$i++;
		}
		
		echo json_encode($data);
		$db_select_tahun=null;
	} 
	catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}

?>