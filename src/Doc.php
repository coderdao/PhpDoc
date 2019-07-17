<?php
/**
 * Function: 文档生成
 * Abo 2019/7/17 16:19
 * Email: abo2013@foxmail.com
 */

namespace Abo\Phpdoc;

use Abo\Phpdoc\Logic\ConstructRightMenuLogic;
use Abo\Phpdoc\Logic\ConstructTableLogic;
use Abo\Phpdoc\Logic\PaseComentLogic;

class Doc
{
    const MAIN_REGEX = '/(\/\*\*.*?\*\s(api)?.*?\*\/\s*(public|private|protected)?\s*function\s+.*?\s*?\()/s';

    public $url = 'http://localhost';
    protected $documentPath;
    protected $savePath;
    protected $name = 'api';

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
     * 开始执行生成
     * @param bool $fetch 是否直接实时输出，默认true，否则生成文件。
     * @return bool|mixed|string
     */
    public function make( $fetch = true )
    {
        $inputData = ''; // 主体部分表格
        $rightList = []; // 侧边栏列表
        $fileList = [];

        $MenuLogic = new ConstructRightMenuLogic();
        $this->getFileList( $this->documentPath, $fileList );


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
        $tempData = str_replace( '{right}', $MenuLogic->construct( $rightList ), $tempData );
        $tempData = str_replace( '{date}', date( 'Y-m-d H:i:s' ), $tempData );

        if( !$fetch ){
            return file_put_contents( $this->savePath . $this->name . '.html', $tempData );
        }else{
            return $tempData;
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
        if ( preg_match_all( self::MAIN_REGEX, $data, $matches ) ) {
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
        $return = ( new ConstructTableLogic( $data ) )->construct();

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
}
