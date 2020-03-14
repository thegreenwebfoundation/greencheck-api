<?php

namespace App\Greencheck;

use Symfony\Component\HttpKernel\KernelInterface;

class ImageGenerator
{
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(KernelInterface $kernel)
    {
        $this->projectDir = $kernel->getProjectDir();
    }

    /**
     * @param $sponsored
     * @param $result
     *
     * @return false|string
     */
    public function createImage($sponsored, $result)
    {
        $font2 = $this->projectDir.'/generator/ClearSans-Regular.ttf';
        $verdana = $this->projectDir.'/generator/OpenSans-Regular.ttf';

        $greenimg = $this->projectDir.'/generator/150119green.png';
        $greyimg = $this->projectDir.'/generator/150119grey.png';

        if ($sponsored) {
            $greenimg = $this->projectDir.'/generator/150109green.png';
            $greyimg = $this->projectDir.'/generator/150109grey.png';
        }

        if ($result['green']) {
            $image = imagecreatefrompng($greenimg);
        } else {
            $image = imagecreatefrompng($greyimg);
        }
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $white = imagecolorallocate($image, 255, 255, 255);
        $grey = imagecolorallocate($image, 100, 100, 100);
        $urlcolor = imagecolorallocate($image, 0, 70, 0);
        $urlshadow = imagecolorallocate($image, 79, 138, 74);
        $whiteshadow = imagecolorallocate($image, 93, 173, 19);
        $greyshadow = imagecolorallocate($image, 170, 170, 170);

        if (!isset($result['partner'])) {
            $result['partner'] = '';
        }

        // Add url to the text
        imagettftext($image, 11, 0, 18, 96, $urlshadow, $verdana, $result['url']);
        imagettftext($image, 11, 0, 17, 95, $urlcolor, $verdana, $result['url']);
        if ($result['green']) {
            if ('' == $result['hostedby']) {
                $hostedtext = 'is hosted green';
            } else {
                $hostedtext = 'is green hosted by ';
                if ('' != $result['partner']) {
                    $hostedtext .= $result['partner'].' ';
                }
                $hostedtext .= $result['hostedby'];
            }

            imagettftext($image, 8, 0, 19, 113, $whiteshadow, $font2, $hostedtext);
            imagettftext($image, 8, 0, 18, 112, $white, $font2, $hostedtext);
        } else {
            imagettftext($image, 8, 0, 19, 113, $greyshadow, $font2, 'is hosted grey');
            imagettftext($image, 8, 0, 18, 112, $grey, $font2, 'is hosted grey');
        }

        ob_start();
        imagepng($image);
        $str = ob_get_clean();

        return $str;
    }
}
