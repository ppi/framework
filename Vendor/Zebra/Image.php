<?php

/**
 *  Methods used with the {@link resize()} method.
 */
define('ZEBRA_IMAGE_BOXED', 0);
define('ZEBRA_IMAGE_NOT_BOXED', 1);
define('ZEBRA_IMAGE_CROP_TOPLEFT', 2);
define('ZEBRA_IMAGE_CROP_CENTER', 3);

// this enables handling of partially broken JPEG files without warnings/errors
ini_set('gd.jpeg_ignore_warning', true);

/**
 *  A compact, lightweight, object-oriented image manipulation library written in and for PHP, that provides methods
 *  for performing several types of image manipulation operations. It doesn't require any external libraries other than
 *  the GD2 extension (with which PHP usually comes precompiled with).
 *
 *  The code is heavily commented and generates no warnings/errors/notices when PHP's error reporting level is set to
 *  E_ALL.
 *
 *  With this library you can rescale, flip, rotate and crop images. It supports loading and saving images in the GIF,
 *  JPEG and PNG formats and preserves transparency for GIF, PNG and PNG24.
 *
 *  The cool thing about it is that it can resize images to exact given width and height and still maintain aspect
 *  ratio.
 *
 *  Visit {@link http://stefangabos.ro/php-libraries/zebra-image/} for more information.
 *
 *  For more resources visit {@link http://stefangabos.ro/}
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @version    2.1.2 (last revision: May 09, 2011)
 *  @copyright  (c) 2006 - 2011 Stefan Gabos
 *  @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
 *  @package    Zebra_Image
 */
class Zebra_Image
{

    /**
     *  Indicates the file system permissions to be set for newly created images.
     *
     *  Better is to leave this setting as it is.
     *
     *  If you know what you are doing, here is how you can calculate the permission levels:
     *
     *  - 400 Owner Read
     *  - 200 Owner Write
     *  - 100 Owner Execute
     *  - 40 Group Read
     *  - 20 Group Write
     *  - 10 Group Execute
     *  - 4 Global Read
     *  - 2 Global Write
     *  - 1 Global Execute
     *
     *  Default is 0755
     *
     *  @var integer
     */
    var $chmod_value;

    /**
     *  If set to FALSE, images having both width and height smaller than the required width and height, will be left
     *  untouched ({@link jpeg_quality} will still apply).
     *
     *  Available only for the {@link resize()} method
     *
     *  Default is TRUE
     *
     *  @var boolean
     */
    var $enlarge_smaller_images;

    /**
     *  In case of an error read this property's value to see the error's code.
     *
     *  Possible error codes are:
     *
     *  - 1:  source file could not be found
     *  - 2:  source file is not readable
     *  - 3:  could not write target file
     *  - 4:  unsupported source file format
     *  - 5:  unsupported target file format
     *  - 6:  GD library version does not support target file format
     *  - 7:  GD library is not installed!
     *
     *  Default is 0 (no error).
     *
     *  @var integer
     */
    var $error;

    /**
     *  Indicates the quality of the output image (better quality means bigger file size).
     *
     *  Available only for the {@link resize()} method and only if the file at {@link target_path} is a jpg/jpeg.
     *
     *  Range is 0 - 100
     *
     *  Default is 85
     *
     *  @var integer
     */
    var $jpeg_quality;

    /**
     *  Specifies whether, upon resizing, images should preserve their aspect ratio.
     *
     *  Available only for the {@link resize()} method
     *
     *  Default is TRUE
     *
     *  @var boolean
     */
    var $preserve_aspect_ratio;

    /**
     *  Indicates whether a target files should preserve the source file's date/time.
     *
     *  Default is TRUE
     *
     *  @since 1.0.4
     *
     *  @var boolean
     */
    var $preserve_time;

    /**
     *  Path to an image file to apply the transformations to.
     *
     *  Supported file types are <b>GIF</b>, <b>PNG</b> and <b>JPEG</b>.
     *
     *  @var    string
     */
    var $source_path;
    
    /**
     *  Path (including file name) to where to save the transformed image.
     *
     *  <i>Can be a different than {@link source_path} - the type of the transformed image will be as indicated by the
     *  file's extension (supported file types are GIF, PNG and JPEG)</i>.
     *
     *  @var    string
     */
    var $target_path;
    
