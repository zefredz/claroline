<?php // $Id$

/*
	To use this function :
	* include this file in your script
	* Call the main function : $id1 = mp3_id( "01.mp3" );
	* all informations are in an array in $id1
 
 */
// This function parse ID3 tag from MP3 file. It's quite fast.
// syntax mp3_id(filename)
// function will return -1 if file not exists or no frame cynch found at the beginning of file. i realized that some songs downloaded thru gnutella have about four lines of text info at the beginning. it seepms players can handle. so i will implement it later.
// variable bitrates are not yet implemented, as they are quite slow to check. you can find them to read lot of first frames and check their bitrates. If theyre not the same, its variable bitrate. and also you then cannot compute real song length, unless you will scan the whole file for frames and compute its length... (at least what i read)
// there is second version of ID3 tag which is tagged at the beginning of the file and its quite large. you can learnt more about at http://www.id3.org/. i dont finding this so interesting. there are too good things on new version: you can write more than 30 chars at field and the tag is on the beginning of file. there are so many fields in v2 that i found really unusefull in many case. while it seems that id3v2 will still write tag v1 at the end, i can see no reason why to implement it, cos it is really 'slow' to parse all these informations.

// You can use 'genres' to determine what means the 'genreid' number. if you think you will not need it, delete it to. And also we need to specify all variables for mp3 header.

 // end
// New function by Luca (18/02/01): devel@lluca.com

 /* This function strip null chars from a string. For example: 
  * If you get a 30 chars string for the comment, but the comment name has 4 chars like "Moon",
  * and it has a track number (ID3 1.1), you get "Moon<all_null_caracters><track#>",
  * compleating the 30 chars, in hex:
  * "4D6F6F6E0000000000000000000000000000000000000000000000000006" where just
  *  ~~~~~~~~                                                  ==
  *     \-------> this is useful data. <-----------------------/
  * This function looks for the first null char, and cut the string
  * so it converts this string to "4D6F6F6E" = "Moon". And then you can look if there is a track number.
  * This function strips trailing spaces too.
  */
 function strip_nulls( $str ) {
   $res = explode( chr(0), $str );
   return chop( $res[0] );
 }

// end


