<?php

function resize_image($immagine="", $new_width="", $new_height="", $crop=false, $posizione="", $rewrite=false){
  //minitest
	/**
	* Ridimensiona l'immagine a seconda delle dimensioni e del modo specificato
	*
	* @param  $immagine  	Il path dell'immagine da resizare
	* @param  $new_width 	La larghezza dalla nuova immagine
	* @param  $new_height 	L'altezza dalla nuova immagine
	* @param  $crop 	Determina se l'immagine viene croppata o se vengono mantenute le proporzioni
	* @param  $posizione 	0 => ritorna immagine con le dimensioni reali dopo il resize.
	* 			LEFT-TOP, LEFT-CENTER, LEFT-BOTTOM, CENTER-TOP, CENTER-CENTER, CENTER-BOTTOM, RIGHT-TOP, RIGHT-CENTER, RIGHT-BOTTOM forzano le dimensioni specificate e posizionano la nuova immagine alla posizione specificata
	* @param  $rewrite 	Forza la riscrittura dell'immagine
	* @return Array
	*/
	
	$debug = false;
	$data_limite = mktime(0, 0, 0, 03, 14, 2013);
	
	$thumb_name = substr_replace(IMG_DIR.$immagine,"/_thumbs/".$new_width."x".$new_height."_",strrpos(IMG_DIR.$immagine,"/"),1);
	$thumb_name_url = substr_replace(IMG_URL.$immagine,"/_thumbs/".$new_width."x".$new_height."_",strrpos(IMG_URL.$immagine,"/"),1);
	$immagine_path = trim(IMG_DIR.$immagine);
	
	$extension = end( explode(".", $immagine) );
	$transparent_extensione = array("png", "PNG", "gif", "GIF");

	if( ( file_exists($immagine_path) && $immagine<>NULL && $immagine!="" && strlen($immagine)>4 ) ){
		
		if( file_exists($thumb_name) ){
			$data_creazione_thumb = filectime($thumb_name);
			
			if( $data_limite > $data_creazione_thumb ){
				//la thumb è più vecchia della mia data limite
				$rewrite = true;
				$debug ? print "riscrivo perchè ".date('YmdHis', $data_limite)." > ".date('YmdHis', $data_creazione_thumb)."<hr/>" : "";
			}
		}
		
		if( !file_exists($thumb_name) or $rewrite ){
			
			$debug ? print "scrivo immagine<hr/>" : "";
		
			//dimensioni immagine originale
			list( $old_width, $old_height ) = getimagesize($immagine_path);
			
			//formato immagine originale
			$old_ratio = round( ($old_width/$old_height), 2 );
			
			//formato nuova immagine
			$new_ratio = round( ($new_width/$new_height), 2 );
			
			if($crop){
				//immagine è da croppare
				$debug ? print "crop<hr/>" : "";
				
				if( $old_ratio >= $new_ratio ){
					//la vecchia immagine è più orizzontale di quella nuova. Resizo la larghezza ed aggiusto altezza di conseguenza
					$w_to_resize = round( ($new_height * $old_width) / $old_height );
					$h_to_resize = $new_height;
				}else{
					//la vecchia immagine è più verticale di quella nuova. Resizo l'altezza ed aggiusto larghezza di conseguenza
					$w_to_resize = $new_width;
					$h_to_resize = round( ($old_height * $new_width) / $old_width );
				}
				
				$debug ? print "resize a $w_to_resize x $h_to_resize<hr/>" : "";
				
				$thumb = new Imagick();
				$thumb->readImage($immagine_path);
				$thumb->resizeImage($w_to_resize,$h_to_resize,Imagick::FILTER_LANCZOS,1,1);
				//posiziono l'immagine a seconda del parametro
				$pos_x = 0;
				$pos_y = 0;
				if( $posizione == "LEFT-TOP" ){
					$pos_x = 0;
					$pos_y = 0;
				}
				if( $posizione == "LEFT-CENTER" ){
					$pos_x = 0;
					$pos_y = ($new_height - $thumb->getImageHeight()) / 2;
				}
				if( $posizione == "LEFT-BOTTOM" ){
					$pos_x = 0;
					$pos_y = $new_height - $thumb->getImageHeight();
				}
				if( $posizione == "CENTER-TOP" ){
					$pos_x = ($new_width - $thumb->getImageWidth()) / 2;
					$pos_y = 0;
				}
				if( $posizione == "CENTER-CENTER" ){
					$pos_x = ($new_width - $thumb->getImageWidth()) / 2;
					$pos_y = ($new_height - $thumb->getImageHeight()) / 2;
				}
				if( $posizione == "CENTER-BOTTOM" ){
					$pos_x = ($new_width - $thumb->getImageWidth()) / 2;
					$pos_y = $new_height - $thumb->getImageHeight();
				}
				if( $posizione == "RIGHT-TOP" ){
					$pos_x = $new_width - $thumb->getImageWidth();
					$pos_y = 0;
				}
				if( $posizione == "RIGHT-CENTER" ){
					$pos_x = $new_width - $thumb->getImageWidth();
					$pos_y = ($new_height - $thumb->getImageHeight()) / 2;
				}
				if( $posizione == "RIGHT-BOTTOM" ){
					$pos_x = $new_width - $thumb->getImageWidth();
					$pos_y = $new_height - $thumb->getImageHeight();
				}
				
				$debug ? print "crop a $pos_x x $pos_y<hr/>" : "";
				
				$thumb->cropImage($new_width, $new_height, abs(round($pos_x)), abs(round($pos_y)) );
				$thumb->writeImage($thumb_name);
				$thumb->clear();
				$thumb->destroy();
				
			}else{
				//immagine mantiene le proporzioni
				$debug ? print "resize normale<hr/>" : "";
				
				if( $old_ratio >= $new_ratio ){
					//la vecchia immagine è più orizzontale di quella nuova. Resizo la larghezza ed aggiusto altezza di conseguenza
					$w_to_resize = $new_width;
					$h_to_resize = round( ($old_height * $new_width) / $old_width );
				}else{
					//la vecchia immagine è più verticale di quella nuova. Resizo l'altezza ed aggiusto larghezza di conseguenza
					$w_to_resize = round( ($new_height * $old_width) / $old_height );
					$h_to_resize = $new_height;
				}
				
				$debug ? print "resize a $w_to_resize x $h_to_resize<hr/>" : "";
				
				$thumb = new Imagick();
				$thumb->readImage($immagine_path);
				$thumb->resizeImage($w_to_resize,$h_to_resize,Imagick::FILTER_LANCZOS,1,1);
				if( $posizione == "" ){
					//resize normale
					$thumb->writeImage($thumb_name);
					
					$debug ? print "img posizione $posizione<hr/>" : "";
				}else{
					//posiziono l'immagine a seconda del parametro
					$pos_x = 0;
					$pos_y = 0;
					if( $posizione == "LEFT-TOP" ){
						$pos_x = 0;
						$pos_y = 0;
					}
					if( $posizione == "LEFT-CENTER" ){
						$pos_x = 0;
						$pos_y = ($new_height - $thumb->getImageHeight()) / 2;
					}
					if( $posizione == "LEFT-BOTTOM" ){
						$pos_x = 0;
						$pos_y = $new_height - $thumb->getImageHeight();
					}
					if( $posizione == "CENTER-TOP" ){
						$pos_x = ($new_width - $thumb->getImageWidth()) / 2;
						$pos_y = 0;
					}
					if( $posizione == "CENTER-CENTER" ){
						$pos_x = ($new_width - $thumb->getImageWidth()) / 2;
						$pos_y = ($new_height - $thumb->getImageHeight()) / 2;
					}
					if( $posizione == "CENTER-BOTTOM" ){
						$pos_x = ($new_width - $thumb->getImageWidth()) / 2;
						$pos_y = $new_height - $thumb->getImageHeight();
					}
					if( $posizione == "RIGHT-TOP" ){
						$pos_x = $new_width - $thumb->getImageWidth();
						$pos_y = 0;
					}
					if( $posizione == "RIGHT-CENTER" ){
						$pos_x = $new_width - $thumb->getImageWidth();
						$pos_y = ($new_height - $thumb->getImageHeight()) / 2;
					}
					if( $posizione == "RIGHT-BOTTOM" ){
						$pos_x = $new_width - $thumb->getImageWidth();
						$pos_y = $new_height - $thumb->getImageHeight();
					}
					
					$debug ? print "img gravity a $pos_x x $pos_y<hr/>" : "";
					
					$imageOutput = new Imagick();
					if( in_array($extension, $transparent_extensione) ){
						$imageOutput->newImage($new_width, $new_height, "none"); // Make the container with transparency
					}else{
						$imageOutput->newImage($new_width, $new_height, "white");
					}
					$imageOutput->compositeImage($thumb, Imagick::COMPOSITE_ADD, abs(round($pos_x)), abs(round($pos_y)) ); // Center the resized image inside of the container
					$imageOutput->writeImage($thumb_name);
				}
				$thumb->clear();
				$thumb->destroy();
			}	

		} //fine if rewrite

		
		if ( file_exists($thumb_name) ){
			//genero valori da ritornare
			$list=getimagesize($thumb_name);
			$height=$list[1];
			$width=$list[0];
		}
		
		$ret = array( $thumb_name_url, $width, $height );
		return $ret;
	}else return false;
}

?>
