<?php
/**
 * 验证码
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @license GPLv3
 */
class captcha
{
    public $width  = 180;
    public $height = 60;
    public $min_word_len = 4;
    public $max_word_len = 6;
    public $session_var = 'CAPTCHA';
    public $background_color = array(255, 255, 255);

    public $colors = array
    (
        array(27,78,181), // blue
        array(22,163,35), // green
        array(214,36,7),  // red
    );

    public $shadow_color = null; //array(255, 255, 255);

    public $line_width = 4;

    public $fonts = array
    (
        'Antykwa'  => array('spacing' => -3, 'minSize' => 20, 'maxSize' => 26, 'font' => 'AntykwaBold.ttf'),
    );

    public $y_period    = 12;
    public $y_amplitude = 14;
    public $x_period    = 11;
    public $x_amplitude = 5;

    public $max_rotation = 8;

    public $scale = 2;

    public $blur = FALSE;
    
    public $imageFormat = 'png';

    public $im;

    public function __construct($config = array()) {}

    public function create_image()
    {
        $ini = microtime(true);

        /** Initialization */
        $this->image_allocate();
        
        /** Text insertion */
        $text = $this->get_random_captcha_text();
        $fontcfg  = $this->fonts['Antykwa'];
        $this->write_text($text, $fontcfg);

        $_SESSION[$this->session_var] = strtolower($text);

        /** Transformations */
        if(!empty($this->line_width)) $this->write_line();
        $this->wave_image();
        if($this->blur && function_exists('imagefilter')) imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
        $this->reduce_image();

        $this->write_image();
        $this->cleanup();
    }

    protected function image_allocate()
    {
        if(!empty($this->im))imagedestroy($this->im);

        $this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);

        $this->gd_bg_color = imagecolorallocate($this->im,
            $this->background_color[0],
            $this->background_color[1],
            $this->background_color[2]
        );
        imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->gd_bg_color);

        // Foreground color
        $color = $this->colors[mt_rand(0, sizeof($this->colors)-1)];
        $this->gd_fg_color = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

        // Shadow color
        if (!empty($this->shadow_color) && is_array($this->shadow_color) && sizeof($this->shadow_color) >= 3) {
            $this->gd_shadow_color = imagecolorallocate($this->im,
                $this->shadow_color[0],
                $this->shadow_color[1],
                $this->shadow_color[2]
            );
        }
    }

    protected function get_random_captcha_text($length = null) {
        if(empty($length))$length = rand($this->min_word_len, $this->max_word_len);
        $words = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";
        $text = "";
        for($i=0; $i<$length; $i++) $text .= substr($words, mt_rand(0, 56), 1);
        return $text;
    }

    protected function write_line()
    {
        $x1 = $this->width*$this->scale*.15;
        $x2 = $this->textFinalX;
        $y1 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
        $y2 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
        $width = $this->line_width/2*$this->scale;

        for($i = $width*-1; $i <= $width; $i++) {
            imageline($this->im, $x1, $y1+$i, $x2, $y2+$i, $this->gd_fg_color);
        }
    }

    protected function write_text($text, $fontcfg = array())
    {
        if(empty($fontcfg)) $fontcfg  = $this->fonts['Antykwa'];

        // Full path of font file
        $fontfile = APP_DIR.DS.'protected'.DS.'resources'.DS.'font'.DS.$fontcfg['font'];

        /** Increase font-size for shortest words: 9% for each glyp missing */
        $letters_missing = $this->max_word_len-strlen($text);
        $font_size_factor = 1+($letters_missing*0.09);

        // Text generation (char by char)
        $x      = 20*$this->scale;
        $y      = round(($this->height*27/40)*$this->scale);
        $length = strlen($text);
        for ($i=0; $i<$length; $i++) {
            $degree   = rand($this->max_rotation*-1, $this->max_rotation);
            $fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale*$font_size_factor;
            $letter   = substr($text, $i, 1);

            if ($this->shadow_color) {
                $coords = imagettftext($this->im, $fontsize, $degree,
                    $x+$this->scale, $y+$this->scale,
                    $this->gd_shadow_color, $fontfile, $letter);
            }
            $coords = imagettftext($this->im, $fontsize, $degree,
                $x, $y,
                $this->gd_fg_color, $fontfile, $letter);
            $x += ($coords[2]-$x) + ($fontcfg['spacing']*$this->scale);
        }

        $this->textFinalX = $x;
    }

    protected function wave_image()
    {
        // X-axis wave generation
        $xp = $this->scale*$this->x_period*rand(1,3);
        $k = rand(0, 100);
        for($i = 0; $i < ($this->width*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                $i-1, sin($k+$i/$xp) * ($this->scale*$this->x_amplitude),
                $i, 0, 1, $this->height*$this->scale);
        }

        // Y-axis wave generation
        $k = rand(0, 100);
        $yp = $this->scale*$this->y_period*rand(1,2);
        for($i = 0; $i < ($this->height*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                sin($k+$i/$yp) * ($this->scale*$this->y_amplitude), $i-1,
                0, $i, $this->width*$this->scale, 1);
        }
    }

    protected function reduce_image()
    {
        $im_resampled = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($im_resampled, $this->im,
            0, 0, 0, 0,
            $this->width, $this->height,
            $this->width*$this->scale, $this->height*$this->scale
        );
        imagedestroy($this->im);
        $this->im = $im_resampled;
    }

    protected function write_image()
    {
        if ($this->imageFormat == 'png' && function_exists('imagepng')) {
            header("Content-type: image/png");
            imagepng($this->im);
        } else {
            header("Content-type: image/jpeg");
            imagejpeg($this->im, null, 80);
        }
    }

    protected function cleanup()
    {
        imagedestroy($this->im);
    }
}
?>
