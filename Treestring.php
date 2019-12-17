<?php
// -----------------------------------
//  Treestring and Rnd String class  |
//  - - - - - - - - - - - - - - - -  |
// (c) by voodooEntity               |
//  - - - - - - - - - - - - - - - -  |
//  > irc.vooodoo.systems [ssl:6697] |
// -----------------------------------


class Treestring {
    
    //set the strength / insesity of the manipulation/obfuscation (increases function/string- size)
    private $secStrength = 15;
    
    //final hash
    private $hashIt = true;
    
    //target directory for vodoo files , for same directory just use false
    private $path;
    
    public function __construct($filePath,$secStrength = false) {
        if($secStrength && is_numeric($secStrength)) {
            $this->secStrength = $secStrength;
        }
        $this->path = $filePath
        $this->determineStrengthParams();
        $this->rand = new rnd();
    }
    
    private function determineStrengthParams() {
        $this->runMax=$this->secStrength*rand(10,15);
        $this->countMax = rand(97,113)*$this->secStrength;
        $this->mapShuffles = rand(20,40)*$this->secStrength;
    }
    
    public function buildKeyMap() {
        $a=range(33,126);
        for($i=0;$i<$this->mapShuffles;$i++) {
            shuffle($a);
        }
        $c="array(";
        $i=33;
        foreach($a as $b) {
            $c.=$i."=>"."'".$b."',";
            $i++;
        }
        $c =rtrim($c,",").")";
        return $c;
    }
    
    private function getFuncArray() {
        $arrFuncs=array(1=>array(
                               "string"=> '$str=strrev($str);'."\n"
                           ),
                        2=>array(
                               "string"=>'$a=str_split($str);'."\n".'$b="";'."\n".'$c=~{a}~;'."\n".'foreach($a as $d) {'."\n".'    $b .= chr($c[ord($d)]);'."\n".'}'."\n".'$str=$b;'."\n",
                                "params"=>array(
                                    "a"=>array(
                                             "type"=>"keymap"
                                         )
                                ),
                           ),
                        3=>array(
                               "string"=>'$s=~{b}~;'."\n".'if($s>1) {'."\n".' $str=$str."~{a}~"; '."\n".'}'."\n".' else'."\n".'{'."\n".'$str="~{a}~".$str;'."\n".'}'."\n",
                               "params"=>array(
                                    "a"=>array(
                                             "type"=>"string",
                                             "minlength"=>1,
                                             "maxlength"=>$this->secStrength*4
                                         ),
                                    "b"=>array(
                                             "type"=>"rand",
                                             "start"=>1,
                                             "stop"=>2
                                         ),
                               )
                           ),
                        4=>array(
                               "string"=>'$a=strlen($str);'."\n".'$b=~{a}~;'."\n".'$c=intval($a/2-1+$b);'."\n".'$tstr=$str;'."\n".'$str=substr($tstr,$c+1).substr($tstr,0,$c);'."\n",
                               "params"=>array(
                                    "a"=>array(
                                        "type"=>"rand",
                                        "start"=>2,
                                        "stop"=>3
                                    )
                               )
                           )
                        );
        return $arrFuncs;
    }
    
    
    public function generate($password,$identifier) {
        $func=$this->buildStringManipulator();
        eval($func);
        if($this->path != false) {
            $target=$this->path.$identifier.".ts";
        } else {
            $target = $identifier.".ts";
        }
        $enc = $this->aes_enc($func,$password);
        file_put_contents($target,$enc);
        if($this->hashIt == true) {
            $str = md5($str);
        }
        return $str;
    }
    
    
    public function parse($password,$identifier) {
        if($this->path != false) {
            $target=$this->path.$identifier.".ts";
        } else {
            $target = $identifier.".ts";
        }
        if(!file_exists($target)) {
            return false;
        }
        $v=file_get_contents($target);
        $c=$this->aes_dec($v,$password);
        eval($c);
        if(!isset($str)) {
            return false;
        }
        if($this->hashIt == true) {
            $str = md5($str);
        }
        return $str;
    }
    
    private function buildStringManipulator() {
        //get the funcs
        $arrFuncs = $this->getFuncArray();
        
        //check the count / set the last to 0
        $c=count($arrFuncs);
        $l=0;
        //preset func body
        $f='$str=$password;'."\n";
        //
        //run through the creation amount
        for($i=0;$i<$this->runMax;$i++) {
            //get a func
            $r=$this->determineFunc($c,rand(1,$this->countMax));
            //make sure its not the same like the last func (dbl strrev is kinda senseless f.e.)
            if($r==$l) {
                $s=1;
                for($a=0;$a<$s;$a++) {
                    $r=$this->determineFunc($c,rand(1,$this->countMax));
                    if($r==$l) {
                        $s++;
                    }
                }
            }
            //we got a new func , safe the id
            $l=$r;
            //get the funcs code
            $str=$arrFuncs[$r]["string"];
            //run thorough the params and generate them
            if(isset($arrFuncs[$r]["params"]) && is_array($arrFuncs[$r]["params"])){
                foreach($arrFuncs[$r]["params"] as $k => $p) {
                    switch($p["type"]) {
                        case 'string':
                            $str = str_replace("~{".$k."}~",$this->rand->getRandomString(rand($p["minlength"],$p["maxlength"])),$str);
                            break;
                        case 'rand':
                            $str = str_replace("~{".$k."}~",rand($p["start"],$p["stop"]),$str);
                            break;
                        case 'keymap':
                            $str = str_replace("~{".$k."}~",$this->buildKeyMap(),$str);
                            break;
                    }
                }
            }
            $f.=$str;
            
            //makes it harder to reverse
            usleep(rand(10,1000*$this->secStrength));
        }
        
        //lets return the shit
        $f.="\n";
        return $f;
    }
    
