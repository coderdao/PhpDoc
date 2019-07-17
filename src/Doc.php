<?php
/**
 * Function: 文档生成
 * Abo 2019/7/17 16:19
 * Email: abo2013@foxmail.com
 */

namespace Abo\Phpdoc;

use Abo\Phpdoc\Logic\PaseComentLogic;

class Doc
{
    public $url = 'http://localhost';

    private $mainRegex = '/(\/\*\*.*?\*\s(api)?.*?\*\/\s*(public|private|protected)?\s*function\s+.*?\s*?\()/s';
    protected $documentPath;
    protected $savePath;
    protected $name = 'api';
    protected $controllerChange = true;
    protected $controllerTimes = 1;
    protected $methodChange = true;
    protected $methodTimes = 2;

    public static function test(){
        echo 'hello';
    }
    public function __construct( $documentPath, $savePath=null )
    {
        $this->documentPath = $documentPath;
        if( $savePath == null ){
            $this->savePath = getcwd() . DIRECTORY_SEPARATOR;
        }else{
            $this->savePath = $savePath;
        }
    }

    /**
     * 设置项目名称
     * @param string $name 项目名称
     * @return void
     */
    public function setName( $name )
    {
        $this->name = $name;
    }

    /**
     * 设置是否开启驼峰转下划线
     * @param bool $controller 文件名 true/false
     * @param bool $method 方法名 true/false
     * @return void
     */
    public function setChange( $controller=true, $method=true )
    {
        $this->controllerChange = $controller;
        $this->methodChange = $method;
    }

    /**
     * 驼峰转下划线转换条件 (出现几次大写字母才转换)
     * @param integer $controller 文件名
     * @param integer $method 方法名
     * @return void
     */
    public function setTimes( $controller=1, $method=2 )
    {
        $this->controllerTimes = $controller;
        $this->methodTimes = $method;
    }

