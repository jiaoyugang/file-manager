<?php
/*
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2021-05-19 13:10:18
 * @LastEditors: sueRimn
 * @LastEditTime: 2021-05-19 20:00:19
 */

namespace Encore\FileManager\Http\Controllers;

use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Hogwarts\Dao\FileGroupModel;
use Hogwarts\Dao\FileModel;
use Illuminate\Http\Request;

class FileManagerController extends BaseManagerController
{
    // 上传文件夹名称
    protected $dirname = "saamcms";
    
    // 上传文件名
    protected $filename = "files";

    /**
    * 文件列表
    */
    public function index(Request $request)
    {
        return Admin::content(function (Content $content) use ($request) {
            $content->header('文件管理');
            $path = $request->get('path', '/');
            
            // dd($this->navigation($this->breadcrumbs($pathInfo['parent_id'],$pathInfo)));
            $view = $request->get('view', 'list');
            $parent_id =  $this->get_group_id(trim($path));
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
            // dd($this->navigation($path));
            $content->body(view("file-manager::$view", [
                'list'   => $list,
                'nav'    => ($path == '/') ? [] : $this->navigation($path),
                'url'    => $this->urls(),
            ]));
        });
    }

    /**
     * 上传文件（支持单图或多图上传）
     */
    public function upload(Request $request)
    {
        $files = $request->file('files');
        $path = $request->post('dir');
        
        $parent_id =  $this->get_group_id(trim($path)); //当前文件夹所在id
        try {
            $fdfs = new \Hogwarts\Service\FastdfsService(config('api.oos'));
            foreach($files as $obj){
                $res = $fdfs->upload($obj,$this->dirname,$this->filename);
                $res = json_decode($res,true);
                if (isset($res['code']) && $res['code'] == 200) {
                    $data = $res['data'];
                    FileModel::create([
                        'file_id'=>$data['id'],
                        'group_id' => $parent_id,
                        'name' => $data['fileName'],
                        'alias_name' => $data['fileName'],
                        'path' => $data['filePath'],
                        'size' => $data['fileSize'],
                        'extension' => $data['fileFormat'],
                        'is_show' => 1,
                        'created_at' => $data['createTime'] ?? date('Y-m-d H:i:s',time()),
                    ]);
                }
            }
            admin_toastr(trans('操作成功'));
        } catch (\Exception $e) {
            admin_toastr($e->getMessage(), 'error');
        }

        return back();
    }

    /**
     * 新建文件夹
     */
    public function newFolder(Request $request)
    {
        $dir = $request->get('dir'); //当前所在文件夹位置
        $name = $request->get('name'); //文件夹名
        $parent_id = $this->get_group_id($dir);
        try {
            $res = FileGroupModel::firstOrCreate(['name' => $name,'parent_id' => $parent_id]);
            if($res){
                return response()->json([
                    'status'  => true,
                    'message' => trans('操作成功'),
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'status'  => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    

    
}