    /**
     *  Constructor of the class.
     *
     *  Initializes the class and the default properties
     *
     *  @return void
     */
    function Zebra_Image()
    {

        // set default values for properties
        $this->chmod_value = 0755;

        $this->error = 0;

        $this->jpeg_quality = 85;

        $this->preserve_aspect_ratio = $this->preserve_time = $this->enlarge_smaller_images = true;

        $this->source_path = $this->target_path = '';

    }

    /**
     *  Crops a portion of the image given as {@link source_path} and outputs it as the file specified as {@link target_path}.
     *
     *  @param  integer     $start_x    x coordinate to start cropping from
     *
     *  @param  integer     $start_y    y coordinate to start cropping from
     *
     *  @param  integer     $end_x      x coordinate where to end the cropping
     *
     *  @param  integer     $end_y      y coordinate where to end the cropping
     *
     *  @since  1.0.4
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    function crop($start_x, $start_y, $end_x, $end_y)
    {
    
        // this method might be also called internally
        // in this case, there's a fifth argument that points to an already existing image identifier
        $args = func_get_args();
        
        // if fifth argument exists
        if (isset($args[4]) && is_resource($args[4])) {

            // that it is the image identifier that we'll be using further on
            $this->source_identifier = $args[4];

            // set this to true so that the script will continue to execute at the next IF
            $result = true;

        // if method is called as usually
        } else {

            // try to create an image resource from source path
            $result = $this->_create_from_source();

        }

        // if image resource was successfully created
        if ($result !== false) {

            // prepare the target image
            $target_identifier = $this->_prepare_image($end_x - $start_x, $end_y - $start_y, -1);
            
            // crop the image
            imagecopyresampled(
            
                $target_identifier,
                $this->source_identifier,
                0,
                0,
                $start_x,
                $start_y,
                $end_x - $start_x,
                $end_y - $start_y,
                $end_x - $start_x,
                $end_y - $start_y

            );

            // write image
            return $this->_write_image($target_identifier);
            
        }

        // if script gets this far, return false
        // note that we do not set the error level as it has been already set
        // by the _create_from_source() method earlier
        return false;

    }

    /**
     *  Flips both horizontally and vertically the image given as {@link source_path} and outputs the resulted image as
     *  {@link target_path}
     *
     *  @since 2.1
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    function flip_both()
    {

        return $this->_flip('both');

    }

    /**
     *  Flips horizontally the image given as {@link source_path} and outputs the resulted image as {@link target_path}
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    function flip_horizontal()
    {

        return $this->_flip('horizontal');
            
    }

    /**
     *  Flips vertically the image given as {@link source_path} and outputs the resulted image as {@link target_path}
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    function flip_vertical()
    {
    
        return $this->_flip('vertical');

    }

    /**
     *  Resizes the image given as {@link source_path} and outputs the resulted image as {@link target_path}.
     *
     *  @param  integer     $width              The width to resize the image to.
     *
     *                                          If set to <b>0</b>, the width will be automatically adjusted, depending
     *                                          on the value of the <b>height</b> argument so that the image preserves
     *                                          its aspect ratio.
     *
     *                                          If {@link preserve_aspect_ratio} is set to TRUE and both this and the
     *                                          <b>height</b> arguments are values greater than <b>0</b>, the image will
     *                                          be resized to the exact required width and height and the aspect ratio
     *                                          will be preserved - (also see the description for the <b>method</b>
     *                                          argument below on how can this be done).
     *
     *                                          If {@link preserve_aspect_ratio} is set to FALSE, the image will be
     *                                          resized to the required width and the aspect ratio will be ignored.
     *
     *                                          If both <b>width</b> and <b>height</b> are set to <b>0</b>, a copy of
     *                                          the source image will be created (<b>jpeg_quality</b> will still apply).
     *
     *                                          If either <b>width</b> or <b>height</b> are set to <b>0</b>, the script
     *                                          will consider the value of the {@link preserve_aspect_ratio} to bet set
     *                                          to TRUE regardless of its actual value!
     *
     *  @param  integer     $height             The height to resize the image to.
     *
     *                                          If set to <b>0</b>, the height will be automatically adjusted, depending
     *                                          on the value of the <b>width</b> argument so that the image preserves
     *                                          its aspect ratio.
     *
     *                                          If {@link preserve_aspect_ratio} is set to TRUE and both this and the
     *                                          <b>width</b> arguments are values greater than <b>0</b>, the image will
     *                                          be resized to the exact required width and height and the aspect ratio
     *                                          will be preserved - (also see the description for the <b>method</b>
     *                                          argument below on how can this be done).
     *
     *                                          If {@link preserve_aspect_ratio} is set to FALSE, the image will be
     *                                          resized to the required height and the aspect ratio will be ignored.
     *
     *                                          If both <b>height</b> and <b>width</b> are set to <b>0</b>, a copy of
     *                                          the source image will be created (<b>jpeg_quality</b> will still apply).
     *
     *                                          If either <b>height</b> or <b>width</b> are set to <b>0</b>, the script
     *                                          will consider the value of the {@link preserve_aspect_ratio} to bet set
     *                                          to TRUE regardless of its actual value!
     *
     *  @param  int     $method                 (Optional) Method to use when resizing images to exact width and height
     *                                          while preserving aspect ratio.
     *
     *                                          If the {@link preserve_aspect_ratio} property is set to TRUE and both the
     *                                          <b>width</b> and <b>height</b> arguments are values greater than <b>0</b>,
     *                                          the image will be resized to the exact given width and height and the
     *                                          aspect ratio will be preserved by using on of the following methods:
     *
     *                                          -   ZEBRA_IMAGE_BOXED - the image will be scalled so that it will fit
     *                                              in a box with the given width and height (both width/height will be
     *                                              smaller or equal to the required width/height) and then it will be
     *                                              centered both horizontally and vertically. The blank area will be
     *                                              filled with the color specified by the <b>bgcolor</b> argument. (the
     *                                              blank area will be filled only if the image is not transparent!)
     *
     *                                          -   ZEBRA_IMAGE_NOT_BOXED - the image will be scalled so that it
     *                                              <i>could</i> fit in a box with the given width and height but will
     *                                              not be enclosed in a box with given width and height. The new width/
     *                                              height will be both smaller or equal to the required width/height
     *
     *                                          -   ZEBRA_IMAGE_CROP_TOPLEFT - after the image has been scaled so that
     *                                              one if its sides meets the required width/height and the other side
     *                                              is not smaller than the required height/width, a region of required
     *                                              width and height will be cropped from the top left corner of the
     *                                              resulted image.
     *
     *                                          -   ZEBRA_IMAGE_CROP_CENTER - after the image has been scaled so that
     *                                              one if its sides meets the required width/height and the other side
     *                                              is not smaller than the required height/width, a region of required
     *                                              width and height will be cropped from the center of the
     *                                              resulted image.
     *
     *                                          Default is ZEBRA_IMAGE_CROP_CENTER
     *
     *  @param  hexadecimal $background_color   (Optional) The hexadecimal color (like "#FFFFFF" or "#FFF") of the
     *                                          blank area. See the <b>method</b> argument.
     *
     *                                          When set to -1 the script will preserve transparency for transparent GIF
     *                                          and PNG images. For non-transparent images the background will be white
     *                                          in this case.
     *
     *                                          Default is #FFF.
     *                                          
     *  @return boolean                         Returns TRUE on success or FALSE on error.
     *
     *                                          If FALSE is returned, check the {@link error} property to see what went
     *                                          wrong
     */
    function resize($width = 0, $height = 0, $method = ZEBRA_IMAGE_CROP_CENTER, $background_color = '#FFF')
    {

        // if image resource was successfully created
        if ($this->_create_from_source()) {

            // if either width or height is to be adjusted automatically
            if ($width == 0 || $height == 0) {

                // set a flag telling the script that, even if $preserve_aspect_ratio is set to false
                // treat everything as if it was set to true
                $auto_preserve_aspect_ratio = true;

            }

            // if aspect ratio needs to be preserved
            if ($this->preserve_aspect_ratio || isset($auto_preserve_aspect_ratio)) {

                // calculate the image's aspect ratio
                $aspect_ratio =

                    $this->source_width <= $this->source_height ?

                        $this->source_height / $this->source_width :

                        $this->source_width / $this->source_height;

                // if height is given and width is to be computed accordingly
                if ($width == 0 && $height > 0) {

                    // the target image's height is as given as argument to the method
                    $target_height = $height;

                    // compute the target image's width, preserving the aspect ratio
                    $target_width =

                        $this->source_width <= $this->source_height ?

                            round($height / $aspect_ratio) :

                            round($height * $aspect_ratio);

                // if width is given and height is to be computed accordingly
                } elseif ($width > 0 && $height == 0) {

                    // the target image's width is as given as argument to the method
                    $target_width = $width;

                    // compute the target image's height, preserving the aspect ratio
                    $target_height =

                        $this->source_width <= $this->source_height ?

                            round($width * $aspect_ratio) :

                            round($width / $aspect_ratio);

                // if both width and height are given
                } elseif ($width > 0 && $height > 0) {

                    // by default, make the target image's width as given as argument to the method
                    $target_width = $width;

                    // compute the target image's height, preserving the aspect ratio
                    $target_height =

                        $this->source_width <= $this->source_height ?

                            round($width * $aspect_ratio) :

                            round($width / $aspect_ratio);

                    // if 
                    if (

                        // images are to be cropped and the computed height is smaller than the required height
                        // (we use this so that the resulting image will fit *exactly* in the given width/height)
                        (($method == ZEBRA_IMAGE_CROP_CENTER || $method == ZEBRA_IMAGE_CROP_TOPLEFT) && $target_height < $height) ||

                        // or images are not to be cropped and the computed height is larger than the required height
                        // (we use this so that the resulting image will fix in a box with the given width/height
                        // without loosing its aspect ratio)
                        (($method == ZEBRA_IMAGE_BOXED || $method == ZEBRA_IMAGE_NOT_BOXED) && $target_height > $height)

                    ) {

                        // make the target image's height as given as argument to the method
                        $target_height = $height;

                        // compute the target image's width, preserving the aspect ratio
                        $target_width =

                            $this->source_width <= $this->source_height ?

                                round($height / $aspect_ratio) :

                                round($height * $aspect_ratio);

                    }

                // if both width and height are 0
                } else {

                    // we will create a copy of the source image
                    $target_width = $this->source_width;

                    $target_height = $this->source_height;

                }

                // notice that, at this point, unless required width and height are 0, the width or height of the image
                // is a value larger than the one allowed we will fix this later on by cropping the image

                // if
                if (

                    // both width and height were given AND
                    $width > 0 && $height > 0 &&

                    // the image cannot be resized to the given width and height without distorting it
                    ($target_width != $width || $target_height != $height) &&

                    // image is not to be cropped
                    $method != ZEBRA_IMAGE_CROP_CENTER && $method != ZEBRA_IMAGE_CROP_TOPLEFT

                ) {

                    ${'target_' . ($this->source_width > $this->source_height ? 'width' : 'height')} =

                        ${($this->source_width > $this->source_height ? 'width' : 'height')};

                    ${'target_' . ($this->source_width > $this->source_height ? 'height' : 'width')} =

                        ${($this->source_width > $this->source_height ? 'width' : 'height')} / $aspect_ratio;

                }

            // if aspect ratio does not need to be preserved
            } else {

                // prepare the target image's width
                $target_width = ($width > 0 ? $width : $this->source_width);

                // prepare the target image's height
                $target_height = ($height > 0 ? $height : $this->source_height);

            }

            // if
            if (

                // all images are to be resized - including images that are smaller than the given width/height OR
                $this->enlarge_smaller_images ||

                // smaller images than the given width/height are to be left untouched
                // but current image is larger than the given width/height
                ($width > 0 && $height > 0 ?

                    ($this->source_width > $width || $this->source_height > $height) :

                    ($this->source_width > $target_width || $this->source_height > $target_height)

                )

            ) {

                // if
                if (

                    // aspect ratio needs to be preserved AND
                    ($this->preserve_aspect_ratio || isset($auto_preserve_aspect_ratio)) &&

                    // neither width nor height are to be computed automatically AND
                    ($width > 0 && $height > 0) &&

                    // images are to be cropped either from the center or from the top-left corner
                    ($method == ZEBRA_IMAGE_CROP_CENTER || $method == ZEBRA_IMAGE_CROP_TOPLEFT)

                ) {

                    // prepare the target image
                    $target_identifier = $this->_prepare_image($target_width, $target_height, -1);

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        0,
                        0,
                        0,
                        0,
                        $target_width,
                        $target_height,
                        $this->source_width,
                        $this->source_height

                    );

                    // if image needs to be cropped from center
                    if ($method == ZEBRA_IMAGE_CROP_CENTER) {

                        // crop accordingly
                        return $this->crop(

                            floor(($target_width - $width) / 2),
                            floor(($target_height - $height) / 2),
                            floor(($target_width - $width) / 2) + $width,
                            floor(($target_height - $height) / 2) + $height,
                            $target_identifier // crop this resource instead

                        );

                    // if image needs to be cropped from the top left corner
                    } else {

                        // crop accordingly
                        return $this->crop(
                            0,
                            0,
                            $width,
                            $height,
                            $target_identifier // crop this resource instead
                        );

                    }

                // if aspect ratio doesn't need to be preserved or
                // it needs to be preserved and method is ZEBRA_IMAGE_BOXED or ZEBRA_IMAGE_NOT_BOXED
                } else {

                    // prepare the target image
                    $target_identifier = $this->_prepare_image(

                        ($width > 0 && $height > 0 && $method != ZEBRA_IMAGE_NOT_BOXED ? $width : $target_width),
                        ($width > 0 && $height > 0 && $method != ZEBRA_IMAGE_NOT_BOXED ? $height : $target_height),
                        $background_color

                    );

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        ($width > 0 && $height > 0 && $method != ZEBRA_IMAGE_NOT_BOXED ? ($width - $target_width) / 2 : 0),
                        ($width > 0 && $height > 0 && $method != ZEBRA_IMAGE_NOT_BOXED ? ($height - $target_height) / 2 : 0),
                        0,
                        0,
                        $target_width,
                        $target_height,
                        $this->source_width,
                        $this->source_height

                    );

                    // if script gets this far, write the image to disk
                    return $this->_write_image($target_identifier);

                }

            // if we get here it means that
            // smaller images than the given width/height are to be left untouched
            // therefore, we save the image as it is
            } else {

                // write the image to disk
                return $this->_write_image($this->source_identifier);

            }

        }
        
        // if script gets this far return false
        // note that we do not set the error level as it has been already set
        // by the _create_from_source() method earlier
        return false;

    }

    /**
     *  Rotates the image given as {@link source_path} and outputs the resulted image as {@link target_path}.
     *
     *  @param  double  $angle                  Angle by which to rotate the image clockwise.
     *
     *                                          Between 0 and 360.
     *
     *  @param  mixed   $background_color       (Optional) The hexadecimal color (like "#FFFFFF" or "#FFF") of the
     *                                          uncovered zone after the rotation.
     *
     *                                          When set to -1 the script will preserve transparency for transparent GIF
     *                                          and PNG images. For non-transparent images the background will be white
     *                                          in this case.
     *
     *                                          Default is -1.
     *
     *  @return boolean                         Returns TRUE on success or FALSE on error.
     *
     *                                          If FALSE is returned, check the {@link error} property to see the error
     *                                          code.
     */
    function rotate($angle, $background_color = -1)
    {
    
        // if image resource was successfully created
        if ($this->_create_from_source()) {

            // angles are given clockwise but imagerotate works counterclockwise so we need to negate our value
            $angle = -$angle;

            // if source image is PNG
            if ($this->source_type == IMAGETYPE_PNG && $background_color == -1) {

                // rotate the image
                // but if using -1 as background color didn't work (as is the case for PNG8)
                if (!($target_identifier = imagerotate($this->source_identifier, $angle, -1))) {

                    // we will be using #FFF as the color to fill the uncovered zone after the rotation
                    $background_color = imagecolorallocate($this->source_identifier, 255, 255, 255);

                    // rotate the image
                    $target_identifier = imagerotate($this->source_identifier, $angle, $background_color);

                }

            // if source image is a transparent GIF
            } elseif ($this->source_type == IMAGETYPE_GIF && $this->source_transparent_color_index >= 0) {

                // convert the background color to RGB values
                $background_color = $this->_hex2rgb($background_color);

                // allocate the color to the image identifier
                $background_color = imagecolorallocate(

                    $this->source_identifier,
                    $background_color['r'],
                    $background_color['g'],
                    $background_color['b']

                );

                // rotate the image
                $this->source_identifier = imagerotate($this->source_identifier, $angle, $background_color);

                // get the width of rotated image
                $width = imagesx($this->source_identifier);

                // get the height of rotated image
                $height = imagesy($this->source_identifier);

                // create a blank image with the new width and height
                // (this intermediary step is for preserving transparency)
                $target_identifier = $this->_prepare_image($width, $height, -1);

                // copy the rotated image on to the new one
                imagecopyresampled($target_identifier, $this->source_identifier, 0, 0, 0, 0, $width, $height, $width, $height);

            // for the other cases
            } else {

                // convert the color to RGB values
                $background_color = $this->_hex2rgb($background_color);

                // allocate the color to the image identifier
                $background_color = imagecolorallocate(

                    $this->source_identifier,
                    $background_color['r'],
                    $background_color['g'],
                    $background_color['b']

                );

                // rotate the image
                $target_identifier = imagerotate($this->source_identifier, $angle, $background_color);

            }

            // write image
            $this->_write_image($target_identifier);

        }

        // if script gets this far return false
        // note that we do not set the error level as it has been already set
        // by the _create_from_source() method earlier
       return false;

    }

    /**
     *  Returns an array containing the image identifier representing the image obtained from {@link $source_path}, the
     *  image's width and height and the image's type
     *
     *  @access private
     */
    function _create_from_source()
    {

        // perform some error checking first
        // if the GD library is not installed
        if (!function_exists('gd_info')) {

            // save the error level and stop the execution of the script
            $this->error = 7;

            return false;

        // if source file does not exist
        } elseif (!is_file($this->source_path)) {

            // save the error level and stop the execution of the script
            $this->error = 1;

            return false;

        // if source file is not readable
        } elseif (!is_readable($this->source_path)) {

            // save the error level and stop the execution of the script
            $this->error = 2;

            return false;

        // if target file is same as source file and source file is not writable
        } elseif ($this->target_path == $this->source_path && !is_writable($this->source_path)) {

            // save the error level and stop the execution of the script
            $this->error = 3;

            return false;

        // try to get source file width, height and type
        // and if it founds an unsupported file type
        } elseif (!list($this->source_width, $this->source_height, $this->source_type) = @getimagesize($this->source_path)) {

            // save the error level and stop the execution of the script
            $this->error = 4;

            return false;

        // if no errors so far
        } else {

            // create an image from file using extension dependant function
            // checks for file extension
            switch ($this->source_type) {

                // if GIF
                case IMAGETYPE_GIF:

                    // create an image from file
                    $identifier = @imagecreatefromgif($this->source_path);

                    // get the index of the transparent color (if any)
                    if (($this->source_transparent_color_index = imagecolortransparent($identifier)) >= 0) {

                        // get the transparent color's RGB values
                        // we have to mute errors because there are GIF images which *are* transparent and everything
                        // works as expected, but imagecolortransparent() returns a color that is outside the range of
                        // colors in the image's pallette...
                        $this->source_transparent_color = @imagecolorsforindex($identifier, $this->source_transparent_color_index);

                    }

                    break;

                // if JPEG
                case IMAGETYPE_JPEG:

                    // create an image from file
                    $identifier = @imagecreatefromjpeg($this->source_path);

                    break;

                // if PNG
                case IMAGETYPE_PNG:

                    // create an image from file
                    $identifier = @imagecreatefrompng($this->source_path);

                    // disable blending
                    imagealphablending($identifier, false);

                    break;

                default:

                    // if unsupported file type
                    // note that we call this if the file is not GIF, JPG or PNG even though the getimagesize function
                    // handles more image types
                    $this->error = 4;

                    return false;

            }

        }
        
        // if target file has to have the same timestamp as the source image
        if ($this->preserve_time) {

            // save it as a global property of the class
            $this->source_image_time = filemtime($this->source_path);

        }

        // make available the source image's identifier
        $this->source_identifier = $identifier;

        return true;

    }

    /**
     *  Converts a hexadecimal representation of a color (i.e. #123456 or #AAA) to a RGB representation.
     *
     *  The RGB values will be a value between 0 and 255 each.
     *
     *  @param  string  $color              Hexadecimal representation of a color (i.e. #123456 or #AAA).
     *
     *  @param  string  $default_on_error   Hexadecimal representation of a color to be used in case $color is not
     *                                      recognized as a hexadecimal color.
     *
     *  @return array                       Returns an associative array with the values of (R)ed, (G)reen and (B)lue
     *
     *  @access private
     */
    function _hex2rgb($color, $default_on_error = '#FFF')
    {

        // if color is not formatted correctly
        if (preg_match('/^#?([a-f]|[0-9]){3}(([a-f]|[0-9]){3})?$/i', $color) == 0) {

            // use the default color
            $color = $default_on_error;

        }

        // trim off the "#" prefix from $background_color
        $color = ltrim($color, '#');

        // if color is given using the shorthand (i.e. "FFF" instead of "FFFFFF")
        if (strlen($color) == 3) {

            $tmp = '';

            // take each value
            for ($i = 0; $i < 3; $i++) {

                // and duplicate it
                $tmp .= str_repeat($color[$i], 2);

            }

            // the color in it's full, 6 characters length notation
            $color = $tmp;

        }

        // decimal representation of the color
        $int = hexdec($color);

        // extract and return the RGB values
        return array(

            'r' =>  0xFF & ($int >> 0x10),
            'g' =>  0xFF & ($int >> 0x8),
            'b' =>  0xFF & $int

        );

    }

    /**
     *  Flips horizontally or vertically or both ways the image given as {@link source_path}.
     *
     *  @since 2.1
     *
     *  @access private
     *
     *  @return boolean     Returns TRUE on success or FALSE on error.
     *
     *                      If FALSE is returned, check the {@link error} property to see the error code.
     */
    function _flip($orientation)
    {

        // if image resource was successfully created
        if ($this->_create_from_source()) {

            // prepare the target image
            $target_identifier = $this->_prepare_image($this->source_width, $this->source_height, -1);

            // flip according to $orientation
            switch ($orientation) {

                case 'horizontal':

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        0,
                        0,
                        ($this->source_width - 1),
                        0,
                        $this->source_width,
                        $this->source_height,
                        -$this->source_width,
                        $this->source_height

                    );

                    break;

                case 'vertical':

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        0,
                        0,
                        0,
                        ($this->source_height - 1),
                        $this->source_width,
                        $this->source_height,
                        $this->source_width,
                        -$this->source_height

                    );

                    break;

                case 'both':

                    imagecopyresampled(

                        $target_identifier,
                        $this->source_identifier,
                        0,
                        0,
                        ($this->source_width - 1),
                        ($this->source_height - 1),
                        $this->source_width,
                        $this->source_height,
                        -$this->source_width,
                        -$this->source_height

                    );

                    break;

            }

            // write image
            return $this->_write_image($target_identifier);

        }

        // if script gets this far, return false
        // note that we do not set the error level as it has been already set
        // by the _create_from_source() method earlier
        return false;

    }

    /**
     *  Creates a blank image of given width and height.
     *
     *  @param  integer     $width              Width of the new image.
     *
     *  @param  integer     $height             Height of the new image.
     *
     *  @param  string      $background_color   (Optional) The hexadecimal color of the background.
     *
     *                                          Can also be -1 case in which the script will try to create a transparent
     *                                          image if possible.
     *
     *                                          Default is "#FFF".
     *
     *  @return                                 Returns the identifier of the newly created image.
     *
     *  @access private
     */
    function _prepare_image($width, $height, $background_color = '#FFF')
    {

        // create a blank image
        $identifier = imagecreatetruecolor((int)$width <= 0 ? 1 : (int)$width, (int)$height <= 0 ? 1 : (int)$height);

        // if we are creating a PNG image
        if ($this->source_type == IMAGETYPE_PNG && $background_color == -1) {

            // disable blending
            imagealphablending($identifier, false);

            // allocate a transparent color
            $transparent_color = imagecolorallocatealpha($identifier, 0, 0, 0, 127);

            // fill the image with the transparent color
			imagefill($identifier, 0, 0, $transparent_color);

            //save full alpha channel information
			imagesavealpha($identifier, true);

        // if source image is a transparent GIF
        } elseif ($this->source_type == IMAGETYPE_GIF && $background_color == -1 && $this->source_transparent_color_index >= 0) {

            // allocate the source image's transparent color also to the new image resource
            $transparent_color = imagecolorallocate(
                $identifier,
                $this->source_transparent_color['red'],
                $this->source_transparent_color['green'],
                $this->source_transparent_color['blue']
            );

            // fill the background of the new image with transparent color
            imagefill($identifier, 0, 0, $transparent_color);

            // from now on, every pixel having the same RGB as the transparent color will be transparent
            imagecolortransparent($identifier, $transparent_color);

        // for other image types
        } else {

            // convert hex color to rgb
            $background_color = $this->_hex2rgb($background_color);

            // prepare the background color
            $background_color = imagecolorallocate($identifier, $background_color['r'], $background_color['g'], $background_color['b']);

            // fill the image with the background color
            imagefill($identifier, 0, 0, $background_color);

        }

        // return the image's identifier
        return $identifier;

    }

    /**
     *  Creates a new image from given image identifier having the extension as specified by {@link target_path}.
     *
     *  @param  $identifier identifier  An image identifier
     *
     *  @return boolean                 Returns TRUE on success or FALSE on error.
     *
     *                                  If FALSE is returned, check the {@link error} property to see the error code.
     *
     *  @access private
     */
    function _write_image($identifier)
    {

        // get target file extension
        $ext = strtolower(substr($this->target_path, strrpos($this->target_path, '.') + 1));

        // image saving process goes according to required extension
        switch ($ext) {

            // if GIF
            case 'gif':

                // if GD support for this file type is not available
                // in version 1.6 of GD the support for GIF files was dropped see
                // http://php.net/manual/en/function.imagegif.php#function.imagegif.notes
                if (!function_exists('imagegif')) {

                    // save the error level and stop the execution of the script
                    $this->error = 6;

                    return false;

                // if, for some reason, file could not be created
                } elseif (@!imagegif($identifier, $this->target_path)) {

                    // save the error level and stop the execution of the script
                    $this->error = 3;

                    return false;

                }

                break;

            // if JPEG
            case 'jpg':
            case 'jpeg':

                // if GD support for this file type is not available
                if (!function_exists('imagejpeg')) {

                    // save the error level and stop the execution of the script
                    $this->error = 6;

                    return false;

                // if, for some reason, file could not be created
                } elseif (@!imagejpeg($identifier, $this->target_path, $this->jpeg_quality)) {

                    // save the error level and stop the execution of the script
                    $this->error = 3;

                    return false;

                }

                break;

            // if PNG
            case 'png':

                // save full alpha channel information
                imagesavealpha($identifier, true);

                // if GD support for this file type is not available
                if (!function_exists('imagepng')) {

                    // save the error level and stop the execution of the script
                    $this->error = 6;

                    return false;

                // if, for some reason, file could not be created
                } elseif (@!imagepng($identifier, $this->target_path)) {

                    // save the error level and stop the execution of the script
                    $this->error = 3;

                    return false;

                }

                break;

            // if not a supported file extension
            default:

                // save the error level and stop the execution of the script
                $this->error = 5;

                return false;

        }

        // if file was created successfully
        // chmod the file
        chmod($this->target_path, intval($this->chmod_value, 8));

        // if target file has to have the same timestamp as the source image
        if ($this->preserve_time && isset($this->source_image_time)) {

            // touch the newly created file
            @touch($this->target_path, $this->source_image_time);

        }

        // return true
        return true;

    }

}