    /**
     * 大驼峰命名法转下划线命名法
     * @param string $str 字符串
     * @param integer $times 出现几次大写字母才转换,默认1次
     * @return string
     */
    private function humpToLine( $str, $times=1 )
    {
        if(preg_match_all('/[A-Z]/',$str) >= $times){
            $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
                return '_'.strtolower($matches[0]);
            },$str);
            if($str[0]=='_'){
                $str = substr_replace($str,'',0,1);
            }
            return $str;
        }
        return $str;
    }

    /**
     * 递归法获取文件夹下文件
     * @param string $path 路径
     * @param array $fileList 结果保存的变量
     * @param bool $all 可选,true全部,false当前路径下,默认true.
     */
    private function getFileList( $path, &$fileList = array(), $all = true )
    {
        if ( !is_dir( $path ) ) {
            $fileList = array();
            return;
        }
        $data = scandir( $path );
        foreach ( $data as $one ) {
            if ( $one == '.' || $one == '..' ) { continue; }
            $onePath = $path . DIRECTORY_SEPARATOR . $one;
            $isDir = is_dir($onePath);
            $extName = substr($one, -4, 4);
            if ($isDir == false and $extName == '.php') {
                $fileList[] = $onePath;
            } elseif ($isDir == true and $all == true) {
                $this->getFileList($onePath, $fileList, $all);
            }
        }
    }

    /**
     * 获取代码文件中所有可以生成api的注释
     * @param string $data 代码文件内容
     * @return array
     */
    private function catchEvery( $data )
    {
        if ( preg_match_all( $this->mainRegex, $data, $matches ) ) {
            return $matches[1];
        }

        return array();
    }

    /**
     * 解析每一条可以生成API文档的注释成数组
     * @param string $data 注释文本 catchEvery返回的每个元素
     * @param string $fileName 文件名
     * @return array|bool
     */
    private function parse( $data, $fileName )
    {
        $fileName = basename($fileName,'.php');

        return ( new PaseComentLogic( $data, $fileName ) )->parse();
    }

    /**
     * 每个文档生成表格
     * @param array $data 每个API的信息 由parse返回的
     * @return string html代码
     */
    private function makeTable( $data )
    {
        $return = '<div id="'.base64_encode($data['requestUrl']).'" class="api-main">
        <div class="title">'.$data['methodName'].'</div>
        <div class="body">
            <table class="layui-table">
                <thead>
                    <tr>
                        <th> '.$data['requestName'].' </th>
                        <th rowspan="3"> '.$data['requestUrl'].' </th>
                    </tr>
                </thead>
            </table>
        </div>';

        // 请求参数
        if( $data['param'] ){
            $return .= '                    <div class="body">
            <table class="layui-table">
                <thead>
                    <tr> <th> 参数名称 </th> <th> 参数类型 </th> <th> 参数说明 </th> </tr>
                </thead>
                <tbody>';
            foreach($data['param'] as $param){
                $return .= '<tr> <td> '.$param['var'].' </td> <td> '.$param['type'].' </td> <td> '.$param['about'].' </td>
            </tr>';
            }
            $return .= '</tbody>
            </table>
        </div>';
        }

        // 返回
        if( $data['return'] ){
            $return .= '<div class="body">
            <table class="layui-table">
                <thead>
                    <tr> <th> 返回名称 </th> <th> 返回类型 </th> <th> 返回说明 </th> </tr>
                </thead>
                <tbody>';
            foreach($data['return'] as $param){
                $return .= '<tr>
                <td> '.$param['var'].' </td> <td> '.$param['type'].' </td> <td> '.$param['about'].' </td>
            </tr>';
            }
            $return .= '</tbody>
            </table>
        </div>';
        }

        $return .= ' <hr>
        </div>';

        return $return;
    }

    /**
     * 生成侧边栏
     * @param array $rightList 侧边列表数组
     * @return string html代码
     */
    private function makeRight( $rightList )
    {
        $return = '';
        foreach( $rightList as $d => $file ) {
            $return .= '<blockquote class="layui-elem-quote layui-quote-nm right-item-title">'.$d.'</blockquote>
            <ul class="right-item">';
            foreach( $file as $one ) {
                $return .= '<li><a href="#'.base64_encode($one['requestUrl']).'"><cite>'.$one['methodName'].'</cite><em>'.$one['requestUrl'].'</em></a></li>';
            }
            $return .= '</ul>';
        }

        return $return;
    }

    /**
     * 开始执行生成
     * @param bool $fetch 是否直接实时输出，默认true，否则生成文件。
     * @return bool|mixed|string
     */
    public function make( $fetch = true )
    {
        $fileList = array();
        $this->getFileList( $this->documentPath, $fileList );
        $inputData = ''; // 主体部分表格
        $rightList = array(); // 侧边栏列表
        foreach( $fileList as $fileName ){
            $fileData = file_get_contents( $fileName );
            $data = $this->catchEvery( $fileData );

            foreach ( $data as $one ) {
                $infoData = $this->parse( $one,$fileName );
                if( $infoData != false ){
                    $rightList[ basename( $fileName ) ][] = array(
                        'methodName' => $infoData[ 'methodName' ],
                        'requestUrl' => $infoData[ 'requestUrl' ],
                    );
                    $inputData .= $this->makeTable( $infoData );
                }
            }
        }

        $tempData = file_get_contents( dirname(__FILE__) . '/Console/stubs/document.stub' );
        $tempData = str_replace( '{name}', $this->name, $tempData );
        $tempData = str_replace( '{main}', $inputData, $tempData );
        $tempData = str_replace( '{right}', $this->makeRight( $rightList ), $tempData );
        $tempData = str_replace( '{date}', date( 'Y-m-d H:i:s' ), $tempData );

        if( !$fetch ){
            return file_put_contents( $this->savePath . $this->name . '.html', $tempData );
        }else{
            return $tempData;
        }
    }

}
