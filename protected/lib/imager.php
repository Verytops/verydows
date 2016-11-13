<?php
class imager
{
    public static function resize($file_path, $max_width = 300, $max_height = 300, $save_path)
    {
        if(!list($orig_w, $orig_h, $mime) = @getimagesize($file_path)) return FALSE;
        
        $xr = $max_width / $orig_w;
        $yr = $max_height / $orig_h;
    
        if(($orig_w <= $max_width) && ($orig_h <= $max_height))
        {
            $targ_w = $orig_w;
            $targ_h = $orig_h;
        }
        elseif(($xr * $orig_h) < $max_height)
        {
            $targ_w = $max_width;
            $targ_h = ceil($xr * $orig_h);
        }
        else
        {
            $targ_w = ceil($yr * $orig_w);
            $targ_h = $max_height;
        }
        
        $orig_im = self::create($file_path, $mime);
        $targ_im = imagecreatetruecolor($targ_w, $targ_h);
        
        if($mime == 1 || $mime == 3) //保持透明度
        {
            imagecolortransparent($targ_im, imagecolorallocatealpha($orig_im, 0, 0, 0, 127));
            imagealphablending($targ_im, false);
            imagesavealpha($targ_im, true);
        }
        
        imagecopyresampled($targ_im, $orig_im, 0, 0, 0, 0, $targ_w, $targ_h, $orig_w, $orig_h);
        
        return self::imtofile($targ_im, $save_path, $mime);
    }
  
    public static function create($file_path, $mime)
    {
        if(empty($file_path)) return FALSE;
        
        switch($mime) 
        {
            case 1:
            case 'image/gif':
                $im = imagecreatefromgif($file_path);
            break;
            
            case 2:
            case 'image/jpeg':
            case 'image/pjpeg':
                $im = imagecreatefromjpeg($file_path);
            break;
            
            case 3:
            case 'image/png':
            case 'image/x-png':
                $im = imagecreatefrompng($file_path);
            break;
            
            case 'base64':
                $im = imagecreatefromstring(base64_decode($file_path));
            break;
            
            default: return FALSE;
        }
        return $im;
    }
    
    public static function crop($im, $region, $save_path, $mime = 'image/jpeg')
    {
        $targ_w = isset($region['tw']) ? $region['tw'] : $region['w'];
        $targ_h = isset($region['th']) ? $region['th'] : $region['h'];

        $dst_r = imagecreatetruecolor($targ_w, $targ_h);
        imagecopyresampled($dst_r, $im, 0, 0, $region['x'], $region['y'], $targ_w, $targ_h, $region['w'], $region['h']);
        return self::imtofile($dst_r, $save_path, $mime);
    }
    
    public static function imtobase64($image_file, $mime)
    {
        $image_base64 = base64_encode(file_get_contents($image_file));
        return "data:{$mime};base64,{$image_base64}";
    }
    
    public static function imtofile($im, $save_path, $mime)
    {
        $save_path = str_replace('/', DS, $save_path);
        if(self::check_save_path($save_path))
        {
            switch($mime) 
            {
                case 1:
                case 'image/gif':
                    $save_path = $save_path . '.gif';
                    imagegif($im, $save_path);
                break;
                
                case 2:
                case 'image/jpeg':
                case 'image/pjpeg':
                    $save_path = $save_path . '.jpg';
                    imagejpeg($im, $save_path, 100);
                break;
                
                case 3:
                case 'image/png':
                case 'image/x-png':
                    $save_path = $save_path . '.png';
                    imagepng($im, $save_path, 0);
                break;
                
                default: return FALSE;
            }
            imagedestroy($im);
            
            return str_replace('\\', '/', $save_path);
        }
        
        return FALSE;
    }
    
    public static function output($im, $mime)
    {
        switch($mime) 
        {
            case 1:
            case 'image/gif':
                header('Content-Type: image/gif'); imagegif($im);
            break;
                
            case 2:
            case 'image/jpeg':
            case 'image/pjpeg':
                header('Content-Type: image/jpeg'); imagejpeg($im);
            break;
                
            case 3:
            case 'image/png':
            case 'image/x-png':
                header("Content-Type: image/png"); imagepng($im);
            break;
                
            default: return FALSE;
        }
        imagedestroy($im);
    }
    
    private static function check_save_path($path)
    {
        $path = substr($path, 0, strrpos($path, DS));
        if(!is_dir($path)) 
        {
            if(!mkdir($path, 0777, TRUE)) return FALSE;
        }
        return TRUE;
    }
}