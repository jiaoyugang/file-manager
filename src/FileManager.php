<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2021-05-19 13:10:18
 * @LastEditors: sueRimn
 * @LastEditTime: 2021-05-19 14:29:29
 */

namespace Encore\FileManager;

use Encore\Admin\Extension;

class FileManager extends Extension
{
    public $name = 'file-manager';

    public $views = __DIR__.'/../resources/views';

    public $assets = __DIR__.'/../resources/assets';

    public $menu = [
        'title' => 'Filemanager',
        'path'  => 'file-manager',
        'icon'  => 'fa-gears',
    ];
}