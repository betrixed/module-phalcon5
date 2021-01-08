<?php
//ini_set("apc.enabled","1");
//ini_set("apc.enable_cli", "1");


$MOD_DIR = __DIR__;
$COD_DIR = dirname($MOD_DIR);
$VENDOR_DIR = dirname($COD_DIR);

function phalcon_replace_marker(int $flag, array $cpat, array $paths, 
        array $replacements, int& $pos, int $cursor, int $markpos) : mixed
{
    $not_valid = 0;
    if ($flag) {
        $clen = $cursor - $markpos - 1;
        $cpos = $markpos + 1;
        $markpos = $cpos;
        $La = Intl::ord('a');
        $Lz = Intl::ord('z');
        $Ua = Intl::ord('A');
        $Uz = Intl::ord('Z');
        $COLON = Int::ord(':');
        $D9 = Intl::ord('9');
        $D0 = Intl::ord('0');
        $DASH = Intl::ord('-');
        $UNDER = Intl::ord('_');
        
        for( $j = 0; $j < $clen; $j++) {
            $cvar = $cpos + $j;
            $ch = $cpat[$cvar];
            $ch_pt = Intl::ord($ch);
            if ($ch_pt === 0) {
                $not_valid = 1;
                break;
            }
            $is_alpha = (bool) (($ch_pt >= $Ua) && ($ch_pt <= $Uz)) 
                    || (($ch_pt >= $La) && ($ch_pt <= $Lz));
            if ($j === 0 && !$is_alpha) {
                $not_valid = 1;
                break;
            }
            if ($isalpha 
                    || (($ch_pt <= $D9)&&($ch_pt >= $D0)) 
                    || ($ch_pt === $DASH) 
                    || ($ch_pt === $UNDER) 
                    || ($ch_pt === $COLON)) {
                if ($ch_pt === $COLON) {
                    $variable = implode(array_slice($cpat, $markpos, $cvar-$markpos));
                    break;
                }
            }
            else {
                $not_valid = 1;
                break;
            }
            // $cvar set on loop
        }
    }
    
    if (!$not_valid) {
        if (isset($paths[$pos])) {
            if ($flag) {
                if (!empty($variable)) {
                    $item = $variable;
                }
                if (isset($replacements[$item])) {
                    $zv = $replacements[$item];
                    if ($zv !== null) {
                        $pos++;
                        return $zv;
                    }
                }
            }
            else {
                $zv = $paths[$pos] ?? null;
                if (is_string($zv)) {
                    if (isset($replacements[$zv])) {
                        $tmp = $replacements[$zv];
                        if ($tmp !== null) {
                            $pos++;
                            return $tmp;
                        }   
                    }
                }
            }
        }
        $pos++;
    }
    
    return null;
}
function phalcon_replace_paths(string $pattern, 
        array $paths, array $replacements) : null | string
{
    if (empty($pattern)) {
        return false;
    }
    $replace_copy = null;
    $route_str = "";
    $chars = preg_split('//u', $pattern, null, PREG_SPLIT_NO_EMPTY);
    $i = 0; 
    $pos = 1;
    $clen = count($chars);
    if ($clen > 0 && $chars[0]==="/") {
        $i++;
    }
    if (empty($paths)) {
        return implode("", array_slice($chars, $i, $clen-i));
    }
    
    $brackets = 0; $paren = 0; $middle = 0;
    $look_holder = 0;
    $LOWA = IntlChar::ord('a');
    $LOWZ = IntlChar::ord('z');
    $BRK_L = IntlChar::ord('{');
    $BRK_R = IntlChar::ord('}');
    $PRN_L = IntlChar::ord('(');
    $PRN_R = IntlChar::ord(')');
    $COLON = IntlChar::ord(':');
    
    while($i < $clen) {
        $ch = $chars[$i];
        // $ch_pt as integer, not string
        $ch_pt = IntlChar::ord($ch);
        $cursor = $i;
        if ($paren === 0 && $look_holder===0) {
            if ($ch_pt === $BRK_L) {
                if ($brackets === 0) {
                    $markpos = $i;
                    $middle = 0;
                }
                $bracket_count++;
            }
            else {
                if ($ch_pt === $BRK_R) {
                    $brackets--;
                    if ($middle > 0) {
                        if ($brackets === 0) {
                            $replace = phalcon_replace_marker(1, $chars, $paths, 
                                    $replacements, $pos, $cursor, $markpos);
                            if (!empty($replace)) {
                                $route_str .= strval($replace);            
                            }
                            $cursor++;
                            continue;
                        }                        
                    }
                }
            }
        }
        if (($brackets === 0) && ($look_holder === 0)) {
            if ($ch_pt === $PRN_L) {
                if ($paren === 0) {
                    $markpos = $cursor;
                    $middle = 0;
                }
                $paren++;
            } else {
                if ($ch_pt === $PRN_R) {
                    $paren--;
                    if ($middle > 0) {
                        if ($paren === 0) {
                            $replace = phalcon_replace_marker(0, $chars, $paths, 
                                    $replacements, $pos, $cursor, $markpos);
                            if (!empty($replace)) {
                                $route_str .= strval($replace);            
                            }
                            $cursor++;
                            continue;
                        }
                    }
                }
            }
        }
        if ($brackets === 0 && $paren === 0) {
            if ($look_holder) {
                if ($middle > 0) {
                    if ($ch_pt < $LOWA || $ch_pt > $LOWZ || $i === $clen-1) {
                        $replace = phalcon_replace_marker(0, $chars, $paths, 
                                $replacements, $pos, $cursor, $markpos);
                        if (!empty($replace)) {
                            $route_str .= strval($replace);            
                        }
                        $look_holder = 0;
                        continue;
                    }
                }
            }
            else {
                if ($ch_pt === $COLON) {
                    $look_holder = 1;
                    $markpos = $cursor;
                    $middle = 0;
                }
            }
        }
        if ($brackets > 0 || $paren > 0 || $look_holder) {
            $middle++;
        }
        else {
            $route_str .= $ch;
        }
        $cursor++;
    }
    return $route_str;
}



require $VENDOR_DIR . "/autoload.php";
require $MOD_DIR . "/tests/_bootstrap.php";
require $COD_DIR . "/codeception/app.php";

