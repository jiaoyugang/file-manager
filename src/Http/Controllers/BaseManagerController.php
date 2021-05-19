<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2021-05-19 16:45:37
 * @LastEditors: sueRimn
 * @LastEditTime: 2021-05-19 20:00:05
 */
namespace Encore\FileManager\Http\Controllers;

use Hogwarts\Dao\FileGroupModel;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class BaseManagerController extends Controller
{

    // 当前页面路径
    protected $path = '/';
    
    // 当前文件默认视图
    protected $view = 'list';

    protected $link = [];

    /**
     * 初始化参数
     */
    public function __construct(Request $request)
    {
        $this->path = $request->get('path', '/');
        $this->view = $request->get('view', 'list');
    }

    /**
     * 文件服务器接口路由列表
     */
    public function urls()
    {
        return [
            'path'       => $this->path,
            'index'      => route('file-manager'),
            'move'       => route('file-move'),
            'delete'     => route('file-delete'),
            'upload'     => route('file-upload'),
            'new-folder' => route('file-new-folder'),
        ];
    }

    /**
     * 导航栏
     */
    public function navigation($path)
    {
        $pathInfo = $this->get_path_info($path);
        $folders = $this->breadcrumbs($pathInfo['parent_id'],$pathInfo);
        $navigation = [];
        $path = '';
        foreach ($folders as $folder) {
            $path = rtrim($path, '/').'/'.$folder;
            $navigation[] = [
                'name'  => $folder,
                'url'   => urldecode(route('file-manager', ['path' => $folder ,'view' => $this->view])),
            ];
        }
        // dd($navigation);
        return $navigation;
    }


    /**
     * 网站面包屑
     */
    public function breadcrumbs($parent_id,$pathInfo)
    {
        if($parent_id){
            if($pathInfo){
                $this->links[$pathInfo['id']] = $pathInfo['name'];
                $res = $this->get_parent_dir($parent_id);
                $this->breadcrumbs($res['parent_id'],$res);
            }
            
        }else{
            $this->links[$pathInfo['id']] = $pathInfo['name'];
        }
        return array_reverse($this->links);
    }
    

    /**
     * 获取父文件夹
     */
    public function get_parent_dir($group_id)
    {
        $pathInfo = FileGroupModel::where(['id' => $group_id])->get(['id','name','level','parent_id'])->toArray();
        return !empty($pathInfo) ? $pathInfo["0"] : [];
    }

    /**
     * 通过文件夹名称，获取文件夹ID
     */
    protected function get_path_info($path)
    {
        $pathInfo = FileGroupModel::where(['name' => $path])->get(['id','name','level','parent_id'])->toArray();
        return !empty($pathInfo) ? $pathInfo["0"] : [];
    }
    
    /**
     * 通过文件夹名称，获取文件夹ID
     */
    protected function get_group_id($path)
    {
        $pathInfo = FileGroupModel::where(['name' => trim($path)])->get(['id','name','level','parent_id'])->toArray();
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