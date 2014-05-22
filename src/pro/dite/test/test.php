<?php

namespace Faker\Provider;

/**
 * @author lsv
 */
class Color extends Base
{

    /**
     * @example '#fa3cc2'
     */
    public static function hexColor()
    {
        return '#' . str_pad(dechex(mt_rand(1, 16777215)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * @example '#ff0044'
     */
    public static function safeHexColor()
    {
        $color = str_pad(dechex(mt_rand(0, 255)), 3, '0', STR_PAD_LEFT);

        return '#' . $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
    }



    /**
     * @example '0,255,122'
     */
    public static function rgbColor()
    {
        return implode(',', static::rgbColorAsArray());
    }
}