    private function determineFunc($c,$s) {
        $r=1;
        for($i=1;$i<$s;$i++) {
             if($r==$c) {
                $r=1;
            } else {
                $r++;
            }
        }
        return $r;
    }
    
    

   public function aes_enc( $msg, $k) {

       # open cipher module (do not change cipher/mode)
       if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
           return false;

       $msg = serialize($msg);                         # serialize
       $iv = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);        # create iv

       if ( mcrypt_generic_init($td, $k, $iv) !== 0 )  # initialize buffers
           return false;

       $msg = mcrypt_generic($td, $msg);               # encrypt
       $msg = $iv . $msg;                              # prepend iv
       $mac = $this->pbkdf2($msg, $k, 1000, 32);       # create mac
       $msg .= $mac;                                   # append mac

       mcrypt_generic_deinit($td);                     # clear buffers
       mcrypt_module_close($td);                       # close cipher module

       $msg = base64_encode($msg);      # base64 encode?

       return $msg;                                    # return iv+ciphertext+mac
   }

   public function aes_dec( $msg, $k ) {

       $msg = base64_decode($msg);          # base64 decode?

       # open cipher module (do not change cipher/mode)
       if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
           return false;

       $iv = substr($msg, 0, 32);                          # extract iv
       $mo = strlen($msg) - 32;                            # mac offset
       $em = substr($msg, $mo);                            # extract mac
       $msg = substr($msg, 32, strlen($msg)-64);           # extract ciphertext
       $mac = $this->pbkdf2($iv . $msg, $k, 1000, 32);     # create mac

       if ( $em !== $mac )                                 # authenticate mac
           return false;

       if ( mcrypt_generic_init($td, $k, $iv) !== 0 )      # initialize buffers
           return false;

       $msg = mdecrypt_generic($td, $msg);                 # decrypt
       $msg = unserialize($msg);                           # unserialize

       mcrypt_generic_deinit($td);                         # clear buffers
       mcrypt_module_close($td);                           # close cipher module

       return $msg;                                        # return original msg
   }

   public function pbkdf2( $p, $s, $c, $kl, $a = 'sha256' ) {

       $hl = strlen(hash($a, null, true)); # Hash length
       $kb = ceil($kl / $hl);              # Key blocks to compute
       $dk = '';                           # Derived key

       # Create key
       for ( $block = 1; $block <= $kb; $block ++ ) {

           # Initial hash for this block
           $ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);

           # Perform block iterations
           for ( $i = 1; $i < $c; $i ++ )

               # XOR each iterate
               $ib ^= ($b = hash_hmac($a, $b, $p, true));

           $dk .= $ib; # Append iterated block
       }
       
       # Return derived key of correct length
       return substr($dk, 0, $kl);
   }
   
   
}



class rnd {
    
    private $chars         = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private $numbers       = "1234567890";
    private $special       = "!%/()=:;.,_-+";
    private $defaultLength = 10;
    
    
    
    public function getRandomString($length = false) {
        return $this->generate($this->chars.
                               $this->numbers.
                               $this->special,$length);
    }
    
    
    public function getRandomNumber($length = false) {
        return $this->generate($this->numbers,$length);
    }
    
    
    public function getRandomChars($length = false) {
        return $this->generate($this->chars,$length);
    }
    
    
    public function getRandomAlphanum($length = false) {
        return $this->generate($this->chars.
                               $this->numbers,$length);
    }
    
    
    private function generate($strPool,$length = false) {
        $return = "";
        if(!$length) {
            $length = $this->defaultLength;
        }
        $poolLength = strlen($strPool);
        for($i=0;$i<$length;$i++){
            $position = $this->getRandPosition($return); 
            $char     = $this->getCharFromPool($strPool);
            $return   = $this->putOnPosition($return,$position,$char);
        }
        return $return;
    }
    
    
    private function putOnPosition($return,$position,$char) {
        if(isset($return)) {
            $returnLen = strlen($return);
            if($returnLen != 1) {
                return substr($return,0,$position-1).$char.substr($return,$position-1,$returnLen-1);
            } else {
                return $return . $char;
            }
        } else {
            return $char;
        }
    }
    
    
    private function getCharFromPool($pool) {
        return $pool[rand(0,strlen($pool)-1)];
    }
    
    
    private function getRandPosition($string){
        if(!$string) {
            return false;
        } else {
            return rand(2,strlen($string)-1);
        }
    }
}


?>