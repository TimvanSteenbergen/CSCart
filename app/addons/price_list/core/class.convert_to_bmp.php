<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

if (!defined('BOOTSTRAP')) { die('Access denied'); }

class ConvertToBMP
{
    public $source = '';
    public $dest = '';

    private $image;
    private $err_code = 0;
    private $err_message = '';
    private $die_on_error = false;

    public function __construct($params = array())
    {
        !isset($params['source']) ? $this->source = '' : $this->source = $params['source'];
        !isset($params['die_on_error']) ? $this->die_on_error = false : $this->die_on_error = $params['die_on_error'];
    }

    public function error($code, $message = '')
    {
        if ($this->die_on_error && $code != 0) {
            die('Code: ' . $code . ';<br />Message: ' . $message);
        } else {
            $this->err_code = $code;
            $this->err_message = $message;
        }

        return true;
    }

    public function get_error()
    {
        return array($this->err_code, $this->err_message);
    }

    public function convert($source = '')
    {
        if (!empty($source)) {
            $this->source = $source;
        }

        if (empty($this->source) || !file_exists($this->source)) {
            $this->error(1, 'The source file "' . $this->source . '" is missing');

            return false;
        }

        switch (exif_imagetype($this->source)) {
            case IMAGETYPE_GIF:
                $result = $this->_convertImage($this->source, 'gif');
                break;
            case IMAGETYPE_JPEG:
                $result = $this->_convertImage($this->source, 'jpg');
                break;
            case IMAGETYPE_PNG:
                $result = $this->_convertImage($this->source, 'png');
                break;
            case IMAGETYPE_BMP:
                $result = $this->image = file_get_contents($this->source);
                break;
            default:
                $this->error(2, 'Unsupported file type');

                return false;
        }

        return $result;
    }

    public function output($filename = '')
    {
        if (empty($this->image)) {
            $this->error(3, 'Result BMP file is empty');

            return false;
        }

        if (!empty($filename)) {
            $this->dest = $filename;
        }

        if (empty($this->dest)) {
            // Output BMP file to browser
            echo '[BMP FILE]';

            $this->error(0);

            return true;

        } else {
            // Save the BMP file to the File System
            $_bmp = fopen($this->dest, 'wb');

            if (empty($_bmp)) {
                $this->error(4, 'Cannot create a destination file: ' . $this->dest);

                return false;
            }

            fwrite($_bmp, $this->image);
            fclose($_bmp);
            @chmod($this->dest, DEFAULT_FILE_PERMISSIONS);

            $this->error(0, 'File was created');

            return true;
        }

    }

    private function _convertImage($source, $type)
    {
        if ($type == 'jpg') {
            $im = imagecreatefromjpeg($source);
        } elseif ($type == 'png') {
            $im = imagecreatefrompng($source);
        } elseif ($type == 'gif') {
            $im = imagecreatefromgif($source);
        }

        if (!$im) {
            return false;
        }

        $w = imagesx($im);
        $h = imagesy($im);
        $result = '';

        if (!imageistruecolor($im)) {
            $tmp = imagecreatetruecolor($w, $h);
            imagecopy($tmp, $im, 0, 0, 0, 0, $w, $h);
            imagedestroy($im);
            $im = & $tmp;
        }

        $biBPLine = $w * 3;
        $biStride = ($biBPLine + 3) & ~3;
        $biSizeImage = $biStride * $h;
        $bfOffBits = 54;
        $bfSize = $bfOffBits + $biSizeImage;

        $result .= substr('BM', 0, 2);
        $result .= pack ('VvvV', $bfSize, 0, 0, $bfOffBits);
        $result .= pack ('VVVvvVVVVVV', 40, $w, $h, 1, 24, 0, $biSizeImage, 0, 0, 0, 0);

        $numpad = $biStride - $biBPLine;
        for ($y = $h - 1; $y >= 0; --$y) {
            for ($x = 0; $x < $w; ++$x) {
                $col = imagecolorat ($im, $x, $y);
                $result .= substr(pack ('V', $col), 0, 3);
            }
            for ($i = 0; $i < $numpad; ++$i) {
                $result .= pack ('C', 0);
            }
        }

        $this->image = $result;

        return true;
    }

}
