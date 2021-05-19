<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2021-05-19 13:10:18
 * @LastEditors: sueRimn
 * @LastEditTime: 2021-05-19 14:46:46
 */

namespace Encore\FileManager\Http\Controllers;

use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;
use Encore\Admin\Facades\Admin;
use Hogwarts\Dao\FileGroupModel;
use Hogwarts\Dao\FileModel;
use Illuminate\Http\Request;

class FileManagerController extends Controller
{
    public function index(Request $request)
    {
        
        return Admin::content(function (Content $content) use ($request) {
            $content->header('文件管理');
            $path = $request->get('path', '/');
            $view = $request->get('view', 'list');
            $parent_id =  $this->find_dir(trim($path));
            // 文件夹
            $dir = $file = [];
            $dirList = FileGroupModel::where(['parent_id' => $parent_id,'is_show' => 1])->get(['id','name','created_at'])->toArray();
            if($dirList){
                foreach($dirList as $key => $value){
                    $url = route('file-manager', ['path' => $value['name'],'view' => $view]);
                    $dir[$key] = [
                        "download" => "",
                        "name" => $value['name'],
                        "preview" => '<a href="'.$url.'"><span class="file-icon text-aqua"><i class="fa fa-folder"></i></span></a>',
                        "isDir" => true,
                        "size" => "- KB",
                        "link" => $url,
                        "url" => '',
                        "time" => $value['created_at'],
                    ];
                }
            }

            // 文件
            $fileList = FileModel::where(['group_id' => $parent_id,'is_show' => 1])->get(['id','alias_name','size','path','created_at'])->toArray();
            if($fileList){
                foreach($fileList as $key => $value){
                    $file[$key] = [
                        "download" => $value['path'].'?dl=1',
                        "icon" => $value['path'],
                        "name" => $value['alias_name'],
                        "preview" => '<span class="file-icon has-img"><img src="'.$value['path'].'" alt="Attachment"></span>',
                        "isDir" => false,
                        "size" => $this->getFilesize($value['size']),
                        "link" => $value['path'],
                        "url" => $value['path'],
                        "time" => $value['created_at'],
                    ];
                }
            }
        
            // 当前文件夹下，所有文件信息（文件和文件夹列表）
            $list = array_merge($dir,$file);
            
            $content->body(view("file-manager::$view", [
                'list'   => $list,
                'nav'    => [],
                'url'    => [
                    'path'       => $path,
                    'index'      => route('file-manager'),
                    'move'       => route('file-move'),
                    'delete'     => route('file-delete'),
                    'upload'     => route('file-upload'),
                    'new-folder' => route('file-new-folder'),
                ],
            ]));
        });
    }

    /**
     * 通过文件夹名称，获取文件夹ID
     */
    protected function find_dir($path)
    {
        $pathInfo = FileGroupModel::where(['name' => trim($path)])->get(['id','name'])->toArray();
        return $pathInfo ? $pathInfo['0']['id'] : 0;
    }

    /**
     * 格式化文件大小
     */
    protected function getFilesize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * 遍历文件目录
     */
    protected function display_tree($dirList)
    {
        $refer = array();
        $tree = array();
        foreach($dirList as $k => $v){
            $refer[$v['id']] = & $dirList[$k];  //创建主键的数组引用
        }
        foreach($dirList as $k => $v){
            $pid = $v['parent_id'];   //获取当前分类的父级id
            if($pid == 0){
                $tree[] = & $dirList[$k];	//顶级栏目
            }else{
                if(isset($refer[$pid])){
                    $refer[$pid]['subcat'][] = & $dirList[$k];	//如果存在父级栏目，则添加进父级栏目的子栏目数组中
                }
            }
        }
        return $tree;
    }

    
}