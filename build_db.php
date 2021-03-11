<?php
	// 176 Kilobyte
	// 179877 byte


	
	function getStringBetween($str,$from,$to)
	{
		$sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
		return substr($sub,0,strpos($sub,$to));
	}

	
	
	$path = "./";

	if ($handlepath = opendir($path)) {
		while (false !== ($file = readdir($handlepath))) {
			if ('.' === $file) continue;
			if ('..' === $file) continue;
			if (strpos($file, '.data') ) continue;
			
			
			// do something with the file
			// ############################################
			// ############################################
			if (strpos($file, '.meta')) {
				// echo $file; // "Checking the existence of the empty string will always return true";
			
			
			
				$handle = fopen($file, "r");
				$count = 0;
				
				
				$filenameonefile = "";
				$sizeonefile = "";
				$inodeonefile = "";
				$atimeonefile = "";
				$mtimeonefile = "";
				$ctimeonefile = "";
				$isdirectory = false;
				
				if ($handle) {
					while (($line = fgets($handle)) !== false) {
						// process the line read.
						switch ($count) {
							case 0:
								// filename
								$filenameonefile = substr(   str_replace("  File:","",$line)   , 1, -1); 
								break;
							case 1:
								// size
								$sizeonefile = intval(getStringBetween($line,"Size:","Blocks:")); 
								if(strpos($line, 'directory')){
									$isdirectory = true;
								}
								break;
							case 2:
								// inode
								$inodeonefile = intval(getStringBetween($line,"Inode:","Links:")); 
								break;
							case 4:
								// Access time
								$parta = str_replace("Access: ","",$line);
								// echo substr($parta, 0, 19) ;
								// die();
								$atimeonefile =  strtotime (    substr($parta, 0, 19)   );
								// $date2 = new DateTimeImmutable($parta);
								// echo $date2->format('U');
								//
								//
								//
								// $partb = str_replace(" +0100","",$parta);
								// $atimeonefile = strtotime(   $partb   ) ; 
								// echo $atimeonefile;
								// echo $atimeonefile;
								break;
							case 5:
								// Modify time
								$parta = str_replace("Modify: ","",$line);
								$mtimeonefile =  strtotime (    substr($parta, 0, 19)   );
								// $date2 = new DateTimeImmutable($parta);
								// echo $date2->format('U');
								break;
							case 6:
								// Change time
								$parta = str_replace("Change: ","",$line);
								$ctimeonefile =  strtotime (    substr($parta, 0, 19)   );
								// $date2 = new DateTimeImmutable($parta);
								// echo $date2->format('U');
								break;
								
								
						}
						$count = $count + 1;
					}
					fclose($handle);
				} else {
					// error opening the file.
				}
			
				
				if ($count == 0 ) continue;
			
			}
			
			
			
			
	
			$filename = $filenameonefile; //     "/private/var/db/diagnostics/Persist/00000000000000b6.tracev3";
			
			$filepart1 = str_replace(".meta","",$file);
			
			
			$filesize = 0;
			$filesize = filesize ( "./".$filepart1.".data" );
			if($filesize == 0) continue; // TODO remove this workaround after bug-fixing sauvegardeEx
			if($isdirectory == true) continue; // TODO remove workaround Sauvegarde is not able to handle new directories properly
			$runs = ceil(   filesize ( "./".$filepart1.".data" ) / 135168   ) ;
			// echo $runs;  // 132000 bytes sind ca 132kB
			$hasharray = array();
			$data_array = array();
			
			
			for ( $x = 0; $x < $runs; $x++ ){
				// 1056000 sind 132000 bytes
				$outputhash = shell_exec('dd skip='.$x.' count=1 if=./'.$filepart1.'.data bs=132k | sha256sum | xxd -r -p | base64 -w 0 > hash.txt');
				$outputhash = shell_exec('cat hash.txt');
				// echo "<pre>$outputhash</pre>";
				array_push($hasharray, $outputhash);

				
				
				$outputdata = shell_exec('dd skip='.$x.' count=1 if=./'.$filepart1.'.data bs=132k | base64 -w 0 > data.txt');
				$outputdata = shell_exec('cat data.txt');
				// echo "<pre>$outputdata</pre>";
				
				$filesizeofoneblock = 135168;
				if( ( $filesize - (135168 * $x) ) < 135168    ){
					$filesizeofoneblock = $filesize - (135168 * $x) ;
				}
				
				
				$post_data = array('hash' => $outputhash,
				'data' => $outputdata,
				'size' => $filesizeofoneblock,
				'cmptype' => 0,
				'uncmpsize' => $filesizeofoneblock);
				array_push( $data_array, $post_data );
				
				
			}


			
			
			
			
			
			$db = new SQLite3('./filecache2.db');
			
			
			// $db->exec("CREATE TABLE cars(id INTEGER PRIMARY KEY, name TEXT, price INT)");
			$data ='{"msg_id": 1, "filetype": 1, "mode": 33188, "atime": '.$atimeonefile.', "ctime": '.$ctimeonefile.', "mtime": '.$mtimeonefile.', "fsize": '.$filesize.', "inode": '.$inodeonefile.', "owner": "root", "group": "root", "uid": 0, "gid": 0, "name": "'.$filename.'", "link": "", "hostname": "iphone11", "data_sent": false, "hash_list": '.json_encode ($hasharray).'}';
			// echo $data;
			$db->exec("INSERT INTO buffers(url, data) VALUES('/Meta.json', '$data')");
			
			
			
			$datadata = '{"data_array": '.json_encode ( $data_array ).'}';
			// echo $datadata;
			$db->exec("INSERT INTO buffers(url, data) VALUES('/Data_Array.json', '$datadata')");
			
			
			
			
			
			$datadata_example = '{"data_array": [{"hash": "BmavODPex973CGxVdDmu3etPKdLZEjImUdTHxI90vPI=", "data": "QH8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=", "size": 512, "cmptype": 0, "uncmpsize": 512}, {"hash": "B2onx55azio9R/ndLoPk/26ohys8Ihj2bJK4m1XzZWA=", "data": "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=", "size": 512, "cmptype": 0, "uncmpsize": 512}, {"hash": "B2onx55azio9R/ndLoPk/26ohys8Ihj2bJK4m1XzZWA=", "data": "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=", "size": 512, "cmptype": 0, "uncmpsize": 512}, {"hash": "B2onx55azio9R/ndLoPk/26ohys8Ihj2bJK4m1XzZWA=", "data": "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=", "size": 512, "cmptype": 0, "uncmpsize": 512}]}';
			
					
					
					
					
					
					
					
					
					
					
			
			
			
			
			
			
			
			
			
			
			// ############################################
			// ############################################
		}
		closedir($handlepath);
	}
	
	


	
	
?>