// here goes the function

 function mp3_id($file) {

 	// see http://www.mp3-tech.org/programmer/frame_header.html
	$version=Array("00"=>2.5, "10"=>2, "11"=>1);
	$layer  =Array("01"=>3, "10"=>2, "11"=>1);
	$crc=Array("Yes", "No");
	$bitrate["0001"]=Array(32,32,32,32,8,8);
	$bitrate["0010"]=Array(64,48,40,48,16,16);
	$bitrate["0011"]=Array(96,56,48,56,24,24);
	$bitrate["0100"]=Array(128,64,56,64,32,32);
	$bitrate["0101"]=Array(160,80,64,80,40,40);
	$bitrate["0110"]=Array(192,96,80,96,48,48);
	$bitrate["0111"]=Array(224,112,96,112,56,56);
	$bitrate["1000"]=Array(256,128,112,128,64,64);
	$bitrate["1001"]=Array(288,160,128,144,80,80);
	$bitrate["1010"]=Array(320,192,160,160,96,96);
	$bitrate["1011"]=Array(352,224,192,176,112,112);
	$bitrate["1100"]=Array(384,256,224,192,128,128);
	$bitrate["1101"]=Array(416,320,256,224,144,144);
	$bitrate["1110"]=Array(448,384,320,256,160,160);
	$bitrate["1111"]=Array("bad","bad","bad","bad","bad","bad");
	$bitindex=Array("1111"=>"0","1110"=>"1","1101"=>"2","1011"=>"3","1010"=>"4","1001"=>"5","0011"=>"3","0010"=>"4","0001"=>"5");
	// second index represent the version 11 = mpeg1 , 10 = mpeg2, 00 = mpeg2.5
	$freq["00"]=Array("11"=>44100,"10"=>22050,"00"=>11025);
	$freq["01"]=Array("11"=>48000,"10"=>24000,"00"=>12000);
	$freq["10"]=Array("11"=>32000,"10"=>16000,"00"=>8000);
	$freq["11"]=Array("11"=>"reserved","10"=>"reserved","00"=>"reserved");
	$mode=Array("00"=>"Stereo","01"=>"Joint stereo","10"=>"Dual channel","11"=>"Mono");
	$copy=Array("No","Yes");
	$padding = Array(0,1);
	
	
   if(!$f=@fopen($file, "r")) { return -1; break; } else {

// read first 4 bytes from file and determine if it is wave file if so, header begins five bytes after word 'data'

     $tmp=fread($f,4);
     if($tmp=="RIFF") {
       $idtag["ftype"]="Wave";
       fseek($f, 0);
       $tmp=fread($f,128);
       $x=StrPos($tmp, "data");
       fseek($f, $x+8);
       $tmp=fread($f,4);
     }

// now convert those four bytes to BIN. maybe it can be faster and easier. dunno how yet. help?

	$bajt = '';
	for($y=0;$y<4;$y++)
	{
		$x=decbin(ord($tmp[$y]));
		for($i=0;$i<(8-StrLen($x));$i++) {$x.="0";}
		$bajt.=$x;
	}

// every mp3 framesynch begins with eleven ones, lets look for it. if not found continue looking for some 1024 bytes (you can search multiple for it or you can disable this, it will speed up and not many mp3 are like this. anyways its not standart)

//     if(substr($bajt,1,11)!="11111111111") {
//        return -1;
//        break;
//     }
     if(substr($bajt,1,11)!="11111111111") {
       fseek($f, 4);
       $tmp=fread($f,2048);
         for($i=0;$i<2048;$i++){
           if(ord($tmp[$i])==255 && substr(decbin(ord($tmp[$i+1])),0,3)=="111") {
              $tmp=substr($tmp, $i,4);
              $bajt="";
              for($y=0;$y<4;$y++) {
                $x=decbin(ord($tmp[$y]));
                for($i=0;$i<(8-StrLen($x));$i++) {$x.="0";}
                $bajt.=$x;
              }
              break;
            }
          }
     }
     if($bajt=="") {
        return -1;
        break;
     }


// now parse all the info from frame header

	$len=filesize($file);
	$idtag["version"]=$version[substr($bajt,11,2)];
	$idtag["layer"]=$layer[substr($bajt,13,2)];
	$idtag["crc"]=$crc[$bajt[15]];
	$idtag["bitrate"]=$bitrate[substr($bajt,16,4)][$bitindex[substr($bajt,11,4)]];
	if( $idtag["bitrate"] == "bad" || $idtag["bitrate"] == 0 || is_null($idtag["bitrate"]) ) return 0;
	$idtag["frequency"]=$freq[substr($bajt,20,2)][substr($bajt,11,2)];
	if( $idtag["frequency"] == "reserved" || is_null($idtag["frequency"]) ) return 0;
	$idtag["padding"]=$copy[$bajt[22]];
	$idtag["mode"]=$mode[substr($bajt,24,2)];
	$idtag["copyright"]=$copy[$bajt[28]];
	$idtag["original"]=$copy[$bajt[29]];
	
	// lets count length of the song	
	if($idtag["layer"]==1) 
	{
	  $fsize=(12*($idtag["bitrate"]*1000)/$idtag["frequency"]+$idtag["padding"])*4; 
	}
	else 
	{
	  $fsize=144*(($idtag["bitrate"]*1000)/$idtag["frequency"]+$idtag["padding"]);
	}
	
	// Modified by Luca (18/02/01): devel@lluca.com
	$idtag["length_sec"]=round($len/Round($fsize)/38.37);
	// end
	$idtag["length"]=date("i:s",round($len/Round($fsize)/38.37));

// now lets see at the end of the file for id3 tag. if exists then  parse it. if file doesnt have an id 3 tag if will return -1 in field 'tag' and if title is empty it returns file name.

     if(!$len) $len=filesize($file);
     fseek($f, $len-128);
     $tag = fread($f, 128);
     if(Substr($tag,0,3)=="TAG") {
       $idtag["file"]=$file;
       $idtag["tag"]=-1;
       // Modified by Luca (18/02/01): devel@lluca.com
       $idtag["title"]=strip_nulls( Substr($tag,3,30) );
       $idtag["artist"]=strip_nulls( Substr($tag,33,30) );
       $idtag["album"]=strip_nulls( Substr($tag,63,30) );
       $idtag["year"]=strip_nulls( Substr($tag,93,4) );
       $idtag["comment"]=strip_nulls( Substr($tag,97,30) );
       // If the comment is less than 29 chars, we look for the presence of a track #
       if ( strlen( $idtag["comment"] ) < 29 ) {
         if ( Ord(Substr($tag,125,1)) == chr(0) ) // If char 125 is null then track (maybe) is present
           $idtag["track"]=Ord(Substr($tag,126,1));
         else // If not, we are sure is not present.
           $idtag["track"]=0;
       } else { // If the comment is 29 or 30 chars long, there's no way to put track #
         $idtag["track"]=0;
       }
       // end
       $idtag["filesize"]=$len;
     } else {
       $idtag["tag"]=0;
     }

	// close opened file and return results.

   if( !isset($idtag["title"]) ) {
     $idtag["title"]=Str_replace("\\","/", $file);
     $idtag["title"]=substr($idtag["title"],strrpos($idtag["title"],"/")+1, 255);
   }
   fclose($f);
   return $idtag;
   }
 }



// end

?>
